<pre>
<?php
echo "Before:";
print_r(\rocketD\util\Cache::getInstance()->getExtendedStats());
\rocketD\util\Cache::getInstance()->clearAllCache();
trace('cache manually cleared', true);
echo "Cleared:";
print_r(\rocketD\util\Cache::getInstance()->getExtendedStats());
?>
