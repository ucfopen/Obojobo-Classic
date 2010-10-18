<?php
/**
 * This is the class that defines the Learning Object data type
 * @author Jacob Bates <jbates@mail.ucf.edu>
 */

/**
 * This is the class that defines the Learning Object data type
 * It is used simply for representing data in memory, and has no methods.
 */
class nm_los_LO
{
	public $loID;				//Number:  database id
	public $title;			//String:  title of the package
	public $languageID;			//String:  ISO code for language
	public $notesID;			//String:  in-depth description of contents of package
	public $notes;
	public $objID;		//String:  the purpose of the package
	public $objective;
	public $learnTime;		//Number:  how much time it takes to finish
	public $version;		//Number:  current whole/final version number
	public $subVersion;		//Number:  current part/draft version number
	public $rootID;			//Number:  id number of the original LO
	public $parentID;         //Number:  id of the parent LO
	public $createTime;			//Number:  date package was created
	public $copyright;		//String:
	public $pages;			//Array:  of Page objects
	public $pGroup;			//QuestionGroup:  
	public $aGroup;			//QuestionGroup:
	//public $layouts;		//Array:  of Layout objects
	public $keywords;		//Array:  of Keyword objects
	public $perms;			//Permissions object:  merged from global and user
	public $summary;		//Summary object, contains content counts for pages & questions for use in Meta and instance calls
	
	function __construct($loID=0, $title='', $languageID='', $notesID='', $objID='', $learnTime=0, $version=0, $subVersion=0, $rootID=0, $parentID=0, $createTime=0, $copyright='', $pages=array(), $pGroup=0, $aGroup=0, $keywords=Array(), $perms=0)
	{
		$this->loID = $loID;
		$this->title = $title;
		$this->languageID = $languageID;
		$this->notesID = $notesID;
		$this->objID = $objID;
		$this->learnTime = $learnTime;
		$this->version = $version;
		$this->subVersion = $subVersion;
		$this->rootID = $rootID;
		$this->parentID = $parentID;
		$this->createTime = $createTime;
		$this->copyright = $copyright;
		$this->pages = $pages;
		$this->pGroup = $pGroup;
		$this->aGroup = $aGroup;
		//$this->layouts = $layouts;
		$this->keywords = $keywords;
		$this->perms = $perms;
	}
	
