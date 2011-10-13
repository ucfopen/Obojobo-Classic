<pre>
<?php
require_once(dirname(__FILE__)."/../../internal/app.php");

echo "Before:";
print_r(\obo\util\Cache::getInstance()->getExtendedStats());
\obo\util\Cache::getInstance()->clearAllCache();
trace('cache manually cleared', true);
echo "Cleared:";
print_r(\obo\util\Cache::getInstance()->getExtendedStats());
?>
