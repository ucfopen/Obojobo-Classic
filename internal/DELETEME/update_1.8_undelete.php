<?php
require_once(dirname(__FILE__)."/../app.php");

$DBM = \rocketD\db\DBManager::getConnection(new \rocketD\db\dbConnectData(\AppCfg::DB_HOST,\AppCfg::DB_USER,\AppCfg::DB_PASS,\AppCfg::DB_NAME,\AppCfg::DB_TYPE));
$DBE = new \rocketD\db\DBEnabled();

$sql = "SELECT *, UNCOMPRESS(cache) AS unc FROM obo_deleted_los";
$inSQL = "INSERT IGNORE INTO obo_los (loID, isMaster, title, languageID, notes, objective, learnTime, pGroupID, aGroupID, version, subVersion, rootLoID, parentLoID, createTime, copyright, numPages, numPQuestions, numAQuestions, deleted)
VALUES ('?','?','?','?','?','?','?','?','?','?','?','?','?','?','?','?','?','?', '1')";

$q = $DBM->query($sql);
while($r = $DBM->fetch_obj($q))
{
	unset($r->cache);
	$lo = $DBE->db_unserialize($r->unc);
	// print_r($lo);
	echo "$r->loID<br>";
	
	$pGroupID = isset($lo->pGroup->id) ? $lo->pGroup->id : $lo->pGroup->qGroupID;
	$aGroupID = isset($lo->aGroup->id) ? $lo->aGroup->id : $lo->aGroup->qGroupID;
	
	$DBM->querySafe($inSQL, $r->loID, 0, $r->title, 1, '', is_object($lo->objective) ? $lo->objective->text : $lo->objective, $lo->learnTime, $pGroupID, $aGroupID, isset($lo->vers_whole) ? $lo->vers_whole : $lo->version, isset($lo->vers_part) ? $lo->vers_part : $lo->subVersion, isset($lo->root) ? $lo->root : $lo->rootID, isset($lo->parent) ? $lo->parent : $lo->parentID, isset($lo->datec) ? $lo->datec : $lo->createTime, '', count($lo->pages), count($lo->pGroup->kids), count($lo->aGroup->kids), 1 );
	checkandMakePages($DBM, $lo->pages, $r->loID);
	checkQGroup($DBM, $lo->aGroup);
	checkQGroup($DBM, $lo->pGroup);
}
echo 'done';

function checkQGroup($DBM, $qgroup)
{
	$QGM = \obo\lo\QuestionGroupManager::getInstance();
	if(!$QGM->getGroup($qgroup->qGroupID))
	{
		// add the qgroup, add the child questions
		$sql = "INSERT INTO obo_lo_qgroups SET qGroupID = '?', userID = '?', name='?', rand='?', allowAlts='?', altMethod='?'";
		echo 'qgroup missing';
		$DBM->querySafe($sql, $qgroup->qGroupID, $qgroup->userID, '', $qgroup->rand, $qgroup->allowAlts, $qgroup->altMethod);
	}

	checkandMakeQuestion($DBM, $qgroup);
}


function checkandMakePages($DBM, $pages, $loID)
{
	$PM = \obo\lo\PageManager::getInstance();
	$sql = "INSERT IGNORE INTO obo_lo_pages SET pageID = '?', pageData = '?'";
	foreach($pages AS $key => $page)
	{
		$id = isset($page->id) ? $page->id : $page->pageID;
		if(!$PM->getPage($id))
		{
			$DBM->querySafe($sql, $id, base64_encode(serialize($page)));
			echo 'p missing';
			print_r($page);
		}
		$PM->mapPageToLO($loID, $id, $key);
	}
}


function checkandMakeQuestion($DBM, $qGroup)
{
	$QGM = \obo\lo\QuestionGroupManager::getInstance();
	$QM = \obo\lo\QuestionManager::getInstance();
	$qs = $qGroup->kids;
	$sql = "INSERT IGNORE INTO obo_lo_questions SET questionData = '?', questionID = '?' ";
	foreach($qs AS $key => $q)
	{
		$id = isset($q->id) ? $q->id : $q->questionID;
		if(!($myq = $QM->getQuestion($id)))
		{
			$DBM->querySafe($sql, base64_encode(serialize($q)), $id);
			echo 'q missing';
		}

		// map the question to this group
		$QGM->mapQuestionToGroup($qGroup->qGroupID, $id, $key, $q->questionIndex);
	}
}
?>