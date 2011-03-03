<?php
namespace rocketD\util;
// Memcache singleton object
class Memcache
{
	static private $instance = NULL;
	protected $mc;

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
		
		if(\AppCfg::CACHE_MEMCACHE)
		{
			$this->connectMemCache();
		}
	}
	
	function connectMemCache()
	{
		$this->mc = new Memcache();
		$hosts = explode(',', \AppCfg::MEMCACHE_HOSTS);
		$ports = explode(',', \AppCfg::MEMCACHE_PORTS);
		foreach($hosts AS $i => $host)
		{
			$this->mc->connect($hosts[$i], $ports[$i]) or trace('connect to memcache server '. $hosts[$i] . ':' . $ports[$i], true);
		}	
	}
	
	public function __call($name, $args)
	{
		if($this->mc)
		{
			return call_user_func_array($this->mc->$name, $args);
		}
		else
		{
			return false;
		}
		
	}

}
?>