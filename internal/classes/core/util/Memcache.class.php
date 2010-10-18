<?php
// Memcache singleton object
class core_util_Memcache extends Memcache 
{
	static private $instance = NULL;
	

	static public function getInstance()
	{
		if(!isset(self::$instance))
		{
			$selfClass = __CLASS__;
			self::$instance = new $selfClass();
		}
		return self::$instance;
	}
	
	
	function __construct()
	{
		
		if(AppCfg::CACHE_MEMCACHE)
		{
			$this->connectMemCache();
		}
	}
	
	function connectMemCache()
	{
		
		$hosts = explode(',', AppCfg::MEMCACHE_HOSTS);
		$ports = explode(',', AppCfg::MEMCACHE_PORTS);
		foreach($hosts AS $i => $host)
		{
			$this->connect($hosts[$i], $ports[$i]) or trace('connect to memcache server '. $hosts[$i] . ':' . $ports[$i], true);
		}	
	}
}
?>