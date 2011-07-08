<?php
if(isset($_REQUEST['instID']))
{
	$targetURL = "http://obo/view/".$_REQUEST['instID'];
	// $targetURL = "https://obojobo.ucf.edu/view/".$_REQUEST['instID'];
	require_once(dirname(__FILE__)."/../../internal/app.php");
	
	// check to see if they are already logged in
	$API = \obo\API::getInstance();
	if($API->getSessionValid())
	{
		// already logged in, just send them in
		header('Location: '.$targetURL);
		exit();	
	}

	// not already logged in, SSO not timed out
	if( isset($_SESSION['PORTAL_SSO_NID']) && isset($_SESSION['PORTAL_SSO_EPOCH']) && $_SESSION['PORTAL_SSO_EPOCH'] >= time() - 1800)
	{
		if( $API->doLogin('', '') )
		{
			header('Location: '.$targetURL);
			exit();
		}
	}
}
trace('SSO REDIRECT ERROR', true);
trace('isntid:' . $_REQUEST['instID'] . ' nid:' . $_SESSION['PORTAL_SSO_NID'] . ' epoch:' . $_SESSION['PORTAL_SSO_EPOCH'] . ' time:' . time(), true);
?>
<html>
	<head>
		<title>Obojobo Login Error</title>
	<head>
	<body bgcolor="#F8F8F8">
		<p>An error occurred trying to log you in to Obojobo, <a href="javascript:history.back()">return to myUCF</a> and click on the link again.</p>
	
	
	</body>
</html>