<pre>
<?php
require_once(dirname(__FILE__)."/../../internal/app.php");

echo "Before:";
print_r(core_util_Cache::getInstance()->getExtendedStats());
core_util_Cache::getInstance()->clearAllCache();
trace('cache manually cleared', true);
echo "Cleared:";
print_r(core_util_Cache::getInstance()->getExtendedStats());
?>
