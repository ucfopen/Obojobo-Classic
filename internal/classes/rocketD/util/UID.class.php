<?php
namespace rocketD\util;
class UID
{
	static public function createUID()
	{
		return uniqid(rand(), true);
	}
}
?>