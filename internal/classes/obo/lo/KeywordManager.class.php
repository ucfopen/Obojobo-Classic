<?php
/**
 * This class handles all database calls and logic pertaining to Keywords
 * @author Jacob Bates <jbates@mail.ucf.edu>
 * @author Luis Estrada <lestrada@mail.ucf.edu>
 */

/**
 * This class handles all database calls and logic pertaining to Keywords
 * This includes creating, retrieving, and deleting of data.
 *
 * Item types:
 * 'l' = learning object
 * 'm' = media
 * 'lt' = learning object title (tokenized)
 * 'mt' = media title (tokenized)
 * 'lay' = layout
 */
namespace obo\lo;
class KeywordManager extends \rocketD\db\DBEnabled
{
	private static $instance;
	
	function __construct()
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
	 * Gathers all keywords associated with a given item
	 * @param $itmid (number) item id
	 * @param $itmtype (string) Type of item (valid: 'l','m','lt','mt','lay')
	 * @return (Array<string>)
	 */
	function getKeywordsFromItem($itmid=0, $itmtype='l')
	{
		if(!is_numeric($itmid) || $itmid < 1)
		{
			return false;
		}
		$qstr = "SELECT ".\cfg_obo_Keyword::NAME." FROM ".\cfg_obo_Keyword::TABLE." AS K
					LEFT JOIN ".\cfg_obo_Keyword::MAP_TABLE." AS kmap ON kmap.".\cfg_obo_Keyword::ID." = K.".\cfg_obo_Keyword::ID."
					WHERE kmap.".\cfg_obo_Keyword::MAP_TYPE." = '?' AND kmap.".\cfg_obo_Keyword::MAP_ITEM." = '?'";
		if(!($q = $this->DBM->querySafe($qstr, $itmtype, $itmid)))
		{
		    $this->DBM->rollback();
		    \rocketD\util\Log::trace(mysql_error(), true);
		    //die();
			return false;
		}
		$keyarr = array();
		while($r = $this->DBM->fetch_obj($q))
			$keyarr[] = $r->{\cfg_obo_Keyword::NAME};

		//Return the Array if keyword objects
		return $keyarr;
	}

