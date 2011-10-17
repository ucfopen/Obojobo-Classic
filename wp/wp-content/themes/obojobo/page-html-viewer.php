<?php
/*
Template Name: HTML Viewer
*/
add_filter('show_admin_bar', '__return_false');

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<!--[if IE]><script>/*@cc_on'abbr article aside audio canvas details figcaption figure footer header hgroup mark menu meter nav output progress section summary time video'.replace(/\w+/g,function(n){document.createElement(n)})@*/</script><![endif]--> 
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<title></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<!-- <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script> -->
<script type="text/javascript" src="/assets/js/jquery/1.6.1/jquery.min.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js"></script>
<!-- <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js"></script> -->
<script type="text/javascript" src="/assets/js/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
<!--<script type="text/javascript" src="/assets/js/poshytip/src/jquery.poshytip.min.js"></script>-->
<script type="text/javascript" src="/assets/js/tipTipv13/jquery.tipTip.js"></script>
<script type="text/javascript" src="/assets/js/jquery.activity-indicator-1.0.0.min.js"></script>
<script type="text/javascript" src="/assets/js/jquery.simplemodal.1.4.1.min.js"></script>
<!--<script type="text/javascript" src="/assets/flowplayer/flowplayer-3.2.6.min.js"></script>-->
<script type="text/javascript" src="/assets/js/swfobject.js"></script>
<!--<script type="text/javascript" src="/assets/jwplayer/jwplayer.js"></script>-->

<script type="text/javascript" src="/assets/js/viewer/obo.util.js"></script>
<script type="text/javascript" src="/assets/js/viewer/obo.model.js"></script>
<script type="text/javascript" src="/assets/js/viewer/obo.remote.js"></script>
<script type="text/javascript" src="/assets/js/viewer/obo.view.js"></script>
<script type="text/javascript" src="/assets/js/viewer/obo.dialog.js"></script>
<script type="text/javascript" src="/assets/js/viewer/obo.loader.js"></script>
<script type="text/javascript" src="/assets/js/viewer/obo.media.js"></script>
<script type="text/javascript" src="/assets/js/viewer/obo.captivate.js"></script>

<!--
<script type="text/javascript" src="/assets/js/viewer.js"></script>
-->

<!-- <script type="text/javascript" src="/assets/js/jquery.history.js"></script> -->

<link rel="stylesheet" type="text/css" href="/assets/js/poshytip/src/tip-twitter/tip-twitter.css">
<link rel="stylesheet" type="text/css" href="/assets/js/tipTipv13/tipTip.css">
<!--<link rel="stylesheet" type="text/css" href="/assets/flowplayer/style.css">-->

<link rel="stylesheet" type="text/css" href="/assets/css/themes/classic.css" media="screen">
<link rel="stylesheet" type="text/css" href="/assets/css/ui-lightness/jquery-ui-1.8.16.custom.css" media="screen">
<!-- <link rel="stylesheet" type="text/css" href="/assets/css/tablet.css" media="screen and (max-width: 957px)" /> -->
<!-- <link rel="stylesheet" type="text/css" href="/assets/css/viewer-phone.css" media="screen and (max-device-width: 500px) and (orientation: portrait)" /> -->


<link id="theme-blue" rel="stylesheet" type="text/css" href="/assets/css/themes/blue.css" media="screen" />
<link rel="stylesheet" href="/assets/js/fancybox/jquery.fancybox-1.3.4.css" type="text/css" media="screen" />

<link href='http://fonts.googleapis.com/css?family=Lato:400,700,900' rel='stylesheet' type='text/css'>


<script type="text/javascript" src="/assets/js/modernizr-2.0.6.js"></script>

<?php wp_head(); ?>
<script type="text/javascript" defer="defer">	
	$(function() {
		
		//@TODO: are these the right params?
		var params = {
			loID:'<?php echo $wp_query->query_vars['loID']; ?>',
			instID:'<?php echo $wp_query->query_vars['instID']; ?>'
		};
		/*
		//@TODO - this syntax is weird
		obo.model = obo.model(obo.view, {});
		obo.model.loadLO(loID, function() {
			obo.view.init($('body'));
		});*/
		obo.model = obo.model(obo.view, {});
		console.log(obo.model);
		obo.model.load(params, function() {
			obo.view.init($('body'));
		});
		/*obo.model.loadInstance('1937', function() {
			obo.view.init($('body'));
		});*/
		
		
	});
	
	
	
	//alert('you tube and obo.video.js');
</script>
</head>
<body <?php body_class(); ?>>

<?php get_footer('html-viewer'); ?>