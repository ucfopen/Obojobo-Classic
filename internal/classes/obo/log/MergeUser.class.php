<?php
namespace obo\log;

class MergeUser extends \obo\log\Trackable
{
	
	public $userIDFrom;
	public $userIDTo;
	
	function __construct($userID = 0, $createTime = 0, $userIDFrom = 0, $userIDTo = 0)
	{
		parent::__construct($userID, $createTime, 0, $userIDFrom, $userIDTo);
		$this->userIDFrom = $userIDFrom;
		$this->userIDTo = $userIDTo;
	}
}
?>