	/**
	 * Gets a list of items that have any of the keywords given
	 * @param $keyArr (Array<string>) An array of keyword strings
	 * @param $itmtype (string) Item type (see table at top of source)
	 */
	function getItemsFromKeywords($keyArr, $itmtype='l')
	{
		if(!is_array($keyArr))
		{
		    return false;
		}

		$qstr = "SELECT ".\cfg_obo_Keyword::MAP_ITEM." FROM ".\cfg_obo_Keyword::MAP_TABLE." WHERE ".\cfg_obo_Keyword::MAP_TYPE."='?' AND ".\cfg_obo_Keyword::ID." IN (
					SELECT ".\cfg_obo_Keyword::ID." FROM ".\cfg_obo_Keyword::TABLE." WHERE ".\cfg_obo_Keyword::NAME." IN (";
		//loop through array to generate comma seperated list of item id's
		$numkeys = count($keyArr);
		for($i = 0; $i < $numkeys; $i++)
		{
			$tempstr = strtolower($keyArr[$i]);
			$qstr .= "'{$tempstr}'";
			if($i != $numkeys-1)
			    $qstr .= ',';
		}
		$qstr .= '))';
		
		//Gather up everything
		if(!($q = $this->DBM->querySafe($qstr, $itmtype)))
		{
		    $this->DBM->rollback();
		    \rocketD\util\Log::trace(mysql_error(), true);
		    //die();
			return false;
		}
		
		if(!($r = $this->DBM->fetch_obj($q)))
		    return false;
		$itmarr = array();
		do
		{
			$itmarr[] = $r->{\cfg_obo_Keyword::MAP_ITEM};
		} while($r = $this->DBM->fetch_obj($q));
		
		//Order the array by how often each id number occurs (from high to low)
		$itmarr = array_count_values($itmarr);	//count number of occurrences
		arsort($itmarr);						//order from high to low
		$itmarr = array_keys($itmarr);			//get the values back in order
		
		//Return the Array of ids
		return $itmarr;
	}

	/**
	 * Checks to see if a keyword already exists 
	 * @param $keyname (string) keyword
	 * @return (Keyword) keyword object if exists
	 * @return (bool) FALSE if keyword does not exist
	 */
	public function keywordExists($keyname='')
	{
		$qstr = "SELECT * FROM ".\cfg_obo_Keyword::TABLE." WHERE ".\cfg_obo_Keyword::NAME."='?' LIMIT 1";
		if( !($q = $this->DBM->querySafe($qstr, $keyname)) )
		{
			$this->DBM->rollback();
			\rocketD\util\Log::trace(mysql_error(), true);
			return false;
		}
		
		if( $r = $this->DBM->fetch_obj($q) )
		{
			return new \obo\lo\Keyword($r->{\cfg_obo_Keyword::ID}, $r->{\cfg_obo_Keyword::NAME});
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Creates a new keyword if that keyword does not exist already 
	 * @param $keyname (string) keyword
	 * @return (Keyword) keyword object
	 */
	public function newKeyword($keyname='')
	{
		if($keyname == '')
		{
		    return false;		
		}
		$key = $this->keywordExists($keyname);
		
		//Make keyword lowercase
		$keyname = strtolower($keyname);
		
		//If the keyword does not exist, create a new one
		if( $key == false )
		{
			//Create line in table
			$qstr = "INSERT INTO ".\cfg_obo_Keyword::TABLE." SET ".\cfg_obo_Keyword::NAME."='?'";
			if( !($q = $this->DBM->querySafe($qstr, $keyname)) )
			{
				$this->DBM->rollback();
				\rocketD\util\Log::trace(mysql_error(), true);
				return false;	
			}
			unset($key);
			$key = new \obo\lo\Keyword($this->DBM->insertID, $keyname);
		}
		return $key;
	}

	/**
	 * Checks to see if the keyword is already linked 
	 * @param $keyid (number) keyword ID
	 * @param $itmid (number) Item ID
	 * @param $itmtype (string) Item type (see table at top of source)
	 * @return (bool) TRUE if linked, FALSE if not
	 */
	private function linkedAlready($keyid=0, $itmid=0, $itmtype='l')
	{
		if($keyid != 0 && $itmid != 0)
		{
		    return false;
		}
		    
		$qstr = "SELECT ".\cfg_obo_Keyword::MAP_ITEM." FROM ".\cfg_obo_Keyword::MAP_TABLE." WHERE ".\cfg_obo_Keyword::ID."='?' AND ".\cfg_obo_Keyword::MAP_ITEM."='?' AND ".\cfg_obo_Keyword::MAP_TYPE."='?' LIMIT 1";
		if(!($q = $this->DBM->querySafe($qstr, $keyid, $itmid, $itmtype)))
		{
		    $this->DBM->rollback();
		    \rocketD\util\Log::trace(mysql_error(), true);
		    //die();
			return false;
		}
		if( $r = $this->DBM->fetch_obj($q) )
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Links keywords to the item from an array of keyword strings 
	 * @param $keyArr (Array<string>) array of keyword strings
	 * @param $itmid (number) Item ID
	 * @param $itmtype (string) Item type (see table at top of source)
	 * @return (bool) FALSE if incorrect parameters
	 */
	public function linkKeywordsFromArray($keyArr, $itmid=0, $itmtype='l')
	{
		if( !is_array($keyArr) || $itmid < 1)
		{
		    return false;
		}

		foreach($keyArr as $val)
		{
			$keyObj = $this->keywordExists($val);	//Check if the keyword exists
			if( $keyObj == false ) //If not, make a new one
				$keyObj = $this->newKeyword($val);
			$this->linkKeyword($keyObj->keywordID, $itmid, $itmtype);
		}
	}

	/**
	 * Links a keyword to an item 
	 * @param $keyid (number) keyword ID
	 * @param $itmid (number) Item ID
	 * @param $itmtype (string) Item type (see table at top of source)
	 * @return (bool) TRUE if link made, FALSE if linked already
	 */
	public function linkKeyword($keyid=0, $itmid=0, $itmtype='l')
    {
		if(!is_numeric($keyid) || $keyid < 0 || !is_numeric($itmid) || $itmid < 0)
		{
		    return false;
		}
		//If the keyword is not linked to this item already, link it
		if(!$this->linkedAlready($keyid, $itmid, $itmtype))
		{
			//Create entry in mapping table
			$qstr = "INSERT INTO ".\cfg_obo_Keyword::MAP_TABLE." SET ".\cfg_obo_Keyword::ID."='?', ".\cfg_obo_Keyword::MAP_TYPE."='?', ".\cfg_obo_Keyword::MAP_ITEM."='?'";
			if( !($q = $this->DBM->querySafe($qstr, $keyid, $itmtype, $itmid)) )
			{
				$this->DBM->rollback();
				\rocketD\util\Log::trace(mysql_error(), true);
				//die();
				return false;	
			}
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Unlinks a keyword from an item 
	 * @param $keyid (number) keyword ID
	 * @param $itmid (number) Item ID
	 * @param $itmtype (string) Item type (see table at top of source)
	 * @return (bool) TRUE if link broken, FALSE if incorrect parameters
	 */
	function unlinkKeyword($keyid=0, $itmid=0, $itmtype='l')
	{
        if(!is_numeric($keyid) || $keyid < 0 || !is_numeric($itmid) || $itmid < 0)
		{
		    return false;
	    }
		//Create entry in mapping table
		$qstr = "DELETE FROM ".\cfg_obo_Keyword::MAP_TABLE." WHERE ".\cfg_obo_Keyword::ID."='?' AND ".\cfg_obo_Keyword::MAP_TYPE."='?' AND ".\cfg_obo_Keyword::MAP_ITEM."='?' LIMIT 1";
		if( !($q = $this->DBM->querySafe($qstr, $keyid, $itmtype, $itmid)) )
		{
			$this->DBM->rollback();
			\rocketD\util\Log::trace(mysql_error(), true);
			return false;	
		}
		return true;
	}

	/**
	 * Unlinks a keyword from an item 
	 * @param $keyid (number) keyword ID
	 * @param $itmid (number) Item ID
	 * @param $itmtype (string) Item type (see table at top of source)
	 * @return (bool) TRUE if link broken, FALSE if incorrect parameters
	 */
	public function unlinkAllKeywords($itemid=0, $itemtype='l')
	{
		if(!is_numeric($itemid) || $itemid < 1)
		{
			return false;
		}
		$qstr = "DELETE FROM ".\cfg_obo_Keyword::MAP_TABLE." WHERE ".\cfg_obo_Keyword::MAP_ITEM."='?' AND ".\cfg_obo_Keyword::MAP_TYPE."='?'";
		if(!($q = $this->DBM->querySafe($qstr, $itemid, $itemtype)))
		{
			$this->DBM->rollback();
			\rocketD\util\Log::trace(mysql_error(), true);
			//die();
			return false;
		}
		
		return true;
	}
	
	public function deleteUnusedKeywords()
	{
		$qstr = "DELETE FROM ".\cfg_obo_Keyword::TABLE." WHERE ".\cfg_obo_Keyword::ID." NOT IN (SELECT ".\cfg_obo_Keyword::ID." FROM ".\cfg_obo_Keyword::MAP_TABLE.")";
		
		if(!($q = $this->DBM->query($qstr)))
		{
            $this->DBM->rollback();
            \rocketD\util\Log::trace(mysql_error(), true);
            //die();
			return false;
		}
		
		return true;
	}
}
?>
