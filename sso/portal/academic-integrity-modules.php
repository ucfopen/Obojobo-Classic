<?php
require_once(dirname(__FILE__)."/../../internal/app.php");

$loggedIn         = \obo\API::getInstance()->getSessionValid();
$im               = \obo\lo\InstanceManager::getInstance();
$output           = [];
$los              = explode(',', \AppCfg::UCF_PORTAL_ORIENTATION_INSTANCES);

//============== BUILD LINKS
foreach ($los AS $instID)
{
	$instData = $im->getInstanceData($instID);
	
	// if we found the instance 
	if ($instData instanceof \obo\lo\InstanceData)
	{
		$output[] = '<li><a href="'.\AppCfg::URL_WEB.'view/'.$instID.'">'.$instData->name.'</a></li>';
	}
}

//================= START TEMPLATE OUTPUT ===========================

?><!DOCTYPE html>
<html class="no-js">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<title>Academic Integrity Learning Modules | Obojobo</title>
	<meta name="viewport" content="width=device-width">

	<link rel="stylesheet" href="/wp/wp-content/themes/obojobo/css/pagelet.css">
	<link href='//fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,700' rel='stylesheet' type='text/css'>

	<script src="/wp/wp-content/themes/obojobo/js/vendor/modernizr-2.6.2.min.js"></script>
</head>
<body>
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
			<p>If you are a New Undergraduate Student or a New Master's Program Student <strong>admitted in <?= \AppCfg::UCF_PORTAL_ORIENTATION_ADMITTED ?></strong> or later, you are required to complete all the Academic Integrity Modules listed below in your group.</p>
			<p>You need to score <strong><?= \AppCfg::UCF_PORTAL_ORIENTATION_MIN_SCORE ?>% or higher on all required modules before <?= \AppCfg::UCF_PORTAL_ORIENTATION_DUE ?></strong>. Otherwise you will receive a hold that prevents you from registering for classes.</p>

		<? if( ! $loggedIn): ?>
			<div class="warning">
				<h2>Notice</h2>
				<p>You'll need to login using your UCF NID or the Online Course Tools tab in the <a href="https://my.ucf.edu/">myUCF Portal</a>.</p>
			</div>
		<? endif; ?>


		<h1>Academic Integrity Modules</h1>

		<h2>New Undergraduate Students</h2>
			<ul>
				<?= $output[0] ?>
			</ul>
		<h2>New Master's Program Students</h2>
			<ul>
				<?= $output[1] ?>
			</ul>

	<? else: ?>
		<p>The Academic Integrity modules are not available at this time, please try again later.</p>
	<? endif; ?>

</section>
</body>
</html>
