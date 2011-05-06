<pre>
<?php
require_once(dirname(__FILE__)."/../../../internal/app.php");

$DBM = \rocketD\db\DBManager::getConnection(new \rocketD\db\dbConnectData(\AppCfg::DB_HOST, \AppCfg::DB_USER, \AppCfg::DB_PASS, \AppCfg::DB_NAME, \AppCfg::DB_TYPE));

$TM = new \obo\log\LogManager();
$time = microtime(1);
if($_REQUEST['uid'])
{
	if($_REQUEST['inst_id'])
	{
		echo "tracking INSTANCE: {$_REQUEST['inst_id']},  UID: {$_REQUEST['uid']}\n";
		$track = $TM->getInteractionLogByUserAndInstance($_REQUEST['inst_id'], $_REQUEST['uid']);
	}
	else
	{
		echo "tracking UID: {$_REQUEST['uid']}\n";
		$track = $TM->getInteractionLogByUser($_REQUEST['uid']);
	}
}
elseif($_REQUEST['lo_id'])
{

	
	
}
elseif($_REQUEST['inst_id'])
{
	echo "tracking INSTID: {$_REQUEST['inst_id']}\n";
	$track = $TM->getInteractionLogByInstance($_REQUEST['inst_id']);
}
else
{
	echo "else";
	
	$track = $TM->getInteractionLogTotals($_REQUEST['inst_id']);
}

if($_REQUEST['formatTime'] == true)
{
	foreach($track['visitLog'] AS $key => $visitLog)
	{
		$track['visitLog'][$key]['createTime'] = date("r ({$visitLog['createTime']})", $visitLog['createTime']);
		foreach($visitLog['logs'] AS $key2 => $log)
		{
			$visitLog['logs'][$key2]->createTime = date("r ({$log->createTime})" , $log->createTime);
		}
	}
}
echo (microtime(1) - $time) . "\n";
print_r($track);
//echo "\n" . (microtime(1) - $time) . "\n";

?>