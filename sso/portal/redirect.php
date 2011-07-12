<?php
require_once(dirname(__FILE__)."/../../internal/app.php");
$API = \obo\API::getInstance();

if(isset($_REQUEST['instID']) )
{
	// Already logged in
	if($API->getSessionValid())
	{
		// already logged in, just send them in
		redirectToLO($_REQUEST['instID']);
	}

	// not already logged in, SSO not timed out
	if( isset($_SESSION['PORTAL_SSO_NID']) && isset($_SESSION['PORTAL_SSO_EPOCH']) && $_SESSION['PORTAL_SSO_EPOCH'] >= time() - \AppCfg::UCF_PORTAL_TIMEOUT)
	{
		if( $API->doLogin('', '') )
		{
			redirectToLO($_REQUEST['instID']);
		}
	}
}
$API->getSessionValid(); // needed to get session variables
trace('SSO REDIRECT ERROR', true);
trace('isntid:' . $_REQUEST['instID'] . ' nid:' . $_SESSION['PORTAL_SSO_NID'] . ' epoch:' . $_SESSION['PORTAL_SSO_EPOCH'] . ' timed out? ' . ($_SESSION['PORTAL_SSO_EPOCH'] >= time()- \AppCfg::UCF_PORTAL_TIMEOUT ? 'nope' : 'yes'), true);


function redirectToLO($instID)
{
	header('Location: '.\AppCfg::URL_WEB . \AppCfg::URL_VIEWER  . $instID . '?login=myUCF');
	exit();
}
?>
<html>
	<head>
		<title>Obojobo Login Error</title>
	<head>
	<body bgcolor="#F8F8F8">
		<p>An error occurred trying to log you in to Obojobo, <a href="javascript:history.back()">return to myUCF</a> and click on the link again.</p>
	</body>
</html>