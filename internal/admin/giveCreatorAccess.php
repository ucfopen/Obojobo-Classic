<?php
// ================ REQUIRE SU STATUS
$API = \obo\API::getInstance();

if(!$API->getSessionValid(cfg_obo_Role::ADMIN))
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
	<title>Give a user Content Creator Access Role</title>
	<meta name="generator" content="TextMate http://macromates.com/">
	<meta name="author" content="Ian Turgeon">
	<!-- Date: 2010-12-01 -->
</head>
<body>
<h1>Give a NID Content Creator Role</h1>
<hr>
<form action="<?php echo $_SERVER['PHP_SELF'] . '?' . $_SERVER["QUERY_STRING"]; ?>" method="get" accept-charset="utf-8">
	<label for="instID">NID:</label><input type="text" name="NID" value="" id="NID"><br>
	<input type="submit" name="submit" value="Give NID Content Creator Role" >
</form>
<hr>
<h1>RESULTS:</h1>
<pre>
<?php
//  RESULT OUTPUT HERE
if($_GET['NID'])
{
	$success = false;
	$AM = \rocketD\auth\AuthManager::getInstance();
	// locate nid or create account
	if(!( $user = $AM->fetchUserByUserName($_GET['NID']) ) )
	{
		// cant find, try to log in to create them
		$authmods = $AM->getAllAuthModules();
		foreach($authmods AS $authMod)
		{
			if(method_exists($authMod, 'syncExternalUser'))
			{
				$user = $authMod->syncExternalUser($_GET['NID']);
			}
		}
	}
	if(isset($user) && $user instanceof \rocketD\auth\User)
	{
		$success = $API->editUsersRoles(array($user->userID), array(cfg_obo_Role::CONTENT_CREATOR) );
	}
	echo $success ? $_GET['NID'] . ' Now has Content Creator Role.' : 'Unable to grant role';
	
}

?>
</pre>
</body>
</html>