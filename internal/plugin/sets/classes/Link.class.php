<?php

require_once("Section.class.php");

class plg_sets_Link extends plg_sets_Section
{
	public $url;
	
	public function toHTML()
	{
		$find    = array('%%TITLE%%', '%%ORDER%%', '%%DESCRIPTION%%', '%%URL%%');
		$replace = array($this->title, $this->order, $this->description, $this->url);
		return str_replace($find, $replace, $GLOBALS['LINK_HTML']);
	}
	
	public function toXML()
	{
		$xml  = parent::toXML();
		$sxml = new SimpleXMLElement($xml);
		$sxml->addChild("url", xmlentities($this->url));
		return $sxml->asXML();
	}
	
	public function toForm()
	{
		$find    = array('%%TITLE%%', '%%ORDER%%', '%%DESCRIPTION%%', '%%URL%%');
		$replace = array(htmlentities($this->title), htmlentities($this->order), htmlentities($this->description), htmlentities($this->url));
		return str_replace($find, $replace, $GLOBALS['LINK_FORM']);
	}
	
	private function parseUrl()
	{
		$sxml      = new SimpleXMLElement($this->xml);
		$this->url = (string)$sxml->url;
	}
	
	public function __construct($param = null)
	{
		parent::__construct("link", $param);
		if (is_string($param) and $param)
		{
			$this->parseUrl();
		}
		if (is_array($param) and $param)
		{
			$this->url = $param['url'];
		}
	}
}

$GLOBALS['LINK_HTML'] = <<<LINKHTML
<div class="link">
	<h2 class="title">
		<span class="text"><a href="%%URL%%">%%TITLE%%</a></span>
		<span class='type'>Link</span>
		<span class="title_end">&nbsp;</span>
	</h2>
	<div class="description">%%DESCRIPTION%%</div>
</div>
LINKHTML;
$GLOBALS['LINK_FORM'] = <<<LINKFORM
<ul class="link">
	<li><h2>
		<span class='text'>%%TITLE%%</span>
		<span class='type'>Link</span>
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
	<li class="url">
		<label>URL</label>
		<input class="url" value="%%URL%%" />
	</li>
</ul>
LINKFORM;

?>
