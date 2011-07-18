<?php
$ASE_timestamp = '1307477316';
$ASE_time = 'June 7, 2011, 4:08 pm';
$ASE_savedby = 'obojobo.ucf.edu,,,10.171.239.241';
$ASE_plugin_raw = <<<'NOWDOC'
a:11:{s:2:"id";s:2:"19";s:4:"name";s:18:"Page TOC Generator";s:11:"description";s:0:"";s:11:"editor_type";s:1:"0";s:8:"category";s:1:"3";s:10:"cache_type";s:1:"0";s:10:"plugincode";s:11580:"/**********************************************************************************************************************
Plugin Name: TOC Generator
Plugin URI:
Description: This plugin is used to automatically generate a table of contents from HTML headings on that page.
Version: V1.0 rewriten by Ian
Author: Samit Vartak
Author URI:

### INSTALLATION: ###

1. Create a new Plugin called "Page TOC Generator".
2. Paste in the PHP code of this file.
3. Go to the "System Events" tab
4. Check the box in front of "OnWebPagePrerender"

### HOW TO USE: ###

1. Create a page in MODx as usual
2. Add appropriate headings on that page to divide up the sections of the page into a sort of outline
3. In the HTML code of that page, add the configuration parameters (see below -- these look like normal HTML comments, but are parsed by the plugin).
4. Put the <!--#toc_plugin#_START_TOC_INDEXING--> tag at the spot where you want to begin indexing the page's headings.
5. Put the <!--#toc_plugin#_END_TOC_INDEXING--> tag where you want the indexing to stop.
6. Put the <!--#toc_plugin#_TOC_OUTPUT--> tag where you want the table of contents to show up.

### CONFIGURATION PARAMETERS: ###

<!--#toc_plugin#_START_CONFIGURATION-->  (optional)
<!--#toc_plugin#_list_type=ul-->                                                   sets the list type (ul/ol).
<!--#toc_plugin#_start_level=1-->                                                 Sets the starting heading level (h1-h6).
<!--#toc_plugin#_end_level=2-->                                                   Sets the ending heading level (h1-h6).
<!--#toc_plugin#_header=Page Contents-->                                Sets the text of the heading for table of contents.
<!--#toc_plugin#_header_tag=h2-->                                             Sets the tag which encloses the heading (could be a heading, <p>, <div>, etc.
<!--#toc_plugin#_parent_tag=div-->                                             Sets the parent tag which encloses table of contents. you can leave it blank.
<!--#toc_plugin#_parent_tag_id=toc-->                                        Sets the CSS id which is applied to the parent tag.
<!--#toc_plugin#_END_CONFIGURATION-->       (optional)


### SAMPLE USAGE: ###

<!--#toc_plugin#_START_CONFIGURATION--> 
<!--#toc_plugin#_list_type=ul-->                  
<!--#toc_plugin#_start_level=2-->                  
<!--#toc_plugin#_end_level=3-->                    
<!--#toc_plugin#_header=Page Contents-->    
<!--#toc_plugin#_header_tag=h2-->               
<!--#toc_plugin#_parent_tag=div-->              
<!--#toc_plugin#_parent_tag_id=toc-->              
<!--#toc_plugin#_END_CONFIGURATION--> 

<!--#toc_plugin#_TOC_OUTPUT-->

<!--#toc_plugin#_START_TOC_INDEXING-->
<h2>Here is a heading</h2>
<p>Here is some text</p>
<h2>Here is another heading</h2>
<h3>Here is a subheading</h3>
<h3>Here is another subheading</h3>
<!--#toc_plugin#_END_TOC_INDEXING-->


### EXPECTED OUTPUT: ###

<div id="toc">
<h2>Page Contents</h2> 
<ul>
    <li><a href="#Here is a heading_1">Here is a heading</a></li>
    <li><a href="#Here_is_another_heading_2">Here is another heading</a>
        <ul>
            <li><a href="#Here_is_a_subheading_3">Here is a subheading</a></li>
            <li><a href="#Here_is_another_subheading_4">Here is another subheading</a></li>
        </ul>
    </li>
</ul>
</div>

<h2><a name="Here is a heading_1" id="Here is a heading_1"></a>Here is a heading</h2>
<p>Here is some text</p>
<h2><a name="Here_is_another_heading_2" id="Here_is_another_heading_2"></a>Here is another heading</h2>
<h3><a name="Here_is_a_subheading_3" id="Here_is_a_subheading_3"></a>Here is a subheading</h3>
<h3><a name="Here_is_another_subheading_4" id="Here_is_another_subheading_4"></a>Here is another subheading</h3>


**********************************************************************************************************************/
//Information gathering
$source = &$modx->documentOutput; //fetching the page source

$search_content1 = '<!--#toc_plugin#_list_type=(.*)-->'; //fetching the list type
preg_match($search_content1, $source, $option1);
$setting1 = $option1[1];

$search_content2 = '<!--#toc_plugin#_start_level=(.*)-->'; //fetching the start level
preg_match($search_content2, $source, $option2);
$setting2 = $option2[1];

$search_content3 = '<!--#toc_plugin#_end_level=(.*)-->'; //fetching the end level
preg_match($search_content3, $source, $option3);
$setting3 = $option3[1];

$search_content4 = '<!--#toc_plugin#_header=(.*)-->'; //fetching the header text
preg_match($search_content4, $source, $option4);
$setting4 = $option4[1];

$search_content5 = '<!--#toc_plugin#_header_tag=(.*)-->'; //fetching the header tag which contains the header
preg_match($search_content5, $source, $option5);
$setting5 = $option5[1];

$search_content6 = '<!--#toc_plugin#_parent_tag=(.*)-->'; //fetching the tag which will enclose table of contents
preg_match($search_content6, $source, $option6);
$setting6 = $option6[1];

$search_content7 = '<!--#toc_plugin#_parent_tag_id=(.*)-->'; //fetching the CSS id which we want to apply to table of contents
preg_match($search_content7, $source, $option7);
$setting7 = $option7[1];

// Settings
$start_tag = "<!--#toc_plugin#_START_TOC_INDEXING-->"; 
$end_tag = "<!--#toc_plugin#_END_TOC_INDEXING-->"; //This plugin generates TOC for the source which is between <!--#toc_plugin#_START_TOC_INDEXING--> and <!--#toc_plugin#_END_TOC_INDEXING-->
$start_level = $setting2; //Starts with H1 tag
$end_level = $setting3; //Ends with h3 tag
$list_type = $setting1; //specify list type (ordered / unordered)
$header = $setting4; //Header for the TOC
$header_tag = $setting5; //The heading will be embeded in this tag
$parent_tag = $setting6; //the Table of contents will be embeded in this tag
$parent_tag_id = $setting7; //the id which gets applied to the parent tag
$test = "test"; //Use this variable for test purpose only....

//Functions
function strip_special_chars($val)
{
	$return_str = "";
	for($i=1; $i <= strlen($val); $i++)
	{
		if ( ((ord(substr($val, $i-1, 1)) >= 97) and (ord(substr($val, $i-1, 1)) <= 122)) or ((ord(substr($val, $i-1, 1)) >= 65) and (ord(substr($val, $i-1, 1)) <= 90)) or ((ord(substr($val, $i-1, 1)) >= 48) and (ord(substr($val, $i-1, 1)) <= 57)))
		{
			$return_str .= substr($val, $i-1, 1);
		}
		else if(ord(substr($val, $i-1, 1)) == 32) 
		{
			$return_str .= "_";		
		}
	}
	return($return_str);
}
//Plugin code starts
$search_content = '/[^|]('.$start_tag.')(.+?)('.$end_tag.')/sim';
preg_match($search_content, $source, $actual_source);
$content = $actual_source[0];
$search_header = '=<h['.$start_level.'-'.$end_level.'][^>]*>(.*)</h['.$start_level.'-'.$end_level.']>=siU';
preg_match_all($search_header, $content, $header_tags_info, PREG_SET_ORDER);

$named_anchors = array();
$header_tags = array();
foreach ($header_tags_info as $val) {
	array_push($header_tags,$val[0]);
	array_push($named_anchors,$val[1]);
}
$i = 0;
if ($header_tag!='')
	{
	$hx = "<".$header_tag.">".$header."</".$header_tag.">";
	}
else $hx='';
if($parent_tag == ""){
$initial_tag = $hx."\n"."<". $list_type ." id=\"".$parent_tag_id."\">";
$final_tag = "\n</".$list_type.">";
}else{
$initial_tag = "\n<". $parent_tag ." id=\"".$parent_tag_id."\">"."\n".$hx."\n<". $list_type .">";
$final_tag = "\n</".$list_type.">\n</".$parent_tag.">";
}
$display_content = $initial_tag;  
$prev_tag = "";
$prev_tag_value = 1;
$current_tag_pointer = "";
$tag_pattern = '=<h['.$start_level.'-'.$end_level.'][^>]*>=siU';
$h1=array();
$h2=array();
$h3=array();
$h4=array();
$h5=array();
$h6=array();
foreach($header_tags as $tags){
	$tag = "";
	$tag_value = 0;
	$replace_var = "";
	if (preg_match("/<h1/", $header_tags[$i])){
		//$tag = "<h1>";
		preg_match_all($tag_pattern, $header_tags[$i], $tag1, PREG_SET_ORDER);
		$tag2 = $tag1[0];
		$tag = $tag2[0];
		$tag_value = 1;
		$current_tag_pointer = &$h1;
	}
	elseif (preg_match("/<h2/", $header_tags[$i])) {
		//$tag = "<h2>";
		preg_match_all($tag_pattern, $header_tags[$i], $tag1, PREG_SET_ORDER);
		$tag2 = $tag1[0];
		$tag = $tag2[0];
		$tag_value = 2;
		$current_tag_pointer = &$h2;
	}
	elseif (preg_match("/<h3/", $header_tags[$i])) {
		//$tag = "<h3>";
		preg_match_all($tag_pattern, $header_tags[$i], $tag1, PREG_SET_ORDER);
		$tag2 = $tag1[0];
		$tag = $tag2[0];
		$tag_value = 3;
		$current_tag_pointer = &$h3;
	}
	elseif (preg_match("/<h4/", $header_tags[$i])) {
		//$tag = "<h4>";
		preg_match_all($tag_pattern, $header_tags[$i], $tag1, PREG_SET_ORDER);
		$tag2 = $tag1[0];
		$tag = $tag2[0];
		$tag_value = 4;
		$current_tag_pointer = &$h4;
	}
	elseif (preg_match("/<h5/", $header_tags[$i])){
		//$tag = "<h5>";
		preg_match_all($tag_pattern, $header_tags[$i], $tag1, PREG_SET_ORDER);
		$tag2 = $tag1[0];
		$tag = $tag2[0];
		$tag_value = 5;
		$current_tag_pointer = &$h5;
	}
	elseif (preg_match("/<h6/", $header_tags[$i])){
		//$tag = "<h6>";
		preg_match_all($tag_pattern, $header_tags[$i], $tag1, PREG_SET_ORDER);
		$tag2 = $tag1[0];
		$tag = $tag2[0];
		$tag_value = 6;
		$current_tag_pointer = &$h6;
	}
	$temp = $i + 1;
	$replace_var = $tag."<a name=\"". strip_special_chars(strip_tags($named_anchors[$i])). "_". $temp ."\" id=\"". strip_special_chars(strip_tags($named_anchors[$i])). "_". $temp."\"></a>".$named_anchors[$i]."</h".$tag_value.">";
	$source = str_replace($tags, $replace_var, $source);

	// is this tag nested below the previous?
	if($tag_value > $prev_tag_value){
		$depthDelta = $tag_value - $prev_tag_value;
		while($depthDelta){
			$display_content .= "\n<li><{$list_type}>";
			$depthDelta--;
		}
	}
	// this tag is above the previous
	else if($tag_value < $prev_tag_value){
		$depthDelta =  $prev_tag_value - $tag_value;
		while($depthDelta){
			$display_content .= "\n</{$list_type}></li>";
			$depthDelta--;
		}		
	}
	
	$display_content .=  "\n<li><a href=\"".$_SERVER["REQUEST_URI"]."#".strip_special_chars(strip_tags($named_anchors[$i]))."_".$temp."\">". strip_tags($named_anchors[$i]) ."</a></li>";
;
	$prev_tag = $tag;
	$prev_tag_value = $tag_value;
	$i++;
}

if(1 < $prev_tag_value){
	$depthDelta =  $prev_tag_value - 1;
	while($depthDelta){
		$display_content .= "\n</{$list_type}></li>";
		$depthDelta--;
	}		
}

$display_content .= $final_tag;

//Removing content from the scorce.
$source = preg_replace("/<!--#toc_plugin#_TOC_OUTPUT-->/", $display_content, $source);
$source = preg_replace("/<!--#toc_plugin#_START_CONFIGURATION-->/", "", $source);
$source = preg_replace("/<!--#toc_plugin#_list_type=".$setting1."-->/", "", $source);
$source = preg_replace("/<!--#toc_plugin#_start_level=".$setting2."-->/", "", $source);
$source = preg_replace("/<!--#toc_plugin#_end_level=".$setting3."-->/", "", $source);
$source = preg_replace("/<!--#toc_plugin#_header=".$setting4."-->/", "", $source);
$source = preg_replace("/<!--#toc_plugin#_header_tag=".$setting5."-->/", "", $source);
$source = preg_replace("/<!--#toc_plugin#_parent_tag=".$setting6."-->/", "", $source);
$source = preg_replace("/<!--#toc_plugin#_parent_tag_id=".$setting7."-->/", "", $source);
$source = preg_replace("/<!--#toc_plugin#_END_CONFIGURATION-->/", "", $source);
$source = preg_replace("/<!--#toc_plugin#_START_TOC_INDEXING-->/", " ", $source);
$source = preg_replace("/<!--#toc_plugin#_END_TOC_INDEXING-->/", " ", $source);
";s:6:"locked";s:1:"0";s:10:"properties";s:0:"";s:8:"disabled";s:1:"0";s:10:"moduleguid";s:2:" ";}'
NOWDOC;
$ASE_plugin_map_to_event_raw = <<<'NOWDOC'
a:1:{i:0;a:3:{s:8:"pluginid";s:2:"19";s:5:"evtid";s:1:"3";s:8:"priority";s:1:"3";}}'
NOWDOC;
?>