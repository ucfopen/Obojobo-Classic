<?php
/**
 * This class contains the public API for the LOS backend
 * @author Jacob Bates <jbates@mail.ucf.edu>
 * @author Luis Estrada <lestrada@mail.ucf.edu>
 */

/**
 * This class was created so that all functionality external to the system could be accessed by instantiating a single class.  This functionality includes user management, login/logout, content management, and learning object management.
 */
class nm_los_API extends core_db_dbEnabled
{

	private static $instance;
	
	static public function getInstance($isRemoting = false)
	{
		if(!isset(self::$instance))
		{
			$selfClass = __CLASS__;
			self::$instance = new $selfClass($isRemoting);
		}
		return self::$instance;
	}

	public function __construct($isRemoting = false)
	{
		parent::__construct();
		// if($isRemoting)
		// {
		// 	//$config->timeLimit = AppCfg::AUTH_TIMEOUT_REMOTING;
		// }
	}
	
	/**
	 * Verifies that the user has a current session and generates a new SESSID for them 
	 * @return (bool) true if user is logged in, false if not
	 */    
	public function getSessionValid($roleName='')
	{
		$UM = core_auth_AuthManager::getInstance();
		return $UM->verifySession($roleName);
	}

	/**
	 * Verifies session and role with a more granular return then verifySession
	 * @param	$roleNames (array)	Role names to check for current session
	 * @return 	(array)	array with the following keys: validSession (bool, user currently has a valid session), roleName (string, name of role checked), hasRole (bool, user is in the role returned in roleName).   
	 */
	public function getSessionRoleValid($roleNames='')
	{

		if(!is_array($roleNames))
		{
			return core_util_Error::getError(2);
		}		
		$AM = core_auth_AuthManager::getInstance();
		$return = array();
		$return['validSession'] = $AM->verifySession();	
		$return['roleNames'] = $roleNames;
		$return['hasRoles'] = array();
		if($return['validSession'] === true && $roleNames != '')
		{
			$roleMan = nm_los_RoleManager::getInstance();
			$roles = $roleMan->getUserRoles($_SESSION['userID']);
			foreach($roleNames as $givenRole)
			{
				foreach($roles as $returnRole)
				{
					if($givenRole == $returnRole->name) $return['hasRoles'][] = $returnRole->name;
				}
			}
		}
		return $return;	
	}
	
	/**** Login Functions ****/
	
	/** 
	 * Logs user into system
	 * @param $userID (string) user login name
	 * @param $pwd (string) hashed password
	 * @return (bool) true if login successful, false if not
	 */
	// security check: Ian Turgeon 2008-05-06 - PASS ( followed down all the way to nm_los_AuthModule)
	public function doLogin($uname, $pwd)
	{
		$UM = core_auth_AuthManager::getInstance();
		return $UM->login($uname, $pwd);
	}
	
	public function doPluginCall($plugin, $method, $args = -1)
	{
		$PM = core_plugin_PluginManager::getInstance();
		return $PM->callAPI($plugin, $method, $args, false); // call the plugin method, but restrict it to whitelisted functions
	}

	public function getCourses()
	{
		if($this->getSessionValid())
		{
			// TODO: NEED TO USE SYSTEM EVENTS
			$PM = core_plugin_PluginManager::getInstance();
			$result = $PM->callAPI('UCFCourses', 'getCourses', array(), true);
		}
		else
		{
			$result = core_util_Error::getError(1);
		}
		return $result;
	}
	
	public function setCourseForInstance($instID, $courseID, $sendScores = false, $gradeBookColumnName = false)
	{
		$result = false;
		if($this->getSessionValid())
		{
			// TODO: NEED TO USE SYSTEM EVENTS
			$user = $this->getUser();
			
			$PM = core_plugin_PluginManager::getInstance();
			
			// Link item to course
			$result = $PM->callAPI('UCFCourses', 'setInstanceCourseLink', array($instID, $courseID), true);
			
			if($sendScores == true && strlen($gradeBookColumnName) > 0)
			{
				// create the gradebook column
				$result = $PM->callAPI('UCFCourses', 'createColumn', array($instID, $courseID, $gradeBookColumnName), true);

				// it worked, return true
				if($result['columnID'] > 0 )
				{
					$result = true;
				}
				// errors returned, return the first one
				elseif(count($result['errors']) > 0)
				{
					$result = $result['errors'][0];
				}
				// no idea what happend, but it didnt work, report a general error
				else
				{
					$result = core_util_Error::getError(0);
				}
			}
		}
		else
		{
			$result = core_util_Error::getError(1);
		}
		return $result;	
	}
	
	/**
	 * Logs out the current active user
	 */
	public function doLogout()
	{
		if($this->getSessionValid())
		{	
			$UM = core_auth_AuthManager::getInstance();
			$UM->logout($_SESSION['userID']);
		}
	}
	
	/**
	 * Gets information about the current user
	 * $return (User) User object
	 * @return (bool) False if error or no login
	 */
	public function getUser(){
		if($this->getSessionValid())
		{
			$UM = core_auth_AuthManager::getInstance();
			$result = $UM->fetchUserByID($_SESSION['userID']);
		}
		else
		{
			$result = core_util_Error::getError(1);
		}
		return $result;
	}

	/**
	 * Deletes a user with id
	 * @return (bool) True if succesful, False if error or no login
	 */
	public function removeUser($userID)
	{

		if($this->getSessionValid())
		{
			$this->DBM->startTransaction();
			$UM = core_auth_AuthManager::getInstance();
			$result = $UM->deleteUserByID($userID);
			$this->DBM->commit();
		}
		else
		{
			$result = core_util_Error::getError(1);
		}
		return $result;
	}

