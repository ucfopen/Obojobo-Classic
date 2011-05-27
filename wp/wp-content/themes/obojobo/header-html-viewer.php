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
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js"></script>
<script type="text/javascript" src="/assets/js/viewer.js"></script>
<script type="text/javascript" src="/assets/js/jquery.history.js"></script>
<link rel="stylesheet" href="/assets/css/viewer.css"> 
<?php wp_head(); ?>
<script type="text/javascript" defer="defer">	
var loID = <?php echo $wp_query->query_vars['loID']; ?>; 
</script>
</head>
<body <?php body_class(); ?>>