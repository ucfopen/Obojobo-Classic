<?php


if(isset($_REQUEST['submit']))
{
  apc_clear_cache();
  apc_clear_cache('user');
  apc_clear_cache('opcode');
  echo "CLEARED!!!!";
}

?>

<form action="<?= $_SERVER['PHP_SELF'] ?>" method="get">
<?= rocketD_admin_tool_get_form_page_input() ?>
<input type="submit" name="submit" value="Clear all Cache &rarr;">
</form>
