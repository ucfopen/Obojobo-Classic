<pre><?php
require_once(dirname(__FILE__)."/../../internal/app.php");


print_r(core_util_Cache::getInstance()->getExtendedStats());

?>