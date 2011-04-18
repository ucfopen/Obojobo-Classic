<?php
// Run upgrades programatically
require_once(dirname(__FILE__)."/../app.php");
$DBM = \rocketD\db\DBManager::getConnection(new \rocketD\db\dbConnectData(\AppCfg::DB_HOST, \AppCfg::DB_USER, \AppCfg::DB_PASS, \AppCfg::DB_NAME, \AppCfg::DB_TYPE));

// set a few environment variables

ini_set('max_execution_time', '500'); //Maximum execution time of each script, in seconds
ini_set('max_input_time', '500'); //Maximum amount of time each script may spend parsing request data
ini_set('memory_limit', '1624M'); //Maximum amount of memory a script may consume (8MB)

// start output buffering
ob_start();

// file that contains all the sql and script timing, executed in order
$upgradeManifest = 'upgradeActions.txt';

$actionList = array(); // Place the action list into this array
// TODO: break up upgradeActions into an index array

// current action to execute - either a script or a sql query
$currentActionIndex = 0;

// locate the next action to execute
if($_SESSION['currentAction'])
{
	$previousActionIndex = $_SESSION['currentActionIndex'];
	$currentActionIndex = $previousActionIndex + 1;
}
$_SESSION['currentActionIndex'] = $currentActionIndex;

// Execute an SQL Query
if($actionList[$currentActionIndex]['actionType'] == 'sql')
{
	$DBM->query($actionList[$currentActionIndex]['query']);
}

// Execute a PHP Script
elseif($actionList[$currentActionIndex]['actionType'] == 'php')
{
	require($actionList[$currentActionIndex]['scriptFile']);
}

// Reload the page if there are more actions left
if(count($actionList) > $currentActionIndex)
{
	echo '<meta http-equiv="refresh" content="5;update.php" />';
}
// flush the output buffer
ob_flush();
?>