<?php
namespace obo\log;

class MediaDownloaded extends \obo\log\Trackable
{
	public $mid;
	function __construct($userID = 0, $createTime = 0, $instID = 0, $mid = 0)
	{
		parent::__construct($userID, $createTime, $instID);
		$this->mid = $mid;
	}
}
?>