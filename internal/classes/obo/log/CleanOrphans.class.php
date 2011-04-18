<?php
namespace obo\log;
class CleanOrphans extends \obo\log\Trackable
{
	public $runTime;
	
	function __construct($userID = 0, $createTime = 0, $instID = 0, $runTime=0)
	{
		parent::__construct($userID, $createTime, $instID);
		$this->$runTime = $runTime;
	}
}
?>