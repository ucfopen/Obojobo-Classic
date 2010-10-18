<?php

class nm_los_tracking_LoggedOut extends nm_los_tracking_Trackable
{
	function __construct($userID = 0, $createTime = 0, $instID = 0)
	{
		parent::__construct($userID, $createTime, $instID);
	}
}
?>