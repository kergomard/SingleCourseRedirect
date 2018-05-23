<?php
require_once './Services/EventHandling/classes/class.ilEventHookPlugin.php';

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
	    	$usr_id = $DIC->user()->getId();
	    	$roles = $rbac->review()->assignedRoles($usr_id);

	    	if (!in_array('2', $roles)) {
	    		do {
		    		$ref_id = $rbac->review()->getFoldersAssignedToRole(array_pop($roles), true);
	    			$node = $DIC->repositoryTree()->getNodeData($ref_id[0]);
	    		} while ($node['type'] != 'crs' && isset($roles[0]));
	    	
	    		if ($node['type'] == 'crs') {
		    		$DIC->ctrl()->redirectToURL(ilLink::_getLink($node['ref_id']));
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
