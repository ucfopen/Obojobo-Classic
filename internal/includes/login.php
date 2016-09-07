<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
	$username = isset($_REQUEST['username']) ? $_REQUEST['username'] : '';
	$password = isset($_REQUEST['password']) ? $_REQUEST['password'] : '';
}

if(empty($username) || empty($password))
{
	$loggedIn = \obo\API::getInstance()->getSessionValid();
}
else
{
	$loggedIn = \rocketD\auth\AuthManager::getInstance()->login($username, $password);
	$notice = 'Invalid Login';
}
