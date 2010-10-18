<?php
/**
 * This class handles all logic for Learning Objects
 * @author Jacob Bates <jbates@mail.ucf.edu>
 * @author Luis Estrada <lestrada@mail.ucf.edu>
 */

/**
 * This class handles all logic for Learning Objects.  This includes creating, retrieving, and deleting of data.
 */
class nm_los_LOManager extends core_db_dbEnabled
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
	 * Gets the root id of a learning object
	 * @param $loID (number) Learning Object ID
	 * @return (number) root Learning Object ID
	 */
	public function getRootId($loID = 0)
	{
	    if(!nm_los_Validator::isPosInt($loID))
		{
		    trace('invalid input', true);
	        return false;
		}
		$qstr = "SELECT ".cfg_obo_LO::ROOT_LO." FROM ".cfg_obo_LO::TABLE." WHERE ".cfg_obo_LO::ID."='?' LIMIT 1";
		if( !($q = $this->DBM->querySafe($qstr, $loID)) )
		{
		    $this->DBM->rollback();
		    trace(mysql_error(), true);
			return false;
		}
		$r = $this->DBM->fetch_obj($q);
		return $r->{cfg_obo_LO::ROOT_LO};
	}
	
	
	public function addToLibrary($loID=0, $allowDerivative=0)
	{

		$roleMan = nm_los_RoleManager::getInstance();
		// must be superUser OR LibararyUser && have write permissions
		if(!$roleMan->isSuperUser())
		{
			if(!$roleMan->isLibraryUser())
			{
				
				
				return core_util_Error::getError(4);
			}
			$lo = $this->getLO($loID);
			 // Didn't do this at the same time as the check above to save these lines from executing if not a lib user
			$permMan = nm_los_PermissionsManager::getInstance();
			$perms = $permMan->getMergedPerms($lo->rootID, cfg_obo_Perm::TYPE_LO);
			if($perms instanceof nm_los_Permissions)
			{
				if(!$perms->isOwner())
				{
					
					
					return core_util_Error::getError(4);
				}
			}
		}
		
		if(!nm_los_Validator::isPosInt($loID) )
		{
			
			
			return core_util_Error::getError(2);
	    }
		

		// check to see if the learning object is a master
		$lo = new nm_los_LO();
		$lo->dbGetMeta($this->DBM, $loID);
		if($lo->version == 0 || $lo->subVersion > 0)
		{
			
			
			return core_util_Error::getError(2);
		}
		

		
		// set permissions
		$this->DBM->startTransaction();
		$permObj = new nm_los_Permissions(0, 1, 0, ($allowDerivative ? 1 : 0), 1, 0, 0, 0, 0, 1);
		$permman = nm_los_PermissionsManager::getInstance();
		$result = $permman->setGlobalPerms($loID, cfg_obo_Perm::TYPE_LO, $permObj);
		$this->DBM->commit();

		return $result;
	}
	/**
	 * NOTE: Scheme for parent and root links:
	 * 
	 * When you create a new draft, a X.1 draft is created.  This has a root that points to itself,
	 * and a parent that points back to (X-1).0.  The X.1 draft is the start of a new master version.
	 * 
	 * X.2, X.3, etc will have a parent of 0, since this parent is ignored.
	 * The roots of X.2, X.3, etc will point to X.1.
	 * 
	 * When you create a master, the drafts will compress and the new (X+1).0 version will take
	 * the root and parent values of the most recent draft.
	 * 
	 * Master objects have roots that point to themselves.
	 *
	 * @param unknown_type $loID
	 * @return unknown
	 */
	// TODO: this function needs to return true only if a master was created
	// TODO: need to add more restrictions to make sure that a draft is converted to a master only if needed
	public function createMaster($loID = 0)
	{
	    if(!nm_los_Validator::isPosInt($loID))
		{
			global $conifg;
			
			return core_util_Error::getError(2);
		}
	    
		//check if user is a Content Creator
		$roleMan = nm_los_RoleManager::getInstance();
		if(!$roleMan->isContentCreator())
		{
			$permMan = nm_los_PermissionsManager::getInstance();
			$lo = $this->getLO($loID);
			if(!($roleMan->isLibraryUser()) || !($permMan->getMergedPerm($lo->rootID, cfg_obo_Perm::TYPE_LO, cfg_obo_Perm::WRITE, $_SESSION['userID'])) )
			{
				
				
				return core_util_Error::getError(4);
			}
		}
		
	    //check if the lo exists
		$lo = new nm_los_LO();
		if($lo->dbGetFull($this->DBM, $loID) == false)
		{
			global $conifg;
			
			return core_util_Error::getError(2);
		}
		// must have a title
		if(! (nm_los_Validator::isString($lo->title)) )
		{
			
			
			return core_util_Error::getError(2);
		}
		// must have an objective id
		if(!(nm_los_Validator::isPosInt($lo->objID))) 
		{
			
			
			return core_util_Error::getError(2);
		}
		// must have an objective string
		if(!(nm_los_Validator::isString($lo->objective))) 
		{
			
			
			return core_util_Error::getError(2);
		}
		// dont allow users to create masters from existing masters, no need - its the same object
		if($lo->version > 0 && $lo->subVersion == 0)
		{
			
			
			return core_util_Error::getError(2);
		}
		// must have at least one page
		if( !(is_array($lo->pages)) || (count($lo->pages) == 0) ) 
		{
			
			
			return core_util_Error::getError(2);
		}	
		// all pages must have titles
		foreach($lo->pages AS &$page)
		{
			if(!(nm_los_Validator::isString($page->title)))
			{
				
				
				return core_util_Error::getError(2);
			}
		}
		
		// must have at least one practice question
		if( !(is_array($lo->pGroup->kids)) || (count($lo->pGroup->kids) == 0) ) 
		{
			
			
			return core_util_Error::getError(2);
		}
		
		// must have at least one assessment question
		if( !(is_array($lo->aGroup->kids)) || (count($lo->aGroup->kids) == 0) ) 
		{
			
			
			return core_util_Error::getError(2);
		}		
		// must have a keyword
		if( !(is_array($lo->keywords)) || (count($lo->keywords) == 0) ) 
		{
			
			
			return core_util_Error::getError(2);
		}		
		
		// must have an learning time estimate
		if( !(isset($lo->learnTime)) || ($lo->learnTime == 0) )
		{
			
			
			return core_util_Error::getError(2);
		}
			       
		//check if user is a Super User
		if(!$roleMan->isSuperUser())
		{	
		    //if the user is not a Super User
			//check if the user has permissions to do this
			$permMan = nm_los_PermissionsManager::getInstance();
			$lo = $this->getLO($loID);
			if(!$permMan->getMergedPerm($this->getRootId($lo->rootID), cfg_obo_Perm::TYPE_LO, cfg_obo_Perm::WRITE, $_SESSION['userID']))
			{
				global $conifg;
				
				return core_util_Error::getError(4);
			}
		}
		
		$rootID= $lo->rootID;
		$parentID = $lo->parentID;

		//Get the ids for all the drafts in order to remove them - 
		// we omit the passed $LOID as it is to be converted to the master
		$qstr = "SELECT ".cfg_obo_LO::ID.", ".cfg_obo_LO::VER." FROM ".cfg_obo_LO::TABLE." WHERE ".cfg_obo_LO::ROOT_LO." = '?' AND ".cfg_obo_LO::SUB_VER." > 0 ORDER BY ".cfg_obo_LO::SUB_VER." ASC";
		if( !($q = $this->DBM->querySafe($qstr, $rootID)) )
		{
		    trace(mysql_error(), true);
    		$this->DBM->rollback();
			return false;
		}
		
		core_util_Cache::getInstance()->clearLO($loID);

		
	    // get all the ids and the first whole version
	    $drafts = array();
	
		while($r = $this->DBM->fetch_obj($q))
		{
			if(!isset($ver_whole))
			{
				$ver_whole = $r->{cfg_obo_LO::VER}; // stores the first whole version
			}
		    if($r->{cfg_obo_LO::ID} != $loID)
			{
			    $drafts[] = $r->{cfg_obo_LO::ID};
				core_util_Cache::getInstance()->clearLO($r->{cfg_obo_LO::ID}); // delete the cache
			}
		}
		
		//Generate a string of draft numbers SQL can use
        $draftstr = implode(',', $drafts);  // 1,3,5,7,9...

		// redirect authors of all the drafts to the new master
		if(count($drafts) > 0)
		{
			//Change lo_id of existing author entries to the new master $loID
			$qstr = "UPDATE `".cfg_obo_LO::MAP_AUTH_TABLE."` SET ".cfg_obo_LO::ID."='?' WHERE ".cfg_obo_LO::ID." IN (".$draftstr.")";
			if( !($q = $this->DBM->querySafe($qstr, $loID)))
			{
                $this->DBM->rollback();
				return false;
			}
			//Update perms
			// TODO: move perm query to PermsManager
			$qstr = "UPDATE `".cfg_obo_Perm::TABLE."` SET ".cfg_obo_Perm::ITEM."='?' WHERE ".cfg_obo_Perm::ITEM."='?' AND `".cfg_obo_Perm::TYPE."`='l'";
			if( !($q = $this->DBM->querySafe($qstr, $loID, $rootID)) )
			{
			    $this->DBM->rollback();
				return false;
			}
		}
		
		/***********************************************************/
		/* Gather how many redundant authors we have */
		$qstr = "SELECT ".cfg_core_User::ID.", count( ".cfg_core_User::ID." ) AS cnt FROM `".cfg_obo_LO::MAP_AUTH_TABLE."` WHERE ".cfg_obo_LO::ID."='?' GROUP BY ".cfg_core_User::ID." ORDER BY cnt DESC";
		if(! ($q = $this->DBM->querySafe($qstr, $loID)))
		{
		    $this->DBM->rollback();
			return false;
		}
		$authors = array();
		//Store how many times each author is used
		while($r = $this->DBM->fetch_obj($q))
		{
		    $authors[$r->{cfg_core_User::ID}] = $r->cnt-1;
		}
		//Delete redundant authors for the drafts (times used - 1)
		foreach($authors as $key => $val)
		{
		    $qstr = "DELETE FROM ".cfg_obo_LO::MAP_AUTH_TABLE." WHERE ".cfg_core_User::ID."='?' AND ".cfg_obo_LO::ID."='?' LIMIT ?";
			if( !($q = $this->DBM->querySafe($qstr, $key, $loID, $val)) )
			{
				$this->DBM->rollback();
				return false;
			}
		}
		/* End gathering redundant authors */

		//Delete all of the drafts and anything else they use, unless those items are used by something else
		if(count($drafts) > 0)
		{
			$qstr = "DELETE FROM ".cfg_obo_LO::TABLE." WHERE ".cfg_obo_LO::ID." IN (".$draftstr.")";
			if(!($q = $this->DBM->query($qstr))) // no need for querySafe, all these val's are out of the database above
			{
                $this->DBM->rollback();
				return false;
			}
			$draftstr .= ","; // the extra comma is for the actual $loID used below.
		}

		//If this is the first final in the tree, make this the rootID
		if( $lo->version == 0 )
		{ 
			$qstr = "UPDATE ".cfg_obo_LO::TABLE." SET ".cfg_obo_LO::VER."=".cfg_obo_LO::VER."+1, ".cfg_obo_LO::SUB_VER."='0', ".cfg_obo_LO::ROOT_LO."='?', ".cfg_obo_LO::PARENT_LO."='?' WHERE ".cfg_obo_LO::ID."='?' LIMIT 1";
			if( !($q = $this->DBM->querySafe($qstr, $loID, $loID, $loID)) )
			{
				$this->DBM->rollback();
				return false;
			}
		}
		else
		{//Change the version to a whole version number    
		    if(count($drafts) == 0)
		    {
		        $qstr = "SELECT * FROM ".cfg_obo_LO::TABLE." WHERE ".cfg_obo_LO::ID."='?' LIMIT 1";
    			if( !($q = $this->DBM->querySafe($qstr, $loID)) )
    			{
    				$this->DBM->rollback();
    				return false;
    			}
    			
    			$r = $this->DBM->fetch_obj($q);
                $r->{cfg_obo_LO::VER}++;
                $r->{cfg_obo_LO::SUB_VER} = 0;

		        $qstr = "UPDATE ".cfg_obo_LO::TABLE." 
						SET 
							`".cfg_obo_LO::TITLE."`='?', 
							`".cfg_obo_Language::ID."`='?', 
							`".cfg_obo_LO::DESC."`='?',
							`".cfg_obo_LO::OBJECTIVE."`='?', 
							`".cfg_obo_LO::LEARN_TIME."`='?', 
							`".cfg_obo_LO::PGROUP."` = '{$r->{cfg_obo_LO::PGROUP}}', 
							`".cfg_obo_LO::AGROUP."` = '{$r->{cfg_obo_LO::AGROUP}}',
							`".cfg_obo_LO::VER."`='{$r->{cfg_obo_LO::VER}}' ,
							`".cfg_obo_LO::SUB_VER."`='{$r->{cfg_obo_LO::SUB_VER}}' ,
							`".cfg_obo_LO::ROOT_LO."`='?' ,
							`".cfg_obo_LO::TIME."`='{$r->{cfg_obo_LO::TIME}}', 
							`".cfg_obo_LO::COPYRIGHT."`='?'
						WHERE ".cfg_obo_LO::ID."='?'";
    			if( !($q = $this->DBM->querySafe($qstr, $r->{cfg_obo_LO::TITLE}, $r->{cfg_obo_Language::ID}, $r->{cfg_obo_LO::DESC}, $r->{cfg_obo_LO::OBJECTIVE}, $r->{cfg_obo_LO::LEARN_TIME}, $loID, $r->{cfg_obo_LO::COPYRIGHT},   $loID)) )

    			{
    			    trace(mysql_error(), true);
    				$this->DBM->rollback();
    				return false;
    			}
		    }
		    else
		    {
    			$qstr = "UPDATE ".cfg_obo_LO::TABLE." SET ".cfg_obo_LO::VER."=".cfg_obo_LO::VER."+1, ".cfg_obo_LO::SUB_VER."='0', ".cfg_obo_LO::ROOT_LO."='?', ".cfg_obo_LO::PARENT_LO." ='?' WHERE ".cfg_obo_LO::ID."='?' LIMIT 1";
    			if( !($q = $this->DBM->querySafe($qstr, $loID, $parentID, $loID)) )
    			{
    			    trace(mysql_error(), true);
    				$this->DBM->rollback();
    				return false;
    			}
    			// TODO: move perms query to PermissionManager
		        $qstr = "UPDATE ".cfg_obo_Perm::TABLE." SET ".cfg_obo_Perm::ITEM."='?' 
		        	WHERE ".cfg_obo_Perm::ITEM."='?' AND `".cfg_obo_Perm::TYPE."`='l' LIMIT 1";
    			if( !($q = $this->DBM->querySafe($qstr, $loID, $rootID)) )
    			{
    			    trace(mysql_error(), true);
    				$this->DBM->rollback();
    				return false;
    			}
    			
    			//$this->deleteLOs();
		    }
		}
		// make sure to delete the cache for the new master
		core_util_Cache::getInstance()->clearLO($loID);
		
		//$system = new nm_los_LOSystem();
		//$system->cleanOrphanData();
		$this->gatherAuthors($loID);
		return true;
	}

	/**
	 * Generates the list of authors found in the 'authors' field of a finalilzed LO.
	 * This should be run whenever a learning object is made final.
	 * @param $loID (number) Learning Object ID
	 */
	private function gatherAuthors($loID)
	{
		$auths = Array();
		$q = $this->DBM->querySafe("SELECT ".cfg_obo_LO::VER.", ".cfg_obo_LO::SUB_VER.", ".cfg_obo_LO::ROOT_LO." FROM ".cfg_obo_LO::TABLE." WHERE ".cfg_obo_LO::ID."='?' LIMIT 1", $loID);
		$r = $this->DBM->fetch_obj($q);

		//If they are trying to gather the authors for a draft, don't allow it
		if($r->{cfg_obo_LO::SUB_VER} != 0)
		{
			trace('gatherAuthors called on a draft', true);
			return false;
		}

		$nextvers = $r->{cfg_obo_LO::VER} - 1;
		$rootID = $r->{cfg_obo_LO::ROOT_LO};

		//Get authors from mapping table and add them to the list
		$q = $this->DBM->querySafe("SELECT ".cfg_core_User::ID." FROM ".cfg_obo_LO::MAP_AUTH_TABLE." WHERE ".cfg_obo_LO::ID."='?'", $loID);
		while($r = $this->DBM->fetch_obj($q)){ $auths[] = $r->{cfg_core_User::ID}; }

		//Convert the authors array into a space-separated list
		$authstr = implode(' ', $auths);

		return $authstr;
	}

	/**
	 * Saves a new draft (even if the learning object is a new rootID) and returns the new id number.
	 * It uses associative arrays because that is what is returned from remoting
	 * @param $loObj (LO) new learning object
	 * @return (LO) learning object (including new id)
	 * @return (bool) false if error
	 */
	public function newDraft($lo)
	{
		
		// must be content creator to start a new draft
		// library user must have write perms to edit a draft (cannot create a new one)
		// super user is free to do anything
		$roleMan = nm_los_RoleManager::getInstance();
		if(!$roleMan->isSuperUser())
		{
			// new lo, must be a content creator
			if($lo['rootID'] == 0)
			{
				if(!$roleMan->isContentCreator())
				{
					
					
					return core_util_Error::getError(4);
				}
			}
			// editing lo, must be libraryUser AND have write perms
			else
			{
				
				$permMan = nm_los_PermissionsManager::getInstance();
				trace($lo['rootID']);
				trace( $_SESSION['userID']);
				if(!($roleMan->isLibraryUser()) || !($permMan->getMergedPerm($lo['rootID'], cfg_obo_Perm::TYPE_LO, cfg_obo_Perm::WRITE, $_SESSION['userID'])) )
				{
					
					
					return core_util_Error::getError(4);
				}
			}
			
		}
		
		// do this after having valid perms
		$system = new nm_los_LOSystem();
		
		$system->cleanOrphanData();
		
		// check for locks on draft if its not new (in which case it cant be locked yet)
		$lockMan = nm_los_LockManager::getInstance();
		if(nm_los_Validator::isPosInt($lo['loID']))
		{
			$lock = $lockMan->lockExists($lo['loID']);	
			if($lock instanceof nm_los_Lock && $lock->userID != $_SESSION['userID'])
			{
				
				
				return core_util_Error::getError(3002);
			}
		}

        //update description and objective
		if($lo['notesID'] == 0 && nm_los_Validator::isString($lo['notes']))
		{
			$lo['notesID'] = $this->updateDescObj($lo['notes']);
		}
		if($lo['objID'] == 0 && nm_los_Validator::isString($lo['objective']))
		{
			$lo['objID'] = $this->updateDescObj($lo['objective']);
		}		
		
		$qgroupMan = nm_los_QuestionGroupManager::getInstance();
		$lo['pGroup'] = $qgroupMan->newGroup($lo['pGroup']);
		$lo['aGroup'] = $qgroupMan->newGroup($lo['aGroup']);

		//increment the partial version number 
	    $qstr = "SELECT MAX( ".cfg_obo_LO::SUB_VER." ) as max, `".cfg_obo_LO::VER."` FROM ".cfg_obo_LO::TABLE." WHERE ".cfg_obo_LO::ROOT_LO." = '?'
	    			GROUP BY ".cfg_obo_LO::VER." ASC LIMIT 1";
	    if(!($q = $this->DBM->querySafe($qstr, $lo['rootID'])))
	    {
	        trace(mysql_error(), true);
			$this->DBM->rollback();
			return false;
	    }
	    //check if the lo is a new root
	    if($r = $this->DBM->fetch_obj($q))
	    {
	        //it is not a new root, get the next version part
    	    $lo['version'] = $r->{cfg_obo_LO::VER};
    	    $lo['subVersion'] = $r->max + 1;
    	    if($lo['subVersion'] == 1)
			{
    	        $lo['parentID'] = $lo['rootID'];
			}
    	    else
			{
    	        $lo['parentID'] = 0;
			}
	    }
	    else
	    {
	        //it is a new root
		    $lo['version'] = 0;
		    $lo['subVersion'] = 1;
		    $lo['parentID'] = 0;
	    }
		//echo print_r($lo, true);
		//add lo information to database
		$qstr = "INSERT INTO ".cfg_obo_LO::TABLE." 
			SET 
			`".cfg_obo_LO::TITLE."`='?', 
			`".cfg_obo_Language::ID."`='?', 
			`".cfg_obo_LO::DESC."`='?',
			`".cfg_obo_LO::OBJECTIVE."`='?', 
			`".cfg_obo_LO::LEARN_TIME."`='?', 
			`".cfg_obo_LO::PGROUP."` = '?', 
			`".cfg_obo_LO::AGROUP."` = '?',
			`".cfg_obo_LO::VER."`='?' ,
			`".cfg_obo_LO::SUB_VER."`='?' ,
			`".cfg_obo_LO::ROOT_LO."`='?' ,
			`".cfg_obo_LO::PARENT_LO."`='?' ,
			`".cfg_obo_LO::TIME."`=UNIX_TIMESTAMP(), 
			`".cfg_obo_LO::COPYRIGHT."`='?'";
						
				
		if( !($q = $this->DBM->querySafe($qstr,	$lo['title'], $lo['languageID'], $lo['notesID'],
		            $lo['objID'], $lo['learnTime'], $lo['pGroup']['qGroupID'], $lo['aGroup']['qGroupID'], 
		            $lo['version'], $lo['subVersion'], $lo['rootID'], $lo['parentID'], $lo['copyright'])) )
		{
		    trace(mysql_error(), true);
			$this->DBM->rollback();
			return false;
		}

		$lo['loID'] = $this->DBM->insertID;
		//check if the lo is a new root
		if($lo['rootID'] == 0)
		{
		    //lo is a new root
		    //make the lo the root
			$lo['rootID'] = $this->DBM->insertID;
			$qstr = "UPDATE ".cfg_obo_LO::TABLE." SET ".cfg_obo_LO::ROOT_LO."='?' WHERE ".cfg_obo_LO::ID."='?'";
			if( !($this->DBM->querySafe($qstr, $lo['rootID'], $lo['loID'])) )
			{
				trace(mysql_error(), true);
				$this->DBM->rollback();
				return false;
			}
			//added permissions to the new lo
			// TODO: move perms query to permission manager
			if( !($this->DBM->querySafe("INSERT INTO `".cfg_obo_Perm::TABLE."` 
				(
					`".cfg_core_User::ID."`,
					`".cfg_obo_Perm::ITEM."`,
					`".cfg_obo_Perm::TYPE."`,
					`".cfg_obo_Perm::READ."`,
					`".cfg_obo_Perm::WRITE."`,
					`".cfg_obo_Perm::COPY."`,
					`".cfg_obo_Perm::PUBLISH."`,
					`".cfg_obo_Perm::G_READ."`,
					`".cfg_obo_Perm::G_WRITE."`,
					`".cfg_obo_Perm::G_COPY."`,
					`".cfg_obo_Perm::G_USE."`,
					`".cfg_obo_Perm::G_GLOBAL."`
				)
				VALUES 
				('?', '?', 'l', '1', '1', '1', '1', '1', '1', '1', '1', '1');", $_SESSION['userID'], $lo['loID'] )) )
			{
				trace(mysql_error(), true);
				$this->DBM->rollback();
				return false;
			}
		}
		else
		{
		    //if the lo is the first draft from a 1.0 object
		    if($lo['subVersion'] == 1)
		    {
                //lo is a new root
    		    //make the lo the root
    			$lo['rootID'] = $this->DBM->insertID;
    			$qstr = "UPDATE ".cfg_obo_LO::TABLE." SET ".cfg_obo_LO::ROOT_LO."='?' WHERE ".cfg_obo_LO::ID."='?'";
    			if( !($this->DBM->querySafe($qstr, $lo['rootID'], $lo['loID'])) )
    			{
    				trace(mysql_error(), true);
    				$this->DBM->rollback();
    				return false;
    			}
    			//added permissions to the new lo
				// TODO: move perm query to permissionmanager
    			if( !($this->DBM->querySafe("INSERT INTO `".cfg_obo_Perm::TABLE."` 
    				(
						`".cfg_core_User::ID."`,
						`".cfg_obo_Perm::ITEM."`,
						`".cfg_obo_Perm::TYPE."`,
						`".cfg_obo_Perm::READ."`,
						`".cfg_obo_Perm::WRITE."`,
						`".cfg_obo_Perm::COPY."`,
						`".cfg_obo_Perm::PUBLISH."`,
						`".cfg_obo_Perm::G_READ."`,
						`".cfg_obo_Perm::G_WRITE."`,
						`".cfg_obo_Perm::G_COPY."`,
						`".cfg_obo_Perm::G_USE."`,
						`".cfg_obo_Perm::G_GLOBAL."`						
					)
					VALUES 
    				('?', '?', 'l', '1', '1', '1', '1', '1', '1', '1', '1', '1');", $_SESSION['userID'], $lo['loID'] )) )
    			{
    				trace(mysql_error(), true);
    				$this->DBM->rollback();
    				return false;
    			}
				// TODO: permsmanager
				//copy permissions over from the x.0 master to the x.1 draft.  We exclude user 0 (global) since this would automatically add items in the public library
				$qStr = "SELECT *
						FROM ".cfg_obo_Perm::TABLE."
						WHERE ".cfg_obo_Perm::ITEM." = 'l'
						AND ".cfg_core_User::ID." != '0'
						AND ".cfg_obo_Perm::ITEM." = '?'
						AND ".cfg_core_User::ID." != '?'";
				if(!($q = $this->DBM->querySafe($qStr, $lo['parentID'], $_SESSION['userID'])))
				{
					$this->DBM->rollback();
					return false;
				}
				// TODO: permsmanager
				while($r = $this->DBM->fetch_obj($q))
				{
					$ins = "INSERT INTO 
						".cfg_obo_Perm::TABLE."
						(
							`".cfg_core_User::ID."`,
							`".cfg_obo_Perm::ITEM."`,
							`".cfg_obo_Perm::TYPE."`,
							`".cfg_obo_Perm::READ."`,
							`".cfg_obo_Perm::WRITE."`,
							`".cfg_obo_Perm::COPY."`,
							`".cfg_obo_Perm::PUBLISH."`,
							`".cfg_obo_Perm::G_READ."`,
							`".cfg_obo_Perm::G_WRITE."`,
							`".cfg_obo_Perm::G_COPY."`,
							`".cfg_obo_Perm::G_USE."`,
							`".cfg_obo_Perm::G_GLOBAL."`
						)
					VALUES ('?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?')";
					if(!($this->DBM->querySafe($ins, $r->{cfg_core_User::ID}, $lo['loID'], $r->{cfg_obo_Perm::TYPE}, $r->{cfg_obo_Perm::READ}, $r->{cfg_obo_Perm::WRITE}, $r->{cfg_obo_Perm::COPY}, $r->{cfg_obo_Perm::PUBLISH}, $r->{cfg_obo_Perm::G_READ}, $r->{cfg_obo_Perm::G_WRITE}, $r->{cfg_obo_Perm::G_COPY}, $r->{cfg_obo_Perm::G_USE}, $r->{cfg_obo_Perm::G_GLOBAL})))
					{
						trace(mysql_error(), true);
						$this->DBM->rollback();
						return false;
					}
				}
		    }
		}
		//update the pages
		$pageMan = nm_los_PageManager::getInstance();
		foreach($lo['pages'] as $key => &$page)
		{
			if($page['questionID'] != -1)
			{
				$page['questionID'] = $lo['pGroup']['kids'][$page['questionID']]['loID'];
			}
			if($page['pageID'] == 0)
			{
				$page = $pageMan->newPage($page);
			}
			$this->addPage($lo['loID'], $page['pageID'], $key);
		}

		//make the user the author of this draft
		$qstr = "INSERT INTO ".cfg_obo_LO::MAP_AUTH_TABLE." SET ".cfg_core_User::ID."='?', ".cfg_obo_LO::ID."='?'";
		if( !($q = $this->DBM->querySafe($qstr, $_SESSION['userID'], $lo['loID'])) )
		{
			trace(mysql_error(), true);
			$this->DBM->rollback();
			return false;
		}
		//Add keywords to the lo
		$keyMan = nm_los_KeywordManager::getInstance();
		$keyMan->linkKeywordsFromArray($lo['keywords'], $lo['loID'], 'l');
		
		//return the new lo from the database
		return $this->getLO($lo['loID'], 'full');
	}
	
	public function makeDerivative($loID)
	{
		if(! nm_los_Validator::isPosInt($loID))
		{
			
			
			return core_util_Error::getError(2);
		}
		
		$system = new nm_los_LOSystem();
		$system->cleanOrphanData();

		$roleMan = nm_los_RoleManager::getInstance();
        //check if user is a Super User
		if(!$roleMan->isSuperUser())
		{	
		    //if the user is not a Super User
			//check to see if user copy perms
			$permMan = nm_los_PermissionsManager::getInstance();
			$lo = $this->getLO($loID);
			if(!$permMan->getMergedPerm($lo->rootID, cfg_obo_Perm::TYPE_LO, cfg_obo_Perm::COPY, $_SESSION['userID']))
			{
				
				
				return core_util_Error::getError(4);
			}
		}		
		
	    $permMan = nm_los_PermissionsManager::getInstance();
	    $rootID = $this->getRootId($loID);
		
		// TODO: maybe do getLO instead, to pull from cache?
	    //check of the root id and the loID are equal (ie must be a master object)
		$qstr = "SELECT * FROM ".cfg_obo_LO::TABLE." WHERE ".cfg_obo_LO::ID."='?'";
	    if(!($q = $this->DBM->querySafe($qstr, $loID)))
		{
			return false;
		}
		$r = $this->DBM->fetch_obj($q);

		//selected object must be a master (1.0, 2.0 3.0)
		if($r->{cfg_obo_LO::ROOT_LO} == $r->{cfg_obo_LO::ID} && $r->{cfg_obo_LO::SUB_VER} == 0)
		{
			$qstr = "INSERT INTO ".cfg_obo_LO::TABLE." 
					SET
					`".cfg_obo_LO::TITLE."`='?', 
					`".cfg_obo_Language::ID."`='?', 
					`".cfg_obo_LO::DESC."`='?',
					`".cfg_obo_LO::OBJECTIVE."`='?', 
					`".cfg_obo_LO::LEARN_TIME."`='?', 
					`".cfg_obo_LO::PGROUP."` = '?', 
					`".cfg_obo_LO::AGROUP."` = '?',
					`".cfg_obo_LO::VER."`='?' ,
					`".cfg_obo_LO::SUB_VER."`='?' ,
					`".cfg_obo_LO::ROOT_LO."`='?' ,
					`".cfg_obo_LO::PARENT_LO."`='?' ,
					`".cfg_obo_LO::TIME."`=UNIX_TIMESTAMP(), 
					`".cfg_obo_LO::COPYRIGHT."`='?'";
    		if( !($q = $this->DBM->querySafe($qstr,	$r->{cfg_obo_LO::TITLE}, $r->{cfg_obo_Language::ID}, $r->{cfg_obo_LO::DESC},
    		            $r->{cfg_obo_LO::OBJECTIVE}, $r->{cfg_obo_LO::LEARN_TIME}, $r->{cfg_obo_LO::PGROUP}, $r->{cfg_obo_LO::AGROUP}, 
    		            1, 0, 0, $r->{cfg_obo_LO::ROOT_LO}, $r->{cfg_obo_LO::COPYRIGHT})) )
    		{
    		    trace(mysql_error(), true);
    			$this->DBM->rollback();
    			return false;
    		}
    		$newloID = $this->DBM->insertID;
    		//update the root, to be its own root, leave the parent as the old lo id
    		$qstr = "UPDATE ".cfg_obo_LO::TABLE." SET ".cfg_obo_LO::ROOT_LO." = '".$newloID."' WHERE ".cfg_obo_LO::ID." = ".$newloID;
		    if( !($q = $this->DBM->query($qstr)) )
    		{
    		    trace(mysql_error(), true);
    			$this->DBM->rollback();
    			return false;
    		}
    		
    		//permissions
			// TODO: move to permsmanager
    		$qstr = "INSERT INTO `".cfg_obo_Perm::TABLE."` 
				(
					`".cfg_core_User::ID."`,
					`".cfg_obo_Perm::ITEM."`,
					`".cfg_obo_Perm::TYPE."`,
					`".cfg_obo_Perm::READ."`,
					`".cfg_obo_Perm::WRITE."`,
					`".cfg_obo_Perm::COPY."`,
					`".cfg_obo_Perm::PUBLISH."`,
					`".cfg_obo_Perm::G_READ."`,
					`".cfg_obo_Perm::G_WRITE."`,
					`".cfg_obo_Perm::G_COPY."`,
					`".cfg_obo_Perm::G_USE."`,
					`".cfg_obo_Perm::G_GLOBAL."`
				)
				VALUES ('".$_SESSION['userID']."', '".$newloID."', 'l', '1', '1', '1', '1', '1', '1', '1', '1', '1');";
			if( !($this->DBM->query($qstr)) )
			{
				trace(mysql_error(), true);
				$this->DBM->rollback();
				//die();
				return false;
			}
    		
			// TODO: move to pagemanager
    		$qstr = "SELECT * FROM ".cfg_obo_Page::MAP_TABLE." WHERE ".cfg_obo_LO::ID."='".$r->{cfg_obo_LO::ID}."'";
    	    if(!($q = $this->DBM->query($qstr)))
    		{
    		    trace(mysql_error(), true);
    			$this->DBM->rollback();
    			return false;
    		}
    		
    		while($page = $this->DBM->fetch_obj($q))
    		{
    		    $qstr = "INSERT INTO ".cfg_obo_Page::MAP_TABLE." (".cfg_obo_LO::ID.",".cfg_obo_Page::MAP_ORDER.",".cfg_obo_Page::ID.")
				VALUES ('".$newloID."', '".$page->itemOrder."', '".$page->pageID."');";
    			if( !($this->DBM->query($qstr)) )
    			{
    				trace(mysql_error(), true);
    				$this->DBM->rollback();
    				return false;
    			}
    		}
    		// TODO: move this query to KeywordManager
		    $qstr = "SELECT * FROM `".cfg_obo_Keyword::MAP_TABLE."` 
		    			WHERE ".cfg_obo_Keyword::MAP_ITEM."='".$r->{cfg_obo_LO::ID}."' AND ".cfg_obo_Keyword::MAP_TYPE."='".cfg_obo_Perm::TYPE_LO."'";
    	    if(!($q = $this->DBM->query($qstr)))
    		{
    		    trace(mysql_error(), true);
    			$this->DBM->rollback();
    			return false;
    		}
    		
    		while($keyword = $this->DBM->fetch_obj($q))
    		{
				// TODO: move this sql query to keyword manager
    		    $qstr = "INSERT INTO `".cfg_obo_Keyword::MAP_TABLE."` (`".cfg_obo_Keyword::ID."`,`".cfg_obo_Keyword::MAP_TYPE."`,`".cfg_obo_Keyword::MAP_ITEM."`)
				VALUES ('".$keyword->keywordID."', '".cfg_obo_Perm::TYPE_LO."', '".$newloID."');";
    			if( !($this->DBM->query($qstr)) )
    			{
    				trace(mysql_error(), true);
    				$this->DBM->rollback();
    				return false;
    			}
    		}
    		
    		return $newloID;
		}
		
       
        return core_util_Error::getError(2); // lo must be a master but isnt
	}
	
	public function removeFromLibrary($loID)
	{
		if(!nm_los_Validator::isPosInt($loID))
		{
			
	       
	        return core_util_Error::getError(2);
	    }   

	    //check to see if the current user has permissions to remove the lo from the library
		$roleMan = nm_los_RoleManager::getInstance();
		if(!$roleMan->isSuperUser())
		{
			if(!$roleMan->isLibraryUser())
			{
				
				
				return core_util_Error::getError(4);
			}
			 // Didn't do this at the same time as the check above to save these lines from executing if not a lib user
			$permMan = nm_los_PermissionsManager::getInstance();
			$lo = $this->getLO($loID);
			$perms = $permMan->getMergedPerms($lo->rootID, cfg_obo_Perm::TYPE_LO);
			if($perms instanceof nm_los_Permissions)
			{
				if(!$perms->isOwner())
				{
					
					
					return core_util_Error::getError(4);
				}
			}
		}
		$system = new nm_los_LOSystem();
		$system->cleanOrphanData();
		
		if($lo = $this->getLO($loID))
		{
			$permMan = nm_los_PermissionsManager::getInstance();
			//if it does have global perms, remove them
			if($permMan->getGlobalPerms($lo->rootID, cfg_obo_Perm::TYPE_LO))
			{
				return $permMan->removeUserPerms($lo->rootID, cfg_obo_Perm::TYPE_LO, 0);
			}
			return false;
		}
		
       
        return core_util_Error::getError(2);
	}

	/**
	 * Delete a learning object
	 * -Must either be SU or have write perms to delete
	 * -Will fail if there are any instances
	 * -Will not fail if there are any derivatives 
	 * -Will not fail if there are any revisions
	 * -Actually deletes lo, supporting pages etc will be deleted later
	 * @param $loID (LO) learning object id
	 * @return (bool) true or false if not deleteable
	 */
	public function delTree($loID)
	{
		$roleMan = nm_los_RoleManager::getInstance();
		$permMan = nm_los_PermissionsManager::getInstance();
		$rootID = $this->getRootId($loID);
		
		// must be superUser OR LibararyUser && have write permissions
		if(!$roleMan->isSuperUser())
		{
			if(!$roleMan->isLibraryUser())
			{
				
				
				return core_util_Error::getError(4);
			}
			$lo = $this->getLO($loID);
			 // Didn't do this at the same time as the check above to save these lines from executing if not a lib user
			trace($lo->rootID . ' ' . $_SESSION['userID']);
			$perms = $permMan->getMergedPerms($lo->rootID, cfg_obo_Perm::TYPE_LO);
			if($perms instanceof nm_los_Permissions)
			{
				if(!$perms->isOwner())
				{
					
					
					return core_util_Error::getError(4);
				}
			}
		}
		
		$qstr = "SELECT ".cfg_obo_LO::ID.", ".cfg_obo_LO::VER.", ".cfg_obo_LO::SUB_VER.", ".cfg_obo_LO::ROOT_LO.", ".cfg_obo_LO::PARENT_LO.", ".cfg_obo_LO::TIME."
					FROM ".cfg_obo_LO::TABLE." WHERE ".cfg_obo_LO::ID."='?'";
	    if(!($q = $this->DBM->querySafe($qstr, $loID)))
		{
			$this->DBM->rollback();
			trace(mysql_error(), true);
			return false;
		}
		$r = $this->DBM->fetch_obj($q);
		
		//User is trying to delete a Master (1.0, 2.0 3.0) check for existing instances
		if($r->{cfg_obo_LO::ROOT_LO} == $r->{cfg_obo_LO::ID} && $r->{cfg_obo_LO::SUB_VER} == 0)
		{
			$instMan = nm_los_InstanceManager::getInstance();
			if(count($instMan->getInstancesFromLOID($loID)) > 0)
			{
				
				
				return core_util_Error::getError(6003);
			}
			// remove all perms for this MASTER since there are no instances
			$permMan = nm_los_PermissionsManager::getInstance();
			if($permMan->removeAllPermsForItem($r->{cfg_obo_LO::ROOT_LO}, cfg_obo_Perm::TYPE_LO))
			{
				
				core_util_Cache::getInstance()->clearLO($r->{cfg_obo_LO::ROOT_LO});
				$system = new nm_los_LOSystem();
				$tracking = nm_los_TrackingManager::getInstance();
				$tracking->trackDeleteLO($r->{cfg_obo_LO::ROOT_LO}, 1);
			}
			$system->cleanOrphanData();
			return true;
		}
		else
		{
			// delete all draft objects
			$qstr = "DELETE FROM ".cfg_obo_LO::TABLE." WHERE ".cfg_obo_LO::ROOT_LO."='?'";
			if(!($q = $this->DBM->querySafe($qstr, $r->{cfg_obo_LO::ROOT_LO})))
			{
				$this->DBM->rollback();
				return false;
			}
			$losDeleted = $this->DBM->affected_rows();
			if($losDeleted > 0)
			{
				$permMan = nm_los_PermissionsManager::getInstance();
				if(!$permMan->removeAllPermsForItem($r->{cfg_obo_LO::ROOT_LO}, cfg_obo_Perm::TYPE_LO))
				{
					$this->DBM->rollback();
					return false;
				}
				
				
				core_util_Cache::getInstance()->clearLO($r->{cfg_obo_LO::ROOT_LO});
				$tracking = nm_los_TrackingManager::getInstance();
				$tracking->trackDeleteLO($r->{cfg_obo_LO::ROOT_LO}, $losDeleted);
			}	
			return true;
		}
	}
	
	
	
	/**
	 * Gets minimum to full information about an LO
	 * @param $loID (number) learning object id
	 * @param $amount (string) Amount of information to get.  (values: 'full', 'meta', 'min')
	 * @return (LO) learning object
	 * @return (bool) False if error
	 */
	public function getLO($loIDArrOrInt=0, $amount='full', $inc_weight=true)
	{
		// whitelist input
		// loid can be an array of pos integers for grabbing meta objects
		if(is_array($loIDArrOrInt))
		{
			foreach($loIDArrOrInt AS $eachLoID)
			{
				if(!nm_los_Validator::isPosInt($eachLoID))
				{
					
			       
			        return core_util_Error::getError(2);
				}
			}
			// force getLO on an array to use meta
			$amount='meta';
			$loArr = $loIDArrOrInt;
		}
		// requesting a single lo, make sure its a positive int
		else
		{
			if(!nm_los_Validator::isPosInt($loIDArrOrInt))
			{
				
		       
		        return core_util_Error::getError(2);
			}
			$loArr = array();
			$loArr[] = $loIDArrOrInt;
		}
		
		// check for rights to see the lo
		$roleMan = nm_los_RoleManager::getInstance();
		// NOTE: requesting an array forces this request to meta mode, so permissions do not need to be checked by iterating the array
		if($amount != 'meta')
		{
			if(!nm_los_Validator::isPosInt($_SESSION['userID']))
			{
				$amount = 'meta';
			}
    		elseif(!$roleMan->isSuperUser())
    		{
    		    $permman = nm_los_PermissionsManager::getInstance();
    			//Check to see if user doesnt have read permissions, if not, they need to be currently visiting this instance
    			if(!$permman->getMergedPerm($this->getRootId($loIDArrOrInt), cfg_obo_Perm::TYPE_LO, cfg_obo_Perm::READ, $_SESSION['userID']))
    			{
    				$visitMan = nm_los_VisitManager::getInstance();
    				$visit = $visitMan->getVisit($GLOBALS['CURRENT_INSTANCE_DATA']['visitID']);
    				if(false && !$permman->getMergedPerm($visit->instID, cfg_obo_Perm::TYPE_INSTANCE, cfg_obo_Perm::READ, $_SESSION['userID'])){
    					// trying a switch to meta instead
    					//return false;
    					$amount = 'meta';
    				}
    			}
		    }
		}
		$returnLOs = array();
		foreach($loArr AS $loID)
		{
			$lo = new nm_los_LO();
			switch($amount)
			{
				case 'content':
				//case 'instance':
					$lo->dbGetContent($this->DBM, $loID);
					break;
				//@ZACH: Now instance doesn't return the assessment questions.
				case 'instance':
					$lo->dbGetInstance($this->DBM, $loID);
					break;
				case 'full': // Get the full LO, authentication should be required
					$lo->dbGetFull($this->DBM, $loID);
					break;
				case 'meta': // Get just meta data, this is publicly availible w/o authentication
				default: // only give out what is publicly avail in the case of a bad $amount value
					$lo->dbGetMeta($this->DBM, $loID);
					break;
			}
			if($lo->loID > 0) $returnLOs[] = $lo; // only return LOs that we can find
		}
		return is_array($loIDArrOrInt) ? $returnLOs : $returnLOs[0]; // return an array if an array was asked for
	}

	/**
	 * Chares an lo from the current user to the specified user ($suid)
	 * If share was unsuccesful for a certain property (ex: read), return FALSE
	 * Will only return TRUE if all properties of the $permObj were set succesfully
	 * @param $loID (Number) learning object id
	 * @param $suid (Number) the user id of user is sharing the lo to
	 * @param $permObj (Permissions) the permissions object
	 * @return (bool) TRUE if share was succesful, FALSE otherwise
	 * 
	 * TODO: develop a way to keep going if the user does not have complete access to the media,
	 * maybe return an array of the media that was not able to be updated with the new perms
	 */
	public function shareLO($loID, $permObj)
	{
		$system = new nm_los_LOSystem();
		$system->cleanOrphanData();
		
		if(!is_numeric($loID) || $loID <= 0)
		{
			trace('invalid input', true);
			return false;
		}

		$permMan = nm_los_PermissionsManager::getInstance();
		
		if($permMan->hasPerms($_SESSION['userID'], $loID, cfg_obo_Perm::TYPE_LO))
		{
			if($permMan->hasPerms($permObj->userID, $loID, cfg_obo_Perm::TYPE_LO))
			{
				if(!$permMan->updateUserPerms($loID, cfg_obo_Perm::TYPE_LO, $permObj))
				{
					trace('insufficient permissions to lo', true);
					return false;
				}
			}
			else
			{
				if(!$permMan->setUserPerms($loID, cfg_obo_Perm::TYPE_LO, $permObj))
				{
					trace('insufficient permissions', true);
					return false;
				}
			}
				
			//Get all the media ids in pages from the lo
			// TODO: move to media manager?
			$qstr = "SELECT DISTINCT M.".cfg_obo_Media::ID." 
						FROM ".cfg_obo_Media::TABLE." AS M, ".cfg_obo_Page::MAP_ITEM_TABLE." AS I, ".cfg_obo_Page::MAP_TABLE." AS P
						WHERE M.".cfg_obo_Page::ITEM_ID." = I.".cfg_obo_Page::ITEM_ID." 
						AND I.".cfg_obo_Page::ID." = P.".cfg_obo_Page::ID." 
						AND P.".cfg_obo_LO::ID." = '?'";

			$q = $this->DBM->querySafe($qstr, $loID);
			while($r = $this->DBM->fetch_obj($q))
			{
				if(!$permMan->hasPerms($_SESSION['userID'], $r->{cfg_obo_Media::ID}, "m"))
				{
					$mediaMan = nm_los_MediaManager::getInstance();
					$media = $mediaMan->getMedia($r->{cfg_obo_Media::ID});
					if($media->auth == $_SESSION['userID'])
					{
						//Add owner permissions to this object for this user
						$permMan->setNewUserPerms($r->{cfg_obo_Media::ID}, 'm', new nm_los_Permissions($_SESSION['userID'], 1, 1, 1, 1, 1, 1, 1, 1, 1, 0));
					}
					else
					{
						trace('current user is not owner of media', true);
						return false;
					}
				}

				if($permMan->hasPerms($permObj->userID, $r->{cfg_obo_Media::ID}, "m"))
				{
					if(!$permMan->updateUserPerms($r->{cfg_obo_Media::ID}, 'm', $permObj))
					{
						trace('insufficient permissions to media', true);
						return false;
					}
				}
				else
				{
					if(!$permMan->setUserPerms($r->{cfg_obo_Media::ID}, 'm', $permObj))
					{
						trace('unable to share media perms', true);
						return false;
					}
				}
			}

			//Get all the media ids in the practice and question groups
			$qstr = "SELECT DISTINCT M.".cfg_obo_Media::ID." 
						FROM ".cfg_obo_QGroup::MAP_TABLE." AS Q, ".cfg_obo_Media::MAP_TABLE." AS M, ".cfg_obo_LO::TABLE." AS L 
						WHERE Q.".cfg_obo_QGroup::ID." IN (L.".cfg_obo_LO::PGROUP.", L.".cfg_obo_LO::AGROUP.")
						AND Q.".cfg_obo_QGroup::MAP_TYPE." = 'q' 
						AND Q.".cfg_obo_QGroup::MAP_CHILD." = M.".cfg_obo_Page::ITEM_ID." 
						AND L.".cfg_obo_LO::ID."='?'";

			$q = $this->DBM->querySafe($qstr, $loID);
			while($r = $this->DBM->fetch_obj($q))
			{
				if(!$permMan->hasPerms($_SESSION['userID'], $r->{cfg_obo_Media::ID}, "m"))
				{
					$mediaMan = nm_los_MediaManager::getInstance();
					$media = $mediaMan->getMedia($r->{cfg_obo_Media::ID});
					if($media->auth == $_SESSION['userID'])
					{
						//Add owner permissions to this object for this user
						$permMan->setNewUserPerms($r->{cfg_obo_Media::ID}, 'm', new nm_los_Permissions($_SESSION['userID'], 1, 1, 1, 1, 1, 1, 1, 1, 1, 0));
					}
					else
					{
						trace('current user is not owner of media', true);
						return false;
					}
				}

				if($permMan->hasPerms($permObj->userID, $r->{cfg_obo_Media::ID}, "m"))
				{
					if(!$permMan->updateUserPerms($r->{cfg_obo_Media::ID}, 'm', $permObj))
					{
						trace('insufficient perms to media', true);
						return false;
					}
				}
				else
				{
					if(!$permMan->setUserPerms($r->{cfg_obo_Media::ID}, 'm', $permObj))
					{
						trace('unable to share media perms', true);
						return false;
					}
				}
			}

			return true;
		}
		trace('insufficient perms to share', true);
		return false;
	}

	/**
	 * Copies the lo and makes it the root of a new derivative tree
	 * @param $loID (number) learning object id
	 * @return (bool) TRUE if copy successful, FALSE if error
	 */
	public function copyLO($loID)
	{
	    return false;
	    
	    /*

		//Get LO from DB so we can check version and perms
		$lo = $this->getLO($loID, 'full');

		echo ' lo partial version '.$lo->subVersion.'<br />';
		echo ' lo copy perm '.$lo->perms->copy.'<br />';
		//If lo is full version and user has permission to copy this object
		if( $lo->subVersion == 0 && $lo->perms->copy ){
			echo 'ok to copy';
			//Change ids to 0 and convert to array
			$lo->loID = 0;
			$lo->notesID = 0;
			//$lo->desc = (array) $lo->desc;
			$lo->objective->notesID = 0;
			$lo->objective = (array) $lo->objective;
			$lo->rootID = 0;
			$pages = $lo->pages;
			foreach($pages as $key => $val){
				$pages[$key]->pageID = 0;
				$items = $pages[$key]->items;
				foreach($items as $key2 => $val2){
					$items[$key2]->pageItemID = 0;
					$media = $items[$key2]->media;
					foreach($media as $key3 => $val3)
					$lo->pages[$key]->items[$key2]->media[$key3] = (array) $val3;
					$lo->pages[$key]->items[$key2] = (array) $val2;
				}
				//settype($pages[$key], 'array');
				$lo->pages[$key] = (array) $val;
			}
				
			$lo->pGroup->qGroupID = 0;
			$pkids = $lo->pGroup->kids;
			foreach($pkids as $key => $val){
				if(isset($pkids[$key]->answers)){
					$pkids[$key]->questionID = 0;
					$answers = $pkids[$key]->answers;
					foreach($answers as $key2 => $val2){
						$answers[$j]->answerID = 0;
						$lo->pGroup->kids[$key]->answers[$key2] = (array) $val2;
					}
				}
				$lo->pGroup->kids[$key] = (array) $val;
			}
			$lo->pGroup = (array) $lo->pGroup;
				
			$lo->aGroup->qGroupID = 0;
			$akids = $lo->aGroup->kids;
			foreach($akids as $key => $val){
				if(isset($akids[$key]->answers)){
					$akids[$key]->questionID = 0;
					$answers = $akids[$key]->answers;
					foreach($answers as $key2 => $val2){
						$answers[$j]->answerID = 0;
						$lo->aGroup->kids[$key]->answers[$key2] = (array) $val2;
					}
				}
				$lo->aGroup->kids[$key] = (array) $val;
			}
			$lo->aGroup = (array) $lo->pGroup;
				
			$lo = (array) $lo;
				
			//Save lo to db, which also sets full permissions
			$lo = $this->newDraft($lo);
			echo '<br />new draft created<br />';
			print_r($lo);
				
			//Copy author list over
			$qstr = "SELECT userID,timestamp FROM ".self::mapping_authors." WHERE lo_id='?'";
			if( !($q = $this->DBM->querySafe($qstr, $loID)) ){
				trace("ERROR: copyLO  ".mysql_error());
				$this->DBM->rollback();
				//die();
				return false;
			}
				
			while($r = $this->DBM->fetch_obj($q)){
				$qstr = "INSERT INTO ".self::mapping_authors." SET userID='{$r->userID}',lo_id='?',timestamp='{$r->timestamp}'";
				if( !($q = $this->DBM->querySafe($qstr, $loID)) ){
					trace("ERROR: copyLO  ".mysql_error());
					$this->DBM->rollback();
					//die();
					return false;
				}
			}
			echo 'done';
			return true;
		}else{		//If they are not allowed to copy, exit function
			return false;
		}
*/
	    
	}

	/**
	 * Gets latest whole version of a LO (not a draft)
	 * @param $loID (number) ID of LO of any version in the same tree
	 * @param $amount (string) Amount of information to get.  (values: 'full', 'meta', 'min')
	 */
	public function getLatestWholeVersion($loID = 0, $amount='full')
	{
		if(!is_numeric($loID) || $loID < 1)
		{
			trace('invalid input', true);
		    return false;
		}
        
		$qstr = "SELECT ".cfg_obo_LO::ID." FROM ".cfg_obo_LO::TABLE." WHERE ".cfg_obo_LO::VER."='0' AND ".cfg_obo_LO::ROOT_LO."=(SELECT ".cfg_obo_LO::ROOT_LO." FROM ".cfg_obo_LO::TABLE." WHERE ".cfg_obo_LO::ID."='?' LIMIT 1) ORDER BY ".cfg_obo_LO::VER." DESC LIMIT 1";
		$q = $this->DBM->querySafe($qstr, $loID);

		if($r = $this->DBM->fetch_obj($q))
		{
			return $this->getLO($r->{cfg_obo_LO::ID}, $amount);
		}
		trace('unable to retrieve latest whole version for: '.$loID, true);
		return false;
	}

	/**
	 * Returns all the newest Drafts that the current user has permissions to
	 */
	public function getMyDrafts()
	{
		// TODO: speed this up by getting all the drafts in one query using grouping OR caching this list
		$permMan = nm_los_PermissionsManager::getInstance();
		$loIDArr = $permMan->getItemsWithPerm(cfg_obo_Perm::TYPE_LO, cfg_obo_Perm::READ);
		$loArr = array();
		foreach($loIDArr as $val)
		{
			if($lo = $this->getLatestDraft($val, 'meta')) // could fix this to be faster?
			{
			    if($lo->subVersion != 0) // if not a master
				{
				    $loArr[] = $lo;
				}
			}
		}
		return $loArr;

	}
	//
	public function getMyMasters()
	{
		// TODO: find a way to do this w/o the sql query
		$permMan = nm_los_PermissionsManager::getInstance();
		$loIDArr = $permMan->getItemsWithPerm(cfg_obo_Perm::TYPE_LO, cfg_obo_Perm::READ, true);
		$loArr = array();
		foreach($loIDArr as $loID)
		{
            $qstr = "SELECT ".cfg_obo_LO::ID." FROM ".cfg_obo_LO::TABLE." WHERE ".cfg_obo_LO::ROOT_LO."='".$loID."' AND ".cfg_obo_LO::SUB_VER." = 0 ORDER BY ".cfg_obo_LO::VER." DESC, ".cfg_obo_LO::SUB_VER." DESC LIMIT 1";
            if(!($q = $this->DBM->query($qstr)))
    		{
				return false;	
    		}
    		if($r = $this->DBM->fetch_obj($q))
			{
                $loArr[] = $this->getLO($r->{cfg_obo_LO::ID}, 'meta');
			}
		}
		
		return $loArr;
	}
	
	public function getMyObjects()
	{
		$drafts = $this->getMyDrafts();
		$masters = $this->getMyMasters();
		return array_merge( $drafts, $masters );
	}
	
    public function getPublicMasters()
    {
		$permman = nm_los_PermissionsManager::getInstance();
		$publicMasters = $permman->getItemsWithPerm(cfg_obo_Perm::TYPE_LO, cfg_obo_Perm::PUBLISH, false, true);

		foreach($publicMasters as $loID)
		{
			$lo = $this->getLO($loID, 'meta');
			$lo->globalPerms = $permman->getGlobalPerms($lo->rootID, cfg_obo_Perm::TYPE_LO); // add in globalPerms
			$loArr[] = $lo;	
		}
		return $loArr;
    }

	/**
	 * Gets the most recent draft of a tree
	 * @param $rootID (number) root learning object id
	 * @return (LO) learning object
	 * @return (bool) False if error
	 */
	private function getLatestDraft($rootID = 0, $amount='full')
	{
		if(!is_numeric($rootID) || $rootID < 1)
		{
			trace('invalid input', true);
			return false; // error: invalid input
		}

		$qstr = "SELECT ".cfg_obo_LO::ID." FROM ".cfg_obo_LO::TABLE." WHERE ".cfg_obo_LO::ROOT_LO."='?' ORDER BY ".cfg_obo_LO::VER." DESC, ".cfg_obo_LO::SUB_VER." DESC LIMIT 1";
		$q = $this->DBM->querySafe($qstr, $rootID);

		if($r = $this->DBM->fetch_obj($q))
		{
			return $this->getLO($r->{cfg_obo_LO::ID}, $amount);
		}
		trace('Unable to find latest draft: ' . $rootID, true);		
		return false; // error: no lo draft exists with that root id
	}

	/**
	 * Gets the most recent draft of a tree
	 * @param $loID (number) learning object id
	 * @return (LO) learning object
	 * @return (bool) False if error
	 */
	public function getLatestDraftByLOID($loID = 0, $amount='full')
	{
	    if(!nm_los_Validator::isPosInt($loID))
		{
			
	       
	        return core_util_Error::getError(2);
		}
		
	    $qstr = "SELECT ".cfg_obo_LO::ROOT_LO." FROM ".cfg_obo_LO::TABLE." WHERE ".cfg_obo_LO::ID."='?'";
		$q = $this->DBM->querySafe($qstr, $loID);
		if($r = $this->DBM->fetch_obj($q))
		{
		    $qstr = "SELECT ".cfg_obo_LO::ID." FROM ".cfg_obo_LO::TABLE." WHERE ".cfg_obo_LO::ROOT_LO."='{$r->{cfg_obo_LO::ROOT_LO}}' ORDER BY ".cfg_obo_LO::VER." DESC, ".cfg_obo_LO::SUB_VER." DESC LIMIT 1";
			if($r = $this->DBM->fetch_obj($this->DBM->query($qstr)))
			{
				return $this->getLO($r->{cfg_obo_LO::ID}, $amount);
			}
		}
		trace('Unable to find LO: ' . $loID, true);
	    return false;
	}
	
	/**
	 * Gets a list of all drafts for a given root id
	 * @param $rootID (number) root learning object id
	 * @param $amount (string) Amount of information to get.  (values: 'full', 'meta', 'min')
	 * @return (Array<LO>) an array of learning objects
	 * @return (bool) False if error
	 */
	public function getDrafts($rootID, $amount='min')
	{
		if(!nm_los_Validator::isPosInt($rootid))
		{
			
	       
	        return core_util_Error::getError(2);
	    }
	
		$ret = array();

		$q = $this->DBM->querySafe("SELECT ".cfg_obo_LO::ID." FROM ".cfg_obo_LO::TABLE." WHERE ".cfg_obo_LO::ROOT_LO."='?' ORDER BY ".cfg_obo_LO::VER." DESC, ".cfg_obo_LO::SUB_VER." DESC", $rootID);

		//Gather los into a list
		while($r = $this->DBM->fetch_obj($q))
		{
			$ret[] = $this->getLO($r->{cfg_obo_LO::ID}, $amount);
		}
		return $ret;
	}

	/**
	 * Gets list of all Objects the user has a certain permission for
	 * @param $userID (number) user id
	 * @param $perm (string) permission (values: 'read', 'write', 'copy', 'publish', 'giveRead', 'giveWrite', 'giveCopy', 'givePublish', 'giveGlobal')
	 * @return (Array<LO>) an array of metadata learning objects
	 * @todo remove $userID from this method and from the getItemsWithPerm method call
	 */
	public function getLOsWithPerm($perm)
	{
		$permMan = nm_los_PermissionsManager::getInstance();
		$loIDArr = $permMan->getItemsWithPerm(cfg_obo_Perm::TYPE_LO, $perm);

		$loArr = array();
		foreach($loIDArr as $loID)
		{
		   $loArr[] = $this->getLatestWholeVersion($loID, 'meta');
		}
		
		return $loArr;
	}

	/**
	 * Adds a page to the mapping table in the order specified
	 * @param $loID (number) Learning Object ID
	 * @param $pgid (number) Page ID
	 * @param $porder (number) Index of page (0,1,2...)
	 */
	private function addPage($loID, $pgid, $porder)
	{
		// TODO: move to pagemanager
		if( !($q = $this->DBM->querySafe("INSERT INTO ".cfg_obo_Page::MAP_TABLE." SET ".cfg_obo_LO::ID."='?', ".cfg_obo_Page::ID."='?', ".cfg_obo_Page::MAP_ORDER."='?'", $loID, $pgid, $porder)) )
		{
			trace(mysql_error(), true);
			$this->DBM->rollback();
			//die();
			return false;
		}
	}

	/**
	 * Updates the description or objective fields of a LO (both are stored in the same table)
	 * @param $objref (Object) Description or Objective object.  Contains 'id' and 'text'.
	 * @return (Object) The Description or Objective object, with the new ID if the object had an 'id' of 0.
	 */
	private function updateDescObj($newDescText)
	{
	    $qstr = "INSERT INTO ".cfg_obo_Text::TABLE." SET ".cfg_obo_Text::TEXT."='?'";
		if( !($q = $this->DBM->querySafe($qstr, $newDescText)) )
		{
			trace(mysql_error(), true);
			$this->DBM->rollback();
			return false;
		}
		return $this->DBM->insertID;
	}

	public function getAssessmentID($lo_id)
	{
		$qstr = "SELECT ".cfg_obo_LO::AGROUP." FROM ".cfg_obo_LO::TABLE." WHERE ".cfg_obo_LO::ID." = '?'";
		
		if(!($q = $this->DBM->querySafe($qstr, $lo_id)))
		{
			$this->DBM->rollback();
			//echo "ERROR: getAssessmentID";
			trace(mysql_error(), true);
			//exit;
			return false;
		}
		
		if($r = $this->DBM->fetch_obj($q))
		{
			return $r->{cfg_obo_LO::AGROUP};
		}
		trace('unable to get assessment id for lo: ' . $loID, true);
		return false;	
	}
/*
    public function saveTemporaryLO($lo)
    {
        clearTemporaryLO();
        $qstr = "INSERT INTO ".self::temp." SET `userID`='?', data='?'";
        if(!($this->DBM->querySafe($qstr, $_SESSION['userID'], base64_encode(serialize($lo)))))
        {
            $this->DBM->rollback();
            trace(mysql_error(), true);
            //die();
			return false;
        }
        return true;
    }
    
    public function getTemporaryLO()
    {
        $qstr = "SELECT data FROM ".self::temp." WHERE `userID`='?'";
		if(!($q = $this->DBM->querySafe($qstr, $_SESSION['userID'])))
		{
    		$this->DBM->rollback();
    		trace(mysql_error(), true);
    		//die();
			return false;	
		}
		
		if($r = $this->DBM->fetch_obj($q))
		{
    		return unserialize(base64_decode($r->data));
		}
		trace('unable to get temporary LO', true);
		return false;
    }
    
    public function clearTemporaryLO()
    {
        $qstr = "DELETE FROM ".self::temp." WHERE `userID`='?'";
		if(!($this->DBM->querySafe($qstr, $_SESSION['userID'])))
		{
    		$this->DBM->rollback();
    		trace(mysql_error(), true);
    		//die();
			return false;	
		}
		
		return true;
    }
  */  
}
?>