<?php
require_once './Services/EventHandling/classes/class.ilEventHookPlugin.php';
require_once 'Services/User/classes/class.ilUserUtil.php';

/**
 * Class ilSingleCourseRedirectPlugin
 * 
 *
 *
 * @author  Stephan Winiker <webmaster@subclauses.net>
 * @version 1.0.0
 */
class ilSingleCourseRedirectPlugin extends ilEventHookPlugin {

	/**
	 * @var
	 */
	protected static $instance;


	/**
	 * @return ilSingleCourseRedirectPlugin
	 */
	public static function getInstance() {
		if (!isset(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	const PLUGIN_NAME = 'SingleCourseRedirect';


	/**
	 * Handle the event
	 *
	 * @param    string        component, e.g. "Services/User"
	 * @param    event         event, e.g. "afterUpdate"
	 * @param    array         array of event specific parameters
	 */
	public function handleEvent($a_component, $a_event, $a_parameter) {

	    if($a_component == 'Services/Authentication' && $a_event == 'afterLogin')
	    {
	    	global $DIC;
	    	$rbac = $DIC->rbac();
	    	$usr = $DIC->user();
	    	$roles = $rbac->review()->assignedRoles($usr->getId());
	    	
	    	if (in_array('2', $roles)) {
	    		if (ilUserUtil::hasPersonalStartingPoint()) {
	    			ilUserUtil::setPersonalStartingPoint(0);
	    			$usr->writePrefs();
	    		}
	    	} else if (in_array('4', $roles)) {
	    		foreach ($roles as $role) {
	    			if (!$rbac->review()->isGlobalRole($role)) {
	    				$role_title = substr(ilObject::_lookupTitle($role), 3, 9);
	    				if ($role_title == 'crs_admin' || $role_title == 'crs_tutor') {
	    					ilUserUtil::setPersonalStartingPoint(0);
	    					$usr->writePrefs();
	    					return;
	    				}
	    			}
	    		}
	    		
	    		do {
		    		$ref_id = $rbac->review()->getFoldersAssignedToRole(array_pop($roles), true);
	    			$node = $DIC->repositoryTree()->getNodeData($ref_id[0]);
	    		} while ($node['type'] != 'crs' && isset($roles[0]));
	    	
	    		if ($node['type'] == 'crs') {
	    			if (!ilUserUtil::hasPersonalStartPointPref() || ilUserUtil::getStartingPoint() != ilUserUtil::START_REPOSITORY_OBJ || ilUserUtil::getPersonalStartingObject() != $node['ref_id']) {
	    						ilUserUtil::setPersonalStartingPoint(ilUserUtil::START_REPOSITORY_OBJ, $node['ref_id']);
	    						$usr->writePrefs();
	    			}
	    		} else {
	    			
	    		}
	    	}
	    }
	}

	
	/**
	 * @return string
	 */
	public function getPluginName() {
		return self::PLUGIN_NAME;
	}

}

?>
