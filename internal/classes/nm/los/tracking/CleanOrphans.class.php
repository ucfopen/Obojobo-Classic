<?php

class nm_los_tracking_CleanOrphans extends nm_los_tracking_Trackable
{
	public $runTime;
	
	function __construct($userID = 0, $createTime = 0, $instID = 0, $runTime=0)
	{
		parent::__construct($userID, $createTime, $instID);
		$this->$runTime = $runTime;
	}
}
?>