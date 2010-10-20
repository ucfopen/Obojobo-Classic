<?php
require_once('config/cfgDefault.php'); // default config
require_once('config/cfgLocal.php'); // local config

/*

	*** set up global functions and dynamic settings ***

*/

// set the error log file
ini_set('error_log', AppCfg::DIR_BASE.AppCfg::DIR_LOGS.'php_errors_'. date('m_d_y', time()) .'.txt');


ini_set('display_errors',0);
ini_set('log_errors',1);
spl_autoload_register('classAutoLoader');
define ("CONFIG_ROOT", dirname(__FILE__) . '/config/');

function trace($arg, $force=0, $incbacklog=0)
{
	core_util_Log::trace($arg, $force, $incbacklog+1);
}

function classAutoLoader($className)
{
	// look at the first 4 characters to determine if its a plugin, config, or regular class file
	$prefix = substr($className, 0, 4);
	switch($prefix)
	{
		case 'plg_': // look in the plugin dir EX: plugin_UCFCourses_UCFCoursesAPI.class.php
			// convert plugins_pluginName_ClassName to plugins/pluginName/classes/ClassName.class.php
			$pkgs = explode("_", $className);
			$file = AppCfg::DIR_BASE . AppCfg::DIR_PLUGIN.$pkgs[1].'/classes/';
			unset($pkgs[0]);
			unset($pkgs[1]);
			$file = $file . implode('/', $pkgs) . '.class.php';
			break;
		case 'cfg_': // look in the config dir EX: config_plugin_UCFCourses
			$file = substr($className, 4);
			$file = CONFIG_ROOT . '/' .str_replace('_', '/', $file) . '.class.php';
			break;
		default: // Look in the app classes dir EX: core_auth_AuthModule
			$file = AppCfg::DIR_BASE . AppCfg::DIR_CLASSES . str_replace('_', '/', $className) . '.class.php';
	}

	
	// try to include
	if(!@include($file))
	{
		// log error on failure
		error_log('AutoLoad Failed to include '.$file);
	}
}
?>