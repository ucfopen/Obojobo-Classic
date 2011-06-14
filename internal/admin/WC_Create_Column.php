<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" accept-charset="utf-8">
	<label for="section_id">Section ID:</label><input type="text" name="section_id" value="" id="section_id"><br>
	<label for="column_title">Column Title:</label><input type="text" name="column_title" value="" id="column_title"><br>
	<label for="instID">instID:</label><input type="text" name="instID" value="" id="instID"><br>
	<input type="submit" name="submit" value="Create Column" >
</form>
<pre>
<?php

	if($_GET['section_id'])
	{
		// test ucf api
		$sectionID = $_GET['section_id'];
		$columnTitle = $_GET['column_title'];
		$instID = $_GET['instID'];
		
		echo "instance ID: $instID\n";
		echo "sectionID: $sectionID\n";
		echo "title: $columnTitle\n";
		echo "Creating Column, please wait...\n\n";
		$t = microtime(true);
		flush();
		
		$PM = \rocketD\plugin\PluginManager::getInstance();
		$column = $PM->callAPI('UCFCourses', 'createColumn', array($instID, $sectionID, $columnTitle), true);

		$t = (microtime(true) - $t);
		echo "created in $t sec \n-----------------------------------------\n";
		print_r($column);
	}

?>
</pre>