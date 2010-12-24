<?php
/**
 * This class defines the data type for Keywords
 * @author Jacob Bates <jbates@mail.ucf.edu>
 */

/**
 * This class defines the data type for Keywords
 * It is used simply for representing data in memory, and has no methods.
 */
namespace obo\lo;
class Keyword
{
	public $keywordID;		//Number:
	public $name;	//String:  The formal name of the keyword

	function __construct($keywordID=0, $name=''){
		$this->keywordID = $keywordID;
		$this->name = $name;
	}
}
?>