<!DOCTYPE html>
<html>
<head>
<title><?php echo $title; ?> | Obojobo Learning Object</title>
<link rel="profile" href="http://gmpg.org/xfn/11">

<?php
// =========================== DEV AND TEST ENVIRONMENTS =============================
if(\AppCfg::ENVIRONMENT == \AppCfgDefault::ENV_DEV)
{
	?>
	<!-- DEV JAVASCRIPT LIBRARIES -->
	<script type="text/javascript" src="/assets/js/jquery-1.7.js"></script>
	<script type="text/javascript" src="/assets/js/modernizr-2.0.6.js"></script>
	<script type="text/javascript" src="/assets/js/jquery.infieldlabel.js"></script>

	<!-- DEV OBOJOBO CSS -->
	<link type="text/css" rel="stylesheet" href="/assets/css/themes/default.css" media="screen">

	<!-- DEV OBOJOBO LOGIN CSS -->
	<link type="text/css" rel="stylesheet" href="/assets/css/login.css" media="screen">

	<!-- GOOGLE FONTS -->
	<link href='https://fonts.googleapis.com/css?family=Lato:400,700,900' rel='stylesheet' type='text/css'>

  <?php
}
// =========================== PRODUCTION ENVIRONMENT =============================
else
{
  ?>

<script type="text/javascript" src="/min/b=assets/js&amp;f=
jquery-1.7.js,
modernizr-2.0.6.js,
jquery.infieldlabel.js"></script>

<link type="text/css" rel="stylesheet" href="/min/b=assets/css&amp;f=themes/default.css,login.css" media="screen">
<link href='https://fonts.googleapis.com/css?family=Lato:400,700,900' rel='stylesheet' type='text/css'>

  <?php
}
?>


<!-- BEGIN IE CONDITIONALS: -->
<!--[if lte IE 7]>
<script type="text/javascript">
  oldBrowser = true;
</script>
<![endif]-->
<!--[if lte IE 8]>
<link rel="stylesheet" type="text/css" href="/assets/css/ie.css" media="screen" />
<![endif]-->
<!--[if IE 9]>
<link rel="stylesheet" type="text/css" href="/assets/css/ie9.css" media="screen" />
<![endif]-->
<!-- END IE CONDITIONALS -->

<style type="text/css">.oldBrowser { background: #eeeeee; }</style>

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
		oldBrowser = true;
	}
}

$('document').ready(function()
{
	if(typeof oldBrowser !== 'undefined' && oldBrowser === true)
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
	$("label").inFieldLabels();
}
</script>


</head>
<body id="login-page">

<header id="login-header">
	<h1 title="<?php echo $title; ?>"><?php echo $title; ?></h1>

	<h2>for <em><?php echo $course; ?></em> (<?php echo $instructor; ?>)</h2>
		
	<h3>Begins: <em><?php echo $startDate; ?></em> at <em><?php echo $startTime; ?></em></h3> 
	<h3>Closes: <em><?php echo $endDate; ?></em> at <em><?php echo $endTime; ?></em></h3> 
</header>

<form id="login-form" class="overview-details " method="post">
	<h1>Login to Begin</h1>
	<?php if(isset($notice)) echo '<p class="login-notice">'.$notice.'</p>'; ?>
	<ul>
		<li>
			<label for="username">UCF NID</label><br>
			<input type="text" id="username" name="username" value="" title="UCF NID" tabindex="1">
		</li>
		<li>
			<label for="password">Password</label><br>
			<input type="password" id="password" name="password" value="" title="Password" tabindex="2">
		</li>
		<li>
			<input type="submit" id="signInSubmit" name="cmdweblogin" value="Login" tabindex="3">
		</li>
	</ul>
	<ul class="foot">
		<li><a href="https://my.ucf.edu/static_support/pidnidwrapper.html">Lookup NID</a></li>
		<li><a href="http://mynid.net.ucf.edu/">Reset Password</a></li>
		<li><a href="/help">Help</a></li>
	</ul>
</form>

<footer id="footer">
	<div class="footer-container">
		<div id="logo">powered by Obojobo</div>
		<h5>&copy; <?php echo date("Y"); ?> University of Central Florida</h5>
		<p></p>
		<p></p>
	</div>
</footer>


</body>
</html>