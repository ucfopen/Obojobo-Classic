<?php
namespace obo\log;

class Trackable
{	
	public $userID;
	public $createTime;
	public $instID;
	public $valueA;
	public $valueB;
	public $valueC;
	
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
		$trackingMan = \obo\log\LogManager::getInstance();
		return $trackingMan->track($this);
	}
}
?>