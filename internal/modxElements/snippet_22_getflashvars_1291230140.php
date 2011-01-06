<?php
$ASE_timestamp = '1291230140';
$ASE_time = 'December 1, 2010, 2:02 pm';
$ASE_savedby = 'obojobo.ucf.edu,,,132.170.240.85';
$ASE_snippet_raw = <<<'NOWDOC'
a:10:{s:2:"id";s:2:"22";s:4:"name";s:12:"getFlashvars";s:11:"description";s:61:"grabs GET variables and formats them to include in flash vars";s:11:"editor_type";s:1:"0";s:8:"category";s:1:"3";s:10:"cache_type";s:1:"0";s:7:"snippet";s:769:"
$output = '';

if(isset($getVars))
{
	$v = explode(',', $getVars);
	if(count($v) > 0)
	foreach($v AS $varName)
	{
		if(isset($_GET[$varName]))
		{
			$output .= 'flashvars.'.$varName.' = "'. $_GET[$varName] . '"; ';
		}
	}
}

if(isset($postVars))
{
	$v = explode(',', $postVars);
	if(count($v) > 0)
	foreach($v AS $varName)
	{
		if(isset($_POST[$varName]))
		{
			$output .= 'flashvars.'.$varName.' = "'. $_POST[$varName] . '"; ';
		}
	}
}

if(isset($cfgVars))
{
	$v = explode(',', $cfgVars);
	if(count($v) > 0)
	foreach($v AS $varName)
	{
		
		if(substr($varName, 0,2) != 'DB' &&  defined('AppCfg::'.$varName))
		{
			$output .= 'flashvars.'.$varName.' = "'. constant('AppCfg::'.$varName). '"; ';
		}
	}
}

return $output;
";s:6:"locked";s:1:"0";s:10:"properties";s:0:"";s:10:"moduleguid";s:2:" ";}'
NOWDOC;
?>