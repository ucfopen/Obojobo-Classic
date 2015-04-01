<?php
namespace obo\log;

class Trackable
{
	public $userID;
	public $logType;
	public $createTime;
	public $instID;
	public $valueA;
	public $valueB;
	public $valueC;

	public function __construct($type = '', $createTime = 0, $instID = 0, $valueA='', $valueB='', $valueC='')
	{
		$this->logType = $type;

		if(isset($_SESSION['userID'])) $this->userID = $_SESSION['userID'];
		else $this->userID = 0;

		if($createTime < 1 ) $createTime = time();
		$this->createTime = $createTime;

		$this->valueA = $valueA;
		$this->valueB = $valueB;
		$this->valueC = $valueC;

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
