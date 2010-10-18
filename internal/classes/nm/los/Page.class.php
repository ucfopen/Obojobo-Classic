<?php
/**
 * This class defines the Page data type
 * @author Jacob Bates <jbates@mail.ucf.edu>
 */

/**
 * This class defines the Page data type.
 * A Page can contain many PageItems.
 * It is used simply for representing data in memory, and has no methods.
 */
class nm_los_Page
{
	public $pageID;				//Number:  database id
	public $title;			//String:  formal title of the page
	public $userID;			//User:  creator of page
	public $layoutID;			//Layout:  size and position data of screen elements
	public $createTime;			//Timestamp:  Creation date
	public $items;			//Array<PageItems>:  of items on page
	public $questionID;			//Number: question id that filters the content page.

	function __construct($pageID=0, $title='', $userID=0, $layoutID=0, $createTime=0, $questionID=0, $items=Array())
	{
		$this->pageID = $pageID;
		$this->title = $title;
		$this->userID = $userID;
		$this->layoutID = $layoutID;
		$this->createTime = $createTime;
		$this->items = $items;
		$this->questionID = $questionID;
	}
}
?>
