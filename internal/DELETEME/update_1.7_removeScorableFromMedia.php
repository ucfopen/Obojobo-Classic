<p>This script will remove 'scorable' from blobs in lo_los_questions and lo_los_pages.  Add ?run=1 to run!</p><pre>
<?php
require_once(dirname(__FILE__)."/../app.php");

$DBM = core_db_DBManager::getConnection(new core_db_dbConnectData(AppCfg::DB_HOST, AppCfg::DB_USER, AppCfg::DB_PASS, AppCfg::DB_NAME, AppCfg::DB_TYPE));

$DBM->startTransaction();

echo "lo_los_questions:\n\n";
$qs = "SELECT * FROM lo_los_questions WHERE 1";
$q = $DBM->querySafe($qs);
if($q)
{
	while($r = $DBM->fetch_obj($q))
	{
		$data = base64_decode($r->questionData);
		
		if(preg_match('/s:8:"scorable";i:0;/', $data))
		{
			$data = preg_replace('/s:8:"scorable";i:0;/', '', $data);
			print_r($r->questionID);
			echo " ";
			$qs2 = "UPDATE lo_los_questions SET questionData='?' WHERE questionID = ?";
			$q2 = $DBM->querySafe($qs2, base64_encode($data), $r->questionID);
			if(!$q2)
			{
				echo "\n\nERROR\n\n";
				$DBM->rollback();
				die();
			}
		}
	}
}

echo "\n\nlo_los_pages:\n\n";
$qs = "SELECT * FROM lo_los_pages WHERE 1";
$q = $DBM->querySafe($qs);
if($q)
{
	while($r = $DBM->fetch_obj($q))
	{
		$data = base64_decode($r->pageData);
		
		if(preg_match('/s:8:"scorable";i:0;/', $data))
		{
			$data = preg_replace('/s:8:"scorable";i:0;/', '', $data);
			print_r($r->pageID);
			echo " ";
			$qs2 = "UPDATE lo_los_pages SET pageData='?' WHERE pageID = ?";
			$q2 = $DBM->querySafe($qs2, base64_encode($data), $r->pageID);
			if(!$q2)
			{
				echo "\n\nERROR\n\n";
				$DBM->rollback();
				die();
			}
		}
	}
}

echo "\n\nDONE\n\n";
if($_GET['run'] == 1)
{
	echo "COMMIT!";
	$DBM->commit();
}
else
{
	echo "ROLLBACK!";
	$DBM->rollback();
}
?>