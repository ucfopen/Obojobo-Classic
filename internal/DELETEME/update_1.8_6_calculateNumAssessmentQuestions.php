<?php
require_once(dirname(__FILE__)."/../app.php");
$DBM = \rocketD\db\DBManager::getConnection(new \rocketD\db\dbConnectData(\AppCfg::DB_HOST, \AppCfg::DB_USER, \AppCfg::DB_PASS, \AppCfg::DB_NAME, \AppCfg::DB_TYPE));

$API = \obo\API::getInstance();
$API->getSessionValid();

$qGroups = array();
//$DBM->startTransaction();

echo "set ?run=1 to commit!";
echo '<pre>';
//9846
//Get all the questions from each qGroup:
$qStr = "SELECT Q.qGroupID, Q.childID, Q.itemOrder, A.questionIndex
FROM obo_map_questions_to_qgroup Q
LEFT JOIN obo_map_qalts_to_qgroup A
ON A.qGroupID = Q.qGroupID
AND Q.childID = A.questionID
WHERE Q.qGroupID IN
(
 SELECT DISTINCT L.aGroupID
 FROM obo_los L
)
ORDER BY Q.itemOrder";
$qGroups = array();
$q = $DBM->query($qStr);
while($r = $DBM->fetch_obj($q))
{
	if(count($qGroups[$r->qGroupID]) == 0)
	{
		$qGroups[$r->qGroupID] = array();
	}
	$qGroups[$r->qGroupID][] = $r;
}

//print_r($qGroups);

$newTotals = array();

foreach($qGroups as $qGroup)
{
	$total = 0;
	$lastIndex = -1;
	foreach($qGroup as $mapping)
	{
		/*
		if(!$mapping->questionIndex)
		{
			echo '';
		}
		else
		{
			echo 'no';
		}
		//exit();*/
		if($mapping->questionIndex == 0)
		{
			//echo('hey');
			$total++;
		}
		else
		{
			if($mapping->questionIndex != $lastIndex)
			{
				$total++;
			}
		}
		$lastIndex = $mapping->questionIndex;
	}
	
	if($mapping->questionIndex && ($mapping->questionIndex != $lastIndex))
	{
		$total++;
	}
	
	$newTotals[$mapping->qGroupID] = $total;
}

//print_r($newTotals);


$DBM->startTransaction();
foreach($newTotals as $key=>$val)
{
	$qStr = 'UPDATE obo_los SET numAQuestions = '.$val.' WHERE aGroupID = '.$key;
	echo $qStr."\n";
	$DBM->query($qStr);
}


if($_GET['run'] == 1)
{
	$DBM->commit();
}
else
{
	$DBM->rollBack();
}
?>