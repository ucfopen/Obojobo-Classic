<?php

require_once("Section.class.php");

class plg_sets_Text extends plg_sets_Section
{
	
	public function toHTML()
	{
		$find    = array('%%TITLE%%', '%%ORDER%%', '%%DESCRIPTION%%');
		$replace = array($this->title, $this->order, $this->description);
		return str_replace($find, $replace, $GLOBALS['TEXT_HTML']);
	}
	
	public function toXML()
	{
		return parent::toXML();
	}
	
	public function toForm()
	{
		$find    = array('%%TITLE%%', '%%ORDER%%', '%%DESCRIPTION%%');
		$replace = array(htmlentities($this->title), htmlentities($this->order), htmlentities($this->description));
		return str_replace($find, $replace, $GLOBALS['TEXT_FORM']);
	}
	
	public function __construct($param = null)
	{
		parent::__construct("text", $param);
		if (is_string($param) and $param){}
		if (is_array($param) and $param){}
	}
}

$GLOBALS['TEXT_HTML'] = <<<TEXTHTML
<div class="text">
	<h2 class="title">
		<span class="text">%%TITLE%%</span>
		<span class="type">Text</span>
		<span class="title_end">&nbsp;</span>
	</h2>
	<div class="description">
	%%DESCRIPTION%%
	</div>
</div>
TEXTHTML;
$GLOBALS['TEXT_FORM'] = <<<TEXTFORM
<ul class="text">
	<li><h2>
		<span class='text'>%%TITLE%%</span>
		<span class='type'>Text</span>
	</h2></li>
	<li class="delete">
		Delete
	</li>
	<li class="title">
		<label>Title</label>
		<input class="title" type="text" value="%%TITLE%%" />
	</li>
	<li class="order">
		<label>Order</label>
		<input class="order" value="%%ORDER%%" />
	</li>
	<li class="description">
		<label>Description</label>
		<textarea rows='0' cols='0' class="description">%%DESCRIPTION%%</textarea>
	</li>
</ul>
TEXTFORM;

?>
