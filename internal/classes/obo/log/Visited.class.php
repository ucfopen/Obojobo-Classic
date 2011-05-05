<?php
namespace obo\log;

class Visited extends \obo\log\Trackable
{
	
	function __construct($userID = 0, $time = 0, $instID = 0, $visitID)
	{
		parent::__construct($userID, $time, $instID);
	}
}
?>