<?php
/**
 * This class defines the Question data type.
 * @author Jacob Bates <jbates@mail.ucf.edu>
 */

/**
 * This class defines the Question data type.
 * It is used simply for representing data in memory, and has no methods.
 */
namespace obo\lo;
class Question
{
	public $questionID;				//Number:
	public $userID;			//Number:  ID of userID
	public $itemType;			//String:  'QA', 'MC', etc
	public $answers;		//Array: answers
	public $perms;			//Permissions object:  merged from global and user
	public $items;			//Array: of page items
	public $questionIndex;
	
	function __construct($questionID=0, $userID=0, $itemType='QA', $answers=Array(), $perms=0, $items=Array())
	{
		$this->questionID = $questionID;
		$this->userID = $userID;
		$this->itemType = $itemType;
		$this->answers = $answers;
		$this->perms = $perms;
		$this->items = $items;
	}
}
?>