	/**
	 * Gets the formatted name of a user given an id
	 * @param $userID (number) User id
	 * @return (User) User object
	 * @return (bool) False if error or no login
	 */
	public function getUserName($userID)
	{
		if($this->getSessionValid())
		{
			$UM = core_auth_AuthManager::getInstance();
			$result = $UM->getName($userID);
		}
		else
		{
			$result = core_util_Error::getError(1);
		}
		return $result;
	}
	
	public function getUserNames($userIDs)
	{
		if($this->getSessionValid())
		{
			$UM = core_auth_AuthManager::getInstance();
			$result = array();
			$curObj;
			foreach($userIDs as $userID)
			{
				$result[] = array(
					'userID' => $userID,
					'userName' => $UM->getNameObject($userID)
				);
			}
		}
		else
		{
			$result =  core_util_Error::getError(1);
		}
		
		return $result;
	}
	
	/**
	 * Gets a list of all the users
	 * @return (Array<User>) array of user objects
	 * @return (bool) False if error or no login
	 */
	// TODO: this may have to change, user list may be too long, 
	//or actually unknown to us because of external authentication systems
	
	public function getUsers()
	{
		if($this->getSessionValid())
		{
			$UM = core_auth_AuthManager::getInstance();
			$result = $UM->getAllUsers();
		}
		else
		{
			$result = core_util_Error::getError(1);
		}
		return $result;	
	}
	
	/**** LO Functions ****/
	/**
	 * Get the entire LO
	 * @param $loID (number) learning object id
	 * @return (LO) learning object
	 * @return (bool) False if error or no login
	 */
	public function getLO($loID, $newest=false)
	{

		// TODO: move validation
	    if(!nm_los_Validator::isPosInt($loID))
		{
			return core_util_Error::getError(2);
		}   

		if($this->getSessionValid())
		{
			$this->DBM->startTransaction();
			$loman = nm_los_LOManager::getInstance();
			// if newest is true, get the newest draft that is related to the passed id
			$loObj = ($newest === true ? $loman->getLatestDraftByLOID($loID) /*newest*/ : $loman->getLO($loID, 'full') /*exact match*/);
			$this->DBM->commit();
		}
		else
		{
			$loObj = core_util_Error::getError(1);
		}
		return $loObj;
	}
	
	
	
	/**
	 * Gets a list of all drafts for a given root id
	 * @param $rootid (number) root learning object id
	 * @return (Array<LO>) an array of minimum learning objects
	 * @return (bool) False if error or no login
	 */
	// TODO: this function should take an LOID instead of a ROOTID
	public function getDraftsOfLO($rootid)
	{
		if($this->getSessionValid()){
			$loman = nm_los_LOManager::getInstance();
			$loArr = $loman->getDrafts($rootid, 'min');
		}
		else
		{
			$loArr = core_util_Error::getError(1);
		}
		return $loArr;
	}
	
	
	/**
	 * Gets only the metadata for the LO (for listing)
	 * @param $loID (number) learning object id
	 * @return (LO) metadata of learning object
	 * @return (bool) False if error or no login
	 */
	public function getLOMeta($loID, $newest=false)
	{

		//TODO: make sure this is secure as possible, it will be open to the public w/o 
		//authentication so it must be safe and as light on processes as possible
		$loMan = nm_los_LOManager::getInstance();
		if($newest === true)
		{
		    $result = $loMan->getLatestDraftByLOID($loID, 'meta');
		}
		else
		{
    		$result = $loMan->getLO($loID, 'meta');

		}
		return $result;
	}
	
	public function getDrafts()
	{
		if($this->getSessionValid())
		{
			$loMan = nm_los_LOManager::getInstance();
			$result = $loMan->getMyDrafts();
		}
		else
		{
			$result = core_util_Error::getError(1);
		}
		return $result;
	}
	
	/**
	 * Returns both drafts and masters.
	 * @author Zachary Berry
	 */
	public function getLOs($optLoIDArray=false)
	{
		if($this->getSessionValid())
		{
			$loMan = nm_los_LOManager::getInstance();
			if( is_array($optLoIDArray) )
			{
				$result = $loMan->getLO($optLoIDArray);
			}
			else
			{
				$result = $loMan->getMyObjects();
			}
		}
		else
		{
			$result = core_util_Error::getError(1);
		}
		return $result;
	}
	
    public function getLibraryLOs()
    {
		
		if($this->getSessionValid())
		{
			$loMan = nm_los_LOManager::getInstance();
			$result = $loMan->getPublicMasters();
		}
		else
		{
			$result = core_util_Error::getError(1);
		}
		return $result;
    }
	
	/**
	 * Saves a new draft (even if the learning object is a new root)  and returns the new id number
	 * @param $loObj (LO) new learning object
	 * @return (LO) learning object (including new id)
	 * @return (bool) False if error or no login
	 * TODO: validate the LO lol
	 */
	public function createDraft($loObj)
	{

		if($this->getSessionValid())
		{
			$this->DBM->startTransaction();
			$loman = nm_los_LOManager::getInstance();
			$loObj = $loman->newDraft($loObj);	
			$this->DBM->commit();
		}
		else
		{
			$loObj = core_util_Error::getError(1);
		}
		return $loObj;
	}
	
	/**
	 * Makes the draft into the final LO, and removes all drafts previous to it
	 * @param $loID (number) learning object id
	 */
	public function createMaster($loID)
	{
		if($this->getSessionValid())
		{
			$this->DBM->startTransaction();
			$loman = nm_los_LOManager::getInstance();
			$result = $loman->createMaster($loID);
			$this->DBM->commit();
		}
		else
		{
			$result = core_util_Error::getError(1);
		}
		return $result;
	}
	
