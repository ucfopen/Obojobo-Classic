<?php
/*
Template Name: HTML Viewe
*/
add_filter('show_admin_bar', '__return_false');

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<title></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<!-- <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script> -->
<script type="text/javascript" src="/assets/js/jquery/1.6.1/jquery.min.js"></script>
<!-- <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js"></script> -->
<script type="text/javascript" src="/assets/js/swfobject.js"></script>
<script type="text/javascript" src="/assets/js/viewer.js"></script>
<script type="text/javascript" src="/assets/js/jquery.history.js"></script>


<link rel="stylesheet" type="text/css" href="/assets/css/viewer-screen.css" media="screen" />
<!-- <link rel="stylesheet" type="text/css" href="/assets/css/tablet.css" media="screen and (max-width: 957px)" /> -->
<link rel="stylesheet" type="text/css" href="/assets/css/viewer-phone.css" media="screen and (max-device-width: 500px) and (orientation: portrait)" />
<link rel="stylesheet" type="text/css" href="/assets/css/viewer.css"> 


<?php wp_head(); ?>
<script type="text/javascript" defer="defer">	
var loID = <?php echo $wp_query->query_vars['loID']; ?>; 
</script>
</head>
<body <?php body_class(); ?>>


<header id='header'>
	<h1 id="lo-title"></h1>
	<div id='logo'>powered by Obojobo</div>
	<nav id="navigation"></nav>
</header>

<div id="content"></div>


<div id="footer">footer</div>

<?php get_footer('html-viewer'); ?>