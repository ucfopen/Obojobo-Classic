<?php
class core_util_UID
{
	static public function createUID()
	{
		return uniqid(rand(), true);
	}
}