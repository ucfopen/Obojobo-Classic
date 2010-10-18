<?php
/**
 * This class handles all logic dealing with PageItems
 * @author Jacob Bates <jbates@mail.ucf.edu>
 * @author Luis Estrada <lestrada@mail.ucf.edu>
 */

/**
 * This class handles all logic dealing with PageItems
 * This includes creating, retrieving, and deleting of data.
 */
class nm_los_PageItemManager extends core_db_dbEnabled
{
	private static $instance;
	
	public function __construct()
	{
	    $this->defaultDBM();
	}
	
	static public function getInstance()
	{
		if(!isset(self::$instance))
		{
			$selfClass = __CLASS__;
			self::$instance = new $selfClass();
		}
		return self::$instance;
	}
	
	/**
	 * Gets a PageItem from the database
	 * @param $itmid (number) PageItem ID
	 * @return (PageItem) the requested PageItem object
	 */
	function getItem($itmid = 0)
	{
        if(!is_numeric($itmid) || $itmid < 1)
		{
            return false;
		}
        $qstr = "SELECT * FROM `".cfg_obo_Page::ITEM_TABLE."` WHERE ".cfg_obo_Page::ITEM_ID."='?' LIMIT 1";
		if(!($q = $this->DBM->querySafe($qstr, $itmid)))
		{
		    core_util_Log::trace(mysql_error(), true);
		    $this->DBM->rollback();
			return false;
		}
		$r = $this->DBM->fetch_obj($q);
		//core_util_Log::trace('get page item: ' . $r->data);
		$itm = new nm_los_PageItem($r->{cfg_obo_Page::ITEM_ID}, $r->{cfg_obo_Page::ITEM_COMPONENT},  $r->{cfg_obo_Page::ITEM_DATA}, Array(), $r->{cfg_obo_Page::ITEM_ADVANCED_EDIT}, unserialize(base64_decode($r->{cfg_obo_Page::ITEM_OPTIONS})));
		
		//Gather media into an Array (nothing is created if no media is found)
		$qstr = "SELECT ".cfg_obo_Media::ID." FROM ".cfg_obo_Media::MAP_TABLE." WHERE ".cfg_obo_Page::ITEM_ID."='?' ORDER BY ".cfg_obo_Media::MAP_ORDER." ASC";
		if(!($q = $this->DBM->querySafe($qstr, $itmid)))
		{
		    $this->DBM->rollback();
		    core_util_Log::trace(mysql_error(), true);
			return false;
		}

		$mediaMan = nm_los_MediaManager::getInstance();
		while($r = $this->DBM->fetch_obj($q))
		{
			$itm->media[] = $mediaMan->getMedia($r->{cfg_obo_Media::ID});
		}
		return $itm;
	}

	/**
	 * Creates a new item and links any media associated with it in lo_map_media
	 * @param $itm (PageItem) new PageItem data
	 * @return (PageItem) the PageItem object with the new ID included
	 */
	function newItem($itm)
	{
		if( !($q = $this->DBM->querySafe("INSERT INTO ".cfg_obo_Page::ITEM_TABLE." SET ".cfg_obo_Page::ITEM_COMPONENT."='?', ".cfg_obo_Page::ITEM_DATA."='?',".cfg_obo_Page::ITEM_ADVANCED_EDIT."='?', ".cfg_obo_Page::ITEM_OPTIONS."='?'", $itm['component'], $itm['data'], $itm['advancedEdit'], base64_encode(serialize($itm['options'])))) )
		{
			core_util_Log::trace(mysql_error(), true);
			$this->DBM->rollback();
			//die();
			return false;
		}
		$itm['pageItemID'] = $itmid = $this->DBM->insertID;
		$arrRef = $itm['media'];

		//Update lo_map_media
		foreach($arrRef as $key => $val)
		{
			if( !($q = $this->DBM->querySafe("INSERT INTO ".cfg_obo_Media::MAP_TABLE." SET ".cfg_obo_Page::ITEM_ID."='?', ".cfg_obo_Media::MAP_ORDER."='?', ".cfg_obo_Media::ID."='?'", $itmid, $key, $val['mediaID'])) )
			{
				core_util_Log::trace(mysql_error(), true);
				$this->DBM->rollback();
				//die();
				return false;
			}
		}

		return $itm;
	}
	
	/**
	 * Deletes a PageItem from the database
	 * @param $itm (number) PageItem ID
	 * @return (bool) TRUE if successful, FALSE if incorrect PageItem ID
	 */
	public function delItem($itm = 0)
	{
		if(!is_numeric($itm) || $itm < 1)
		{
			return false;
		}

		//Delete media references
		$qstr = "DELETE FROM ".cfg_obo_Media::MAP_TABLE." WHERE ".cfg_obo_Page::ITEM_ID."='?'";
		if( !($q = $this->DBM->querySafe($qstr, $itm)) )
		{
			$this->DBM->rollback();
			core_util_Log::trace(mysql_error(), true);
			//die();
			return false;	
		}
		
		//Delete item
		$qstr = "DELETE FROM ".cfg_obo_Page::ITEM_TABLE." WHERE ".cfg_obo_Page::ITEM_ID."='?' LIMIT 1";
		if( !($q = $this->DBM->querySafe($qstr, $itm)) )
		{
			$this->DBM->rollback();
			core_util_Log::trace(mysql_error(), true);
			//die();
			return false;	
		}
		
		return true;
	}
}
?>
