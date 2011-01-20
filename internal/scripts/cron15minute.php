<?php
/* ==============

This file executes every 15 minutes, initiated by a cron job
Ex: 15,30,45,59 * * * * php cron15minute.php

This script is the main scheduled task runner

================ */
require_once(dirname(__FILE__)."/../app.php");


//******************************** SYNC FAILURE QUEUE ****************************
$t = microtime(1);
$PM = core_plugin_PluginManager::getInstance();
$result = $PM->callAPI('UCFCourses', 'sendFailedScoreSetRequests', array(), true);
core_util_Log::profile('cron', "'sendFailedScoreSetRequests','".time()."','".round((microtime(true) - $t),5)."','{$result['updated']}','{$result['total']}'\n");

//******************************** NID UPDATES ****************************
$t = microtime(1);
$AM = core_auth_AuthManager::getInstance();
$authMods = $AM->getAllAuthModules();
foreach($authMods AS $curAuthMod)
{
	if(method_exists($curAuthMod, 'updateNIDChanges'))
	{
		$result = $curAuthMod->updateNIDChanges();
		core_util_Log::profile('cron', "'updateNIDChanges','".time()."','".time()."','".round((microtime(true) - $t),5)."','{$result['updated']}','{$result['total']}'\n");
	}
}


?>