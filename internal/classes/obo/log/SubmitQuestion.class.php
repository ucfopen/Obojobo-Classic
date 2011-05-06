<?php
namespace obo\log;

class SubmitQuestion extends \obo\log\Trackable
{
	public $qGroupID;
	public $questionID;
	public $answer;
	// TOOD: add log for the score?
	function __construct($userID = 0, $createTime = 0, $instID = 0, $qGroupID, $questionID, $answer)
	{
		parent::__construct($userID, $createTime, $instID, $questionID, $answer, $qGroupID);

		$this->questionID = $questionID;
		$this->answer = $answer;
		$this->qGroupID = $qGroupID;
	}
}
?>