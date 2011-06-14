<h2>Process 10 Score Submissions:</h2>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" accept-charset="utf-8">
	<input type="hidden" name="nid" value="go" id="nid">
	<p><input type="submit" value="Continue &rarr;"></p>
</form>
<pre>
<?php

if(!empty($_GET['nid']) )
{
	$PM = \rocketD\plugin\PluginManager::getInstance();
	$result = $PM->callAPI('UCFCourses', 'sendFailedScoreSetRequests', array(), true);
	print_r($result);
}

?>
</pre>