	public function dbGetFull($DBM, $loID, $getMeta=false)
	{
		// whitelist input
		if(!$DBM)
		{
			core_util_Log::trace('no DBM sent.', true);
			return false;
		}
		if(!is_numeric($loID) || $loID <= 0)
		{
			
			$error = AppCfg::ERROR_TYPE;
			new $error(2);
			return false;
		}
		
		$oboCache = core_util_Cache::getInstance();
		// full isnt in cache, try to get meta
		if($getMeta)
		{
			if($lo = $oboCache->getLOMeta($loID))
			{
				$this->becomeCachedLO($lo);
				return true;
			}
		}
		else
		{
			// try to get from cache
			if($lo = $oboCache->getLO($loID))
			{
				$this->becomeCachedLO($lo);
				return true;
			}
		}
		
		// if not, build and cache it
		$q = $DBM->querySafe("SELECT * FROM ".cfg_obo_LO::TABLE." WHERE ".cfg_obo_LO::ID."='?' LIMIT 1", $loID);
		if($r = $DBM->fetch_obj($q))
		{
			// no cache found, retrieve the lo 
			$this->__construct((int)$r->{cfg_obo_LO::ID}, $r->{cfg_obo_LO::TITLE}, (int)$r->{cfg_obo_Language::ID}, $r->{cfg_obo_LO::DESC}, $r->{cfg_obo_LO::OBJECTIVE}, (int)$r->{cfg_obo_LO::LEARN_TIME}, (int)$r->{cfg_obo_LO::VER}, (int)$r->{cfg_obo_LO::SUB_VER}, (int)$r->{cfg_obo_LO::ROOT_LO}, (int)$r->{cfg_obo_LO::PARENT_LO}, (int)$r->{cfg_obo_LO::TIME}, $r->{cfg_obo_LO::COPYRIGHT});

			if($getMeta == false)
			{
				// drop in a shell for the aGroup and pGroup IDs (need to put the id's in the cache so we can build the full aGroup and pGroup seperately)
				$this->pGroup = new nm_los_QuestionGroup($r->{cfg_obo_LO::PGROUP});
				$this->aGroup = new nm_los_QuestionGroup($r->{cfg_obo_LO::AGROUP});

				// Get Pages (page manager caches these internally, temp var needed to prevent caching it here)
				$pgman = nm_los_PageManager::getInstance();
				$pages = $pgman->getPagesForLOID($loID);

				// grab full question groups  (question group manager caches these internally, temp var needed to prevent caching it here)
				$pGroup = new nm_los_QuestionGroup();
				$pGroup->getFromDB($DBM, $r->{cfg_obo_LO::PGROUP}, true);

				$aGroup = new nm_los_QuestionGroup();
				$aGroup->getFromDb($DBM, $r->{cfg_obo_LO::AGROUP}, true);
				
				//Update layouts since we just loaded the pages
				// TODO: theres something wrong with this, the singleton class gets built strangely and == comparisons fail if built more then once
				//$layman = nm_los_LayoutManager::getInstance();
				//$this->layouts = $layman->layouts;
				
				// put page structures into this object after caching
				$this->pages = $pages;
				$this->pGroup = $pGroup;
				$this->aGroup = $aGroup;
				
				// build summary object
				$this->summary = array(
					'contentSize' => count($pages),
					'practiceSize' => $pGroup->quizSize,
					'assessmentSize' => $aGroup->quizSize
				);
			}
			else
			{
				$pgman = nm_los_PageManager::getInstance();
				$qGroupMan = new nm_los_QuestionGroupManager();
				// build summary object
				$this->summary = array(
					'contentSize' => $pgman->getPageCountForLOID($loID),
					'practiceSize' => $qGroupMan->getQuizSize($r->{cfg_obo_LO::PGROUP}),
					'assessmentSize' => $qGroupMan->getQuizSize($r->{cfg_obo_LO::AGROUP})
				);
				
			}

			// grap keywords
			$keyman = nm_los_KeywordManager::getInstance();
			$this->keywords = $keyman->getKeywordsFromItem($loID, 'l');
			
			//Update layouts since we just loaded the pages
			// TODO: theres something wrong with this, the singleton class gets built strangely and == comparisons fail if built more then once
			//$layman = nm_los_LayoutManager::getInstance();
			//$this->layouts = $layman->layouts;
			
			//grab description
			$this->notes = '';
			if(nm_los_Validator::isPosInt($this->notesID))
			{			
				if($q = $DBM->querySafe("SELECT * FROM `".cfg_obo_Text::TABLE."` WHERE ".cfg_obo_Text::ID."='?' LIMIT 1", $this->notesID))
				{
					$this->notes = $DBM->fetch_obj($q)->text;	
				}
			}
			
			$this->objective = '';
			if(nm_los_Validator::isPosInt($this->objID))
			{
				if($q = $DBM->querySafe("SELECT * FROM `".cfg_obo_Text::TABLE."` WHERE ".cfg_obo_Text::ID."='?' LIMIT 1", $this->objID))
				{
					$this->objective = $DBM->fetch_obj($q)->text;	
				}
			}
			
			//TODO: Get the actual names of authors
			if($getMeta == false)
			{
				$oboCache->setLO($loID, $this);
			}
			else
			{
				$oboCache->setLOMeta($loID, $this);
			}
			
			// get perms
			$permman = nm_los_PermissionsManager::getInstance();
			$this->perms = $permman->getMergedPerms((int)$r->{cfg_obo_LO::ROOT_LO}, cfg_obo_Perm::TYPE_LO);
			return true;
		}
		core_util_Log::trace('unable to locate LO: ' . $loID, true);
		return false;
	}
	
	public function dbGetMeta($DBM, $loID)
	{
		if($this->dbGetFull($DBM, $loID, true))
		{			
			unset($this->aGroup);
			unset($this->pGroup);
			unset($this->notesID);
			//unset($this->layouts);
			unset($this->objID);
			unset($this->pages);
			return true;
		}
		return false;
	}
	
	public function dbGetContent($DBM, $loID)
	{
		if($this->dbGetFull($DBM, $loID))
		{
			unset($this->aGroup->kids);
			return true;
		}
		return false;
	}	
	
	public function dbGetInstance($DBM, $loID)
	{
		if($this->dbGetFull($DBM, $loID))
		{
			// remove answers and feedback from assessment
			// foreach($this->aGroup->kids AS $apage)
			// {
			// 	foreach($apage->answers AS $answer)
			// 	{
			// 		unset($answer->weight);
			// 		unset($answer->feedback);
			// 	}
			// }
			if(is_object($lo) && is_object($lo->pGroup))	unset($lo->pGroup->kids);
			if(is_object($lo) && is_object($lo->aGroup))	unset($lo->aGroup->kids);
		}
		return false;
	}
	
	protected function becomeCachedLO($lo)
	{
		foreach($lo AS $key => &$value)
		{
			$this->$key = $value;
		}

		// always get perms for the current user
		$permman = nm_los_PermissionsManager::getInstance();
		if(isset($_SESSION))
		{
			$this->perms = $permman->getMergedPerms((int)$this->rootID, cfg_obo_Perm::TYPE_LO, $_SESSION['userID']);
		}
		
	}
}
?>
