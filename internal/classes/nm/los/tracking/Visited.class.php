<?php

class nm_los_tracking_Visited extends nm_los_tracking_Trackable
{
	public $visitID;
	
	function __construct($userID = 0, $time = 0, $instID = 0, $visitID)
	{
		parent::__construct($userID, $time, $instID);
		$this->visitID = $visitID;
	}
}
?>