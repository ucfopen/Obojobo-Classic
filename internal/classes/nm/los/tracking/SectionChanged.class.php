<?php

class nm_los_tracking_SectionChanged extends nm_los_tracking_Trackable
{
	const OVERVIEW = 0;
	const CONTENT = 1;
	const PRACTICE = 2;
	const ASSESSMENT = 3;
	
	public $to;

	function __construct($userID = 0, $createTime = 0, $instID = 0, $to = -1)
	{
		parent::__construct($userID, $createTime, $instID);
		$this->to = $to;
	}
}
?>