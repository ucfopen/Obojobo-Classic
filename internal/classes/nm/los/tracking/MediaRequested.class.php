<?php

class nm_los_tracking_MediaRequested extends nm_los_tracking_Trackable
{
	public $mediaID;
	function __construct($userID = 0, $createTime = 0, $instID = 0, $mediaID = 0)
	{
		parent::__construct($userID, $createTime, $instID);
		$this->mediaID = $mediaID;
	}
}
?>