	public function createDerivative($loID)
	{
		if($this->getSessionValid())
		{
			$this->DBM->startTransaction();
			$loman = nm_los_LOManager::getInstance();
			$result = $loman->createDerivative($loID);
			$this->DBM->commit();
		}
		else
		{
			$result = core_util_Error::getError(1);
		}
		return $result;
	}
	
    public function removeLibraryLO($loID)
	{

		if($this->getSessionValid())
		{
			$this->DBM->startTransaction();
			$loman = nm_los_LOManager::getInstance();
			$result = $loman->removeFromLibrary($loID);
			$this->DBM->commit();
		}
		else
		{
			$result = core_util_Error::getError(1);
		}
		return $result;
	}

	/**
	 * Deletes an entire LO history.  All previous versions.. everything.
	 * @param $rootid (number) root learning object id
	 */
	public function removeLO($loID)
    {
		if($this->getSessionValid())
		{
			$this->DBM->startTransaction();
			$loman = nm_los_LOManager::getInstance();
			$result = $loman->delTree($loID);
			$this->DBM->commit();
		}
		else
		{
			$result = core_util_Error::getError(1);
		}
		return $result;
	}

	/**
	 * Locks the LO
	 * @param $loID (number) learning object id
	 */
	public function createLOLock($loID)
	{
		if($this->getSessionValid())
		{
			$this->DBM->startTransaction();
			$lockMan = nm_los_LockManager::getInstance();
			$loObj = $lockMan->lockLO($loID);
			$this->DBM->commit();
		}
		else
		{
			$loObj = core_util_Error::getError(1);
		}
		return $loObj;
	}
	
	/**
	 * Unlocks the LO
	 * @param $loID (number) learning object id
	 */
	public function removeLOLock($loID)
	{
		
		if($this->getSessionValid())
		{
			$this->DBM->startTransaction();
			$lockMan = nm_los_LockManager::getInstance();
			$result = $lockMan->unlockLO($loID);
			$this->DBM->commit();
		}
		else
		{
			$result = core_util_Error::getError(1);
		}
		return $result;
	}
	
	// TODO: this should get all instances of an LO with permissions showing ownership optional param to only return the current user's instances
	public function getInstancesOfLO($loID)
	{		
		if($this->getSessionValid())
		{
			$instMan = nm_los_InstanceManager::getInstance();
			$result = $instMan->getInstancesFromLOID($loID);
		}
		else
		{
			$result = core_util_Error::getError(1);
		}
		return $result;
	}

	/**** Instance Functions ****/
	/**
	 * Creates a new instance of a learning object
	 * @param $instArr (Array) Array of information about the new instance
	 * @return (number) new instance id
	 * @return (bool) False if error or no login
	 */
	public function createInstance($name, $loID, $course, $startTime, $endTime, $attemptCount, $scoreMethod = 'h', $allowScoreImport = true)
	{
		if($this->getSessionValid()){
			$this->DBM->startTransaction();
			$instman = nm_los_InstanceManager::getInstance();
			$result = $instman->newInstance($name, $loID, $course, $startTime, $endTime, $attemptCount, $scoreMethod, $allowScoreImport);
			$this->DBM->commit();
		}
		else
		{
			$result = core_util_Error::getError(1);
		}
		return $result;
	}
	
	/**
	 * Retrieves an instance from the database
	 * @param $instID (number) ID of instance to retrieve
	 * @return (LO) learning object
	 * @return (bool) False if error or no login
	 */
	public function createInstanceVisit($instID)
	{
		if($this->getSessionValid())
		{
			
			$instman = nm_los_InstanceManager::getInstance();
			$result = $instman->createInstanceVisit($instID);
		}
		else
		{
			$result = core_util_Error::getError(1);
		}
		return $result;
	}

	public function getInstanceData($instID)
	{
		
		$instman = nm_los_InstanceManager::getInstance();
		// return 
		return $instman->getInstanceData($instID);

	}

	/**
	 * Gets a list of all instances this user has write access to
	 * @return (Array<Instance>) array of instance objects
	 * @return (bool) False if error or no login
	 */
	public function getInstances()
	{
		
		if($this->getSessionValid())
		{
			$instman = nm_los_InstanceManager::getInstance();
			$result = $instman->getAllInstances();
		}
		else
		{
			$result = core_util_Error::getError(1);
		}
		return $result;
	}
	
	/**
	 * Updates an instance of a learning object
	 * @param $instArr (Array) Array of information about the instance
	 * @param (Array) The instance Array
	 */
	public function editInstance($name, $instID, $course, $startTime, $endTime, $attemptCount, $scoreMethod, $allowScoreImport)
	{
		if($this->getSessionValid())
		{
			$this->DBM->startTransaction();
			$instman = nm_los_InstanceManager::getInstance();
			$result = $instman->updateInstance($name, $instID, $course, $startTime, $endTime, $attemptCount, $scoreMethod, $allowScoreImport);
			$this->DBM->commit();
		}
		else
		{
			$result = core_util_Error::getError(1);
		}
		return $result;
	}

	public function removeInstance($instID)
	{
		if($this->getSessionValid())
		{
			$this->DBM->startTransaction();
			$instMan = nm_los_InstanceManager::getInstance();
			$result = $instMan->deleteInstance($instID);
			$this->DBM->commit();
		}
		else
		{
			$result = core_util_Error::getError(1);
		}
		return $result;
	}
	

