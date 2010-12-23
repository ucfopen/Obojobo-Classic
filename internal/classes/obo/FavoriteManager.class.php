<?php
/**
 * This class handles all database calls and logic pertaining to Favorite LOs
 * @author Luis Estrada <lestrada@mail.ucf.edu>
 */

/**
 * This class handles all database calls and logic pertaining to Favorite LOs
 */
namespace obo;
class FavoriteManager extends \rocketD\db\DBEnabled
{
	// INCOMPLETE
	/*
	const table = "lo_map_favorite";
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
	
	public function addFavorite($loid = 0)
	{
	    if(!is_numeric($loid) || $loid < 1)
            return new \obo\util\Error(0); // error: invalid input
        
        //check to see if it exists
        $qstr = "SELECT * FROM `".self::table."` WHERE `uid`='?' AND `loid`='?'";
        $q = $this->DBM->querySafe($qstr, $_SESSION['UID'], $loid);

        if($this->DBM->fetch_num($q) > 0)
            return new \obo\util\Error(0);
        
	    $qstr = "INSERT INTO ".self::table." SET `uid`='?', loid='?'";
		if( !($q = $this->DBM->querySafe($qstr, $_SESSION['UID'], $loid)) )
		{
		    \rocketD\util\Log::trace(mysql_error(), true);
			$this->DBM->rollback();
			//die();
			return false;
		}
		
		return true;
	}
	
	public function deleteFavorite($loid = 0)
	{
        if(!is_numeric($loid) || $loid < 1)
            return new \obo\util\Error(0); // error: invalid input

        //check to see if it exists
        $qstr = "SELECT * FROM `".self::table."` WHERE `uid`='?' AND `loid`='?'";
        $q = $this->DBM->querySafe($qstr, $_SESSION['UID'], $loid);

        if($this->DBM->fetch_num($q) == 0)
            return new \obo\util\Error(0);
            
        $qstr = "DELETE FROM `".self::table."` WHERE `uid`='?' AND `loid`='?' LIMIT 1";
	    if( !($q = $this->DBM->querySafe($qstr, $_SESSION['UID'], $loid)) )
	    {
			\rocketD\util\Log::trace(mysql_error(), true);
			$this->DBM->rollback();
			//die();
			return false;	
		}
		
		return true;
	}
	
	public function getFavorites()
	{
	    $qstr = "SELECT `loid` FROM `".self::table."` WHERE `uid`='?'";
		if( !($q = $this->DBM->querySafe($qstr, $_SESSION['UID'])) )
	    {
			\rocketD\util\Log::trace(mysql_error(), true);
			$this->DBM->rollback();
			//die();
			return false;	
		}
		
		$loArr = array();
		$loMan = \obo\lo\LOManager::getInstance();
		while($r = $this->DBM->fetch_obj($q))
		{
		    $lo = $loMan->getLO($r->loid, 'meta');
		    if($lo !== false)
                $loArr[] = $lo; 
		}
		
		return $loArr;
	}
	*/
}
?>