<?php
$ASE_timestamp = '1291228266';
$ASE_time = 'December 1, 2010, 1:31 pm';
$ASE_savedby = 'obo,,iturgeon,127.0.0.1';
$ASE_plugin_raw = <<<'NOWDOC'
a:11:{s:2:"id";s:1:"8";s:4:"name";s:14:"Markdown 1.1.7";s:11:"description";s:0:"";s:11:"editor_type";s:1:"0";s:8:"category";s:2:"10";s:10:"cache_type";s:1:"0";s:10:"plugincode";s:904:"/**
 *  Markdown Parser for MODx
 *  "by" Guillaume Grenier (I just changed 2 or 3 things...)
 *  A rip-off of the Textile plugin by Raymond Irving
 *
 *  Uses the PHP Markdown Extra parser by Michel Fortin
 *  <http://www.michelf.com/projects/php-markdown/>
 *  Markdown is by John Gruber
 *  <http://daringfireball.net/projects/markdown/>
 */ 

global $MarkdownObj;

$e = &$modx->Event;

switch ($e->name) {
	case "OnWebPagePrerender":	
		include_once($modx->config["base_path"].'/assets/plugins/markdown.php');
		$doc = $modx->documentOutput;
		preg_match_all("|<markdown>(.*)</markdown>|Uis",$doc,$matches);
		for ($i=0;$i<count($matches[0]);$i++) {
			$tag = $matches[0][$i];
			$text = Markdown($matches[1][$i]);
			$doc = str_replace($tag,$text,$doc);
		}
		$modx->documentOutput = $doc;
		break;
		
	default:	// stop here
		return; 
		break;	
}

return $markdown;";s:6:"locked";s:1:"0";s:10:"properties";s:0:"";s:8:"disabled";s:1:"0";s:10:"moduleguid";s:2:" ";}'
NOWDOC;
$ASE_plugin_map_to_event_raw = <<<'NOWDOC'
a:1:{i:0;a:3:{s:8:"pluginid";s:1:"8";s:5:"evtid";s:1:"3";s:8:"priority";s:1:"2";}}'
NOWDOC;
?>