	/**
	 * Gets list of all Media that are globally viewable or user has rights to view it
	 * @return (Array<Media>) an array of minimum media objects
	 * @return (bool) False if error or no login
	 */
	public function getMedia($optMediaIDArray=false)
	{
		
		if($this->getSessionValid())
		{
			$mediaMan = nm_los_MediaManager::getInstance();
			$result = $mediaMan->getAllMedia($optMediaIDArray);
		}
		else
		{
			$result = core_util_Error::getError(1);
		}
		return $result;
	}
	
	/**
	 * Alters an existing media object in the database
	 * @param $mediaObj (Media) media object
	 * @return (Media) full media object
	 * @return (bool) False if error or no login
	 * TODO: validate media object AND maybe prevent new objects from being made here? (id = 0)
	 */
	public function editMedia($mediaObj, $visitKey=-1)
	{
	    if(!nm_los_Validator::isPosInt($mediaObj['mediaID']))
		{
			// TODO: this is wrong
			return false;
	    }
		if($this->getSessionValid())
		{
			// TODO:
			// require the visitKey if not a content creator
			if($visitKey != -1)
			{
				$VM = nm_los_VisitManager::getInstance();
				if(!$VM->registerCurrentViewKey($visitKey))
				{
					return core_util_Error::getError(5);
				}
			}			
			$this->DBM->startTransaction();
			$mediaMan = nm_los_MediaManager::getInstance();
			$result = $mediaMan->saveMedia(new nm_los_Media($mediaObj));
			$this->DBM->commit();
		}
		else
		{
			$result = core_util_Error::getError(1);
		}
		return $result;
	}
	
	/**
	 * Deletes an existing media object from the database
	 * @param $mid (number) media ID
	 * @return (bool) True if delete was successful, False if error or no login
	 */
	public function removeMedia($mid)
	{
	    if(!nm_los_Validator::isPosInt($mid))
		{
	        return false;
	    }   
		
		
		if($this->getSessionValid())
		{
			$this->DBM->startTransaction();
			$mediaMan = nm_los_MediaManager::getInstance();
			$result = $mediaMan->deleteMedia($mid);
			$this->DBM->commit();
		}
		else
		{
			$result = core_util_Error::getError(1);
		}
		return $result;
	}
	
	/**** Permission Functions ****/
	/** 
	 * Sets permissions for all users for an item
	 * @param $itemID (number) item ID to set permissions for
	 * @param $item_type (string) l = learning object, m = media(future), q = question(future)
	 * @param $permObj (Permissions) The new global permissions for the object
	 * @return (bool) True if change occurred, False if error or no login
	 */
	public function createLibraryLO($loID, $allowDerivative)
	{

		if($this->getSessionValid())
		{
			$lom = nm_los_LOManager::getInstance();
			$result = $lom->addToLibrary($loID, $allowDerivative);
		}
		else
		{
			$result = core_util_Error::getError(1);
		}
		return $result;
	}
	
	/*
	
	!!! example JSON call to add 1,2,3,4,5,6 perms and remove 1,2,3,4,5 perms for instancID 500
	[{"userID":1,"perm":"1"},{"userID":1,"perm":"2"},{"userID":1,"perm":"3"},{"userID":1,"perm":"4"},{"userID":1,"perm":"5"},{"userID":1,"perm":"6"}]
	500
	1
	[{"userID":1,"perm":"1"},{"userID":1,"perm":"2"},{"userID":1,"perm":"3"},{"userID":1,"perm":"4"},{"userID":1,"perm":"5"}]
	
	
	*/
	public function editUsersPerms($permObjects, $itemID = 0, $itemType = 'l', $removePerms = 0)
	{		
	    if(!nm_los_Validator::isPosInt($itemID))
		{
			return core_util_Error::getError(2);
	    }   
		
		if($this->getSessionValid())
		{
			// Switch used temporarily to allow us to use 2 permission systems
			switch($itemType)
			{
				case cfg_core_Perm::TYPE_INSTANCE:
					$PMan = nm_los_PermManager::getInstance();
					// add perms
					if(is_array($permObjects) && count($permObjects) > 0 )
					{
						foreach($permObjects AS $value)
						{
							$result = $PMan->setPermsForUserToItem($value['userID'], cfg_core_Perm::TYPE_INSTANCE, $itemID, $value['perm'], array() );
						}
					}
					// remove perms
					if(is_array($removePerms) && count($removePerms) > 0)
					{
						foreach($removePerms as $value)
						{
							$result = $PMan->setPermsForUserToItem($value['userID'], cfg_core_Perm::TYPE_INSTANCE, $itemID, array(), $value['perm'] );
						}
					} 
					
					break;
				default:
					if(!nm_los_Validator::isItemType($itemType))
					{
						return core_util_Error::getError(2);
				    }
					foreach($permObjects as $permObj)
					{
						if(!nm_los_Validator::isPermObj($permObj))
						{
							return core_util_Error::getError(2);
						}
					}
					$this->DBM->startTransaction();
					$permMan = nm_los_PermissionsManager::getInstance();
					$result = $permMan->setUsersPerms($permObjects, $itemID, $itemType, new nm_los_Permissions($permObj));
					if($result)
					{
						$this->DBM->commit();
					}
					else
					{
						$this->DBM->rollback();
					}
					break;
			}
		}
		else
		{
			$result = core_util_Error::getError(1);
		}
		return $result;
	}
	
