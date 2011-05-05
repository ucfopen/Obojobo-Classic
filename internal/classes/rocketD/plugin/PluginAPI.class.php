<?php
/*
* Plugin API base class.  All plugin modules must extend this class
* Any methods exposed in this class or those that extend it are publicly availible through the API so make sure all security restrictions are in place
*/
namespace rocketD\plugin;
abstract class PluginAPI extends \rocketD\db\DBEnabled
{	

	const PUBLIC_FUNCTION_LIST = '';
	abstract static public function getInstance();

}
?>