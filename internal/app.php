<?php
//namespace RD;
require_once('config/cfgLocal.php'); // local config

/*

	*** set up global functions and dynamic settings ***

*/

// set the error log file
ini_set('error_log', \AppCfg::DIR_BASE.\AppCfg::DIR_LOGS.'php_errors_'. date('m_d_y', time()) .'.txt');


ini_set('display_errors',0);
ini_set('log_errors',1);
spl_autoload_register('classAutoLoader');
define ("CONFIG_ROOT", dirname(__FILE__) . '/config/');

function trace($arg, $force=0, $incbacklog=0)
{
	\rocketD\util\Log::trace($arg, $force, $incbacklog+1);
}

function classAutoLoader($className)
{
	

	// classname is using namespaces
	if(strpos($className , '\\') !== false)
	{
		
		$file = \AppCfg::DIR_BASE . \AppCfg::DIR_CLASSES  . str_replace('\\', '/', $className) . '.class.php';
	}
	// classname is using old nm_class package notation
	else
	{
		$prefix = substr($className, 0, 4);
		switch($prefix)
		{
			case 'plg_': // look in the plugin dir EX: plugin_UCFCourses_UCFCoursesAPI.class.php
				// convert plugins_pluginName_ClassName to plugins/pluginName/classes/ClassName.class.php
				$pkgs = explode("_", $className);
				$file = \AppCfg::DIR_BASE . \AppCfg::DIR_PLUGIN.$pkgs[1].'/classes/';
				unset($pkgs[0]);
				unset($pkgs[1]);
				$file = $file . implode('/', $pkgs) . '.class.php';
				break;
			case 'cfg_': // look in the config dir EX: config_plugin_UCFCourses
				$file = substr($className, 4);
				$file = CONFIG_ROOT . '/' .str_replace('_', '/', $file) . '.class.php';
				break;
			default: // Look in the app classes dir EX: \rocketD\auth\AuthModule
				// log the class that wasn't found
				if( ! preg_match('/^Smarty/', $className)) error_log($className);
				$file = \AppCfg::DIR_BASE . \AppCfg::DIR_CLASSES . str_replace('_', '/', $className) . '.class.php';
		}
	}
	// try to include
	if(!@include($file))
	{
		// log error on failure
		if(\AppCfg::DEBUG_MODE == true)
		{
			@$dt = debug_backtrace(3);
			error_log('autoload failed to load class "'. basename($className). " file $file");
			if(count($dt) > 3)
			{
				error_log(' -> referenced from ' . basename($dt[2]['file']) . '#' . $dt[2]['line'] . ' -> ' . $dt[2]['function']);
			}
		}
	}
}
?>
