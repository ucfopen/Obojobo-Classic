<pre>
<?php
require_once(dirname(__FILE__)."/../app.php");


$DBM = core_db_DBManager::getConnection(new core_db_dbConnectData(AppCfg::DB_HOST, AppCfg::DB_USER, AppCfg::DB_PASS, AppCfg::DB_NAME, AppCfg::DB_TYPE));
$DBM->startTransaction();

$API = nm_los_API::getInstance();

$numruns = 1;


// Test to make sure we dont have the updated objects

$qm = nm_los_QuestionManager::getInstance();
if(method_exists($qm, 'getQuestionNew'))
{
	exit("The php classes appear to be updated already, revert the php classes to the unaltered code to create the new LO structures");
}

if($_REQUEST['doit'] != 1)
{
	exit("!!! Make sure your server classes are still pulling lo's from the old tables: <a href=\"update_1.7_rebuildLOs.php?doit=1\">Run Conversion</a>");
}

// create pages 2
$DBM->query("DROP TABLE IF EXISTS obo_lo_pages");
$DBM->query("CREATE TABLE `obo_lo_pages` (
  `pageID` bigint(255) unsigned NOT NULL AUTO_INCREMENT,
  `pageData` blob NOT NULL,
  PRIMARY KEY (`pageID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=15148 ");


// create questions table
$DBM->query("DROP TABLE IF EXISTS obo_lo_questions");
$DBM->query("CREATE TABLE `obo_lo_questions` (
  `questionData` blob NOT NULL,
  `questionID` bigint(255) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`questionID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9004 ");

// new los table
$DBM->query("DROP TABLE IF EXISTS obo_los");
$DBM->query("CREATE TABLE `obo_los` (
  `loID` bigint(255) unsigned NOT NULL AUTO_INCREMENT,
  `isMaster` enum('0','1') NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `languageID` bigint(255) NOT NULL,
  `notes` longtext NOT NULL,
  `objective` longtext NOT NULL,
  `learnTime` int(11) NOT NULL DEFAULT '0',
  `pGroupID` bigint(255) unsigned NOT NULL,
  `aGroupID` bigint(255) unsigned NOT NULL,
  `version` int(20) unsigned NOT NULL DEFAULT '0',
  `subVersion` int(20) unsigned NOT NULL DEFAULT '0',
  `rootLoID` bigint(255) unsigned NOT NULL DEFAULT '0',
  `parentLoID` bigint(20) unsigned NOT NULL DEFAULT '0',
  `createTime` int(25) unsigned NOT NULL DEFAULT '0',
  `copyright` longtext NOT NULL,
  `numPages` int(10) unsigned NOT NULL,
  `numPQuestions` int(10) unsigned NOT NULL,
  `numAQuestions` int(10) unsigned NOT NULL,
  PRIMARY KEY (`loID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=15377 ");


$q = $DBM->querySafe("SELECT ".cfg_obo_LO::ID." FROM ".cfg_obo_LO::TABLE);
while($r = $DBM->fetch_obj($q))
{
	
	$lo = getNormalLO($r->{cfg_obo_LO::ID}, $DBM);

	// $pageGroup = s($lo->pages);
	// $DBM->query("INSERT INTO lo_los_pages
	// SET
	// loID = '$lo->loID',
	// pageData = '$pageGroup'");
	
	// put each page into the new pages table
	foreach($lo->pages AS $index => $page)
	{
		$DBM->query("INSERT Ignore INTO obo_lo_pages SET pageID ='$page->pageID',  pageData = '".s($page)."'");
	}
	
	// put each question into the new questions table
	foreach($lo->pGroup->kids AS $index => $question)
	{
		$s = s($question);
		$DBM->query("INSERT Ignore INTO obo_lo_questions
		SET
		questionID = '$question->questionID',
		questionData = '$s'");
	}
	
	// put each question into the new questions table
	foreach($lo->aGroup->kids AS $index => $question)
	{
		$s = s($question);
		$DBM->query("INSERT IGNORE INTO obo_lo_questions
		SET
		questionID = '$question->questionID',
		questionData = '$s'");
		
	}

	
	// put the flatter LO's into the new lo table
	$DBM->querySafe("INSERT Ignore INTO obo_los
	SET
	loID = '$lo->loID',
	isMaster = '".($lo->subVersion == 0 ? 1 : 0)."',
	title = '?',
	languageID = '$lo->languageID',
	notes = '?',
	objective = '?',
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
	numAQuestions = '".$lo->summary['assessmentSize']."'", $lo->title, $lo->notes, $lo->objective);
	
}
$DBM->commit();

echo "done inserting serialized data\n";
echo "Now: 1 - update Server Classes     AND     2 - Execute the SQL updates up to line 93\n";
echo "When your done, test a few LO's using the next script: <a href=\"testBuildLOs.php\">Test LOs</a>";


function getNormalLO($loID, $DBM)
{
	$lo = new nm_los_LO();
	$lo->dbGetFull($DBM, $loID);
	return $lo;
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