<pre>
<?php
require_once(dirname(__FILE__)."/../../internal/app.php");

core_util_Log::dumpProfile('amfphp_Filters');
core_util_Log::dumpProfile('amfphp_Methods');
core_util_Log::dumpProfile('memcache_missed');


echo('profiles dumped');

?>