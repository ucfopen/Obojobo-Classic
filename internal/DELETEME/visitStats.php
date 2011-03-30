<?php
require_once(dirname(__FILE__)."/../app.php");
$DBM = \rocketD\db\DBManager::getConnection(new \rocketD\db\dbConnectData(\AppCfg::DB_HOST, \AppCfg::DB_USER, \AppCfg::DB_PASS, \AppCfg::DB_NAME, \AppCfg::DB_TYPE));

$TM = new \obo\log\LogManager();
$API = \obo\API::getInstance();
$API->getSessionValid();
$prev_instID = 0;

// get all the visits (not calculated) that are atleast 6 hours old
$sql = "SELECT * FROM obo_log_visits WHERE overviewTime = 0 AND contentTime = 0 AND practiceTime = 0 AND assessmentTime = 0 AND createTime < ". (time() -21600)  ." ORDER BY instID DESC";
$q = $DBM->query($sql);
while($r = $DBM->fetch_obj($q))
{
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
			// update the db
			$DBM->querySafe("UPDATE obo_log_visits SET overviewTime = '?', contentTime = '?', practiceTime = '?', assessmentTime = '?' WHERE visitID = '?'", $vLog['sectionTime']['overview'], $vLog['sectionTime']['content'], $vLog['sectionTime']['practice'], $vLog['sectionTime']['assessment'], $visit->visitID);
			break;
		}
	}
}
?>