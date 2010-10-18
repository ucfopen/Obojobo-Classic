<?php
class core_plugin_PluginManager
{
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
	
	public function pluginInstalled($pluginName, $includeNonCorePlugins = false)
	{
		trace(AppCfg::CORE_PLUGINS);
		if(strpos(AppCfg::CORE_PLUGINS, $pluginName) !== false)
		{
			return true;
		}
		if($includeNonCorePlugins)
		{
			if(strpos(AppCfg::AUTH_PLUGINS, $pluginName) !== false)
			{
				return true;
			}
			if(strpos(AppCfg::COURSE_PLUGINS, $pluginName) !== false)
			{
				return true;
			}
		}
		return false;
	}
	
	public function getAPI($pluginName)
	{
		
		
		// make sure the plugin is enabled
		if($this->pluginInstalled($pluginName))
		{
			// make a classname based off plugin name
			$pluginAPIClassName = 'plg_' . $pluginName . '_'  . strtoupper(substr($pluginName, 0,1)) . substr($pluginName, 1)  . 'API';
			$class = call_user_func(array($pluginAPIClassName, 'getInstance'));
			if(is_subclass_of($class, 'core_plugin_PluginAPI'))
			{
				return $class;
			}
		}
		else
		{
			trace('Plugin access attempted for disabled plugin: '.$pluginName,true);
		}
		return false;
	}
	
	public function get($pluginName)
	{
		
		
		// make sure the plugin is enabled
		if($this->pluginInstalled($pluginName, true))
		{
			// make a classname based off plugin name
			$pluginAPIClassName = 'plg_' . $pluginName . '_'  . strtoupper(substr($pluginName, 0,1)) . substr($pluginName, 1);
			$class = call_user_func(array($pluginAPIClassName, 'getInstance'));
			return $class;
		}
		else
		{
			trace('Plugin access attempted for disabled plugin: '.$pluginName,true);
		}
		return false;
	}
	
	public function callAPI($plugin, $method, $args=-1)
	{
		if(is_array($args) && $pluginAPI = $this->getAPI($plugin))
		{
			
			if(method_exists($pluginAPI, $method))
			{
				$r = new ReflectionMethod($pluginAPI, $method); 
				if($r->isPublic())
				{
					if($args == -1) $args = array();
					return call_user_func_array(array($pluginAPI, $method), $args);
				}
			}
			
			
			
			return core_util_Error::getError(201);
		}
		
		
		return core_util_Error::getError(200);
	}
	
	public function call($plugin, $method, $args)
	{
		if($plugin = $this->get($plugin))
		{
			
			if(method_exists($plugin, $method))
			{
				$r = new ReflectionMethod($plugin, $method); 
				if($r->isPublic())
				{
					return call_user_func_array(array($plugin, $method), $args);
				}
			}
			
			
			
			return core_util_Error::getError(201);
		}
		
		
		return core_util_Error::getError(200);
	}
}
?>