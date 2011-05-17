<?php
if($_REQUEST['submit'])
{
	\rocketD\util\Cache::getInstance()->clearAllCache();
	trace('cache manually cleared', true);
}

echo '<form action="'. $_SERVER['PHP_SELF'] .'" method="get">
	'.rocketD_admin_tool_get_form_page_input().'
	<input type="submit" name="submit" value="Clear all Cache &rarr;">
</form>
<h2>Raw Memcache Test</h2>';

$startValue = (string)microtime(1);
$memcache_obj = new Memcache;
$memcache_obj->connect(\AppCfg::MEMCACHE_HOSTS, \AppCfg::MEMCACHE_PORTS);
$memcache_obj->set('testmemcache', $startValue, false, 30);
$endValue = $memcache_obj->get('testmemcache');
echo "IN: $startValue<br>OUT $endValue";

echo '<p style="width: 500px; font-size: small; background-color:#dddddd; padding: 10px">'. ($startValue === $endValue ? 'PASS' : 'FAIL') .'</p>';

echo '<h2>Current Stats:</h2><pre>';

print_r(\rocketD\util\Cache::getInstance()->getExtendedStats());

?>
</pre>