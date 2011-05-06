<?php
namespace obo\log;

class SectionChanged extends \obo\log\Trackable
{
	const OVERVIEW = 0;
	const CONTENT = 1;
	const PRACTICE = 2;
	const ASSESSMENT = 3;
	
	public $to;

	function __construct($userID = 0, $createTime = 0, $instID = 0, $to = -1)
	{
		parent::__construct($userID, $createTime, $instID, $to);
		$this->to = $to;
	}
}
?>