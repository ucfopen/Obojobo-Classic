<?php

// ADDs visitid to each log and moves the hard to get to data from a compresses serialized string to sortable columns
require_once(dirname(__FILE__)."/../app.php");
$DBM = \rocketD\db\DBManager::getConnection(new \rocketD\db\DBConnectData(\AppCfg::DB_HOST, \AppCfg::DB_USER, \AppCfg::DB_PASS, \AppCfg::DB_NAME, \AppCfg::DB_TYPE));



$q = $DBM->query("SELECT *, UNCOMPRESS(data) AS data FROM obo_logs WHERE itemType = 'SubmitQuestion'");

while($r = $DBM->fetch_obj($q))
{
	$data = deserializeData($r->data);
	$a = NULL;
	$b = NULL;
	$c = NULL;
	switch($r->itemType)
	{
		case 'DeleteLO':
			$a = $data->loID;
			$b = $data->numDeleted;
			break;
		case 'EndAttempt':
			$a = $data->attemptID;
			break;
		case 'ImportScore':
			$a = $data->attemptID;
			break;
		case 'LoginAttempt':
			$a = $data->code;
			$b = $data->userName;
			break;
		case 'MediaDeleted':
			$a = $data->mid;
			break;
		case 'MediaDownloaded':
			$a = $data->mid;
			break;
		case 'MediaRequestCompleted':
			$a = $data->mediaID;
			break;
		case 'MediaUploaded':
			$a = $data->mid;
			break;
		case 'MergeUser':
			$a = $data->userIDFrom;
			$b = $data->userIDTo;
			break;
		case 'PageChanged':
			$a = $data->to;
			$b = $data->in;
			break;
		case 'ResumeAttempt':
			$a = $data->attemptID;
			break;
		case 'SectionChanged':
			$a = $data->to;
			break;
		case 'StartAttempt':
			$a = $data->attemptID;
			break;
		case 'SubmitMedia':
			$a = $data->questionID;
			$b = $data->score;
			$c = $data->qGroupID;
			break;
		case 'SubmitQuestion':
			$a = $data->questionID;
			$b = $data->answer;
			$c = $data->qGroupID;
			break;
	}
	
	$DBM->query("UPDATE obo_logs SET valueA = '$a', valueB = '$b', valueC = '$c' WHERE trackingID = '$r->trackingID' ");
}


function deserializeData($data)
{	
	$data = preg_replace_callback('/(\d+):"(nm_los_tracking_)/', 'fixObject', $data);
	$data = unserialize($data);
	return $data;
}

function fixObject($matches)
{
	return ($matches[0]-7) . ':"\\obo\\log\\';
}

exit('done');
?>