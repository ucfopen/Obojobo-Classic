<?php
$DBM = \rocketD\db\DBManager::getConnection(new \rocketD\db\DBConnectData(\AppCfg::DB_HOST, \AppCfg::DB_USER, \AppCfg::DB_PASS, \AppCfg::DB_NAME, \AppCfg::DB_TYPE));

$TM = new \obo\log\LogManager();
$AM = new \rocketD\auth\AuthManager();
$time = microtime(1);

echo '<h1>Choose one:</h1><form action="'. $_SERVER['PHP_SELF'] .'" method="get" accept-charset="utf-8">
	<ul>
	<li><label for="nid">NID</label><input type="text" name="nid" value="" id="nid">
	'.rocketD_admin_tool_get_form_page_input().'</li>
	<li><label for="uid">UID</label><input type="text" name="uid" value="" id="uid">
	'.rocketD_admin_tool_get_form_page_input().'</li>
	<li><label for="inst_id">InstID</label><input type="text" name="inst_id" value="" id="inst_id">
	'.rocketD_admin_tool_get_form_page_input().'</li>
	</ul>
	<p><input type="submit" value="Continue &rarr;"></p>
</form>';

echo '<pre>';

if($_GET['nid'])
{
	$u = $AM->fetchUserByUserName($_GET['nid']);
	$_GET['uid'] = $u->userID;
}

if($_GET['uid'])
{
	if($_GET['inst_id'])
	{
		echo "tracking INSTANCE: {$_GET['inst_id']},  UID: {$_GET['uid']}\n";
		$track = $TM->getInteractionLogByUserAndInstance($_GET['inst_id'], $_GET['uid']);
	}
	else
	{
		echo "tracking UID: {$_GET['uid']}\n";
		$track = $TM->getInteractionLogByUser($_GET['uid']);
	}
}
elseif($_GET['lo_id'])
{

	
	
}
elseif($_GET['inst_id'])
{
	echo "tracking INSTID: {$_GET['inst_id']}\n";
	$track = $TM->getInteractionLogByInstance($_GET['inst_id']);
}
else
{
	echo "else";
	// $track = $TM->getInteractionLogTotals($_REQUEST['inst_id']);
}

if($_GET['formatTime'] == true)
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

?>
</pre>