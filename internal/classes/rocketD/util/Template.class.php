<?php
namespace rocketD\util;

class Template
{
	static public function getInstance($type = 'Smarty')
	{
		switch($type)
		{
			case 'Smarty':
				include_once(\AppCfg::DIR_BASE.\AppCfg::DIR_SCRIPTS.'smarty/Smarty.class.php');
				$engine = new \Smarty();
				$engine->compile_dir = \AppCfg::DIR_BASE.\AppCfg::DIR_TEMPLATES.'compiled/';
				return $engine;
			default:
				return \rocketD\util\Error::getError(0, "Template engine '$type' not found");
		}
	}
}
