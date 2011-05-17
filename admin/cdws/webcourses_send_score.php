<?php
require_once(dirname(__FILE__)."/../../internal/app.php");
// Check for super user
$API = \obo\API::getInstance();

if(!$API->getSessionValid(\cfg_obo_Role::EMPLOYEE_ROLE))
{
	header('HTTP/1.0 404 Not Found');
	exit();
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Test Set Score</title>
	<meta name="generator" content="TextMate http://macromates.com/">
	<meta name="author" content="Ian Turgeon">
	<!-- Date: 2010-12-01 -->
</head>
<body>
<p>You must meet one of the following restrictions to set a score:</p>
<ul>
	<li>Be a super user</li>
	<li>Be the student</li>
	<li>Have ownership or editing rights to the instance</li>
</ul>
<hr>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" accept-charset="utf-8">
	<label for="instID">InstID:</label><input type="text" name="instID" value="" id="instID"><br>
	<label for="studentUserID">StudentUserID:</label><input type="text" name="studentUserID" value="" id="studentUserID"><br>
	<label for="score">Score:</label><input type="text" name="score" value="" id="score"><br>
	<input type="submit" name="submit" value="Set Score" >
</form>
<hr>
<pre>
<?php

	if($_GET['studentUserID'])
	{
		// test ucf api
		$student = $_GET['studentUserID'];
		$instID = $_GET['instID'];
		$score = $_GET['score'];
		
		echo "studentid: $student\n";
		echo "instid: $instID\n";
		echo "Score: $score\n";
		echo "Setting Score, please wait...\n\n";
		
		$t = microtime(true);
		flush();
		
		$PM = \rocketD\plugin\PluginManager::getInstance();
		$grade = $PM->callAPI('UCFCourses', 'sendScore', array($instID, $student, $score), true);
		
		$t = (microtime(true) - $t);
		echo "Score set in $t sec \n-----------------------------------------\n";
		print_r($grade);
	}
?>
</pre>
</body>
</html>