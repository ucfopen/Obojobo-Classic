<?php
$hasTime = ! empty($startTime) && ! empty($endTime) && $startTime > 0 && $endTime > 0;
$time = empty($title) ? '' : $title;
?><!DOCTYPE html>
<html>
<head>
<title><?php echo $title; ?> | Obojobo Learning Object</title>
<?php if(\AppCfg::ENVIRONMENT == \AppCfgDefault::ENV_DEV) : ?>
	<!-- DEV JAVASCRIPT LIBRARIES -->
	<script type="text/javascript" src="/assets/js/jquery.js"></script>
	<script type="text/javascript" src="/assets/js/modernizr.js"></script>
	<script type="text/javascript" src="/assets/js/date.format.js"></script>

	<!-- DEV OBOJOBO CSS -->
	<link type="text/css" rel="stylesheet" href="/assets/css/themes/default.css" media="screen">

	<!-- DEV OBOJOBO LOGIN CSS -->
	<link type="text/css" rel="stylesheet" href="/assets/css/login.css" media="screen">

	<!-- GOOGLE FONTS -->
	<link href='//fonts.googleapis.com/css?family=Lato:400,700,900' rel='stylesheet' type='text/css'>

<?php else: ?>

	<script type="text/javascript" src="/min/b=assets/js&amp;f=jquery.js,modernizr.js,date.format.js"></script>
	<link type="text/css" rel="stylesheet" href="/min/b=assets/css&amp;f=themes/default.css,login.css" media="screen">
	<link href='//fonts.googleapis.com/css?family=Lato:400,700,900' rel='stylesheet' type='text/css'>

<?php endif; ?>


<!-- BEGIN IE CONDITIONALS: -->
<!--[if lte IE 7]>
<script type="text/javascript">
  __oldBrowser = true;
</script>
<![endif]-->
<!--[if lte IE 8]>
<link rel="stylesheet" type="text/css" href="/assets/css/ie.css" media="screen" />
<![endif]-->
<!-- END IE CONDITIONALS -->

<style type="text/css">.__oldBrowser { background: #eeeeee; }</style>

<script type="text/javascript">
// Guess if they have an old browser
// We check for IE <= 7 up in the IE conditionals.
// We assume Chrome is up to date
if(typeof navigator.userAgent !== 'undefined')
{
	var ua = navigator.userAgent;
	// find firefox version 3.5 and below:
	var oldFirefox = /firefox\/(3\.[0-5]|[0-2]\.)/gi;
	// find opera version 10 and below:
	var oldOpera = /opera (10|[0-9])\.|opera.*?version\/(10|[0-9])\./gi;
	// find safari version 3 and below:
	var oldSafari = /safari\/[0-3]\./gi;

	if(oldFirefox.test(ua) || oldOpera.test(ua) || oldSafari.test(ua))
	{
		__oldBrowser = true;
	}
}

$('document').ready(function()
{
	if(typeof __oldBrowser !== 'undefined' && __oldBrowser === true)
	{
		$('body *').hide();
		$('html').addClass('older-browser-background');
		$('body').attr('id', '');
		$('body').append('<div id="older-browser-container"></div>');
		$('#older-browser-container').load('/assets/templates/viewer.html #older-browser-dialog', function() {
			$('#ignore-older-browser-warning').click(function(event) {
				event.preventDefault();
				$('body').attr('id', 'login-page');
				$('html').removeClass('older-browser-background');
				$('#older-browser-dialog').remove();
				$('body *').show();
				initLoginScreen();
			});
		});
	}
	else
	{
		initLoginScreen();
	}
});

function initLoginScreen()
{
	<?php if($hasTime) : ?>
		var startTime = new Date(<?php echo $startTime * 1000; ?>);
		var endTime = new Date(<?php echo $endTime * 1000; ?>);
		$('#start-time').html('Begins: <em>' + startTime.format('mm/dd/yy') + '</em> at <em>' + startTime.format('h:MM TT') + '</em>');
		$('#end-time').html('Closes: <em>' + endTime.format('mm/dd/yy') + '</em> at <em>' + endTime.format('h:MM TT') + '</em>');
	<?php endif ?>
}
</script>
</head>
<body id="login-page">
	<div id="wrapper">
		<div id="login-box">
			<header id="login-header">
				<h1 title="<?php echo $title; ?>"><?php echo $title; ?></h1>

				<?php if (isset($course)): ?>
					<h2>for <em><?php echo $course; ?></em> (<?php echo $instructor; ?>)</h2>
				<?php endif; ?>

				<?php if(isset($_REQUEST['loID']) || $hasTime) : ?>

					<h3 id="start-time">Begins: <em>Date Here</em> at <em>Time Here</em></h3>
					<h3 id="end-time">Closes: <em>Date Here</em> at <em>Time Here</em></h3>
				<?php endif ?>
			</header>

			<form id="login-form" class="overview-details " method="post">
				<?php include(\AppCfg::LOGIN_TEMPLATE); ?>
			</form>
		</div>

		<div id="push"></div>
	</div>
	<footer id="footer">
		<div class="footer-container">
			<div id="logo">powered by Obojobo</div>
			<h5>&copy; <?php echo date("Y"); ?> University of Central Florida</h5>
			<p></p>
			<p></p>
		</div>
	</footer>
<?php include(\AppCfg::DIR_BASE . 'assets/templates/google_analytics.php'); ?>
</body>
</html>
