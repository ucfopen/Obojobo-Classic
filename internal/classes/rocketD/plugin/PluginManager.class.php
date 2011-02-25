<?php
namespace rocketD\plugin;
class PluginManager
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
		if(strpos(\AppCfg::CORE_PLUGINS, $pluginName) !== false)
		{
			return true;
		}
		if($includeNonCorePlugins)
		{
			if(strpos(\AppCfg::AUTH_PLUGINS, $pluginName) !== false)
			{
				return true;
			}
			if(strpos(\AppCfg::COURSE_PLUGINS, $pluginName) !== false)
			{
				return true;
			}
		}
		trace("plugin missing: $pluginName - installed: " . \AppCfg::CORE_PLUGINS, true);
		return false;
	}
	
	protected function getAPI($pluginName)
	{
		// make sure the plugin is enabled
		if($this->pluginInstalled($pluginName))
		{
			// make a classname based off plugin name
			$pluginAPIClassName = 'plg_' . $pluginName . '_'  . strtoupper(substr($pluginName, 0,1)) . substr($pluginName, 1)  . 'API';
			$class = call_user_func(array($pluginAPIClassName, 'getInstance'));
			if(is_subclass_of($class, '\rocketD\plugin\PluginAPI'))
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
	
	/**
	 * Call a plugin method using it's API
	 *
	 * @param string $plugin	Name of plugin to call - this points to the file /plugins/pluginName/classes/pluginNameAPI.class.php
	 * @param string $method	Name of the public method to call - this must be public AND must be listed in the plugin white list constant for the API class (unless you set bypassPublicRestriction)
	 * @param array $args	Array of arguments that are sent to the requested function
	 * @param boolean $bypassPublicRestriction	 set to true if you need to bypass the public whitelist restrictions, designed to limit direct public api access from the client
	 * @return void
	 * @author Ian Turgeon
	 */
	public function callAPI($plugin, $method, $args=-1, $bypassPublicRestriction=0)
	{
		if( $pluginAPI = $this->getAPI($plugin))
		{
			
			if(method_exists($pluginAPI, $method))
			{
				// check to see if the function is whitelisted or bypass
				if($bypassPublicRestriction != true)
				{
					//$publicFunctions = explode(',', $pluginAPI::PUBLIC_FUNCTION_LIST);
					$publicFunctions = explode(',', constant(get_class($pluginAPI) . '::PUBLIC_FUNCTION_LIST'));
					if(!in_array($method, $publicFunctions))
					{
						return \rocketD\util\Error::getError(201);
					}
				}
		
				// call the api function
				if(!is_array($args)) $args = array($args); // if the argument is just one value, make it an array with the value as the first item
				return call_user_func_array(array($pluginAPI, $method), $args);
		
			}
			return \rocketD\util\Error::getError(201);
		}
		return \rocketD\util\Error::getError(200);
	}

}
?>