	public function removeUsersPerms($users, $itemID, $itemType)
	{
	    if(!nm_los_Validator::isUserArray($users) || !nm_los_Validator::isPosInt($itemID) || !nm_los_Validator::isItemType($itemType))
		{
			return core_util_Error::getError(2);
		}
		if($this->getSessionValid())
		{
			$this->DBM->startTransaction();
			$permman = nm_los_PermissionsManager::getInstance();
			$result = $permman->removeUsersPerms($users, $itemID, $itemType);
			if($result)
			{
				$this->DBM->commit();
			}
			else
			{
				$this->DBM->rollback();
			}
		}
		else
		{
			$result = core_util_Error::getError(1);
		}
		
		return $result;	    
	}
	
	/**
	 * Enter description here...
	 *
	 * @param Number $itemID
	 * @param String $itemType
	 * @return bool if error
	 */
	public function getItemPerms($itemID = 0, $itemType = 'l')
	{
	    if(!nm_los_Validator::isPosInt($itemID))
		{
			return core_util_Error::getError(2);
	    }    
		
		if($this->getSessionValid())
		{
			
			switch($itemType)
			{
				case cfg_core_Perm::TYPE_INSTANCE:
					$PMan = nm_los_PermManager::getInstance();
					$result = $PMan->getAllUsersIDsForItem(cfg_core_Perm::TYPE_INSTANCE, $itemID);
					
					break;
				default:
					if(!nm_los_Validator::isItemType($itemType))
					{
						return core_util_Error::getError(2);
				    }
					$permman = nm_los_PermissionsManager::getInstance();
					$result = $permman->getPermsForItem($itemID, $itemType);
					break;
			}
		}
		else
		{
			$result = core_util_Error::getError(1);
		}
		return $result;
	}
	

	/****  Quiz Functions ****/
	/**
	 * Starts a new attempt if there are no unfinished attempts for the qGroupID
	 * If an unfinshed attempt is found it return the quiz state array with past answered questions
	 * 
	 * @param $qGroupID (number) question group id
	 * @return (bool) false if error
	 * @return (bool) true if new attempt was created
	 * @return (Array) quizstate array
	 */
	public function trackAttemptStart($visitKey, $qGroupID)
	{
		if(nm_los_Validator::isPosInt($qGroupID))
		{
			
			if($this->getSessionValid())
			{
				$VM = nm_los_VisitManager::getInstance();
				if(!$VM->registerCurrentViewKey($visitKey))
				{
					return core_util_Error::getError(5);
				}
				$this->DBM->startTransaction();
				$attemptMan = nm_los_AttemptsManager::getInstance();
				$ret = $attemptMan->startAttempt($qGroupID);
				$this->DBM->commit();
				return $ret;
			}
			else
			{
				return core_util_Error::getError(1);
			}
			
		}
		return core_util_Error::getError(2);
	}
	
	/**
	 * Submits a question for grading (if the user has an open instance)
	 * @param $qGroupID (number) question group id
	 * @param $questionID (number) question id
	 * @param $answer (string) submitted answer text (from user)
	 * @return (Array) array with elements 'answerID', 'weight', and 'feedback'
	 * @return (bool) False if error or no login
	 */
	public function trackSubmitQuestion($visitKey, $qGroupID, $questionID, $answer)
	{
		// register visitKey first
		if(nm_los_Validator::isPosInt($qGroupID) && !empty($questionID))
		{
			
			if($this->getSessionValid())
			{
				$VM = nm_los_VisitManager::getInstance();
				if(!$VM->registerCurrentViewKey($visitKey))
				{
					return core_util_Error::getError(5);
				}
				$this->DBM->startTransaction();
				$scoreman = nm_los_ScoreManager::getInstance();
				$result = $scoreman->submitQuestion($qGroupID, $questionID, $answer);
				$this->DBM->commit();
				return $result;
			}
			else
			{
				return core_util_Error::getError(1);
			}
			
		}
		return core_util_Error::getError(2);
		
	}
	
	/**
	 * Submits a media score (if the user has an open instance)
	 * @param $qGroupID (number) question group id
	 * @param $mid (number) media id
	 * @param $score (number) submitted score (from user)
	 * 
	 * @todo fix what it should it return
	 * @return (Array) array with elements 'answerID', 'weight', and 'feedback'
	 * @return (bool) False if error or no login
	 */
	public function trackSubmitMedia($visitKey, $qGroupID, $questionID, $score)
	{
		// register visitKey first

		if(nm_los_Validator::isPosInt($qGroupID) && nm_los_Validator::isPosInt($questionID) && nm_los_Validator::isScore($score))
		{	
			
			if($this->getSessionValid())
			{
				$VM = nm_los_VisitManager::getInstance();
				if(!$VM->registerCurrentViewKey($visitKey))
				{
					return core_util_Error::getError(5);
				}
				$this->DBM->startTransaction();
				$scoreman = nm_los_ScoreManager::getInstance();
				$result = $scoreman->submitQuestion($qGroupID, $questionID, $score);
				$this->DBM->commit();
				return $result;
			}
			else
			{
				return core_util_Error::getError(1);
			}
			
		}
		return core_util_Error::getError(2);
	}
	
