<?php

// $API = \obo\API::getInstance();
// $los = $API->getLOs();

$value = microtime(1);


$memcache_obj = new Memcache;
$memcache_obj->connect(\AppCfg::MEMCACHE_HOSTS, \AppCfg::MEMCACHE_PORTS);

$memcache_obj->add('testmemcache', $value, false, 30);
$result = $memcache_obj->get('testmemcache');

echo "IN: $value<br>OUT $result";

?>