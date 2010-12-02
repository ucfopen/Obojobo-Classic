<?php
class plg_UCFAuthMod_UCFAuthModAPI extends core_plugin_PluginAPI
{
	// Block all API calls directly referencing this api

	const $PUBLIC_FUNCTION_LIST = ''; // dont allow any direct calls
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