<?php
namespace obo\log;

class PageChanged extends \obo\log\Trackable
{
    const OVERVIEW = 0;
	const CONTENT = 1;
	const PRACTICE = 2;
	const ASSESSMENT = 3;
    
	public $to;
	public $in;
	function __construct($userID = 0, $createTime = 0, $instID = 0, $to = -1, $in = -1)
	{
		parent::__construct($userID, $createTime, $instID, $to, $in);
		$this->to = $to;
		$this->in = $in;
	}
}
?>