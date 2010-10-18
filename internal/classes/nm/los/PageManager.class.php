<?php
/**
 * This class contains all logic pertaining to Pages
 * @author Jacob Bates <jbates@mail.ucf.edu>
 * @author Luis Estrada <lestrada@mail.ucf.edu>
 */

/**
 * This class contains all logic pertaining to Pages
 * This includes creating, retrieving, and deleting of data.
 */
class nm_los_PageManager extends core_db_dbEnabled
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
	 * Accepts a Page object, saves it into the database, and assigns the Page object a new ID number
	 * @param $pageObj (Page) new Page object
	 * @return (Page) Page object with new ID 
	 */
	public function newPage($pageObj)
	{
		$qstr = "INSERT INTO ".cfg_obo_Page::TABLE." SET ".cfg_obo_Page::TITLE."='?', ".cfg_core_User::ID."='?', ".cfg_obo_Layout::ID."='?', ".cfg_obo_Question::ID."='?', ".cfg_obo_Page::TIME."=UNIX_TIMESTAMP()";
		if( !($q = $this->DBM->querySafe($qstr, $pageObj['title'], $pageObj['userID'], $pageObj['layoutID'], $pageObj['questionID'])) )
		{
			trace(mysql_error(), true);
			$this->DBM->rollback();
			return false;
		}
		
		$pageObj['pageID'] = $this->DBM->insertID;
		$pgid = $this->DBM->insertID;
		
		//Make new items if they are needed, otherwise link them to the current page
		$itemMan = nm_los_PageItemManager::getInstance();
		foreach($pageObj['items'] as $key => &$val)
		{
			if($val['pageItemID'] == 0)
			{
				$val = $itemMan->newItem($val);
			}
		    $qstr = "INSERT INTO ".cfg_obo_Page::MAP_ITEM_TABLE." SET ".cfg_obo_Page::ID."='?', ".cfg_obo_Page::MAP_ITEM_ORDER."='?', ".cfg_obo_Page::ITEM_ID."='?'";
			if( !($q = $this->DBM->querySafe($qstr, $pgid, $key, $val['pageItemID'])) )
			{
				trace(mysql_error(), true);
				$this->DBM->rollback();
    			return false;
			}
		}

		return $pageObj;
	}

	/**
	 * Deletes a Page from the database
	 * @param $pid (number) Page ID
	 * @return (bool) True if successful, False if incorrect parameter
	 * @deprecated not used anymore...
	 */
	public function delPage($pid=0)
	{
		if(!is_numeric($pid) || $pid <= 0)
		{
			trace('failed input validation', true);
			return false;
		}
		
		//Gather up a list of page items to delete
		$qstr = "SELECT 
				".cfg_obo_Page::MAP_ITEM." 
			FROM ".cfg_obo_Page::MAP_TABLE."
			WHERE ".cfg_obo_Page::ID."='?'
			AND ".cfg_obo_Page::MAP_ITEM."
			NOT IN (
					SELECT ".cfg_obo_Page::MAP_ITEM." 
					FROM ".cfg_obo_Page::MAP_TABLE."
					WHERE ".cfg_obo_Page::ID." != '?'
			)";

		$q = $this->DBM->querySafe($qstr, $pid, $pid);
		
		$itemMan = nm_los_PageItemManager::getInstance();			
		while($r = $this->DBM->fetch_obj($q))
		{
			$itemMan->delItem($r->{cfg_obo_Page::MAP_ITEM});
		}
		//Clean out entries for this group in the mapping table
		if( !($q = $this->DBM->querySafe("DELETE FROM ".cfg_obo_Page::MAP_TABLE." WHERE ".cfg_obo_Page::ID."='?'", $pid)) )
		{
			$this->DBM->rollback();
			//die();
			return false;	
		}
		
		//Delete the page
		if( !($q = $this->DBM->querySafe("DELETE FROM ".cfg_obo_Page::TABLE." WHERE ".cfg_obo_Page::ID."='?' LIMIT 1", $pid)) )
		{
			$this->DBM->rollback();
			return false;	
		}
		
		return true;
	}

	/**
	 * Gets a Page object from the database
	 * @param $pgid (number) Page ID
	 * @return (Page) requested Page object
	 */
	public function getPage($pgid = 0)
	{
		if(!is_numeric($pgid) || $pgid <= 0)
		{
			
			
			core_util_Error::getError(2);
			return false;
		}
		
        //get page row from database
        $qstr = "SELECT * FROM ".cfg_obo_Page::TABLE." WHERE ".cfg_obo_Page::ID."='?' LIMIT 1";
	    if(!($q = $this->DBM->querySafe($qstr, $pgid)))
		{
			trace(mysql_error(), true);
			$this->DBM->rollback();
			return false;
		}
		
		//check if the page exists
		if(!($r = $this->DBM->fetch_obj($q)))
		{
		    return false; // error: page does not exist
		}
		
		//if the question for the page has id = 0 then set it to -1
		if($r->{cfg_obo_Question::ID} == 0)
		{
			$r->{cfg_obo_Question::ID} = -1;
		}
		
		//create the page object
		$pg = new nm_los_Page($r->{cfg_obo_Page::ID}, $r->{cfg_obo_Page::TITLE}, $r->{cfg_core_User::ID}, $r->{cfg_obo_Layout::ID}, $r->{cfg_obo_Page::TIME}, $r->{cfg_obo_Question::ID});
		
		//// TODO: does this do anthing?
		//$layoutMan = nm_los_LayoutManager::getInstance();
		//$layoutMan->getLayout($r->{cfg_obo_Layout::ID});
		////
		//get items that are in the page
		
		$qstr = "SELECT ".cfg_obo_Page::ITEM_ID." FROM ".cfg_obo_Page::MAP_ITEM_TABLE." WHERE ".cfg_obo_Page::ID."='?' ORDER BY ".cfg_obo_Page::MAP_ITEM_ORDER." ASC";
		if(!($q = $this->DBM->querySafe($qstr, $pgid)))
		{
		    trace(mysql_error(), true);
			$this->DBM->rollback();
			return false;
		}
		
		//add the items to the page
		$itemMan = nm_los_PageItemManager::getInstance();
		while($r = $this->DBM->fetch_obj($q))
		{
			$pg->items[] = $itemMan->getItem($r->{cfg_obo_Page::ITEM_ID});
		}
		
		return $pg;
	}
	
	public function getPageCountForLOID($loID)
	{
		if(!is_numeric($loID) || $loID <= 0)
		{
			trace('failed input validation', true);
			return false;
		}
	
		
		if($pages = core_util_Cache::getInstance()->getPagesForLOID($loID))
		{
			return count($pages);
		}
		$q = $this->DBM->querySafe("SELECT Count(".cfg_obo_Page::ID.") AS num FROM ".cfg_obo_Page::MAP_TABLE." WHERE ".cfg_obo_LO::ID."='?' ORDER BY ".cfg_obo_Page::MAP_ORDER." ASC", $loID);
		if($r = $this->DBM->fetch_obj($q))
		{
			return $r->num;
		}
		return null;
	}
	
	public function getPagesForLOID($loID)
	{
		if(!is_numeric($loID) || $loID <= 0)
		{
			trace('failed input validation', true);
			return false;
		}
	
		
				
		// try to retrieve from cache first
		
		if($pages = core_util_Cache::getInstance()->getPagesForLOID($loID))
		{
			return $pages;
		}

		$pages = array();
		$q = $this->DBM->querySafe("SELECT ".cfg_obo_Page::ID." FROM ".cfg_obo_Page::MAP_TABLE." WHERE ".cfg_obo_LO::ID."='?' ORDER BY ".cfg_obo_Page::MAP_ORDER." ASC", $loID);
		while($r = $this->DBM->fetch_obj($q))
		{
			$pages[] = $this->getPage($r->{cfg_obo_Page::ID});
		}
	
		core_util_Cache::getInstance()->setPagesForLOID($loID, $pages);
		
		return $pages;
	}

}
?>
