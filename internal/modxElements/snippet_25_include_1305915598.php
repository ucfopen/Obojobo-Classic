<?php
$ASE_timestamp = '1305915598';
$ASE_time = 'May 20, 2011, 2:19 pm';
$ASE_savedby = 'obojobo.ucf.edu,,,10.171.239.241';
$ASE_snippet_raw = <<<'NOWDOC'
a:10:{s:2:"id";s:2:"25";s:4:"name";s:7:"include";s:11:"description";s:44:"include a file [!include? &file='somefile'!]";s:11:"editor_type";s:1:"0";s:8:"category";s:1:"3";s:10:"cache_type";s:1:"0";s:7:"snippet";s:156:"
/*
 *	&file 	- if undefined will use PHP_SELF to detect the filename.
 */
 
if (file_exists($file))
{
	$include = include($file);
}

return '';
";s:6:"locked";s:1:"0";s:10:"properties";s:0:"";s:10:"moduleguid";s:2:" ";}'
NOWDOC;
?>