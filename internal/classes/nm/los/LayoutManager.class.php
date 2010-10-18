<?php
/**
 * This class is a singleton class that manages page layouts
 * @author Jacob Bates <jbates@mail.ucf.edu>
 * @author Luis Estrada <lestrada@mail.ucf.edu>
 */

/**
 * This class is a singleton class that manages page layouts
 * This includes creating, retrieving, and deleting of data.
 */
class nm_los_LayoutManager extends core_db_dbEnabled
{
	static $instance;
	public $layouts;

	static public function getInstance()
	{
		if(!isset(self::$instance))
		{
			$selfClass = __CLASS__;
			self::$instance = new $selfClass();
		}
		return self::$instance;
	}
	
	function __construct()
	{
		//This is done so that flash treats this as an object instead of an array
		$this->defaultDBM();
	}

	/**
	 * Loads the requested layout into its own list of layouts (the public member $layouts)
	 * @param $layoutID (number) layout ID
	 * @return (Layout) The requested layout
	 * @return (bool) FALSE if incorrect parameters
	 */
	function getLayout($layoutID=0)
	{
		if(nm_los_Validator::isPosInt($layoutID) && !isset($this->layouts[$layoutID])) //If the layout hasn't already been loaded, load it
		{
			$this->defaultDBM();
			$q = $this->DBM->querySafe("SELECT * FROM ".cfg_obo_Layout::TABLE." WHERE ".cfg_obo_Layout::ID."='?' LIMIT 1", $layoutID);
			$r = $this->DBM->fetch_obj($q);
			$itmMan = nm_los_LayoutItemManager::getInstance();
			$itmMan->getItemsFromList($r->{cfg_obo_Layout::ITEMS});
			$this->getTagsForLayout($r->{cfg_obo_Layout::ID});
			$layout = new nm_los_Layout((int)$r->{cfg_obo_Layout::ID}, $r->{cfg_obo_Layout::TITLE}, 0, $itmMan->getItemsFromList($r->{cfg_obo_Layout::ITEMS}), $this->getTagsForLayout($r->{cfg_obo_Layout::ID}));
			$this->layouts[$layoutID] = $layout;
			return $layout;
		}
		return false;
	}
	
	/**
	 * Gets all the layouts available
	 * @return (Array<Layout>) The array of all layouts
	 */
	function getAllLayouts()
	{
		$q = $this->DBM->query("SELECT * FROM ".cfg_obo_Layout::TABLE); // no need for querySafe
		$itmMan = nm_los_LayoutItemManager::getInstance();
		$layouts = array();	
		while($r = $this->DBM->fetch_obj($q))
		{
			$layouts[$r->{cfg_obo_Layout::ID}] = new nm_los_Layout($r->{cfg_obo_Layout::ID}, $r->{cfg_obo_Layout::TITLE}, '', $itmMan->getItemsFromList($r->{cfg_obo_Layout::ITEMS}), $this->getTagsForLayout($r->{cfg_obo_Layout::ID}));
		}
		return $layouts;
	}

	/**
	 * Gets an array of tag strings for the given layout
	 * @param $lay_id (number) Layout ID
	 * @return (Array<string>) An array of tag names
	 */
	private function getTagsForLayout($lay_id)
	{    
		$qstr = "SELECT ".cfg_obo_Layout::TAG_TITLE." FROM ".cfg_obo_Layout::TAG_TABLE." WHERE ".cfg_obo_Layout::ID." IN
		(SELECT ".cfg_obo_Keyword::ID." FROM ".cfg_obo_Keyword::MAP_TABLE." WHERE ".cfg_obo_Keyword::MAP_TYPE."='lay' AND ".cfg_obo_Keyword::MAP_ITEM."='?')";
		if( !($q = $this->DBM->querySafe($qstr, $lay_id)) )
		{
			core_util_Log::trace(mysql_error(), true);
			$this->DBM->rollback();
			return false;
		}
		
		$tagarr = array();
		while( $r = $this->DBM->fetch_obj($q) )
		{
			$tagarr[] = $r->{cfg_obo_Layout::TAG_TITLE};
		}
		return $tagarr;
	}

	/**
	 * Gets all tags from the database
	 * @return (Array<string>) An array of tag names
	 */
	public function getAllTags()
	{
		$qstr = "SELECT ".cfg_obo_Layout::TAG_TITLE." FROM ".cfg_obo_Layout::TAG_TABLE."";
		
		if( !($q = $this->DBM->query($qstr)) ) // no need for querySafe
		{
			core_util_Log::trace(mysql_error(), true);
			$this->DBM->rollback();
			return false;
		}
		
		$tagArr = array();
		while( $r = $this->DBM->fetch_obj($q) )
		{
			$tagArr[] = $r->{cfg_obo_Layout::TAG_TITLE};
		}
		return $tagArr;
	}
}
?>
