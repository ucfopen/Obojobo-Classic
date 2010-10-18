<?php

class nm_los_tracking_DeleteLO extends nm_los_tracking_Trackable
{
	public $numDeleted;
	public $loID;
	
	function __construct($userID = 0, $createTime = 0, $instID = 0, $loID = 0, $numDeleted = 0)
	{
		parent::__construct($userID, $createTime, $instID);
		$this->numDeleted = $numDeleted;
		$this->$loID = $loID;
	}
}
?>