<?php

class nm_los_tracking_ImportScore extends nm_los_tracking_Trackable
{
	public $attemptID;
	
	function __construct($userID = 0, $createTime = 0, $instID = 0, $attemptID = 0)
	{
		parent::__construct($userID, $createTime, $instID);
		$this->attemptID = $attemptID;
	}
}
?>