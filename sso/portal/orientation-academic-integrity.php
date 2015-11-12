<?php
// This page deals with outbound pagelet links. It'll show a login screen and redirect people to the module page after they are logged in

require_once(dirname(__FILE__)."/../../internal/app.php");

// when the user hit's the login button
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
	$username = isset($_REQUEST['username']) ? $_REQUEST['username'] : '';
	$password = isset($_REQUEST['password']) ? $_REQUEST['password'] : '';
	$loggedIn = \rocketD\auth\AuthManager::getInstance()->login($username, $password, ['force_check_idp' => false]);
	$notice   = 'Invalid Login';
}
else
{
	if ($_REQUEST['ucf_id'])
	{
		$loggedIn = \rocketD\auth\AuthManager::getInstance()->login('','');
	}

	$loggedIn = \obo\API::getInstance()->getSessionValid();
}

if ($loggedIn)
{
	header("Location: /sso/portal/academic-integrity-modules.php", true, 307);
	exit();
}

// show the login screen
$title = "Academic Integrity Modules";
require(dirname(__FILE__)."/../../assets/templates/login.php");
