<!DOCTYPE html>
<!--[if lt IE 7 ]> <html class="ie ie6"> <![endif]-->
<!--[if IE 7 ]>    <html class="ie ie7"> <![endif]-->
<!--[if IE 8 ]>    <html class="ie ie8"> <![endif]-->
<!--[if IE 9 ]>    <html class="ie ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html class=""> <!--<![endif]-->
<head>
	<meta charset="utf-8" />
	<title>Title</title>
	<link rel="stylesheet" type="text/css" href="/assets/css/lti.css">

	  <!-- GOOGLE FONTS -->
	<link href='http://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,700' rel='stylesheet' type='text/css'>

	<!--[if lt IE 9]>
		<script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
</head>
<body>
	<section style="display:block;" id="success">
		<header>
			<span class="logo-text">Obojobo&reg;</span>
			<div class="blue-flourish"></div>
		</header>
		<div class="grey-box">
			<h1>Obojobo Instance Connected:</h1>
			<h2 class="selected-instance-title">{$instanceTitle}</h2>
			<span class="note">Scores for this Obojobo assignment will sync with your Canvas gradebook</span>
		</div>
		<a href="{$previewLink}" class="preview-link" target="_blank">Preview this instance in a new window...</a>
	</section>
</body>
</html>