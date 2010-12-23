<?php
class plg_sets_SetsAPI extends \rocketD\plugin\PluginAPI
{

	const PUBLIC_FUNCTION_LIST = ''; // dont allow any direct calls
	private static $instance;
	static public function getInstance()
	{
		if(!isset(self::$instance))
		{
			$selfClass = __CLASS__;
			self::$instance = new $selfClass();
		}
		return self::$instance;
	}
}
?>