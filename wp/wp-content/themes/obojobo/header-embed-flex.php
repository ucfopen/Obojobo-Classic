<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */
$swf = get_post_meta($post->ID, 'flex_swf', true);
wp_enqueue_script('swfobject', '/assets/js/swfobject.js');
add_filter('show_admin_bar', '__return_false');

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<title><?php
	/*
	 * Print the <title> tag based on what is being viewed.
	 */
	global $page, $paged;

	wp_title( '|', true, 'right' );

	// Add the blog name.
	bloginfo( 'name' );

	// Add the blog description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
	{
		echo " | $site_description";
	}
	// Add a page number if necessary:
	if ( $paged >= 2 || $page >= 2 )
	{
		echo ' | ' . sprintf( __( 'Page %s', 'twentyten' ), max( $paged, $page ) );
	}
	?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'stylesheet_url' ); ?>" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<?php wp_head(); ?>
<style type="text/css" media="screen">
	html, body{
		overflow: hidden;
		margin:0 !important;
		padding:0;
		height:100%;
	}
</style>
<script type="text/javascript">
		
	// START PREVENT CLOSE
	window.onbeforeunload = confirmExit;

	 function thisMovie(movieName) {
	     if (navigator.appName.indexOf("Microsoft") != -1) {
	         return window[movieName];
	     } else {
	         return document[movieName];
	     }
	 }

	function confirmExit(){
		$isCloseable = thisMovie("<?php echo $swf; ?>").isCloseable();
		if($isCloseable !== true){
			return $isCloseable;
		}
	}
	// END PREVENT CLOSE
</script>	
<script type="text/javascript" defer="defer">	
	// SWFOBJECT
	var flashvars = new Object();
 	flashvars.view = "<?php echo $wp_query->query_vars['instID']; ?>"; 
 	flashvars.preview = "<?php echo $wp_query->query_vars['loID']; ?>"; 

	var params = new Object();
	params.menu = "false";
	params.allowScriptAccess = "sameDomain";
	params.allowFullScreen = "true";
	params.base = "/assets/flash/";
	params.bgcolor = "#869ca7";

	var attributes = new Object(); 
	attributes.id = "<?php echo $swf; ?>";
	attributes.name = "<?php echo $swf; ?>";

	swfobject.embedSWF( "/assets/flash/<?php echo $swf; ?>", "flexApp", "100%", "100%", "10",  "/assets/flash/expressInstall.swf", flashvars, params, attributes);
</script>
</head>
<body <?php body_class(); ?>>
