<h2>REMOVE LO FROM Public Library:</h2>
<form action="<?php echo $_SERVER['PHP_SELF'] . '?' . $_SERVER["QUERY_STRING"]; ?> method="get" accept-charset="utf-8">
	<label for="loID">loID</label><input type="text" name="loID" value="" id="loID">
	<?php echo rocketD_admin_tool_get_form_page_input(); ?>
	<p><input type="submit" value="Continue &rarr;"></p>
</form>
<pre>
<?php
if(isset($_GET['loID']) && is_numeric($_GET['loID']) && $_GET['loID'] > 0)
{

	var_dump(\obo\perms\PermissionsManager::getInstance()->removeUsersPerms(array(0), $_GET['loID'], \cfg_obo_Perm::TYPE_LO));

}
$api = \obo\API::getInstance();
$i = 1;
foreach ($api->getLibraryLOs() as $lo) {
	echo $i++ .":\t[$lo->loID] $lo->title v$lo->version.$lo->subVersion \n";
}
?>
</pre>