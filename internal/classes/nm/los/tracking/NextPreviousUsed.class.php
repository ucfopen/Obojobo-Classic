<?php

class nm_los_tracking_NextPreviousUsed extends nm_los_tracking_Trackable
{
    const NEXT = 0;
	const PREVIOUS = 1;
	
	public $dir;
	function __construct($userID = 0, $createTime = 0, $instID = 0, $dir = -1)
	{
		parent::__construct($userID, $createTime, $instID);
		$this->dir = $dir;
	}
}
?>