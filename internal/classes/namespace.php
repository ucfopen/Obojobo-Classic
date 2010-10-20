<?php
spl_autoload_register('nmNameSpaceAutoloader');
define ("CLASS_ROOT", dirname(__FILE__).'/');

//function __autoload ($className)
function nmNameSpaceAutoloader($className)
{
	$packageSplit = CLASS_ROOT . str_replace('_', '/', $className) . '.class.php';
	// try to include
	if(!@include($packageSplit))
	{
		// log error on failure
		error_log('AutoLoad Failed to include '.$packageSplit);
	}
}
?>