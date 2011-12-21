<h2>Add LO To Public Library:</h2>
<form action="<?php echo $_SERVER['PHP_SELF'] . '?' . $_SERVER["QUERY_STRING"]; ?> method="get" accept-charset="utf-8">
	<label for="loID">loID</label><input type="text" name="loID" value="" id="loID">
	<?php echo rocketD_admin_tool_get_form_page_input(); ?>
	<p><input type="submit" value="Continue &rarr;"></p>
</form>
<pre>
<?php
if(isset($_GET['loID']) && is_numeric($_GET['loID']) && $_GET['loID'] > 0)
{

	// give userid 0 full perms (places this item in the public lib & makes it derivable)
	$permObj = new \obo\perms\Permissions(0, 1, 1, 1, 1, 1, 1, 1, 1, 1);

	// must be su or owner to do this
	var_dump(\obo\perms\PermissionsManager::getInstance()->setGlobalPerms($_GET['loID'], \cfg_obo_Perm::TYPE_LO, $permObj));
}
$api = \obo\API::getInstance();
$i = 1;
foreach ($api->getLibraryLOs() as $lo) {
	echo $i++ .":\t[$lo->loID] $lo->title v$lo->version.$lo->subVersion \n";
}
?>
</pre>