<?php

abstract class plugin_sets_Section
{
	protected $xml;
	
	public	$title,
		$order,
		$description;
		
	private function parseDescription()
	{
		$sxml = new SimpleXMLElement($this->xml);
		$this->description = (string)$sxml->description;
	}
	private function parseOrder()
	{
		$sxml = new SimpleXMLElement($this->xml);
		$this->order = (int)$sxml->order;
	}
	private function parseTitle()
	{
		$sxml = new SimpleXMLElement($this->xml);
		$this->title = (string)$sxml->title;
	}
	
	public function toXML()
	{
		$sxml = new SimpleXMLElement("<section/>");
		$sxml->addAttribute("type", $this->type);
		$sxml->addChild("title", xmlentities($this->title));
		$sxml->addChild("order", xmlentities($this->order));
		$sxml->addChild("description", xmlentities($this->description));
		return $sxml->asXML();
	}
	
	abstract function toHTML();
	abstract function toForm();
	
	public function __construct($type, $param = null)
	{
		$this->type = $type;
		if (is_string($param) and $param)
		{
			$this->xml = $param;
			$this->parseDescription();
			$this->parseOrder();
			$this->parseTitle();
		}
		if (is_array($param) and $param)
		{
			$this->title       = $param['title'];
			$this->order       = $param['order'];
			$this->description = $param['description'];
		}
	}
}
?>
