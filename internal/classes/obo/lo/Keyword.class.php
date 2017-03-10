<?php

namespace obo\lo;
class Keyword
{
	public $keywordID; // Number:
	public $name; // String:  The formal name of the keyword

	function __construct($keywordID=0, $name='')
	{
		$this->keywordID = $keywordID;
		$this->name = $name;
	}
}
