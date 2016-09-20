<?php

namespace obo\lo;
class Page
{
	const SECTION_CONTENT = 1;
	const SECTION_PRACTICE = 2;
	const SECTION_ASSESSMENT = 3;

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

	public function __sleep()
	{
		return ['pageID', 'title', 'userID', 'layoutID', 'createTime', 'items', 'questionID'];
	}
}
