<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
	$username = isset($_REQUEST['username']) ? $_REQUEST['username'] : '';
	$password = isset($_REQUEST['password']) ? $_REQUEST['password'] : '';
	$result   = \rocketD\auth\AuthManager::getInstance()->login($username, $password);
	$loggedIn = ($result === true && !($result instanceof \obo\util\Error));
	$notice = $loggedIn ? '' : 'Invalid Login';
}
else
{
	$loggedIn = \obo\API::getInstance()->getSessionValid();
}
