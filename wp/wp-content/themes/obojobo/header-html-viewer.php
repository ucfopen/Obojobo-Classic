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
<?php wp_head(); ?>
<script type="text/javascript" defer="defer">	
// REMOTE - GET USER
var loID = <?php echo $wp_query->query_vars['loID']; ?>; 

function makeCall(method, callback, arguments)
{
	console.log(method);
	jQuery.ajax({
		url: "http://obo/assets/gateway-json.php/loRepository."+method+"/"+arguments.join("/"),
		context: document.body,
		dataType: 'json',
		success: callback
	});		
}

jQuery(window).load(function()
{
	
	makeCall('getLOMeta', onGetLOMeta, [loID]);
	makeCall('getLoginOptions', onGetLoginOptions , []);
	makeCall('getSessionValid', onGetSessionValidInit, []);

	// PLACE RESULTS INTO THE SELECT BOX
	function onGetLOMeta(metaLO)
	{
		// console.log(metaLO);
		$("#title").text(metaLO.title);
		$("#version").text(metaLO.version + '.' + metaLO.subVersion);
		$("#language").text(metaLO.languageID);
		$("#objective").append(cleanFlashHTML(metaLO.objective));
		$("#learn-time").text(metaLO.learnTime);
		$("#key-words").text(metaLO.keywords.join(", "));
		$("#content-size").text(metaLO.summary.contentSize);
		$("#practice-size").text(metaLO.summary.practiceSize);
		$("#assessment-size").text(metaLO.summary.assessmentSize);
	}
	
	function onGetLoginOptions(result){}

	function onGetSessionValidInit(result)
	{
		// just go ahead and get the full lo
		makeCall('getLO', onGetLO, [loID]);
	}
	
	function onGetLO(result)
	{
		$(result.pages).each(function(index, val){
			
			$('#content-pages').append('<div id="content-'+index+'" class="content-page page-layout-'+val.layoutID+'"><h3>' + val.title+ ' (layout '+val.layoutID+')</h3></div>');
			var page = $('#content-'+index);
			$(val.items).each(function(itemIndex, item){
				// console.log(item.component);
				switch(item.component)
				{
					case 'MediaView':
						page.append(formatPageItemMediaView(item));
						activateSWFs();
						break;
					case 'TextArea':
						page.append(formatPageItemTextArea(item));
						break;
				}
				
			});
		});
		
		$(result.pGroup.kids).each(function(index, val){
			var page = '<h3>' + val.questionID+ '</h3>';
			$('#practice-pages').append(page);
		});
		
		$(result.aGroup.kids).each(function(index, val){
			var page = '<h3>' + val.questionID+ '</h3>';
			$('#assessment-pages').append(page);
		});
	}
	
	function activateSWFs(){
		
		var flashvars = new Object();
		
		var params = new Object();
		params.menu = "false";
		params.allowScriptAccess = "sameDomain";
		params.allowFullScreen = "true";
		params.bgcolor = "#869ca7";
		
		
		$('.swf').each(function(index, val){
			var mediaID = val.id.split('media-')[1];
			
			swfobject.embedSWF( "/media/"+mediaID, 'media-'+mediaID, parseInt($(val).css('width')), parseInt($(val).css('height')), "10",  "/assets/flash/expressInstall.swf", flashvars, params);
		});
		

	}
	
	function formatPageItemTextArea(pageItem)
	{
		var pageItemHTML = $('<div class="page-item"></div>');
		pageItemHTML.append(cleanFlashHTML(pageItem.data));
		return pageItemHTML;
	}
	
	function formatPageItemMediaView(pageItem)
	{
		var pageItemHTML = $('<div class="page-item"></div>');
		pageItemHTML.append(displayMedia(pageItem.media[0]));
		return pageItemHTML;
	}
	
	
	
	function displayMedia(mediaItem)
	{
		var mediaHTML = $('<div class="mediaItem"></div>');
		switch(mediaItem.itemType)
		{
			case 'pic':
				mediaHTML.append('<img id="media-'+mediaItem.mediaID+'" class="pic" src="/media/'+ mediaItem.mediaID +'" title="'+mediaItem.title+'" alt="'+ mediaItem.title +'">');
				break;
			case 'swf':
				mediaHTML.append('<div id="media-'+mediaItem.mediaID+'" class="swf" style="height:'+mediaItem.height+'px;width:'+mediaItem.width+'px;">SWF '+mediaItem.title+'</div>');
				break;
			case 'flv':
				mediaHTML.append('<div id="media-'+mediaItem.mediaID+'" class="flv" style="height:'+mediaItem.height+'px;width:'+mediaItem.width+'px;background-color:#ccc;">FLV '+mediaItem.title+'</div>');
				break;
			
		}
		return mediaHTML
	}
	
	function cleanFlashHTML(input)
	{
		// console.log(input);
		
		// get rid of all the textformat tags
		var pattern = /<\/?textformat\s?.*?>/gi;
		input = input.replace(pattern, "");

		// combine <p><font>...</font></p> tags to just <p></p>
		pattern = /<p\s?(.*?)><font.*?(?:FACE="(\w+)").*?(?:SIZE="(\d+)").*?(?:COLOR="(#\d+)").*?>/gi;
		// input = input.replace(pattern, '<p style="font-family:$2;font-size:$3px;color:$4;">');
		input = input.replace(pattern, '<p>');

		pattern = /<\/font><\/p>/gi;
		input = input.replace(pattern, "</p>");

		// convert lone <font>...</font> tags to spans
		pattern = /<font.*?(?:KERNING="\d+")?.*?(?:FACE="(\w+)")?.*?(?:SIZE="(\d+)")?.*?(?:COLOR="(#\d+)")?.*?>/gi;
		// input = input.replace(pattern, '<span style="font-family:$1;font-size:$2px;color:$3;">');
		input = input.replace(pattern, '<span>');

		pattern = /<\/font>/gi;
		input = input.replace(pattern, "</span>");

		// find empty tags keeping space in them
		pattern = /<(\w+?)[^>]*?>(\s*?)<\/\1>/gi;
		input = input.replace(pattern, "$2");
		
		pattern = /<(\w+)>(\s*?)<\/\1>/gi;
		input = input.replace(pattern, "$2");

		// remove any previously added ul tags
		pattern = /<\/?ul>/gi;
		input = input.replace(pattern, "");
		
		// add <ul></ul> arround list items
		pattern = /<LI>([\s\S]*?)<\/LI>/gi;
		input = input.replace(pattern, "<ul><li>$1</li></ul>");
		
		// kill extra </ul><ul> that are back to back - this will make proper lists
		pattern = /<\/ul><ul>/gi;
		input = input.replace(pattern, "");
		
		// console.log(input);

		return input;
	}
		
	
});
</script>

<style type="text/css" media="screen">

</style>
</head>
<body <?php body_class(); ?>>