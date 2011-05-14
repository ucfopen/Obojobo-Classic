<?php
/**
 * @package RocketD
 * @version 1
 */
/*
Plugin Name: RocketD Integration Module
Description: Log in using authentication from a rocketD implimentation
Author: Ian Turgeon
Version: 1
*/


add_filter('authenticate', rocketD_auth_check_password, 1, 3);
function rocketD_auth_check_password($user, $username, $password)
{
	require_once(dirname(__FILE__)."/../../../../internal/app.php");
	$API = \obo\API::getInstance();
	$result = $API->doLogin($username, $password);
	if($result === true)
	{
		$user = $API->getUser();
		
		// look for an existing user
		$sanitizedUsername = sanitize_user(esc_sql($username), true);
		$wp_user_id = username_exists($sanitizedUsername);

		// create one if it doesnt exist
		if(!$wp_user_id)
		{
			$random_password = wp_generate_password(100,false);
			$wp_user_id = wp_create_user($user->login, $random_password, $user->email);
		}
		
		// update the user info
		wp_update_user(array('ID' => $wp_user_id, 'display_name' => $user->first . ' ' . $user->last));
		// add_user_meta($wp_user_id, 'first_name', $user->first, true);
		// add_user_meta($wp_user_id, 'last_name', $user->last, true);
		$wpUser = new WP_User($wp_user_id);

		$roles = $API->getUserRoles();
		
		$groups = array();
		foreach($roles as $role)
		{
			$groups[] = $role->name;
		}
		
		if(in_array('Administrator', $groups))
		{
			$wpUser->set_role('administrator');
		}
		else
		{
			$wpUser->set_role('');
		}
		
		return $wpUser;
	}
	else
	{
		remove_action('authenticate', 'wp_authenticate_username_password', 20); // prevent any other authentication from working
		return new WP_Error('invalid_username', __('<strong>Obojobo Login Failure</strong> Your NID and NID password did not authenticate.'));
	}
}


/*********************** DEBUGGING CODE ****************************/


function trace2($traceText)
{
	
	@$dt = debug_backtrace();
	// if traceText is an object, print_r it
	if(is_object($traceText) || is_array($traceText))
	{
		$traceText = print_r($traceText, true);
	}
	
	if(is_array($dt))
	{
		writeLog(basename($dt[0]['file']).'#'.$dt[0]['line'].': '.$traceText, false);
		return; // exit here if either of these methods wrote to the log
		
	}
	// couldnt get backtrace, just export what we have
	if(is_object($traceText) || is_array($traceText))
	{
		writeLog('printr: ' .print_r($traceText, true));
	}
	else
	{
		writeLog('trace: ' .$traceText);
	}
}

function writeLog($output, $fileName=false)
{	
	// create the log directory if it doesnt exist
	$fileName = dirname(__FILE__) . '/trace.txt';

	$fh = fopen($fileName, 'a');
	fwrite($fh, $output . "\n");
	fclose($fh);
	
}



?>
