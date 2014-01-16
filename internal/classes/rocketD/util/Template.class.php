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
				$templateDir = \AppCfg::DIR_BASE.\AppCfg::DIR_TEMPLATES;
				$engine->compile_dir = $templateDir.'compiled/';

				if ( ! is_dir($engine->compile_dir) || ! is_writable($engine->compile_dir) || ! is_executable($engine->compile_dir) || ! is_readable($engine->compile_dir))
				{
					\rocketD\util\Error::getError(0, "SMARTY DIR {$engine->compile_dir} does not exist or is not readable, writable and executable!");
					return false;
				}

				return $engine;
			default:
				\rocketD\util\Error::getError(0, "Template engine '$type' not found");
				return false;
		}
	}
}
