<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Generate a Rocket Duck Password</title>
	<meta name="generator" content="TextMate http://macromates.com/">
	<meta name="author" content="Ian Turgeon">
	<!-- Date: 2010-11-15 -->
</head>
<body>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" accept-charset="utf-8">
		<label for="password">password</label><input type="text" name="password" value="" id="password">
		
		<p><input type="submit" value="Generate &rarr;"></p>
	</form>
	<?php

	if($_REQUEST['password'])
	{
		echo $_REQUEST['password'];
		$salt = md5(uniqid (rand(), true));
		$pw = md5($salt . md5($_REQUEST['password']));
		echo '<br>changetime: ' . time();
		echo '<br>password: '. $pw;
		echo '<br>salt: ' . $salt ;
	}

	?>
</body>
</html>
