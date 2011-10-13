<pre><?php
require_once(dirname(__FILE__)."/../../internal/app.php");


print_r(\obo\util\Cache::getInstance()->getExtendedStats());

?>