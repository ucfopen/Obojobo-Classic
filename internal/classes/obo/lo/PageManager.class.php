<?php

namespace obo\lo;
class PageManager extends \rocketD\db\DBEnabled
{
	use \rocketD\Singleton;

	/**
	 * Accepts a Page object, saves it into the database, and assigns the Page object a new ID number
	 * @param $pageObj (Page) new Page object
	 * @return (boolean) false if no page changes were saved
	 */
	public function newPage($page)
	{
		if( !($page instanceof \obo\lo\Page) )
		{
			return false;
		}

		if($page->pageID == 0)
		{
			$qstr = "INSERT INTO ".\cfg_obo_Page::TABLE." SET ".\cfg_obo_Page::PAGE_DATA."='?'";
			if( !($q = $this->DBM->querySafe($qstr, $this->db_serialize($page)) ) )
			{
				trace($this->DBM->error(), true);
				$this->DBM->rollback();
				return false;
			}
			$page->pageID = $this->DBM->insertID;

			return true;
		}
		return false;
	}

	public function mapPageToLO($loID, $pageID, $orderIndex)
	{
		$qstr = "INSERT IGNORE INTO ".\cfg_obo_Page::MAP_TABLE." SET ".\cfg_obo_LO::ID."='?', ".\cfg_obo_Page::ID."='?', ".\cfg_obo_Page::MAP_ORDER."='?'";
		if( !( $this->DBM->querySafe($qstr, $loID, $pageID, $orderIndex) ) )
		{
			trace($this->DBM->error(), true);
			$this->DBM->rollback();
			return false;
		}
		return true;
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

		//Clean out entries for this group in the mapping table
		if( !($q = $this->DBM->querySafe("DELETE FROM ".\cfg_obo_Page::MAP_TABLE." WHERE ".\cfg_obo_Page::ID."='?'", $pid)) )
		{
			$this->DBM->rollback();
			return false;
		}

		//Delete the page
		if( !($q = $this->DBM->querySafe("DELETE FROM ".\cfg_obo_Page::TABLE." WHERE ".\cfg_obo_Page::ID."='?' LIMIT 1", $pid)) )
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
			\rocketD\util\Error::getError(2);
			return false;
		}

		//get page row from database
		$qstr = "SELECT * FROM ".\cfg_obo_Page::TABLE." WHERE ".\cfg_obo_Page::ID."='?' LIMIT 1";
		if(!($q = $this->DBM->querySafe($qstr, $pgid)))
		{
			trace($this->DBM->error(), true);
			return false;
		}

		//check if the page exists
		if(!($r = $this->DBM->fetch_obj($q)))
		{
			trace('page does not exist ' . $pageid, true);
			return false; // error: page does not exist
		}
		$page = $this->db_unserialize($r->{\cfg_obo_Page::PAGE_DATA});
		$page->pageID = $r->{\cfg_obo_Page::ID};

		return $page;
	}

	public function getPageCountForLOID($loID)
	{
		if(!is_numeric($loID) || $loID <= 0)
		{
			trace('failed input validation', true);
			return false;
		}

		if($pages = \rocketD\util\Cache::getInstance()->getPagesForLOID($loID))
		{
			return count($pages);
		}
		$q = $this->DBM->querySafe("SELECT Count(".\cfg_obo_Page::ID.") AS num FROM ".\cfg_obo_Page::MAP_TABLE." WHERE ".\cfg_obo_LO::ID."='?' ORDER BY ".\cfg_obo_Page::MAP_ORDER." ASC", $loID);
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

		if($pages = \rocketD\util\Cache::getInstance()->getPagesForLOID($loID))
		{
			return $pages;
		}

		$pages = array();
		$q = $this->DBM->querySafe("SELECT ".\cfg_obo_Page::ID." FROM ".\cfg_obo_Page::MAP_TABLE." WHERE ".\cfg_obo_LO::ID."='?' ORDER BY ".\cfg_obo_Page::MAP_ORDER." ASC", $loID);
		while($r = $this->DBM->fetch_obj($q))
		{
			$pages[] = $this->getPage($r->{\cfg_obo_Page::ID});

		}

		\rocketD\util\Cache::getInstance()->setPagesForLOID($loID, $pages);

		return $pages;
	}
}
