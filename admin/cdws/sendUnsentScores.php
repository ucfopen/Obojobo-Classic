<?php
require_once(dirname(__FILE__)."/../../internal/app.php");
// Check for super user
$API = \obo\API::getInstance();
$result = $API->getSessionRoleValid(array('SuperUser'));
if(! in_array('SuperUser', $result['hasRoles']) )
{
	exit();
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Send Scores that Previously Failed to Send</title>
	<meta name="generator" content="TextMate http://macromates.com/">
	<meta name="author" content="Ian Turgeon">
	<!-- Date: 2011-01-07 -->
</head>
<body>
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
</body>
</html>
