<?php

class plg_sets_Asset extends plg_sets_Section
{
	public $id;
	
	public function toHTML()
	{
		$find    = array('%%TITLE%%', '%%ORDER%%', '%%DESCRIPTION%%', '%%ID%%', '%%MEDIA%%', '%%TYPE%%');
		$media = $GLOBALS['API']->getMedia(array($this->id));
		$replace = array(
			$this->title, 
			$this->order, 
			$this->description, 
			$this->id, 
			$this->getMediaHTML(),
			$media[0]->itemType
		);
		return str_replace($find, $replace, $GLOBALS['ASSET_HTML']);
	}
	
	public function toXML()
	{
		$xml  = parent::toXML();
		$sxml = new SimpleXMLElement($xml);
		$sxml->addChild("id", xmlentities($this->id));
		return $sxml->asXML();
	}
	
	public function toForm()
	{
		$find    = array('%%TITLE%%', '%%ORDER%%', '%%DESCRIPTION%%', '%%OPTIONS%%');
		$replace = array(htmlentities($this->title), htmlentities($this->order), htmlentities($this->description), $this->getAssetList());
		return str_replace($find, $replace, $GLOBALS['ASSET_FORM']);
	}
	
	private function parseId()
	{
		$sxml     = new SimpleXMLElement($this->xml);
		$this->id = (string)$sxml->id;
	}
	
	private function getMediaHTML()
	{
		return null;
	}
	
	private function getAssetList()
	{
		$assets = $GLOBALS['API']->getMedia();
		if (empty($assets)) return null;
		
		$options = array();
		#create option array
		foreach ($assets as $asset)
		{
			$option   = new SimpleXMLElement("<option/>");
			$option->addAttribute("value", $asset->mediaID);
			$option[0] = "{$asset->itemType}: {$asset->title}";
			if ($this->id == $asset->mediaID)
			{
				$option->addAttribute("selected", "selected");
			}
			array_push($options, $option);
		}
		
		#create output list
		$output = '';
		foreach ($options as $option)
		{
			$output .= str_replace('<?xml version="1.0"?>', "", $option->asXML());
		}
		return $output;
	}
	
	public function __construct($param = null)
	{
		parent::__construct("asset", $param);
		if (is_string($param) and $param)
		{
			$this->parseId();
		}
		if (is_array($param) and $param)
		{
			$this->id = $param['id'];
		}
	}
}

$GLOBALS['ASSET_HTML'] = <<<ASSETHTML
<div class="asset">
	<h2 class="title">
		<span class='text'><a href='{$GLOBALS['MEDIA_URL']}?id=%%ID%%'>%%TITLE%%</a></span>
		<span class='type'>Asset(%%TYPE%%)</span>
		<span class="title_end">&nbsp;</span>
	</h2>
	<div class="description">
	%%DESCRIPTION%%
	</div>
</div>
ASSETHTML;
$GLOBALS['ASSET_FORM'] = <<<ASSETFORM
<ul class="asset">
	<li><h2>
		<span class='text'>%%TITLE%%</span>
		<span class='type'>Asset</span>
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
	<li class="asset_id">
		<label>Choose Asset</label>
		<select class="asset_id">
			<option value="-1">choose...</option>
			%%OPTIONS%%
		</select>
	</li>
</ul>
ASSETFORM;


?>
