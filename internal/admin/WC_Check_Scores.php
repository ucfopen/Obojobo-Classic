<?php
$DBM = \rocketD\db\DBManager::getConnection(new \rocketD\db\DBConnectData(\AppCfg::DB_HOST,\AppCfg::DB_USER,\AppCfg::DB_PASS,\AppCfg::DB_NAME,\AppCfg::DB_TYPE));
//if(!$DBM->db_select('los_backup')) exit('unable to connect to backup database');

//Select all the records from plg_wc_grade_log

//look at instId and studentID, get all the attempts for that as well as the instance score method

//calculate the final score, compare to the score in 'score'

$qStr = "SELECT * FROM plg_wc_grade_log";
$result = $DBM->query($qStr);
$logs = array();
while($r = $DBM->fetch_obj($result))
{
	$logs[] = $r;
}

echo '<pre>';
//print_r($logs);

$SM = \obo\ScoreManager::getInstance();
$IM = \obo\lo\InstanceManager::getInstance();
$numErrors = 0;
foreach($logs as $log)
{
	
	$instData = $IM->getInstanceData($log->instID);
	
	$qStr = "SELECT * FROM obo_lo_instances WHERE instID=".$log->instID;
	//echo $qStr;
	$result = $DBM->query($qStr);
	$instance = $DBM->fetch_obj($result);
	//print_r($instance);
	$allScores = $SM->getScores($instData->instID, $log->studentID);
	$scores = $allScores[0]; // this returns an array of users, we just want the first one since we only asked for one
	//print_r($scores);
	//exit();
	$score = $SM->calculateUserOverallScoreForInstance($instData, $scores);
	if($score != $log->score)
	{
		echo "SCORE SENT TO WC: ".$log->score.", CALCULATED SCORE: ".$score."\n";
		print_r($log);
		echo "\n\n\n";
		$numErrors++;
	}
	//echo '('.$score.'='.$log->score.')';
}

echo "errors: ".$numErrors;
?>