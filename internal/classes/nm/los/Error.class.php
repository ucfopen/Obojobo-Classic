<?php
class nm_los_Error extends core_util_Error
{
	
	protected function logError()
	{
		
		if(isset($_SESSION['userID']))
		{
//			$trackingMan = nm_los_TrackingManager::getInstance();
			//$trackingMan->track($this);
		}
		parent::logError();
	}
	
	protected function getErrorString($id = 0)
	{
		// TODO: allow system events to register error types
		
		
	    if(!is_numeric($id))
	        $id = 0;

		switch($id)
		{
			/* Common Errors */
			case 0: return "General Error.";
			case 1: return "Invalid Session, User not logged in.";
			case 2: return "Invalid input.";
			case 3: return "Session Timeout.";
			case 4: return "Insufficent Permissions.";
			case 5: return "Invalid Visit Key";
			case 6: return "Rate Limiter Hit";
			
			/* Client Errors */
			case 100: return "Client Side Trace.";
			case 101: return "Client Side Error.";
			case 102: return "Viewer Client Error";

			/* Plugin Errors */
			case 200: return "Plugin disabled or missing";
			case 201: return "Plugin method not found";
			
			/* UserManager Errors */
			case 1000: return "General User Manager Error.";
			case 1001: return "Invalid input.";
			case 1002: // fall through
			case 1003: return "Username and/or password incorrect.";
			case 1004: return "Password expired and needs to be changed.";
			case 1005: return "Server not able to send password change email.";
			case 1006: return "ResetKey Expired";
			case 1007: return "Auto Login after password reset with key failed";
				
			/* AttemptsManager Errors */
			case 2000: return "General Attempts Manager Error.";
			case 2001: return "Invalid input.";
			case 2002: return "No visit id assigned.";
			case 2003: return "No practice attempts available.";
			case 2004: return "No assessment attempts available.";
			case 2005: return "Question group not found.";
			case 2006: return "Cannot register attempt ID, missing current instance";
			case 2007: return "Cannot submit for score importing if an attempt has already been started";
			case 2008: return "Score already imported";
			case 2009: return "Score importing not allowed";
			case 2010: return "Can not start assessment due to instance being closed.";
			/* LockManager Errors */
			case 3000: return "General Lock Manager Error.";
			case 3001: return "Invalid input.";
			case 3002: return "LO Locked";
				
			/* InstanceManager Errors */
			case 4000: return "General Instance Manager Error.";
			case 4001: return "Invalid Input";
			case 4002: return "Instance does not exist.";
			case 4003: return "Instance is not currently active.";
			case 4004: return "Not enough permissions.";
			case 4005: return "Not able to set Visit Key";
			
			/* PermissionsManager Errors*/
			case 5003: return "Cannot remove permissions for sole owner.";
			
			/* LOManager Errors*/
			case 6003: return "Can't delete LO, there is an existing instance.";
			case 6004: return "There are no drafts to create a master from.";
			case 6005: return "Master version already exists.";
			
			/* UCFCourses Plugin Errors */
			case 7000: return "Course does not exist.";
			case 7001: return "User does not exist.";
			case 7002: return "User is not an instructor of requested course.";
			case 7003: return "Could not connect to Webcourses.";
			case 7004: return "Could not fetch columns.";
			case 7005: return "Grade book column already exists.";
			case 7006: return "Grade book column does not exist.";
			case 7007: return "Unable to create grade book column.";
			case 7008: return "Specified score is an incorrect format.";
			case 7009: return "Unable to get student list.";
			case 7010: return "Unable to get member description.";
			case 7011: return "User is not a student of the specified course.";
			case 7012: return "Course could not be found.";
			case 7013: return "General course plugin error.";
			
			    
			default: return "General Error.";
		}
	}
}
?>
