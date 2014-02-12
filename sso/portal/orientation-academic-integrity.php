<?php
require_once(dirname(__FILE__)."/../../internal/app.php");

$nid              = $_REQUEST['nid'] ?: '';
$timestamp        = $_REQUEST['epoch'] ?: 0;
$hash             = $_REQUEST['hash'] ?: '';
$scores           = array();
$isDevEnvironment = preg_match('/^https:\/\/(patest|padev)\d*?\.net\.ucf\.edu\/psp\/PA(TEST|DEV)/', $_SERVER['HTTP_REFERER']); // figure out if this is being accessed via the test or dev portals
$output           = [];
$validHash        = false;
$notExpired       = false;
$loggedIn         = false;


function formatDisplayForInstance($instID)
{
	$im = \obo\lo\InstanceManager::getInstance();
	$instData = $im->getInstanceData($instID);
	
	// if we found the instance 
	if ($instData instanceof \obo\lo\InstanceData)
	{
		return '<li><a href="'.\AppCfg::URL_WEB.'view/'.$instID.'">'.$instData->name.'</a></li>';
	}
	else
	{
		return false;
	}
}

if ($isDevEnvironment)
{
	$los = ['2329','2329','2330','2330'];
}
else
{
	$los = explode(',', \AppCfg::UCF_PORTAL_ORIENTATION_INSTANCES);
}

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

//============== BUILD LINKS
foreach ($los AS $instID)
{
	if ($display = formatDisplayForInstance($instID))
	{
		$output[] = $display;
	}
}


?><!DOCTYPE html>
<html class="no-js">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<title>Learning Modules, Reinvented. | Obojobo</title>
	<meta name="viewport" content="width=device-width">

	<link rel="stylesheet" href="/wp/wp-content/themes/obojobo/css/pagelet.css">
	<link href='//fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,700' rel='stylesheet' type='text/css'>

	<script src="/wp/wp-content/themes/obojobo/js/vendor/modernizr-2.6.2.min.js"></script>
</head>
<header>
	<div class="header-container">
		<div class="logo"><span>Obojobo</span></div>
		<nav>
			<ul>
				<li class="about">
					<a href="/about">About</a>
					<ul>
						<li><a href="http://honor.sdes.ucf.edu/integrity">Academic Integrity Modules</a></li>
						<li><a href="http://honor.sdes.ucf.edu/">Honor Your Knighthood</a></li>
					</ul>
				</li>
				<li class="help">
					<a href="/help">Help</a>
					<ul>
						<li><a href="/help/faq">FAQ</a></li>
						<li><a href="http://honor.sdes.ucf.edu/integrity">Honor Your Knighthood Help</a></li>
						<li><a href="/help/support">Obojobo Support</a></li>
					</ul>
				</li>
			</ul>
		</nav>
	</div>
</header>

<section class="hero" >
	<h1>Are You an Incoming Student?</h1>
</section>

<section class="modules">
	<? if (count($output)): ?>
			<p>If you are a New Undergraduate Student or a New Master's Program Student <strong>admitted in <?= \AppCfg::UCF_PORTAL_ADMITTED ?></strong> or later, you are required to complete all the Academic Integrity Modules listed below in your group.</p>
			<p>You need to score <strong><?= \AppCfg::UCF_PORTAL_MIN_SCORE ?>% or higher on all required modules before <?= \AppCfg::UCF_PORTAL_DUE ?></strong>. Otherwise you will receive a hold that prevents you from registering for classes.</p>

		<? if( ! $validHash || ! $notExpired): ?>
			<div class="warning">
				<h2>Notice</h2>
				<? if( !$validHash && !$notExpired): ?>
					<p>You'll need to login using your UCF NID or the Online Course Tools tab in the <a href="https://my.ucf.edu/">myUCF Portal</a>.</p>
				<? else: ?>
					<p>Link Expired. You'll need to return to the Online Course Tools tab in the <a href="https://my.ucf.edu/">myUCF Portal</a> to log in.</p>
				<? endif; ?>
			</div>
		<? endif; ?>


		<h1>Academic Integrity Modules</h1>

		<h2>New Undergraduate Students</h2>
			<ul>
				<?= $output[0] ?>
				<?= $output[1] ?>
				<?= $output[2] ?>
			</ul>
		<h2>New Master's Program Students</h2>
			<ul>
				<?= $output[3] ?>
			</ul>

		<? if($isDevEnvironment): ?>
			<hr>
			<h2>Displaying Temporary Test Links</h2>
			<p>The above learning objects are only for testing single sign on in the Dev and Test Portals.</p>
			<p>They were randomly selected from the public Information Literacy Modules.</p>
			<p>Note that the links <strong>DO</strong> go to the production server.</p>
		<? endif; ?>

	<? else: ?>
		<p>The Academic Integrity modules are not available at this time, please try again later.</p>
	<? endif; ?>

</section>
</body>
</html>
