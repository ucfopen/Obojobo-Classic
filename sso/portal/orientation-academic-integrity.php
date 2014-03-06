<?php
// This little masterpiece redirects the single sign on links coming out of the UCF portal to
// another page so that the sso variables aren't in the url.  Yea, sending a POST request
// would be a better idea but getting CS&T to update code in Peoplesoft pagelets is a little
// like watching babies invent the wheel.  So, we'll do it instead.  STAY AGILE!

require_once(dirname(__FILE__)."/../../internal/app.php");

$nid              = $_REQUEST['nid'] ?: '';
$timestamp        = $_REQUEST['epoch'] ?: 0;
$hash             = $_REQUEST['hash'] ?: '';
$isDevEnvironment = preg_match('/^https:\/\/(patest|padev)\d*?\.net\.ucf\.edu\/psp\/PA(TEST|DEV)/', $_SERVER['HTTP_REFERER']); // figure out if this is being accessed via the test or dev portals
$validHash        = false;
$notExpired       = false;
$loggedIn         = false;


// ************* TESTING CODE ************
if (\AppCfg::ENVIRONMENT == \AppCfgDefault::ENV_DEV)
{
	$nid       = 'iturgeon';
	$timestamp = time()-90000;
	$hash      = md5($nid.$timestamp.\AppCfg::UCF_PORTAL_ORIENTATION_SECRET);
	$los       = explode(',', \AppCfg::UCF_PORTAL_ORIENTATION_INSTANCES);
}

//=============== CHECK THE HASH AND TIMEOUT
$validHash  = md5($nid.$timestamp.\AppCfg::UCF_PORTAL_ORIENTATION_SECRET) === $hash;
$notExpired = (int)$timestamp >= time() - \AppCfg::UCF_PORTAL_ORIENTATION_TIMEOUT;

if ($validHash && $notExpired)
{
	$loggedIn = \obo\API::getInstance()->getSessionValid();

	// not already logged in...
	if ( ! $loggedIn)
	{
		// store session variables for authentication
		$_SESSION['PORTAL_SSO_NID']   = $nid;
		$_SESSION['PORTAL_SSO_EPOCH'] = $timestamp;

		$loggedIn = \obo\API::getInstance()->doLogin('', '');
	}
}

// 307 Temporary Redirect
header("Location: /sso/portal/academic-integrity-modules.php", true, 307);