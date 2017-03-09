<?php

namespace obo\lo;
class Question
{
	public $questionID; // Number:
	public $userID; // Number:  ID of userID
	public $itemType; // String:  'QA', 'MC', etc
	public $answers; // Array: answers
	public $perms; // Permissions object:  merged from global and user
	public $items; // Array: of page items
	public $questionIndex;
	public $feedback;

	function __construct($questionID=0, $userID=0, $itemType='QA', $answers=Array(), $perms=0, $items=Array())
	{
		$this->questionID = $questionID;
		$this->userID = $userID;
		$this->itemType = $itemType;
		$this->answers = $answers;
		$this->perms = $perms;
		$this->items = $items;
		$this->questionIndex = 0;
	}

	public function __sleep()
	{
		if(isset($this->feedback) && $this->feedback instanceof \stdClass) $this->feedback = (array) $this->feedback;
		return ['questionID', 'userID', 'itemType', 'answers', 'perms', 'items', 'feedback', 'questionIndex'];
	}
}
