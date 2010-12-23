<?php
namespace obo\log;

class LoggedIn extends \obo\log\Trackable
{
	function __construct($userID = 0, $createTime = 0, $instID = 0)
	{
		parent::__construct($userID, $createTime, $instID);
	}
}
?>