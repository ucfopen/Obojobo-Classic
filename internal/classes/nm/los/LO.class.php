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
	const DRAFT = 'newDraft';
	const MASTER = 'master';
	const DERIVATIVE = 'derivative';
	
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
	public $keywords;		//Array:  of Keyword objects
	public $perms;			//Permissions object:  merged from global and user
	public $summary;		//Summary object, contains content counts for pages & questions for use in Meta and instance calls
	public $isMaster;
	
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
			$this->__construct((int)$r->{cfg_obo_LO::ID}, $r->{cfg_obo_LO::TITLE}, (int)$r->{cfg_obo_Language::ID}, 0, 0, (int)$r->{cfg_obo_LO::LEARN_TIME}, (int)$r->{cfg_obo_LO::VER}, (int)$r->{cfg_obo_LO::SUB_VER}, (int)$r->{cfg_obo_LO::ROOT_LO}, (int)$r->{cfg_obo_LO::PARENT_LO}, (int)$r->{cfg_obo_LO::TIME}, $r->{cfg_obo_LO::COPYRIGHT});
			
			// set rootID if its zero (stored as zero when the rootID is the current loID)
			if($this->rootID == 0) $this->rootID = $this->loID;
			if($this->rootID == $this->loID) $this->isMaster = true;
			
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
				
				// put page structures into this object after caching
				$this->pages = $pages;
				$this->pGroup = $pGroup;
				$this->aGroup = $aGroup;
			}

			// build summary object
			$this->summary = array(
				'contentSize' => $r->{cfg_obo_LO::NUM_PAGES},
				'practiceSize' => $r->{cfg_obo_LO::NUM_PRACTICE},
				'assessmentSize' => $r->{cfg_obo_LO::NUM_ASSESSMENT}
			);

			// grab keywords
			$keyman = nm_los_KeywordManager::getInstance();
			$this->keywords = $keyman->getKeywordsFromItem($loID, 'l');

			$this->notes = $r->{cfg_obo_LO::NOTES};
			$this->objective = $r->{cfg_obo_LO::OBJECTIVE};
			
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
			$this->perms = $permman->getMergedPerms((int)$this->rootID, cfg_obo_Perm::TYPE_LO);
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
	
	/**
	 * Save a learning object to the database, supports saving a new draft, revision draft, a master, or a derivative
	 *
	 * @param string $DBM 
	 * @param string $saveType	The type of lo being saved, options are "newDraft", "revisionDraft", "master", and "derivative"
	 * @return void
	 * @author Ian Turgeon
	 */
	public function saveAs($DBM, $saveType)
	{
		switch($saveType)
		{
			case self::DRAFT:
			
				/************************** NEW DRAFT **********************
				 * rootID is set to it's own loID
				 * parentID is set to zero - it has no parent
				************************************************************/
				if($this->loID == 0 && $this->rootID == 0 && $this->parentID == 0)
				{
					if($this->loID != 0) return false;
					$this->version = 0; // force 0.1
					$this->subVersion = 1; // force 0.1
					$success = $this->dbStore($DBM);
//					$this->rootID = $this->loID; // the client needs the rootID not to be 0 (rootID 0 is only on the database side)
					return $success;
				}
				
				/*************************** DRAFT REVISION OF MASTER *****************
				 * rootID is carried over from the item it is a revision of - it should always point to the lowest revision of the current full version, X.1
				 * parentID is the loID of the previous full version if there is one. 1.0 has none, 1.1 points at 1.0, 2.0 points at 1.0, 2.1 points at 2.0
				 ***********************************************************/
				else if($this->loID > 0 && $this->rootID > 0  && $this->subVersion == 0)
				{
					$this->subVersion = 1;
					$this->parentID = $this->loID;
					$this->rootID = 0;
					$this->loID = 0;
					$success = $this->dbStore($DBM);
//					$this->rootID = $this->loID; // the client needs the rootID not to be 0 (rootID 0 is only on the database side)
					
					// copy the permissions from the master object to the new draft
					$PM = nm_los_PermissionsManager::getInstance();
					$PM->copyPermsToItem($this->parentID, $this->loID, cfg_obo_Perm::TYPE_LO);
					return $success;
				}

				/*************************** DRAFT REVISION OF DRAFT *****************
				 * rootID is carried over from the item it is a revision of - it should always point to the lowest revision of the current full version, X.1
				 * parentID is the loID of the previous full version if there is one. 1.0 has none, 1.1 points at 1.0, 2.0 points at 1.0, 2.1 points at 2.0
				 ***********************************************************/
				else
				{
					$this->subVersion++; // incriment subVersion
					//$this->parentID = 0;
					$this->loID = 0;
					$success = $this->dbStore($DBM);
					return $success;
				}
				break;
				
			case self::MASTER:
				/*************************** MASTER *************************
				 * rootID is it's own loID - NOTE: in the database we store rootID as 0, which implies it is the same as the loID
				 * parentID is the previous full version, if there is one. 1.0 has none, 2.0 points at 1.0
				 * drafts for a master are removed  1.1, 1.2, 1.3 are removed leaving only 2.0
				 ************************************************************/
				$this->dbGetFull($DBM, $this->loID);// masters must come from the database
				
				if($this->isValidMaster() !== true) return core_util_Error::getError(2); // Validate
				
				core_util_Cache::getInstance()->clearLO($this->loID); // delete the cache
				
				$rootDraftLoID = ($this->rootID == 0 ? $this->loID : $this->rootID); // store the rootID for later use - if root is zero - this is the root
				
				/********* CHECK FOR PR-EXISTING DESIRED VERSION **********/
				/* (make sure the desired X.0 version doesnt already exist by checking for LO's with a parentID of my current rootID) */
				$qstr = "SELECT * FROM ".cfg_obo_LO::TABLE." WHERE ".cfg_obo_LO::VER." = '?' AND ".cfg_obo_LO::SUB_VER." = '0' AND ".cfg_obo_LO::PARENT_LO." = '?'";
				$r = $DBM->querySafe($qstr,  $this->version + 1, $this->loID);
				if($DBM->fetch_num != 0)
				{
					return core_util_Error::getError(6005); // Master version already exists
				}
				
				//************** SET MASTER REQUIREMENTS ************/
				$this->isMaster = true;
				$this->version = $this->version + 1;
				$this->subVersion = 0;
				
				// 1.0 has no parent !!! UNLESS its a deriviative TODO: deal with derivatives here? - may be elsewhere in createDerivative
				if($this->version == 1)
				{
					$this->parentID = 0;
				}
				
				/************** CREATE NEW MASTER LO RECORD ***************/
				$qstr ="INSERT INTO ".cfg_obo_LO::TABLE." (".cfg_obo_LO::MASTER.", ".cfg_obo_LO::TITLE.", ".cfg_obo_Language::ID.", ".cfg_obo_LO::NOTES.", ".cfg_obo_LO::OBJECTIVE.", ".cfg_obo_LO::LEARN_TIME.", ".cfg_obo_LO::PGROUP.", ".cfg_obo_LO::AGROUP.", ".cfg_obo_LO::VER.", ".cfg_obo_LO::SUB_VER.", ".cfg_obo_LO::ROOT_LO.", ".cfg_obo_LO::PARENT_LO.", ".cfg_obo_LO::TIME.", ".cfg_obo_LO::COPYRIGHT.", ".cfg_obo_LO::NUM_PAGES.", ".cfg_obo_LO::NUM_PRACTICE.", ".cfg_obo_LO::NUM_ASSESSMENT.")
											SELECT 1 AS ".cfg_obo_LO::MASTER.", ".cfg_obo_LO::TITLE.", ".cfg_obo_Language::ID.", ".cfg_obo_LO::NOTES.", ".cfg_obo_LO::OBJECTIVE.", ".cfg_obo_LO::LEARN_TIME.", ".cfg_obo_LO::PGROUP.", ".cfg_obo_LO::AGROUP.", $this->version AS ".cfg_obo_LO::VER.", $this->subVersion AS ".cfg_obo_LO::SUB_VER.", 0 AS ".cfg_obo_LO::ROOT_LO.", $this->parentID AS ".cfg_obo_LO::PARENT_LO.", ".time()." AS ".cfg_obo_LO::TIME.", ".cfg_obo_LO::COPYRIGHT.", ".cfg_obo_LO::NUM_PAGES.", ".cfg_obo_LO::NUM_PRACTICE.", ".cfg_obo_LO::NUM_ASSESSMENT." FROM ".cfg_obo_LO::TABLE." WHERE ".cfg_obo_LO::ID." = '?'";
				if(!($q = $DBM->querySafe($qstr, $this->loID)))
				{
					$DBM->rollback();
					trace(mysql_error(), true);
					return false;
				}
				$this->loID = $DBM->insertID;
				
				/********* ASSOCIATE PAGES *************/
				if(is_array($this->pages))
				{
					$pageman = nm_los_PageManager::getInstance();
					foreach($this->pages AS $orderIndex => $page)
					{
						$pageman->mapPageToLO($this->loID, $page->pageID, $orderIndex); // map this page
					}
				}
				/********* ASSOCIATE KEYWORDS *************/
				$KM = nm_los_KeywordManager::getInstance();
				foreach($this->keywords AS $tag)
				{
					if($newTag = $KM->newKeyword($tag))
					{
						$KM->linkKeyword($newTag->keywordID, $this->loID, cfg_obo_Perm::TYPE_LO);
					}
				}
				
				/******** MOVE PERMISSIONS **********/
				$PM = nm_los_PermissionsManager::getInstance();
				$PM->movePermsToItem($rootDraftLoID, $this->loID, cfg_obo_Perm::TYPE_LO);
				
				/********* REMOVE DRAFTS *************/
				$this->destroyDrafts($DBM, $rootDraftLoID, $this->loID);
				
				/************* KEEP TRACK OF MEDIA USED IN LO *******************/
				$this->associateMediaUsedInLO();
				
				break;
				
			case self::DERIVATIVE:
				/*************************** DERIVATIVE *********************
				 * rootID is it's own loID
				 * parentID is the loID of the item it is a derivative of
				 ***********************************************************/
				
				$this->dbGetFull($DBM, $this->loID);// masters must come from the database
				if($this->isValidMaster(true) !== true) return core_util_Error::getError(2); // Validate

				$this->parentID = $this->loID; // link parent to previous root id
				$this->loID = 0;// force it to save a copy
				$this->version = 1; // reset
				$this->subVersion = 0; // reset
				$this->rootID = 0; // mark as a new LO
				
				$this->dbStore($DBM);
				
				break;
				
			default:
				return false;
				break;
		}
		return true;
	}
	
	/**
	 * Internal function that associates all the media used in this LO in the database
	 *
	 * @return void
	 * @author Ian Turgeon
	 */
	private function associateMediaUsedInLO()
	{
		// serialize so we can quickly search for mediaIDs
		// catches string or int types ----ex:   s:7:"mediaID";s:10:"34"   OR   s:7:"mediaID";i:2129
		if( preg_match_all('/s:7:"mediaID";(?:(?:i:)|(?:s:\d+:"))(\d+)/', serialize($this), $matches))
		{
			$mediaIDs = array();
			foreach($matches[1] AS $match)
			{
				$mediaIDs[] = $match; // place all MediaIDs in an array
			}

			$mediaIDs = array_unique($mediaIDs);
			$MM = nm_los_MediaManager::getInstance();
			foreach($mediaIDs AS $mediaID)
			{
				$MM->associateMediaWithLO($mediaID, $this->loID);
			}
		}
	}
	
	/**
	 * Remove drafts when publishing a master, Note that this does not remove orphaned qGroups, questions, or pages.
	 *
	 * @param string $DBM 
	 * @param string $delRootID 
	 * @param string $newLoID 
	 * @return boolean success
	 * @author Ian Turgeon
	 */
	private function destroyDrafts($DBM, $delRootID, $newLoID)
	{
		/************ GATHER DRAFTS TO DELETE **************/
		$qstr = "SELECT ".cfg_obo_LO::ID.", ".cfg_obo_LO::VER.", ".cfg_obo_LO::AGROUP.", ".cfg_obo_LO::PGROUP." FROM ".cfg_obo_LO::TABLE." WHERE (".cfg_obo_LO::ROOT_LO." = '?' OR ".cfg_obo_LO::ID." = '?' ) AND ".cfg_obo_LO::SUB_VER." > 0 ORDER BY ".cfg_obo_LO::SUB_VER." ASC";
		if( !($q = $DBM->querySafe($qstr, $delRootID, $delRootID)) )
		{
		    trace(mysql_error(), true);
			return false;
		}
	    $drafts = array();
		while($r = $DBM->fetch_obj($q))
		{
			$drafts[] = $r->{cfg_obo_LO::ID};
			core_util_Cache::getInstance()->clearLO($r->{cfg_obo_LO::ID}); // delete the cache
		}

		if(count($drafts) > 0)
		{
			//**************** ASSOCIATE ALL DRAFT AUTHORS WITH NEW MASTER ***************************
			//Generate a string of draft numbers SQL can use
			$draftstr = implode(',', $drafts);  // 1,3,5,7,9...
			//Change lo_id of existing author entries to the new master $loID
			$qstr = "UPDATE IGNORE `".cfg_obo_LO::MAP_AUTH_TABLE."` SET ".cfg_obo_LO::ID."='?' WHERE ".cfg_obo_LO::ID." IN (".$draftstr.")";
			if( !($q = $DBM->querySafe($qstr, $loID)))
			{
                $DBM->rollback();
				return false;
			}
			
			//************** DELETE DRAFTS *************************
			$qstr = "DELETE FROM ".cfg_obo_LO::TABLE." WHERE ".cfg_obo_LO::ID." IN (".$draftstr.")";
			if(!($q = $DBM->query($qstr))) // no need for querySafe, all these val's are out of the database above
			{
                $DBM->rollback();
				return false;
			}
			
			
			//************** DELETE MEDIA ASSOCIATIONS *********************
			$qstr = "DELETE FROM ".cfg_obo_Media::MAP_TABLE." WHERE ".cfg_obo_LO::ID." IN (".$draftstr.")";
			if(!($q = $DBM->query($qstr))) // no need for querySafe, all these val's are out of the database above
			{
                $DBM->rollback();
				return false;
			}
		}
		return true;
	}
	
	
	public function isValidMaster($isDerivative=false)
	{
		// id must already exist
		if( !( nm_los_Validator::isPosInt($this->loID) ))
		{
			trace('invalid loid');
			return false;
		} 
		// must have a title
		if(!(nm_los_Validator::isString($this->title)))
		{
			trace('invalid title');
			return false;
		}
		// must have an objective string
		if(!(nm_los_Validator::isString($this->objective)))
		{
			trace('invalid objective');
			return false;
		}
		// dont allow users to create masters from existing masters, no need - its the same object
		if(!$isDerivative && $this->version > 0 && $this->subVersion == 0)
		{
			trace('invalid versions');
			return false;
		}
		// must have at least one page
		if( !(is_array($this->pages)) || (count($this->pages) == 0) )
		{
			trace('invalid pages');
			return false;
		}
		// all pages must have titles
		foreach($this->pages AS &$page)
		{
			if(!(nm_los_Validator::isString($page->title)))
			{
				trace('invalid page titles');
				return false;
			}
		}
		// must have at least one practice question
		if( !(is_array($this->pGroup->kids)) || (count($this->pGroup->kids) == 0) )
		{
			trace('invalid practice group');
			return false;
		}
		// must have at least one assessment question
		if( !(is_array($this->aGroup->kids)) || (count($this->aGroup->kids) == 0) )
		{
			trace('invalid assessment group');
			return false;
		}
		// must have a keyword
		if( !(is_array($this->keywords)) || (count($this->keywords) == 0) )
		{
			trace('invalid keywords');
			return false;
		}
		// must have an learning time estimate
		if( !(isset($this->learnTime)) || ($this->learnTime == 0) )
		{
			trace('invalid learn time');
			return false;
		}
		return true;
	}
	
	private function dbStore($DBM)
	{
		/**
		 * TODO: if the loid is set, update the lo 
		 * ROOTID of 0 means its a brand new learning object
		 * 
		 */
		
		// LoID is dirtied when saving a new draft, draft revision, derivative, or new master 
		if($this->loID == 0)
		{
			
			/********** CHECK QUESTION GROUPS *********/
			// New question groups if needed
			// qgm will check to see if the qGroupID is dirty and create if needed
			// we need qGroupID's before inserting into the LO table below
			$qgm = nm_los_QuestionGroupManager::getInstance();
			$qgm->newGroup($this->pGroup); // the referenced objects ids will be updated if needed
			$qgm->newGroup($this->aGroup);
			
			//********* Build Summary Object **************/
			// build summary object
			$this->summary = array(
				'contentSize' => count($this->pages),
				'practiceSize' => count($this->pGroup->kids),
				'assessmentSize' => count($this->aGroup->kids)
			);
			
			
			/********** UPDATE LO **********************/
			$qstr = "INSERT INTO ".cfg_obo_LO::TABLE." SET `".cfg_obo_LO::TITLE."` = '?', `".cfg_obo_Language::ID."` = '?',`".cfg_obo_LO::NOTES."` = '?', `".cfg_obo_LO::OBJECTIVE."` = '?', `".cfg_obo_LO::LEARN_TIME."` = '?', `".cfg_obo_LO::PGROUP."` = '?', `".cfg_obo_LO::AGROUP."` = '?', `".cfg_obo_LO::VER."` = '?', `".cfg_obo_LO::SUB_VER."` = '?', `".cfg_obo_LO::ROOT_LO."` = '?', `".cfg_obo_LO::PARENT_LO."` = '?', `".cfg_obo_LO::TIME."` = UNIX_TIMESTAMP(), `".cfg_obo_LO::COPYRIGHT."` = '?',  ".cfg_obo_LO::NUM_PAGES." = '?', ".cfg_obo_LO::NUM_PRACTICE." = '?', ".cfg_obo_LO::NUM_ASSESSMENT." = '?'";
			$q = $DBM->querySafe($qstr, $this->title, $this->languageID, $this->notes, $this->objective, $this->learnTime, $this->pGroup->qGroupID, $this->aGroup->qGroupID, $this->version, $this->subVersion, $this->rootID, $this->parentID, $this->copyright, $this->summary['contentSize'], $this->summary['practiceSize'], $this->summary['assessmentSize']);
			if(!$q)
			{
			    trace(mysql_error(), true);
				$DBM->rollback();
				return false;
			}
			
			/************ UPDATE OBJECT IN MEMORY **********/
			$this->loID = $DBM->insertID;
			if($this->rootID == 0) $this->rootID = $this->loID; // rootID is 0 for masters in the database, but needs to be set for the clients
			
			
			/********* CHECK PAGES *************/
			// We Needed the loID before mapping these pages
			if(is_array($this->pages))
			{
				$pageman = nm_los_PageManager::getInstance();
				foreach($this->pages AS $orderIndex => $page)
				{
					$pageman->newPage($page); // newPage() only saves if id is 0
					$pageman->mapPageToLO($this->loID, $page->pageID, $orderIndex); // map this page
				}
			}
			
			/******** SET PERMISSIONS ON THE ROOT OBJECT **********/
			if($this->rootID == $this->loID)
			{
				$PM = nm_los_PermissionsManager::getInstance();
				$PM->setFullPermsForItem($this->loID, cfg_obo_Perm::TYPE_LO);
				
				$this->perms = new nm_los_Permissions($_SESSION['userID'], 1,1,1,1,1,1,1,1,1);
				
			}
			
			/************* ADD KEYWORDS *******************/
			$KM = nm_los_KeywordManager::getInstance();
			if(is_array($this->keywords) && count($this->keywords) > 0 )
			{
				foreach($this->keywords AS $tag)
				{
					if($newTag = $KM->newKeyword($tag))
					{
						$KM->linkKeyword($newTag->keywordID, $this->loID, 'l');
					}
					
				}
			}
			
			/************* KEEP TRACK OF MEDIA USED IN LO *******************/
			$this->associateMediaUsedInLO();
			return true;
		}
	}
	
}

?>