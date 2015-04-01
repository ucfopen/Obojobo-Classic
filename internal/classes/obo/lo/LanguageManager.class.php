<?php

namespace obo\lo;
class LanguageManager extends \rocketD\db\DBEnabled
{
	use \rocketD\Singleton;

	/**
	 * Gets all available languages
	 * @return (Array<Array>) Array of languages, containing 'id' and 'name' values
	 */
	// TODO: FIX RETURN FOR DB ABSTRACTION
	public function getAllLanguages()
	{

		// check memcache
 		$oboCache = \rocketD\util\Cache::getInstance();
		if($langs = $oboCache->getAllLangs)
		{
			return $langs;
		}

		if( !($q = $this->DBM->query("SELECT * FROM ".\cfg_obo_Language::TABLE)) ) // no need for querySae
		{
			$this->DBM->rollback();
			trace(mysql_error(), true);
			return false;
		}

		$langs = array();
		while( $r = $this->DBM->fetch_obj($q) )
		{
			$langs[] = array('languageID' => $r->{\cfg_obo_Language::ID}, 'name' => $r->{\cfg_obo_Language::NAME});
		}
		$oboCache->setAllLangs($langs);
		return $langs;
	}
}
