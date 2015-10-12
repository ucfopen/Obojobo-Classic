<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
	$username = isset($_REQUEST['username']) ? $_REQUEST['username'] : '';
	$password = isset($_REQUEST['password']) ? $_REQUEST['password'] : '';
	$loggedIn = \rocketD\auth\AuthManager::getInstance()->login($username, $password);
}
else
{
	$loggedIn = $API->getSessionValid();
}

if ($loggedIn === true)
{
	header("Location: $_SERVER['REQUEST_URI']"); // Redirect
}

// render page with this notice
$notice = 'Invalid Login';
