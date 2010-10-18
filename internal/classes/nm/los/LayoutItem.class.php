<?php
/**
 * This class defines the Layout Item data type
 * @author Jacob Bates <jbates@mail.ucf.edu>
 */

/**
 * This class defines the Layout Item data type
 * It is used simply for representing data in memory, and has no methods.
 */
class nm_los_LayoutItem
{
	public $layoutItemID;
	public $name;			//String:  name of item (maps to name of page_item)
	public $component;		//String:  what component the item uses
	public $x;				//Number:
	public $y;				//Number:
	public $width;			//Number:
	public $height;			//Number:
	public $data;			//String:  default data for the page_item

	function __construct($layoutItemID=0, $name='', $component='', $x=0, $y=0, $width=0, $height=0, $data='')
	{
		$this->layoutItemID = $layoutItemID;
		$this->name = $name;
		$this->component = $component;
		$this->x = $x;
		$this->y = $y;
		$this->width = $width;
		$this->height = $height;
		$this->data = $data;
	}
}
?>