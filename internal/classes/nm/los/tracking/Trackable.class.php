<?php

class nm_los_tracking_Trackable
{	
	public $userID;
	public $createTime;
	public $instID;
	
	public function __construct($userID = 0, $createTime = 0, $instID = 0)
	{
		if($userID < 1)
		{
			if(isset($_SESSION['userID'])) $userID = $_SESSION['userID'];
		}
		$this->userID = $userID;
		
		if($createTime < 1 ) $createTime = time();
		$this->createTime = $createTime;
		
		if($instID < 1)
		{
			if(isset($GLOBALS['CURRENT_INSTANCE_DATA']['instID'])) $instID = $GLOBALS['CURRENT_INSTANCE_DATA']['instID'];	
		} 
		$this->instID = $instID;
	}

	public function track()
	{
		$trackingMan = nm_los_TrackingManager::getInstance();
		return $trackingMan->track($this);
	}
}
?>