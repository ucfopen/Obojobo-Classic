<?php

namespace obo\lo;
class Answer
{
	public $answerID; // Number:
	public $userID; // Number:  ID number of author
	public $answer; // String:  the text of the answer
	public $weight; // Number:  between 0 and 100, the percentage value of this answer
	public $feedback; // String:  text shown to user if this answer is chosen

	function __construct($answerID=0, $userID=0, $answer='', $weight=0, $feedback='')
	{
		$this->answerID = $answerID;
		$this->userID = $userID;
		$this->answer = $answer;
		$this->weight = $weight;
		$this->feedback = $feedback;
	}

	public function cleanForAssessmentAttempt($clearAnswer = false)
	{
		$this->weight = null;
		$this->feedback = null;
		if($clearAnswer) $this->answer = null;
	}

	public function __sleep()
	{
		if(isset($this->feedback) && $this->feedback instanceof \stdClass) $this->feedback = (array) $this->feedback;
		return ['answerID', 'userID', 'answer', 'weight', 'feedback'];
	}
}
