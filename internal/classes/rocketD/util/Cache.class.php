<?php
namespace rocketD\util;
// Memcache singleton object
abstract class Cache
{
	static private $instance = NULL;


	static public function getInstance()
	{
		if(!isset(self::$instance))
		{
			$selfClass = \AppCfg::CACHE_CLASS;
			self::$instance = new $selfClass();
		}
		return self::$instance;
	}

	abstract public function doRateLimit($ip);
	abstract public function setModUCFExternalUser($username, $userData);
	abstract public function clearAllUsers();
	abstract public function clearAllCache();
}
