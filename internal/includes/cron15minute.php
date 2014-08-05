<?php
/* ==============

This file executes every 15 minutes, initiated by a cron job
Ex: 15,30,45,59 * * * * php cron15minute.php

This script is the main scheduled task runner

================ */
require_once(dirname(__FILE__)."/../app.php");
ini_set('error_log', AppCfg::DIR_BASE.AppCfg::DIR_LOGS.'php_cron_'. date('m_d_y', time()) .'.txt');

//******************************** CALCULATE VISIT LOGS ****************************
$t = microtime(1);
$VM = \obo\VisitManager::getInstance();
$count = $VM->calculateVisitTimes();
\rocketD\util\Log::profile('cron', "'calculateVisitLogs','".time()."','".round((microtime(true) - $t),5)."','{$count}','{$count}'");


?>
