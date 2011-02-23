<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Decode value or Tracking Log</title>
	<meta name="generator" content="TextMate http://macromates.com/">
	<meta name="author" content="Ian Turgeon">
	<!-- Date: 2010-11-17 -->
</head>
<body>

<form action="<?php echo $_SERVER['PHP_SEFL']; ?>" method="post" accept-charset="utf-8">
	<h1>Decode a pasted value</h1>
	<label for="rawinput">Paste Encoded Data:</label><br><textarea rows="10" cols="100" name="rawinput" id="rawinput"></textarea><br>
	<label for="base_64_decode">base 64 decode?</label><input type="checkbox" name="base_64_decode" id="base_64_decode"><br>
	<label for="unserialize">unserialize?</label><input type="checkbox" name="unserialize"  id="unserialize"><br>
	<label for="json_encode">Encode in JSON</label><input type="checkbox" name="json_encode" id="json_encode"><br><hr>
	<label for="get_tracking_value">Retrieve a Log by ID</label><input type="text" name="get_tracking_value" value="" id="get_tracking_value"><br>
	<label for="page_id">Retrieve a Page by ID</label><input type="text" name="page_id" value="" id="page_id"><br>
	<label for="question_id">Retrieve a Question by ID</label><input type="text" name="question_id" value="" id="question_id"><br>
	<label for="loid">Retrieve a LO by ID</label><input type="text" name="loid" value="" id="loid"><br>
	
	
	
	

	<p><input type="submit" value="Continue &rarr;"></p>
</form>
<hr>
<pre>
	<?php
	
	if($_POST['rawinput'])
	{
		$data = $_POST['rawinput'];
	}
	
	if($_POST['get_tracking_value'])
	{
		require_once(dirname(__FILE__)."/../../../internal/app.php");
		
		$DBM = core_db_DBManager::getConnection(new core_db_dbConnectData(AppCfg::DB_HOST, AppCfg::DB_USER, AppCfg::DB_PASS, AppCfg::DB_NAME, AppCfg::DB_TYPE));
		if($q = $DBM->querySafe("SELECT * FROM ".cfg_obo_Track::TABLE." WHERE ".cfg_obo_Track::ID." = '?'", $_POST['get_tracking_value']))
		{
			if($r = $DBM->fetch_obj($q))
			{
				$data = $r->{cfg_obo_Track::DATA};
				$_POST['base_64_decode'] = 0;
				$_POST['mysql_decompress'] = 1;
				$_POST['unserialize'] = 1;
			}
		}
		
	}
	if($_POST['page_id'])
	{
		require_once(dirname(__FILE__)."/../../../internal/app.php");
		$PM = nm_los_PageManager::getInstance();
		$data = $PM->getPage($_POST['page_id']);
		$_POST['base_64_decode'] = 0;
		$_POST['mysql_decompress'] = 0;
		$_POST['unserialize'] = 0;
	}

	if($_POST['question_id'])
	{
		require_once(dirname(__FILE__)."/../../../internal/app.php");
		$QM = nm_los_QuestionManager::getInstance();
		$data = $QM->getQuestion($_POST['question_id']);
		$_POST['base_64_decode'] = 0;
		$_POST['mysql_decompress'] = 0;
		$_POST['unserialize'] = 0;
	}
	
	if($_POST['loid'])
	{
		require_once(dirname(__FILE__)."/../../../internal/app.php");
		$DBM = core_db_DBManager::getConnection(new core_db_dbConnectData(AppCfg::DB_HOST, AppCfg::DB_USER, AppCfg::DB_PASS, AppCfg::DB_NAME, AppCfg::DB_TYPE));
		$data = new nm_los_LO();
		$data->dbGetFull($DBM, $_POST['loid']);
		$_POST['base_64_decode'] = 0;
		$_POST['mysql_decompress'] = 0;
		$_POST['unserialize'] = 0;
	}
	
	if($_POST['mysql_decompress'])
	{
		$data = gzuncompress(substr($data, 4));
	}

	if($_POST['base_64_decode'])
	{
		$data = base64_decode($data);
	}

	if($_POST['unserialize'])
	{
		require_once(dirname(__FILE__)."/../../../internal/app.php");
		$data = unserialize($data);
	}
	
	if($_POST['json_encode'])
	{
		print_r(json_encode($data));
	}
	
	print_r($data);


	?>
</body>
</html>
