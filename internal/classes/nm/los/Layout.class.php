<?php
/**
 * This class defines the data type for Layouts
 * @author Jacob Bates <jbates@mail.ucf.edu>
 */

/**
 * This class defines the data type for Layouts
 * It is used simply for representing data in memory, and has no methods.
 */
class nm_los_Layout
{
	public $layoutID;			//Number:  layout id
	public $name;		//String:  layout name
	public $thumb;		//Number:  ID of thumbnail image
	public $items;		//String:  item ids separated by " " (spaces)
	public $tags;		//Array:  of strings

	function __construct($layoutID=0, $name='', $thumb=0, $items='', $tags=array())
	{
		$this->layoutID = $layoutID;
		$this->name = $name;
		$this->thumb = $thumb;
		$this->items = $items;
		$this->tags = $tags;
	}
}
?>