	/**
	 * This function end the attempt with the specified qGroupID.
	 * 
	 * @param $qGroupID (Number) question group id
	 * @return (bool) false if error
	 * @return (Number) the score of the submitted quiz
	 */
	public function trackAttemptEnd($visitKey, $qGroupID)
	{
		// register visitKey first

		if(nm_los_Validator::isPosInt($qGroupID))
		{
			
			if($this->getSessionValid())
			{
				$VM = nm_los_VisitManager::getInstance();
				if(!$VM->registerCurrentViewKey($visitKey))
				{
					return core_util_Error::getError(5);
				}				
				$this->DBM->startTransaction();
				$attemptMan = nm_los_AttemptsManager::getInstance();
				$result = $attemptMan->endAttempt($qGroupID);
				$this->DBM->commit();
			}
			else
			{
				$result = core_util_Error::getError(1);
			}
			return $result;
		}
		return core_util_Error::getError(2);
	}
	

	
/*
	
	 * Gets the final score for a certain question group
	 * @param $qGroupID (number) question group id
	 * @return (number) final score for the question group
	 * @return (bool) False if error or no login
	 
	public function getScore($qGroupID)
	{
		$this->DBM->startTransaction();
		
		if($this->getSessionValid())
		{
			$scoreman = nm_los_ScoreManager::getInstance();
			$result = $scoreman->getScore($qGroupID);
		}
		else
			$result = false;
		$this->DBM->commit();
		return $result;
	}
*/
	/**
	 * Gets a listing of all final scores for all users of a learning object instance (for faculty)
	 * @param $instid (number) instance id
	 * @return (Array<Array>) An array of final score entries, with fields 'id', 'qGroupID', 'score', 'userID', 'user_name'
	 * @return (bool) False if error or no login
	 */
	public function getScoresForInstance($instid)
	{
		if(nm_los_Validator::isPosInt($instid))
		{		
			if($this->getSessionValid())
			{
				$scoreman = nm_los_ScoreManager::getInstance();
				$result = $scoreman->getScores($instid);
			}
			else
			{
				$result = core_util_Error::getError(1);
			}
			return $result;
		}
		return core_util_Error::getError(2);
	}
	
	
	public function getVisitTrackingData($userID, $instid)
	{
		if(nm_los_Validator::isPosInt($instid) && nm_los_Validator::isPosInt($userID))
		{
			if($this->getSessionValid())
			{
				$TM = nm_los_TrackingManager::getInstance();
				return $TM->getInteractionLogByUserAndInstance($instid, $userID);
			}
			else
			{
				$result = core_util_Error::getError(1);
			}
		}
		return false;
	}
	
	public function getInstanceTrackingData($instID)
	{
		if(nm_los_Validator::isPosInt($instID) && nm_los_Validator::isPosInt($instID))
		{
			if($this->getSessionValid())
			{
				$TM = nm_los_TrackingManager::getInstance();
				return $TM->getInteractionLogByInstance($instID);
			}
			else
			{
				$result = core_util_Error::getError(1);
			}
				
		}
		return false;
		

	}
	
	/** @author Zachary Berry **/
	public function getQuestionResponses($instid, $questionid)
	{
		if(nm_los_Validator::isPosInt($instid) && nm_los_Validator::isPosInt($questionid))
		{
			
			if($this->getSessionValid())
			{
				$scoreman = nm_los_ScoreManager::getInstance();
				$result = $scoreman->getQuestionResponses($instid, $questionid);
			}
			else
			{
				$result = core_util_Error::getError(1);
			}
			return $result;
		}
		return core_util_Error::getError(2);
	}


	/********* Misc Functions *********/
	/**
	 * Gets all available languages
	 * @return (Array<Array>) Array of languages, containing 'id' and 'name' values
	 * @return (number) -1 if error or no login
	 */
	public function getLanguages()
	{	
		$langman = nm_los_LanguageManager::getInstance();
		$result = $langman->getAllLanguages();
		return $result;
	}

	public function getSession()
	{
		
		if($this->getSessionValid())
		{
			$UM = core_auth_AuthManager::getInstance();
			$result = $UM->getSessionID();
		}
		else
		{
			$result = core_util_Error::getError(1);
		}
		return $result;
	}
	
	/****  Roles Functions ****/
	public function getRoles()
	{
		if($this->getSessionValid())
		{
			$roleMan = nm_los_RoleManager::getInstance();
			$result = $roleMan->getAllRoles();
		}
		else
		{
			$result = core_util_Error::getError(1);
		}
		return $result;
	}
	
	public function getUserRoles($userID = 0)
	{
		trace($userID);
		if(nm_los_Validator::isPosInt($userID, true))
		{		
			
			if($this->getSessionValid())
			{
				$roleMan = nm_los_RoleManager::getInstance();
				$result = $roleMan->getUserRoles($userID);
			}
			else
			{
				$result = core_util_Error::getError(1);
			}
			return $result;
		}
		return core_util_Error::getError(2);
	}
	
	// TODO: this is quite similar to getUserInRole, either rename or redundent
	// Function accepts RoleID as a positive int, or a stringRoleName
	public function getUsersInRole($roleNames)
	{
		if($this->getSessionValid())
		{
			$roleMan = nm_los_RoleManager::getInstance();
			$roleIDs = $roleMan->getRoleIDsFromNames($roleNames);
			if($roleIDs == false || $roleID instanceof core_util_Error)
			{
				return false;
			}
			
			$result = $roleMan->getUsersInRole($roleIDs);
		}
		else
		{
			$result = core_util_Error::getError(1);
		}
		return $result;

	}

	public function createRole($roleName)
	{
		if(nm_los_Validator::isRoleName($roleName))
		{		
			
			if($this->getSessionValid())
			{
				$roleMan = nm_los_RoleManager::getInstance();
				$result = $roleMan->createRole($roleName);
			}
			else
			{
				$result = core_util_Error::getError(1);
			}
			return $result;
		}
		return core_util_Error::getError(2);
	}
	
	public function createExternalMediaLink($mediaObj)
	{
		if($this->getSessionValid())
		{
			$this->DBM->startTransaction();
			$mediaMan = nm_los_MediaManager::getInstance();
			$result = $mediaMan->newMedia(new nm_los_Media($mediaObj));
			$this->DBM->commit();
		}
		else
		{
			$result = core_util_Error::getError(1);
		}
		return $result;
	}
	
