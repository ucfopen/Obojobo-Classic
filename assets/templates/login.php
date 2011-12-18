<!DOCTYPE html>
<html>
<head>
<title><?php echo $title; ?> | Obojobo Learning Object</title>
<link rel="profile" href="http://gmpg.org/xfn/11">

<!-- Minify using Minify -->
<script type="text/javascript" src="/min/b=assets/js&amp;f=
jquery-1.7.js,
jquery.infieldlabel.js"></script>


<!-- Minify using Minify -->
<link type="text/css" rel="stylesheet" href="/min/b=assets/css/themes&amp;f=default.css" media="screen">
<link type="text/css" rel="stylesheet" href="/min/b=assets/css&amp;f=login.css" media="screen">

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
  // Guess if they have an old browser
  // We check for IE <= 7 up in the IE conditionals.
  // We assume Chrome is up to date

  $('document').ready(function(){
	$("label").inFieldLabels(); 
  });
 
</script>


</style>
</head>
<body>
<section id="content" style="margin-top: 0px; opacity: 1; ">

	<div id="login-page">
	<h1 id="lo-title" title="<?php echo $title; ?>"><?php echo $title; ?></h1>
		<h2 class="class-times" >for <em><?php echo $course; ?></em> (<?php echo $instructor; ?>)</h2>
			
		<h3 >Begins: <em><?php echo $startDate; ?></em> at <em><?php echo $startTime; ?></em></h3> 
		<h3 >Closes: <em><?php echo $endDate; ?></em> at <em><?php echo $endTime; ?></em></h3> 

		<div class="overview-details">
			
			<div class="main">
				<div id="login-error">
					UCF NID or Password Incorrect. <a href="help">Click Here For Help.</a>
					<ul>
						<li><a href="https://my.ucf.edu/static_support/pidnidwrapper.html">Lookup your NID</a></li>
						<li><a href="http://mynid.net.ucf.edu/">Reset your password</a></li>
						<li><a href="http://learn.ucf.edu/support/">Contact Support</a></li>
					</ul>
				</div>
				<div id="form">
					<form method="post" class="form-content" action="">
						<h2>Login to Begin</h2>
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
								<input type="submit" id="signInSubmit" name="cmdweblogin" value="Login" tabindex="3"> <div id="login-loading">Loading...</div>
							</li>
						</ul>
						<ul class="foot">
							<li><a href="https://my.ucf.edu/static_support/pidnidwrapper.html">Lookup NID</a></li>
							<li><a href="http://mynid.net.ucf.edu/">Reset Password</a></li>
							<li><a href="/help">Help</a></li>
						</ul>
					</form>
				</div>
			</div>
		</div>
	</div>

</section>

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