<pre>
<?php
require_once(dirname(__FILE__)."/../../internal/app.php");

\rocketD\util\Log::dumpProfile('amfphp_Filters');
\rocketD\util\Log::dumpProfile('amfphp_Methods');
\rocketD\util\Log::dumpProfile('memcache_missed');


echo('profiles dumped');

?>