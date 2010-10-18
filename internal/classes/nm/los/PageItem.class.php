<?php
/**
 * This class defines the PageItem data type.
 * @author Jacob Bates <jbates@mail.ucf.edu>
 */

/**
 * This class defines the PageItem data type.
 * It is used simply for representing data in memory, and has no methods.
 */
class nm_los_PageItem
{
	public $pageItemID;
	public $component;
	//public $layoutItemID;
	public $data;
	public $media;
	public $advancedEdit;
	public $options;
	
	function __construct($pageItemID=0, $component='', $data='', $media=Array(), $advancedEdit=0, $options=NULL)
	{
		$this->pageItemID = $pageItemID;
		//$this->layoutItemID = $layoutItemID;
		$this->component = $component;
		$this->data = $data;
		$this->media = $media;
		$this->advancedEdit = $advancedEdit;
		$this->options = $options;
	}
}
?>
