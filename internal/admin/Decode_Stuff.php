
<form action="<?php echo $_SERVER['PHP_SELF'] . '?' . $_SERVER["QUERY_STRING"]; ?>" method="post" accept-charset="utf-8">
	<h1>Decode a pasted value</h1>
	<label for="rawinput">Paste Encoded Data:</label><br><textarea rows="10" cols="100" name="rawinput" id="rawinput"></textarea><br>
	<label for="base_64_decode">base 64 decode?</label><input type="checkbox" name="base_64_decode" id="base_64_decode"><br>
	<label for="unserialize">unserialize?</label><input type="checkbox" name="unserialize"  id="unserialize"><br>
	<label for="json_encode">Encode in JSON</label><input type="checkbox" name="json_encode" id="json_encode"><br><hr>
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
	
	if($_POST['page_id'])
	{
		$PM = \obo\lo\PageManager::getInstance();
		$data = $PM->getPage($_POST['page_id']);
		$_POST['base_64_decode'] = 0;
		$_POST['mysql_decompress'] = 0;
		$_POST['unserialize'] = 0;
	}

	if($_POST['question_id'])
	{
		$QM = \obo\lo\QuestionManager::getInstance();
		$data = $QM->getQuestion($_POST['question_id']);
		$_POST['base_64_decode'] = 0;
		$_POST['mysql_decompress'] = 0;
		$_POST['unserialize'] = 0;
	}
	
	if($_POST['loid'])
	{
		$DBM = \rocketD\db\DBManager::getConnection(new \rocketD\db\DBConnectData(\AppCfg::DB_HOST, \AppCfg::DB_USER, \AppCfg::DB_PASS, \AppCfg::DB_NAME, \AppCfg::DB_TYPE));
		$data = new \obo\lo\LO();
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
		$data = unserialize($data);
	}
	
	if($_POST['json_encode'])
	{
		print_r(json_encode($data));
	}
	
	print_r($data);


	?>