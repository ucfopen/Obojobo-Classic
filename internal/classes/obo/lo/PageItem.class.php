<?php

namespace obo\lo;
class PageItem
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

	public function __sleep()
	{
		if(isset($this->options) && isset($this->$feedback) && $this->feedback instanceof \stdClass) $this->options = (array) $this->options;
		return ['pageItemID', 'component', 'data', 'media', 'advancedEdit', 'options'];
	}
}
