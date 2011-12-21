<h2>Display Media:</h2>
<form action="<?php echo $_SERVER['PHP_SELF'] . '?' . $_SERVER["QUERY_STRING"]; ?> method="get" accept-charset="utf-8">
	<label for="mediaid">Media ID</label><input type="text" name="mediaid" value="" id="mediaid">
	<?php echo rocketD_admin_tool_get_form_page_input(); ?>
	<p><input type="submit" value="Continue &rarr;"></p>
</form>
<pre>
<?php
if(isset($_GET['mediaid']) && is_numeric($_GET['mediaid']) && $_GET['mediaid'] > 0)
{

	$mediaid = $_GET['mediaid'];
	$uid = 0;
	$email = '';
	$mm = \obo\lo\MediaManager::getInstance();
	$result = $mm->getMedia($mediaid);
	
	var_dump($result);
}
?>
</pre>