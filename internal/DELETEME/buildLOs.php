<pre>
<?php
require_once(dirname(__FILE__)."/../app.php");


$DBM = \rocketD\db\DBManager::getConnection(new \rocketD\db\dbConnectData(\AppCfg::DB_HOST, \AppCfg::DB_USER, \AppCfg::DB_PASS, \AppCfg::DB_NAME, \AppCfg::DB_TYPE));
$DBM->startTransaction();

$API = \obo\API::getInstance();

$numruns = 1;
 

// $len = $numruns;
// $times = array();
// while($len--)
// {
// 
// 	$q = $DBM->query("SELECT ".\cfg_obo_LO::ID." FROM ".\cfg_obo_LO::TABLE);
// 	$t = microtime(1);
// 
// 	while($r = $DBM->fetch_obj($q))
// 	{
// 		$lo = getNormalLO($r->{\cfg_obo_LO::ID}, $DBM);
// 
// 	}
// 	$times[] = microtime(1) - $t;
// 
// }
// 
// echo 'detail structure: ' . array_sum($times)/count($times) . "s average for $numruns iterations\n";
// 

// end detailed mode


//lets insert our serialized data


// Test to make sure we dont have the updated objects

$qm = \obo\lo\QuestionManager::getInstance();
if(method_exists($qm, 'getQuestionNew'))
{
	exit("The php classes appear to be updated already, revert the php classes to the unaltered code to create the new LO structures");
}

if($_REQUEST['doit'] != 1)
{
	exit("!!! Make sure your server classes are still pulling lo's from the old tables: <a href=\"buildLOs.php?doit=1\">Run Conversion</a>");
}





$q = $DBM->querySafe("SELECT ".\cfg_obo_LO::ID." FROM ".\cfg_obo_LO::TABLE);
while($r = $DBM->fetch_obj($q))
{
	
	$lo = getNormalLO($r->{\cfg_obo_LO::ID}, $DBM);

	// $pageGroup = s($lo->pages);
	// $DBM->query("INSERT INTO lo_los_pages
	// SET
	// loID = '$lo->loID',
	// pageData = '$pageGroup'");
	
	// put each page into the new pages table
	foreach($lo->pages AS $index => $page)
	{
		$DBM->query("INSERT INTO lo_los_pages2 SET pageID ='$page->pageID',  pageData = '".s($page)."'");
	}
	
	// put each question into the new questions table
	foreach($lo->pGroup->kids AS $index => $question)
	{
		$s = s($question);
		$DBM->query("INSERT IGNORE INTO lo_los_questions
		SET
		questionID = '$question->questionID',
		questionData = '$s'");
	}
	
	// put each question into the new questions table
	foreach($lo->aGroup->kids AS $index => $question)
	{
		$s = s($question);
		$DBM->query("INSERT IGNORE INTO lo_los_questions
		SET
		questionID = '$question->questionID',
		questionData = '$s'");
		
	}
	
	// put the flatter LO's into the new lo table
	$DBM->query("INSERT INTO lo_los2
	SET
	loID = '$lo->loID',
	isMaster = '$lo->isMaster',
	title = '$lo->title',
	languageID = '$lo->languageID',
	notes = '$lo->notes',
	objective = '$lo->objective',
	learnTime = '$lo->learnTime',
	pGroupID = '".$lo->pGroup->qGroupID."',
	aGroupID = '".$lo->aGroup->qGroupID."',
	version = '$lo->version',
	subVersion = '$lo->subVersion',
	rootLoID = '$lo->rootID',
	parentLoID = '$lo->parentID',
	createTime = '$lo->createTime',
	copyright = '$lo->copyright',
	numPages = '".$lo->summary['contentSize']."',
	numPQuestions = '".$lo->summary['practiceSize']."',
	numAQuestions = '".$lo->summary['assessmentSize']."'");
	
}
$DBM->commit();

echo "done inserting serialized data\n";
echo "Now update Server Classes to build LO's from the new tables.\n";
echo "When your done, test a few LO's using the next script: <a href=\"testBuildLOs.php\">Test LOs</a>";


// $len = $numruns;
// $times = array();
// while($len--)
// {
// 
// 	$q = $DBM->query("SELECT loID FROM lo_los2");
// 
// 	$t = microtime(1);
// 	while($r = $DBM->fetch_obj($q))
// 	{
// 		$lo = getSerializedLO($r->loID, $DBM);
// 	}
// 	$times[] = microtime(1) - $t;
// }
// echo 'serialized structure: ' . array_sum($times)/count($times) . "s average for $numruns iterations\n";


function getNormalLO($loID, $DBM)
{
	$lo = new \obo\lo\LO();
	$lo->dbGetFull($DBM, $loID);
	return $lo;
}

function getSerializedLO($loID, $DBM)
{
	$q = $DBM->query("SELECT *,(SELECT data FROM lo_los_questions WHERE loID = L.loID AND type = 'p') AS practiceData, (SELECT data FROM lo_los_questions WHERE loID = L.loID AND type = 'a') AS assessmentData FROM lo_los2 AS L, lo_los_pages AS P WHERE P.loID = L.loID AND L.loID = $loID");

	if($r = $DBM->fetch_obj($q))
	{
		return new \obo\lo\LO($r->loID, $r->title, $r->languageID, 0, 0, $r->learnTime, $r->version, $r->subVersion, $r->rootLoID, $r->parentLoID, $r->createTime, $r->copyright, unserialize(base64_decode($r->pageData)), unserialize(base64_decode($r->practiceData)), unserialize(base64_decode($r->assessmentData)), array(), $perms);
	}
}

function s($obj)
{
	return  base64_encode(serialize($obj));
}

function uns($obj)
{
	return unserialize(base64_decode($obj));
}
?>