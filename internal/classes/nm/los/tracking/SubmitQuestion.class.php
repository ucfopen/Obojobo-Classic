<?php

class nm_los_tracking_SubmitQuestion extends nm_los_tracking_Trackable
{
	public $qGroupID;
	public $questionID;
	public $answer;
	
	function __construct($userID = 0, $createTime = 0, $instID = 0, $qGroupID, $questionID, $answer)
	{
		parent::__construct($userID, $createTime, $instID);
		$this->qGroupID = $qGroupID;
		$this->questionID = $questionID;
		$this->answer = $answer;
	}
}
?>