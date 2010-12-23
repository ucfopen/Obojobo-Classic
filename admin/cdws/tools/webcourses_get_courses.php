<?php

	require_once(dirname(__FILE__)."/../../../internal/app.php");
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
	<title>Test Get Courses</title>
	<meta name="generator" content="TextMate http://macromates.com/">
	<meta name="author" content="Ian Turgeon">
	<!-- Date: 2010-12-01 -->
</head>
<body>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" accept-charset="utf-8">
	<input type="submit" name="submit" value="Get My Courses" >
</form>
<pre>
<?php

if($_GET['submit'])
{
	// test ucf api

	echo "Fetching Courses, please wait...\n\n";
	$t = microtime(true);
	flush();
	
	$API = \obo\API::getInstance();
	$sections = $API->getCourses();
	
	$t = (microtime(true) - $t);
	echo count($sections) . " courses fetched in $t sec \n-----------------------------------------\n";
	print_r($sections);
}
?>
</pre>
</body>
</html>