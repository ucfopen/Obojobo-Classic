<?php
/**
 * This class handles all database calls and logic pertaining to Favorite LOs
 * @author Luis Estrada <lestrada@mail.ucf.edu>
 */

/**
 * This class handles all database calls and logic pertaining to Favorite LOs
 */
class nm_los_FavoriteManager extends core_db_dbEnabled
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
            return new nm_los_Error(0); // error: invalid input
        
        //check to see if it exists
        $qstr = "SELECT * FROM `".self::table."` WHERE `uid`='?' AND `loid`='?'";
        $q = $this->DBM->querySafe($qstr, $_SESSION['UID'], $loid);

        if($this->DBM->fetch_num($q) > 0)
            return new nm_los_Error(0);
        
	    $qstr = "INSERT INTO ".self::table." SET `uid`='?', loid='?'";
		if( !($q = $this->DBM->querySafe($qstr, $_SESSION['UID'], $loid)) )
		{
		    core_util_Log::trace(mysql_error(), true);
			$this->DBM->rollback();
			//die();
			return false;
		}
		
		return true;
	}
	
	public function deleteFavorite($loid = 0)
	{
        if(!is_numeric($loid) || $loid < 1)
            return new nm_los_Error(0); // error: invalid input

        //check to see if it exists
        $qstr = "SELECT * FROM `".self::table."` WHERE `uid`='?' AND `loid`='?'";
        $q = $this->DBM->querySafe($qstr, $_SESSION['UID'], $loid);

        if($this->DBM->fetch_num($q) == 0)
            return new nm_los_Error(0);
            
        $qstr = "DELETE FROM `".self::table."` WHERE `uid`='?' AND `loid`='?' LIMIT 1";
	    if( !($q = $this->DBM->querySafe($qstr, $_SESSION['UID'], $loid)) )
	    {
			core_util_Log::trace(mysql_error(), true);
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
			core_util_Log::trace(mysql_error(), true);
			$this->DBM->rollback();
			//die();
			return false;	
		}
		
		$loArr = array();
		$loMan = nm_los_LOManager::getInstance();
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