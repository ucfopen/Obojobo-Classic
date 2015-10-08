<?php
// TODO: see if we can move this stuff into the SAML auth mod

// ================ LOGIN OR CHECK EXISTING LOGIN ===========================
//  cmdweblogin = login submit form, SAMLResponse = we just came back from SAML
if (isset($_REQUEST['cmdweblogin']) || isset($_REQUEST['SAMLResponse']))
{
	if ( ! isset($_REQUEST['username'])) $_REQUEST['username'] = "";
	if ( ! isset($_REQUEST['password'])) $_REQUEST['password'] = "";
	$url = $_SERVER['REQUEST_URI'];

	// is this the saml Assertion Consumer Service?
	if ($url != "/saml/acs")
	{
		setcookie("redir", $url, 0, "/"); //store the redirect location in a cookie
	}
	else
	{
		$url = $_COOKIE['redir']; // get the redirect location from the cookie
		setcookie("redir", '', time() - 3600, "/"); // delete the cookie
	}

	// waterfall through the authentication modules
	$loggedIn = $API->doLogin($_REQUEST['username'],  $_REQUEST['password']);

	if ($loggedIn === true)
	{
		// Redirect
		header("Location: $url");
	}

	// render page with this notice
	$notice = 'Invalid Login';
}
else
{
	$loggedIn = $API->getSessionValid();
}
