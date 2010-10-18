<?php

class nm_los_tracking_LoginAttempt extends nm_los_tracking_Trackable
{
	public $code;
	public $userName;
	function __construct($userID = 0, $createTime = 0, $instID = 0, $userName, $code)
	{
		parent::__construct($userID, $createTime, $instID);
		$this->code = $code;
		$this->userName = $userName;
	}
}
?>