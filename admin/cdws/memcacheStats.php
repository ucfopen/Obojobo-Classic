<pre><?php
require_once(dirname(__FILE__)."/../../internal/app.php");


print_r(\rocketD\util\Cache::getInstance()->getExtendedStats());

?>