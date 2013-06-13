<?php
/**
 * This class defines the Media data type
 * @author Luis Estrada <lestrada@mail.ucf.edu>
 */

/**
 * This class defines the Errors
 * It is used to 
 */
namespace rocketD\util;
class Error
{
	public $errorID;	    //Number:  Error id
	public $message;		//String:  Error message

	public function __construct($id = 0, $message = '', $data=-1)
	{
		$this->errorID = $id;
		$this->message = "ERROR: ".$this->getErrorString($id).($message == '' ? '' : ' : '.$message);
		
		if($data == -1)
		{
	        $this->data = array_slice(debug_backtrace(), 0, \AppCfg::DEBUG_BACKTRACE);
		}
		else
		{
			$this->data = $data;
		}
		
		$this->logError();
	}
	
	public static function getError($type, $message='', $data=-1)
	{
		$e = \AppCfg::ERROR_TYPE;
		return new $e($type, $message, $data);
	}
	
	protected function logError()
	{
		if(\AppCfg::DEBUG_LOG_ERRORS)
		{
			switch($this->errorID){
				case 1: // fall through
				case 1006: // fall through
				case 1004: // fall through
				case 1003: // fall through
					break;
				default:
					trace(print_r($this->data, true), true);
					break;
			}
		}
		unset($this->data);
	}
	
	protected function getErrorString($id = 0)
	{
	    if(!is_numeric($id))
		{
			$id = 0;
		}

		switch($id)
		{
			/* Common Errors */
			case 0: return "General Error.";
			case 1: return "Invalid Session, User not logged in.";
			case 2: return "Invalid input.";
			case 3: return "Session Timeout.";
			
			/* Client Errors */
			case 100: return "Client Side Trace.";
			case 101: return "Client Side Error.";

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
			
			/* PermissionsManager Errors*/
			case 5003: return "Cannot remove permissions for sole owner.";
			
			default: return "General Error.";
		}
	}
}
?>