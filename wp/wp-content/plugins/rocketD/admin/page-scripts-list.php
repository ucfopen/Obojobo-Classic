<h2>Click on a script to run it</h2>
<ul>
<?php
	require_once(dirname(__FILE__)."/../../../../../internal/app.php");
	if( $handle = opendir(\AppCfg::DIR_BASE . \AppCfg::DIR_ADMIN) )
	{
		while( false !== ( $file = readdir($handle) ) )
		{
			if($file != '.' && $file != '..')
			{
				echo "<li><a href=\"http://obo/wp/wp-admin/admin.php?page=rocketD_sub_add_new&script=$file\">$file</a></li>\n";
			}
		}
		closedir($handle);
	}
?>
</ul>

<?php
if($_GET['script'])
{
	include(\AppCfg::DIR_BASE . \AppCfg::DIR_ADMIN . $_REQUEST['script']);
	
}
?>