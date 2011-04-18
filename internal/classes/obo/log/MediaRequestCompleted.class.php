<?php
namespace obo\log;

class MediaRequestCompleted extends \obo\log\Trackable
{
	public $mediaID;
	function __construct($userID = 0, $createTime = 0, $instID = 0, $mediaID = 0)
	{
		parent::__construct($userID, $createTime, $instID, $mediaID);
		$this->mediaID = $mediaID;
	}
}
?>