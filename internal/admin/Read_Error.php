<pre>
<?php

$DBM = \rocketD\db\DBManager::getConnection(new \rocketD\db\DBConnectData(\AppCfg::DB_HOST, \AppCfg::DB_USER, \AppCfg::DB_PASS, \AppCfg::DB_NAME, \AppCfg::DB_TYPE));

if($_REQUEST['uid'])
{
	if($_REQUEST['inst_id'])
	{

			$qs = "SELECT *a FROM obo_logs WHERE uid='?' AND inst_id='?' AND `type` > 0 ";
			$q = $DBM->querySafe($qs, $_REQUEST['uid'],$_REQUEST['inst_id']);			

	}
	else{
			$qs = "SELECT * FROM obo_logs WHERE uid='?' AND `type` > 0 ";
			$q = $DBM->querySafe($qs, $_REQUEST['uid']);			
	}
}
else{
	if($_REQUEST['inst_id'])
	{

			$qs = "SELECT * FROM obo_logs WHERE inst_id='?' AND `type` > 0 ";
			$q = $DBM->querySafe($qs,$_REQUEST['inst_id']);			

	}
	else{
			$qs = "SELECT * FROM obo_logs WHERE `type` > 0 ";
			$q = $DBM->querySafe($qs);
	}	
}

if($q){
	while($r = $DBM->fetch_obj($q))
	{
		print_r($r);
	}
}


?>