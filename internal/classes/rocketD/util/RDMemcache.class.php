<?php
namespace rocketD\util;
// Memcache singleton object
class RDMemcache
{
	static private $instance = NULL;
	protected $mc = NULL;
	protected $memEnabled = false;

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
		if(\AppCfg::CACHE_MEMCACHE === true)
		{
			$this->connectMemCache();
		}
	}
	
	function connectMemCache()
	{
		if(!isset($this->mc))
		{
			$this->memEnabled = true;
			try
			{
				$this->mc = new \Memcache;
				$hosts = explode(',', \AppCfg::MEMCACHE_HOSTS);
				$ports = explode(',', \AppCfg::MEMCACHE_PORTS);
				foreach($hosts AS $i => $host)
				{
					$this->mc->connect($hosts[$i], $ports[$i]) or trace('connect to memcache server '. $hosts[$i] . ':' . $ports[$i], true);
				}
			}
			catch(\Exception $e)	
			{
				trace($e, true);
				$this->memEnabled = false;
			}		
		}
	}
	
	public function __call($name, $args)
	{
		if($this->memEnabled)
		{
			return call_user_func_array(array($this->mc, $name), $args);
		}
		else
		{
			return false;
		}
	}
}
?>