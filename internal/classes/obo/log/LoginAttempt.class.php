<?php
namespace obo\log;

class LoginAttempt extends \obo\log\Trackable
{
	public $code;
	public $userName;
	function __construct($userID = 0, $createTime = 0, $instID = 0, $userName, $code)
	{
		parent::__construct($userID, $createTime, $instID);
		$this->valueA = $this->code = $code;
		$this->valueB = $this->userName = $userName;
	}
}
?>