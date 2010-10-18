<?php

class nm_los_tracking_MergeUser extends nm_los_tracking_Trackable
{
	
	public $userIDFrom;
	public $userIDTo;
	
	function __construct($userID = 0, $createTime = 0, $userIDFrom = 0, $userIDTo = 0)
	{
		parent::__construct($userID, $createTime, 0);
		$this->userIDTo = $userIDTo;
		$this->userIDFrom = $userIDFrom;
	}
}
?>