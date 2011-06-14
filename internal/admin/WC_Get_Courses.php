<h2>List A User's Courses:</h2>
<form action="<?php echo $_SERVER['PHP_SELF'] . '?' . $_SERVER["QUERY_STRING"]; ?> method="get" accept-charset="utf-8">
	<label for="nid">NID</label><input type="text" name="nid" value="" id="nid">
	<p><input type="submit" value="Continue &rarr;"></p>
</form>
<pre>
<?php
if(!empty($_GET['nid']) )
{
	echo $_GET['nid'] . "'s Courses:<br>";
	flush();
	$PM = \rocketD\plugin\PluginManager::getInstance();
	$result = $PM->callAPI('UCFCourses', 'testOnlyGetCourses', array($_GET['nid']), true);
	print_r($result);
}
?>
</pre>