<html class="js history rgba multiplebgs"><head>
<title><?php echo $title; ?> | Obojobo Learning Object</title>
<link rel="profile" href="http://gmpg.org/xfn/11">

<!-- Minify using Minify -->
<script type="text/javascript" src="/min/b=assets/js&amp;f=
jquery-1.7.js,
jquery.infieldlabel.js"></script>



<!-- Minify using Minify -->
<link type="text/css" rel="stylesheet" href="/min/b=assets/css/themes&amp;f=classic.css,blue.css" media="screen">

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

<style>
/* -----------------------------------------------
 * Login Page
 */


#login-page form h2{
	margin-top:31px;
}


#login-page #form ul li {
	list-style-type:none;
	margin: 0 auto;
	margin-bottom: 5px;
	width:315px;
	padding-left:0;
	padding-right:0;
}
 

#login-page #login-error{
	position: absolute;
	top: 59px;
}

#login-page #login-error {
	background: url('../images/error_bar_bg.png') repeat-x;
	height: 107px;
	overflow: hidden;
	color: #163d47;
	/*font-family: 'FuturaICGDemiRegular', Futura ICG, Futura, Verdana, Arial, sans-serif;*/
	font-size: 19px;
	display: block;
	padding-top: 10px;
	text-shadow: 0px 1px 0px #e5e5ee;
	display: none;
	text-align: center;
	width: 100%;
	z-index: 2500;
}

#password, #username {
	width: 300px;
	-moz-background-clip: border;
	-moz-background-inline-policy: continuous;
	-moz-background-origin: padding;
	-moz-border-radius: 4px;
	-webkit-border-radius: 4px;
	border: 1px solid #DDD;
	color: #000;
	font-size: 14px;
	margin: 0 0 10px 0;
	padding: 7px 10px 8px;
	z-index: 5;
	border-image: initial;
}

#login-embed .foot li, #login-page #form ul.foot li, #login-page #form ul.foot li {
	display: inline;
	padding: 0 !important;
	position: relative;
	background: none !important;
	margin:0 6px;
}


form label, form input {
	position: absolute;
	top: 0;
	left: 0;
	z-index: 1000;
	cursor: text;
}

form label {
	z-index: 2000;
	top: 7px;
	left: 11px;
	color: #000;
	cursor: text;
	opacity: 0.6;
	font: normal 14px Verdana;
	font-family: Verdana, sans, sans-serif;
}

ul, menu, dir {
	display: block;
	list-style-type: disc;
	-webkit-margin-before: 1em;
	-webkit-margin-after: 1em;
	-webkit-margin-start: 0px;
	-webkit-margin-end: 0px;
	-webkit-padding-start: 40px;
}

form li {
	position: relative;
	z-index: 2000;
	padding: 0 0 20px 0;
}

#signInSubmit {
	display: block;
	width: 82px;
	height: 30px;
	cursor: pointer;
	font-family: "lato",Arial;
	background: url('/assets/images/viewer/login_button.png');
	border: none;
	font-weight: bold;
	font-size: 12pt;
	color: #303030;
	text-shadow: #c2c2c2 1px 1px 1px;
	border-image: initial;
}

#login-loading {
	display: block;
	height: 0;
	padding-top: 16px;
	overflow: hidden;
	width: 16px;
	cursor: pointer;
	background: url('../images/loading.gif') no-repeat;
	left: 90px;
	top: 6px;
	background-position: bottom center;
	position: absolute;
	display: none;
}

.login-form {
	margin: 0 auto;
	width: 49%;
	margin-top: 2em;
	text-align: left;
	display: block;
	text-align: center;
	background: #f2e6e1;
	border: 1px solid rgba(0, 0, 0, .2);
	-moz-box-shadow: 0px 1px 5px #AAA;
	-webkit-box-shadow: 0px 1px 5px #AAA;
	box-shadow: 0px 1px 5px #AAA;
	-moz-border-radius: 7px;
	-webkit-border-radius: 7px;
	border-radius: 7px;
	margin-bottom: 2em;
	border-image: initial;
}

#login-page{
	border-bottom: 1px solid gray;
	background: -moz-linear-gradient(white, #e7e7e7);
	background: -ms-linear-gradient(white, #e7e7e7);
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, white), color-stop(100%, #e7e7e7));
	background: -webkit-linear-gradient(white, #e7e7e7);
	background-image: -webkit-linear-gradient(top, #DDD, rgb(231, 231, 231));
	background-repeat-x: initial;
	background-repeat-y: initial;
	background-attachment: initial;
	background-position-x: initial;
	background-position-y: initial;
	background-origin: initial;
	background-clip: initial;
	background-color: initial;
	background: -o-linear-gradient(white, #e7e7e7);
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='white', endColorstr='#e7e7e7');
	-ms-filter: "progid:DXImageTransform.Microsoft.gradient(startColorstr='white', endColorstr='#e7e7e7')";
	background: linear-gradient(white, #e7e7e7);
	z-index: 0;
	-moz-box-shadow: 0px 1px 5px #333;
	-webkit-box-shadow: 0px 1px 5px #333;
	box-shadow: 0px 1px 5px #333;
	-moz-border-radius: 7px;
	-webkit-border-radius: 7px;
	border-radius: 7px;
	margin-bottom: 2em;
	border-image: initial;
		width:60%;
	margin:0 auto;
}

#login-page
{
	font-family: "lato",Arial;
}

#login-page h1#lo-title {
	font-weight: 900;
	font-size: 29px;
	margin: 0px;
	background: #5572a2;
	padding: 33px 10px;
	color: #FFF;
	text-shadow: 0px -1px 0px #555, 0px 1px 0px rgba(255,255,255,0.3);
	background: -moz-linear-gradient(#8ea4b8, #48677f);
	background: -ms-linear-gradient(#8ea4b8, #48677f);
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #8ea4b8), color-stop(100%, #48677f));
	background: -webkit-linear-gradient(white, #48677f);
	background-image: -webkit-linear-gradient(top, #8ea4b8, #48677f);
	background-repeat-x: initial;
	background-repeat-y: initial;
	background-attachment: initial;
	background-position-x: initial;
	background-position-y: initial;
	background-origin: initial;
	background-clip: initial;
	background-color: initial;
	background: -o-linear-gradient(#8ea4b8, #48677f);
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='white', endColorstr='#48677f');
	-ms-filter: "progid:DXImageTransform.Microsoft.gradient(startColorstr='#8ea4b8', endColorstr='#48677f')";
	background: linear-gradient(#8ea4b8, #48677f);
	border-bottom: 1px solid #111;
}


#login-page h3 {
	font-size:17px;
}

#login-page h3 em{
	font-size:20px;
	color:#000;
	font-style:normal;
}

#login-page h2.class-times{
	margin-top:0px;
	margin-bottom:30px;
	font-size:18px;
	color:#555;
	font-style:italic;
	padding:5px 0;
	border-top:1px solid #ddd;
	border-bottom:1px solid #AAA;
	background-color:#BBB;
}

#login-page h2 em{
	font-size:1.2em;
	color:#86273d;
	font-style:normal;
}
.form-content ul.foot{
	margin: 30px 0px;
}

html
{
	background: url('/assets/images/viewer/themes/blue/bg.png');
}
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
		<h5>© 2011 University of Central Florida</h5>
		<p></p>
		<p></p>
	</div>
</footer>

</body>
</html>