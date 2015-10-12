<?php
namespace rocketD;

trait Singleton
{
	private static $instance;

	public static function getInstance()
	{
		static $instance;
		return isset($instance) ? $instance : $instance = new static;
	}

	private function __wakeup() {}

	private function __clone() {}
}
