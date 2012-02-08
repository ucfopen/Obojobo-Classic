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

add_filter('rewrite_rules_array', 'rocketD_modify_rewrite_rules');
add_filter('query_vars', 'rocketD_add_query_vars');
add_action('wp_loaded', 'rocketD_flush_rewrite_rules');

// changes to these require you to update permalinks in wp admin
function rocketD_modify_rewrite_rules($rules)
{
	$newrules = array();
	// add rule for viewing instances
	//$newrules['view/(\d+?)/?$'] = 'index.php?pagename=view&instID=$matches[1]';
	$newrules['view/(\d+?)/?$'] = 'index.php?pagename=view&instID=$matches[1]';
	// add rule for previewing los
	// $newrules['preview/(\d+?)/?$'] = 'index.php?pagename=view&loID=$matches[1]';
	$newrules['preview/(\d+?)/?$'] = 'index.php?pagename=view&loID=$matches[1]';
	// add rule for previewing previous draft los
	$newrules['preview/(\d+)/history/(\d+?)/?$'] = 'index.php?pagename=view&loID=$matches[2]';
	$rules =  $newrules + $rules;
	
	return $rules;
}

function rocketD_flush_rewrite_rules(){
	$rules = get_option('rewrite_rules');
	if(!isset( $rules['view/(\d+?)/?$'] ) ) 
	{
		global $wp_rewrite;
		$wp_rewrite->flush_rules();
	}
}

function rocketD_add_query_vars($vars)
{
	array_push($vars, 'instID');
	array_push($vars, 'loID');
	return $vars;
}


// LISTEN TO THE AUTHENTICATE FILTER - USE Obojobo to see if the user's crudentials are right
// If they are - give them the proper role, create a wordpress user, and log em in to both systems


add_filter('authenticate', 'rocketD_auth_check_password', 1, 3);
function rocketD_auth_check_password($user, $username, $password)
{
	require_once(dirname(__FILE__)."/../../../../internal/app.php");
	$API = \obo\API::getInstance();
	
	// no crudentials sent - likely to be just checking to see if the user is logged in
	if(empty($username) && empty($password))
	{
		// If already logged in - then just jump strait to linking to their wordpress user
		$alreadyLoggedIn = $API->getSessionValid();
		if($alreadyLoggedIn === true)
		{
			$user = $API->getUser();
			// look for an existing user
			$sanitizedUsername = sanitize_user(esc_sql($user->login), true);
			$wp_user_id = username_exists($sanitizedUsername);
		
			// create one if it doesnt exist
			if(!$wp_user_id)
			{
				$random_password = wp_generate_password(100,false);
				$wp_user_id = wp_create_user($user->login, $random_password, $user->email);
			}
			$wpUser = new WP_User($wp_user_id);
			return $wpUser;
		}
	}
	// crudentials sent - lets look to see if the authenticate in the app
	else
	{
		$result = $API->doLogin($username, $password);
		if($result === true)
		{
			$user = $API->getUser();
		
			// look for an existing user
			$sanitizedUsername = sanitize_user(esc_sql($user->login ), true);
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
			else if(in_array('SuperStats', $groups))
			{
				$wpUser->set_role('super_stats');
			}
			else
			{
				$wpUser->set_role('');
			}
		
			return $wpUser;
		}
	}

	remove_action('authenticate', 'wp_authenticate_username_password', 20); // prevent any other authentication from working
	return new WP_Error('invalid_username', __('<strong>Obojobo Login Failure</strong> Your NID and NID password did not authenticate.'));
	
}

// Log the user out of the RocketD application
add_filter('wp_logout', 'rocketD_auth_logout');
function rocketD_auth_logout()
{
	require_once(dirname(__FILE__)."/../../../../internal/app.php");
	$API = \obo\API::getInstance();
	$API->doLogout();
}


function rocketD_admin_tool_get_form_page_input()
{
	return '<input type="hidden" name="page" value="'.$_REQUEST['page'].'">';
	
}


// create custom plugin settings menu
add_action('admin_menu', 'rocketD_plugin_menu');

function rocketD_plugin_menu() {
	//create custom post type menu
	add_menu_page('Obo Admin Tools', 'Obo Admin Tools', 'administrator', 'rocketD_admin_tools_menu', 'rocketD_tools_scripts');	
	
	require_once(dirname(__FILE__)."/../../../../internal/app.php");
	if( $handle = opendir(\AppCfg::DIR_BASE . \AppCfg::DIR_ADMIN) )
	{
		while( false !== ( $file = readdir($handle) ) )
		{
			if(substr($file, -4) == '.php')
			{
				$toolName = preg_replace('/_/', ' ', substr($file, 0, -4));
				$toolsPage = add_submenu_page('rocketD_admin_tools_menu', $toolName, $toolName, 'administrator', 'rocketD_admin_tool_'.$file, 'rocketD_admin_tool_run');
				add_action( "admin_print_scripts-$toolsPage", 'rocketD_admin_tool_head' );
			}
		}
		closedir($handle);
	}
}

function rocketD_admin_tool_run() {
	if(strpos($_REQUEST['page'], 'rocketD_admin_tool_') !== false )
	{
		$page = explode('rocketD_admin_tool_', $_REQUEST['page']);
		include(\AppCfg::DIR_BASE . \AppCfg::DIR_ADMIN . $page[1]);
	}
}

function rocketD_admin_tool_head()
{
	$plugindir = get_settings('siteurl').'/wp-content/plugins/'.dirname(plugin_basename(__FILE__));

	wp_enqueue_script('datepicker', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.6/jquery-ui.min.js', array('jquery'));  
	wp_enqueue_script( 'tablesorter', $plugindir.'/js/jquery.tablesorter.min.js' , array('jquery'));
	wp_enqueue_script( 'tablesorter-pager', $plugindir.'/js/jquery.tablesorter.pager.js' , array('jquery'));

	wp_enqueue_style( 'datepicker', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.10/themes/base/jquery-ui.css');
}


add_action( 'admin_init', 'establish_header' );

function establish_header()
{
	require_once(dirname(__FILE__)."/../../../../internal/app.php");
	$API = \obo\API::getInstance();
	$API->getSessionValid();
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



/*
https://obojobo.ucf.edu/view/3921

https://obojobo.ucf.edu/lo/evaluating-web-sites/2.342

https://obojobo.ucf.edu/inst/evaluating-web-sites/2.342

https://obojobo.ucf.edu/view/evaluating-web-sites/2.342

https://obojobo.ucf.edu/evaluating-web-sites/preview/3.23

https://obojobo.ucf.edu/evaluating-web-sites/11Spring/AML3930H-0001

https://obojobo.ucf.edu/11Spring/AML3930H-0001/evaluating-web-sites

https://obojobo.ucf.edu/view/evaluating-web-sites/3234/

https://obojobo.ucf.edu/view/3123/evaluationg-web-sites/

https://obojobo.ucf.edu/inst/3123/evaluationg-web-sites/

https://obojobo.ucf.edu/evaluationg-web-sites/AML3930H-0001-11Spring

https://obojobo.ucf.edu/view/evaluationg-web-sites/in/idv-essentials-11Summer(3)

https://obojobo.ucf.edu/evaluationg-web-sites/idv-essentials-11Summer

https://obojobo.ucf.edu/lo/3123/evaluationg-web-sites/

https://obojobo.ucf.edu/preview/3123/evaluationg-web-sites/

*/
?>
