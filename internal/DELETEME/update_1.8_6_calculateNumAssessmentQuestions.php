<?php
require_once(dirname(__FILE__)."/../app.php");
$DBM = \rocketD\db\DBManager::getConnection(new \rocketD\db\dbConnectData(\AppCfg::DB_HOST, \AppCfg::DB_USER, \AppCfg::DB_PASS, \AppCfg::DB_NAME, \AppCfg::DB_TYPE));

$API = \obo\API::getInstance();
$API->getSessionValid();

$qGroups = array();
//$DBM->startTransaction();

echo "set ?run=1 to commit!";

$sql = "
	SELECT A.qGroupID, A.questionID, A.questionIndex, B.itemOrder
	FROM  `obo_map_qalts_to_qgroup` AS A, `obo_map_questions_to_qgroup` AS B
	WHERE questionIndex < 0
	AND A.questionID = B.childID
	AND A.qGroupID = B.qGroupID
	ORDER BY A.qGroupID, B.itemOrder";
	
$q = $DBM->query($sql);
while($r = $DBM->fetch_obj($q))
{
	if(count($qGroups[$r->qGroupID]) == 0)
	{
		$qGroups[$r->qGroupID] = array();
	}
	$qGroups[$r->qGroupID][] = $r;
}

foreach($qGroups as $qGroup)
{
	//print_r($qGroup);
	
	$firstItemOrder = $qGroup[0]->itemOrder;
	$lastQuestionIndex = $qGroup[0]->questionIndex;
	$toChange = array();
	$numCompressed = 0;
	for($i = 0; $i < count($qGroup); $i++)
	{
		if($lastQuestionIndex != $qGroup[$i]->questionIndex)
		{
			for($j = 0; $j < count($toChange); $j++)
			{
			//	echo 'set qGroup['.$j.']->newQuestionIndex = '.$firstItemOrder;
				$qGroup[$toChange[$j]]->newQuestionIndex = $firstItemOrder + 1 - $numCompressed;
			}
			$firstItemOrder = $qGroup[$i]->itemOrder;
			$numCompressed += count($toChange) - 1;
			$toChange = array();
		}
		//echo 'push '.$i.' to toChange  ';
		$toChange[] = $i;
		
		$lastQuestionIndex = $qGroup[$i]->questionIndex;
	}
	
	for($j = 0; $j < count($toChange); $j++)
	{
		$qGroup[$toChange[$j]]->newQuestionIndex = $firstItemOrder + 1 - $numCompressed;
	}
}

echo '<pre>';
//////print_r($qGroups);
//exit();
$DBM->startTransaction();

foreach($qGroups as $qGroup)
{
	foreach($qGroup as $mapping)
	{
		$qStr = 'UPDATE obo_map_qalts_to_qgroup SET questionIndex='.$mapping->newQuestionIndex.' WHERE qGroupID='.$mapping->qGroupID.' AND questionID='.$mapping->questionID;
		echo $qStr;
		echo "\n";
		$DBM->query($qStr);
	}
}

//Now we want to make sure that the orders are correct
$qStr = "
SELECT obo_map_questions_to_qgroup.qGroupID, obo_map_questions_to_qgroup.childID, obo_map_questions_to_qgroup.itemOrder, obo_map_qalts_to_qgroup.questionIndex
FROM obo_map_questions_to_qgroup
LEFT JOIN obo_map_qalts_to_qgroup
ON obo_map_questions_to_qgroup.childID = obo_map_qalts_to_qgroup.questionID
AND obo_map_questions_to_qgroup.qGroupID = obo_map_qalts_to_qgroup.qGroupID
ORDER BY obo_map_questions_to_qgroup.qGroupID, obo_map_questions_to_qgroup.itemOrder";
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

////////print_r($qGroups);

$num = 0;
echo count($qGroups);
$numErrors = 0;

foreach($qGroups as $qGroup)
{
	$correctQuestionIndex = 0;
	$lastQuestionIndex = -999;
	$correctData = true;
	
	foreach($qGroup as $mapping)
	{
		if($mapping->questionIndex < 0)
		{
				echo "ERROR\n";
				echo "Negative index for:\n";
				print_r($mapping);
				echo "For QGroup:\n";
				print_r($qGroup);

				echo 'QUITTING';
				$numErrors++;
				exit();
				// 
		}
		
		if($mapping->questionIndex == 0 || $mapping->questionIndex != $lastQuestionIndex)
		{
			$correctQuestionIndex++;
		}
		
		if($mapping->questionIndex > 0 && $mapping->questionIndex != $correctQuestionIndex)
		{
			echo "ERROR\n";
			echo "Incorrect index for:\n";
			print_r($mapping);
			echo "It should be ".$correctQuestionIndex."\n";
			echo "For QGroup:\n";
			print_r($qGroup);
			
			echo 'QUITTING';
			$numErrors++;
			exit();
		}
		
		$lastQuestionIndex = $mapping->questionIndex;
	}
}

echo "\n\n\n";
echo $numErrors;

if($_GET['run'] == 1)
{
	$DBM->commit();
}
else
{
	$DBM->rollBack();
}
?>