	public function uploadMedia($fileData, $filename, $title, $description, $copyright, $length=0)
	{
		if($this->getSessionValid())
		{
			trace('uploadMedia', true);
			$this->DBM->startTransaction();
			$mediaMan = nm_los_MediaManager::getInstance();
			$result = $mediaMan->handleMediaUpload2($fileData, $filename, $title, $description, $copyright, $length);
			$this->DBM->commit();
		}
		else
		{
			$result = core_util_Error::getError(1);
		}
		return $result;
	}
	
	public function removeRole($roleName)
	{
		if(nm_los_Validator::isRoleName($roleName))
		{		
			
			if($this->getSessionValid())
			{
				$roleMan = nm_los_RoleManager::getInstance();
				$result = $roleMan->deleteRole($roleName);
			}
			else
			{
				$result = core_util_Error::getError(1);
			}
			return $result;
		}
		return core_util_Error::getError(2);
	}
	
	public function removeUsersRoles($users, $roles)
	{
		if(nm_los_Validator::isUserArray($users) && nm_los_Validator::isRoleArray($roles))
		{
			
			if($this->getSessionValid())
			{
				$this->DBM->startTransaction();
				$roleMan = nm_los_RoleManager::getInstance();
				$result = $roleMan->removeUsersFromRoles($users, $roles);
				$this->DBM->commit();	
			}
			else
			{
				$result = core_util_Error::getError(1);
			}
			return $result;
		}
		return core_util_Error::getError(2);
	}
	
	/**
	 * @author Zachary Berry
	 */
	public function editUsersRoles($users, $roles)
	{
		if(nm_los_Validator::isUserArray($users) && nm_los_Validator::isRoleArray($roles))
		{		
			
			if($this->getSessionValid())
			{
				$this->DBM->startTransaction();
				$roleMan = nm_los_RoleManager::getInstance();
				$result = $roleMan->addUsersToRoles($users, $roles);
				$this->DBM->commit();
			}
			else
			{
				$result = core_util_Error::getError(1);
			}
			return $result;
		}
		return core_util_Error::getError(2);
	}
	
	/****	Tracking Functions ***/

	public function trackPageChanged($visitKey, $pageID, $section)
	{
		if(!empty($pageID) && nm_los_Validator::isSection($section))
		{
			
			if($this->getSessionValid())
			{
				$VM = nm_los_VisitManager::getInstance();
				if(!$VM->registerCurrentViewKey($visitKey))
				{
					return core_util_Error::getError(5);
				}
				
				if($VM->getCurrentViewKeyInstID() > 0)
				{
					$this->DBM->startTransaction();
					$trackingMan = nm_los_TrackingManager::getInstance();
					$result = $trackingMan->trackPageChanged($pageID, $section);
					$this->DBM->commit();
				}
				else
				{
					$result = core_util_Error::getError(4003);
				}
			}
			else
			{
				$result = core_util_Error::getError(1);
			}
			return $result;
		}
		return core_util_Error::getError(2);
	}

	public function trackSectionChanged($visitKey, $section)
	{
		if(nm_los_Validator::isSection($section) )	
		{
			
			if($this->getSessionValid())
			{
				$VM = nm_los_VisitManager::getInstance();
				if(!$VM->registerCurrentViewKey($visitKey))
				{
					return core_util_Error::getError(5);
				}
								
				if( $VM->getCurrentViewKeyInstID() > 0 )
				{
					$this->DBM->startTransaction();
					$trackingMan = nm_los_TrackingManager::getInstance();
					$result = $trackingMan->trackSectionChanged($section);
					$this->DBM->commit();
				}
				else
				{
					$result = core_util_Error::getError(4003);
				}
			}
			else
			{
				$result = core_util_Error::getError(1);
			}
			return $result;
		}
		return core_util_Error::getError(2);
	}

	
	// TODO: validation
	public function trackComputerData($data)
	{
		if($this->getSessionValid())
		{
			$this->DBM->startTransaction();
			$compDataMan = nm_los_ComputerDataManager::getInstance();
			$result = $compDataMan->addComputerData($data);
			$this->DBM->commit();
		}
		else
		{
			$result = core_util_Error::getError(1);
		}
		return $result;
	}
	

    public function trackVisitResume($visitKey, $instID)
    {
		// register visitKey first

		if(nm_los_Validator::isPosInt($instID) )
		{
			if($this->getSessionValid())
			{
				$VM = nm_los_VisitManager::getInstance();
				if(!$VM->registerCurrentViewKey($visitKey))
				{
					return core_util_Error::getError(5);
				}
				
				$this->DBM->startTransaction();
	            $visitMan = nm_los_VisitManager::getInstance();
	            $result = $visitMan->resumeVisit($instID);
				$this->DBM->commit();
			}
			else
			{
				$result = core_util_Error::getError(1);
			}
			return $result;
		}
		return core_util_Error::getError(2);
    }

	public function getPasswordReset($username, $email, $returnURL)
	{
		// needs to be exposed to non-logged in users
		if(nm_los_Validator::isString($username) && nm_los_Validator::isString($email) && nm_los_Validator::isString($returnURL) )
		{
			$UM = core_auth_AuthManager::getInstance();
			return $UM->requestPasswordReset($username, $email, $returnURL);
		}
		return core_util_Error::getError(2);
	}
	
	public function editPassword($oldPassword, $newPassword)
	{
		if(nm_los_Validator::isString($oldPassword) && nm_los_Validator::isString($newPassword) )
		{		
			// session wont verify, so can't do it here
			$AM = core_auth_AuthManager::getInstance();
			return $AM->changePassword($oldPassword, $newPassword);
		}
		return core_util_Error::getError(2);
	}
	
