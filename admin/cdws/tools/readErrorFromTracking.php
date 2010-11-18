<pre>
<?php
require_once(dirname(__FILE__)."/../../../internal/app.php");

$DBM = core_db_DBManager::getConnection(new core_db_dbConnectData(AppCfg::DB_HOST, AppCfg::DB_USER, AppCfg::DB_PASS, AppCfg::DB_NAME, AppCfg::DB_TYPE));

if($_REQUEST['uid'])
{
	if($_REQUEST['inst_id'])
	{

			$qs = "SELECT *, UNCOMPRESS(data) AS data FROM obo_logs WHERE uid='?' AND inst_id='?' AND `type` NOT LIKE 'nm%%'";
			$q = $DBM->querySafe($qs, $_REQUEST['uid'],$_REQUEST['inst_id']);			

	}
	else{
			$qs = "SELECT *, UNCOMPRESS(data) AS data FROM obo_logs WHERE uid='?' AND `type` NOT LIKE 'nm%%'";
			$q = $DBM->querySafe($qs, $_REQUEST['uid']);			
	}
}
else{
	if($_REQUEST['inst_id'])
	{

			$qs = "SELECT *, UNCOMPRESS(data) AS data FROM obo_logs WHERE inst_id='?' AND `type` NOT LIKE 'nm%%'";
			$q = $DBM->querySafe($qs,$_REQUEST['inst_id']);			

	}
	else{
			$qs = "SELECT *, UNCOMPRESS(data) AS data FROM obo_logs WHERE `type` NOT LIKE 'nm%%'";
			$q = $DBM->querySafe($qs);			
	}	
}

if($q){
	while($r = $DBM->fetch_obj($q))
	{
		$r->data = unserialize($r->data);
		print_r($r);
	}
}


?>