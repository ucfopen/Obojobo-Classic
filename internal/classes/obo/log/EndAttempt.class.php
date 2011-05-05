<?php
namespace obo\log;

class EndAttempt extends \obo\log\Trackable
{
	public $attemptID;
	
	function __construct($userID = 0, $createTime = 0, $instID = 0, $attemptID = 0)
	{
		parent::__construct($userID, $createTime, $instID, $attemptID);
		$this->attemptID = $attemptID;
	}
}
?>