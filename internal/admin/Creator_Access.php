<h1>Give a NID Content Creator Role</h1>
<hr>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" accept-charset="utf-8">
	<label for="instID">NID:</label><input type="text" name="NID" value="" id="NID"><br>
	<?php echo 	rocketD_admin_tool_get_form_page_input(); ?>
	<input type="submit" name="submit" value="Give NID Content Creator Role" >
</form>
<hr>
<?php

$API = \obo\API::getInstance();

//  RESULT OUTPUT HERE
if($_GET['NID'])
{
	?>
	<h1>RESULTS:</h1>
	<pre>
	<?php
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
	echo "</pre>";
	echo $success ? $_GET['NID'] . ' Now has Content Creator Role.' : 'Unable to grant role';
	
}

echo "<pre>";
$users = $API->getUsersInRole(cfg_obo_Role::CONTENT_CREATOR);
print_r($users);

?>
</pre>