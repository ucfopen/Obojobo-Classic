<?php
require_once(dirname(__FILE__)."/../internal/app.php");

$rssurl="http://twitter.com/statuses/user_timeline/14709413.rss";
$localfile = \AppCfg::DIR_BASE.\AppCfg::DIR_SCRIPTS.'rssCache/' . urlencode($rssurl); //Name cache file based on RSS URL
$cacheminutes=30; //typecast "cachetime" parameter as integer (0 or greater)


function fetchfeed(){
	global $rssurl, $localfile;
	$contents=file_get_contents($rssurl); //fetch RSS feed
	$fp=fopen($localfile, "w");
	fwrite($fp, $contents); //write contents of feed to cache file
	fclose($fp);
}

function outputrsscontent(){
	global $rssurl, $localfile, $cacheminutes;
	if (!file_exists($localfile)){ //if cache file doesn't exist
		touch($localfile); //create it
		chmod($localfile, 0666);
		fetchfeed(); //then populate cache file with contents of RSS feed
	}
	else if (((time()-filemtime($localfile))/60)>$cacheminutes) //if age of cache file great than cache minutes setting
	fetchfeed();
	header('Content-type: text/xml');
	readfile($localfile); //return the contents of the cache file
}

outputrsscontent();
?>