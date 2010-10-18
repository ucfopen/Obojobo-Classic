<?php
/**
 * This class handles all logic for languages
 * @author Jacob Bates <jbates@mail.ucf.edu>
 * @author Luis Estrada <lestrada@mail.ucf.edu>
 */


/**
 * This class handles all logic for languages
 * This includes creating, retrieving, and deleting of data.
 */
class nm_los_LanguageManager extends core_db_dbEnabled
{
	private static $instance;
	
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
	 * Gets all available languages
	 * @return (Array<Array>) Array of languages, containing 'id' and 'name' values
	 */
	// TODO: FIX RETURN FOR DB ABSTRACTION
	public function getAllLanguages()
	{
		
		// check memcache
 		$oboCache = core_util_Cache::getInstance();
		if($langs = $oboCache->getAllLangs)
		{
			return $langs;
		}
		
		if( !($q = $this->DBM->query("SELECT * FROM ".cfg_obo_Language::TABLE)) ) // no need for querySae
		{
			$this->DBM->rollback();
			core_util_Log::trace(mysql_error(), true);
			return false;
		}
		
		$langs = array();
		while( $r = $this->DBM->fetch_obj($q) )
		{
			$langs[] = array('languageID' => $r->{cfg_obo_Language::ID}, 'name' => $r->{cfg_obo_Language::NAME});
		}
		$oboCache->setAllLangs($langs);
		return $langs;
	}
}

?>