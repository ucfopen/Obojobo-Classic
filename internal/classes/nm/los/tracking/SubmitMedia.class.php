<?php

class nm_los_tracking_SubmitMedia extends nm_los_tracking_Trackable
{
	public $qGroupID;
	public $questionID;
	public $score;
	
	function __construct($userID = 0, $createTime = 0, $instID = 0, $qGroupID, $questionID, $score)
	{
		parent::__construct($userID, $createTime, $instID);
		$this->qGroupID = $qGroupID;
		$this->questionID = $questionID;
		$this->score = $score;
	}
}
?>