	public function editPasswordWithKey($username, $key, $newpass)
	{
		if(nm_los_Validator::isString($username) && nm_los_Validator::isSHA1($key) && nm_los_Validator::isString($newpass))
		{
			$UM = core_auth_AuthManager::getInstance();
			if($UM->changePasswordWithKey($username, $key, $newpass) === true)
			{
				//try to automatically log them in if the reset was successful
				if(!$UM->login($username, $newpass))
				{
					core_util_Error::getError(1007);
				}
				return true;
			}
			return false;
		}
		return core_util_Error::getError(2);
	}
	
	public function editExtraAttempts($userID, $instID, $count)
	{
		if(	nm_los_Validator::isPosInt($userID) &&
			nm_los_Validator::isPosInt($instID) &&
			nm_los_Validator::isInt($count)
		)
		{
			if($this->getSessionValid())
			{
				$this->DBM->startTransaction();
				$attemptMan = nm_los_AttemptsManager::getInstance();
				$result = $attemptMan->setAdditionalAttempts($userID, $instID, $count);
				$this->DBM->commit();
			}
			else
			{
				$result = core_util_Error::getError(1);
			}
		}
		else
		{
			$result = core_util_Error::getError(2);
		}
		
		return $result;
	}
	
	public function removeExtraAttempts($userID, $instID)
	{
		if(nm_los_Validator::isPosInt($userID) && nm_los_Validator::isPosInt($instID))
		{
			if($this->getSessionValid())
			{
				$this->DBM->startTransaction();
				$attemptMan = nm_los_AttemptsManager::getInstance();
				$result = $attemptMan->removeAdditionalAttempts($userID, $instID, $count);
				$this->DBM->commit();
			}
			else
			{
				$result = core_util_Error::getError(1);
			}
		}
		else
		{
			$result = core_util_Error::getError(2);
		}
		
		return $result;
	}
	
	/* @author: Zachary Berry */
	public function trackClientError($client, $message, $data)
	{
		if($this->getSessionValid())
		{
			if(nm_los_Validator::isClientType($client) && nm_los_Validator::isString($message) && nm_los_Validator::isString($data))
			{
				$clientError = new stdClass();
				$clientError->client = $client;
				$clientError->message = $message;
				$clientError->data = $data;
				$result = core_util_Error::getError(101, $message, $clientError);
				return true;
			}
			else
			{
				$result = core_util_Error::getError(2);
			}
		}
		else
		{
			$result = core_util_Error::getError(1);
		}
		
		return $result;

	}
	
	public function getLOsWithMedia($mid)
	{
		if($this->getSessionValid())
		{
			$mediaMan = nm_los_MediaManager::getInstance();
			$loMan = nm_los_LOManager::getInstance();
			$result = $mediaMan->locateLOsWithMedia($mid);
			if( !($result instanceof core_util_Error) )
			{
				$result = $loMan->getLO($result);
			}
		}
		else
		{
			$result = core_util_Error::getError(1);
		}
		return $result;
	}
	
	public function getLoginOptions()
	{
		$options = array();
		$options[] = array(
			'title' => 'UCF Login',
			'thumb' => 'assets/shared/images/login_UCF_icon.png',
			'desc' => 'Log in using your NID or Guest Pass.',
			'userNameLabel' => 'NID/Username',
			'userNameHelp' => 'Your NID, or Network ID, is typically composed of the first two letters of your first name and a random six-digit number. If your first name has only one character, the character is duplicated to create the two letters for your NID. If you work at UCF, your NID may be different from the default format. <u><a href="https://my.ucf.edu/static_support/pidnidwrapper.html" target="_blank">Get your NID</a></u>.',
			'passwordLabel' => 'Password',
			'passwordHelp' => 'Your NID password is shared for several systems on campus including the Main Computer Labs, Webcourses@UCF, Obojobo, and several others.  Visit the central reset form to <u><a href="https://www.secure.net.ucf.edu/extranet/reset/validation.aspx?type=nid" target="_blank">change your password</a></u>.   If you are a guests without a UCF ID Card will need to call the Service Desk (407-823-5117) to reset your password.',
			'overviewHelp' => 'Overview help here!',
			'md5Password' => false,
			'canActivate' => false,
			'activateAction' => '',
			'canReset' => true,
			'resetAction' => 'https://www.secure.net.ucf.edu/extranet/reset/validation.aspx?type=nid',
			'resetRequestDialog' => "Use this form if your username starts with a '~' (tilde).\n\nWe'll send you an email with further details.",
			'resetRequestHelp' => 'You must know your username and email address that we have on record. Use this form only if your username starts with a tilde (example: ~ucf123). <br><br>Once submitted, you will receive an email containing instructions about completing the reset process.',
			'priority' => 1
 		);
		return $options;
	}
	
	public function doMergeUsers($userIDFrom, $userIDTo)
	{
		if($this->getSessionValid())
		{
			$LOS = new nm_los_LOSystem();
			$result =  $LOS->mergeUsers($userIDFrom, $userIDTo);
		}
		else
		{
			$result = core_util_Error::getError(1);
		}
		return $result;
	}
	
	public function doImportEquivalentAttempt($visitKey)
	{
		if($this->getSessionValid())
		{
			$AM = new nm_los_AttemptsManager();
			$result =  $AM->useEquivalentAttempt($visitKey);
		}
		else
		{
			$result = core_util_Error::getError(1);
		}
		return $result;
	}

}
?>