<?php
require_once(dirname(__FILE__)."/../../internal/app.php");
$API = \obo\API::getInstance();

if(isset($_REQUEST['instID']) )
{
	// Already logged in
	if($API->getSessionValid())
	{
		redirectToLO($_REQUEST['instID']);
		exit();
	}

	// not already logged in, Check hash
	$NID       = $_REQUEST['nid'];
	$timestamp = $_REQUEST['epoch'];
	$hash      = $_REQUEST['hash'];

	// check hash
	if(md5($NID.$timestamp.\AppCfg::UCF_PORTAL_SECRET) !== $hash)
	{
		trace('PORTAL SSO hash mismatch', true);
		trace('our hash: '.md5($NID.$timestamp.\AppCfg::UCF_PORTAL_SECRET).' received: '.$hash, true);
	}
	// check timeout: is hashtime less then now - maxage?
	elseif((int)$timestamp < (time() - \AppCfg::UCF_PORTAL_TIMEOUT))
	{
		trace('PORTAL SSO hash timeout', true);
		trace("Now:".time().", HashTime: {$timestamp}, Timeout: ".\AppCfg::UCF_PORTAL_TIMEOUT, true);
	}
	// good hash! go go
	else
	{
		// store session variables for authentication
		$_SESSION['PORTAL_SSO_NID'] = $NID;
		$_SESSION['PORTAL_SSO_EPOCH'] = $timestamp;

		if($API->doLogin('', ''))
		{
			redirectToLO($_REQUEST['instID']);
			exit();
		}
		else
		{
			trace('PORTAL SSO doLogin FAILED', true);
		}
	}
}
else
{
	trace('PORTAL SSO no instID in request', true);
	trace($_REQUEST, true);
}

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