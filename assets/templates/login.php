<!DOCTYPE html>
<html>
<head>
<title><?php echo $title; ?> | Obojobo Learning Object</title>
<link rel="profile" href="http://gmpg.org/xfn/11">

<!-- Minify using Minify -->
<script type="text/javascript" src="/min/b=assets/js&amp;f=
jquery-1.7.js,
modernizr-2.0.6.js,
jquery.infieldlabel.js"></script>


<!-- Minify using Minify -->
<link type="text/css" rel="stylesheet" href="/min/b=assets/css/themes&amp;f=default.css" media="screen">
<link type="text/css" rel="stylesheet" href="/min/b=assets/css&amp;f=login.css" media="screen">

<link href='http://fonts.googleapis.com/css?family=Lato:400,700,900' rel='stylesheet' type='text/css'>

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

<script type="text/javascript">
  $('document').ready(function(){
	$("label").inFieldLabels(); 
  });
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
		<h5>&copy; 2011 University of Central Florida</h5>
		<p></p>
		<p></p>
	</div>
</footer>


</body>
</html>