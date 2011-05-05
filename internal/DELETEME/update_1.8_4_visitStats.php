<?php;
require_once(dirname(__FILE__)."/../app.php");
$DBM = \rocketD\db\DBManager::getConnection(new \rocketD\db\dbConnectData(\AppCfg::DB_HOST, \AppCfg::DB_USER, \AppCfg::DB_PASS, \AppCfg::DB_NAME, \AppCfg::DB_TYPE));

// require('update_1.8_support_LogManager.php');
$TM = \obo\log\LogManager::getInstance();
$API = \obo\API::getInstance();
$API->getSessionValid();
$prev_instID = 0;
echo 'getting visits<br>';
// get all the visits (not calculated) that are atleast 6 hours old
$sql = "SELECT * FROM obo_log_visits  ORDER BY instID";
$q = $DBM->query($sql);
while($r = $DBM->fetch_obj($q))
{
	echo 'visit Found<br>';
	flush();
	$visit = $r;
	if($prev_instID != $visit->instID)
	{
		$track = $TM->getInteractionLogByInstance($visit->instID);
	}
	
	$prev_instID = $visit->instID;
	
	foreach($track['visitLog'] AS $vLog)
	{
		if($vLog['visitID'] == $visit->visitID)
		{
			echo 'visit Time Logged<br>';
			flush();
			// update the db
			$DBM->querySafe("UPDATE obo_log_visits SET overviewTime = '?', contentTime = '?', practiceTime = '?', assessmentTime = '?' WHERE visitID = '?'", $vLog['sectionTime']['overview'], $vLog['sectionTime']['content'], $vLog['sectionTime']['practice'], $vLog['sectionTime']['assessment'], $visit->visitID);
			break;
		}
	}
}

?>