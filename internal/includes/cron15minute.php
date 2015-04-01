<?php
/* ==============

This file executes every 15 minutes, initiated by a cron job
Ex: 15,30,45,59 * * * * php cron15minute.php

This script is the main scheduled task runner

================ */
require_once(dirname(__FILE__)."/../app.php");

//******************************** CALCULATE VISIT LOGS ****************************
$t = microtime(1);
$VM = \obo\VisitManager::getInstance();
$count = $VM->calculateVisitTimes();
profile('cron', "'calculateVisitLogs','".time()."','".round((microtime(true) - $t),5)."','{$count}','{$count}'");
