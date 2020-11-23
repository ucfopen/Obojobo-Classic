<?php
namespace obo;

class API extends \rocketD\db\DBEnabled
{
	use \rocketD\Singleton;

	public function __construct($isRemoting = false)
	{
		parent::__construct();
	}

	/**
	 * Verifies that the user has a current session and generates a new SESSID for them
	 * @return (bool) true if user is logged in, false if not
	 */
	public function getSessionValid($roleName='')
	{
		$UM = \rocketD\auth\AuthManager::getInstance();
		//trace($UM->verifySession($roleName));
		return $UM->verifySession($roleName);
	}

	/**
	 * Verifies session and role with a more granular return then verifySession
	 * @param	(args - either one array or multiple strings)	Role names to check for current session
	 * @return 	(array)	array with the following keys: validSession (bool, user currently has a valid session), roleName (string, name of role checked), hasRole (bool, user is in the role returned in roleName).
	 */
	public function getSessionRoleValid()
	{
		// role names come from the arguments.
		// this function either takes in an array of strings as one argument
		// or multiple strings in multiple arguments (to support Flex and JSON gateways)
		$roleNames = func_get_args();
		if(count($roleNames) > 0 && is_array($roleNames[0]))
		{
			$roleNames = $roleNames[0];
		}

		if(count($roleNames) == 0)
		{
			return \rocketD\util\Error::getError(2);
		}

		$AM = \rocketD\auth\AuthManager::getInstance();
		$return = array();
		$return['validSession'] = $AM->verifySession();
		$return['roleNames'] = $roleNames;
		$return['hasRoles'] = array();
		if($return['validSession'] === true && $roleNames != '')
		{
			$roleMan = \obo\perms\RoleManager::getInstance();
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

	public function doPluginCall($plugin, $method, $args = -1)
	{
		$PM = \rocketD\plugin\PluginManager::getInstance();
		return $PM->callAPI($plugin, $method, $args, false); // call the plugin method, but restrict it to whitelisted functions
	}

	/**
	 * Logs out the current active user
	 */
	public function doLogout()
	{
		if($this->getSessionValid())
		{
			$UM = \rocketD\auth\AuthManager::getInstance();
			$UM->logout($_SESSION['userID']);
		}
	}

	/**
	 * Gets information about the current user
	 * $return (User) User object
	 * @return (bool) False if error or no login
	 */
	public function getUser($username = false )
	{
		if ( ! $this->getSessionValid())
		{
			return \rocketD\util\Error::getError(1);
		}

		$UM = \rocketD\auth\AuthManager::getInstance();

		if($username === false)
		{
			$result = $UM->fetchUserByID($_SESSION['userID']);
		}
		else
		{
			if(\obo\perms\RoleManager::getInstance()->isAdministrator())
			{
				$result = $UM->fetchUserByUserName($username);
			}

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
			$UM = \rocketD\auth\AuthManager::getInstance();
			$result = $UM->getName($userID);
		}
		else
		{
			$result = \rocketD\util\Error::getError(1);
		}
		return $result;
	}

	public function getUserNames($userIDs)
	{
		if(is_string($userIDs)) {
			$userIDs = explode(',', $userIDs);
		}

		if($this->getSessionValid())
		{
			$UM = \rocketD\auth\AuthManager::getInstance();
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
			$result =  \rocketD\util\Error::getError(1);
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
			$UM = \rocketD\auth\AuthManager::getInstance();
			$result = $UM->getAllUsers();
		}
		else
		{
			$result = \rocketD\util\Error::getError(1);
		}
		return $result;
	}

	public function getUserCMSData($username)
	{
		if($this->getSessionValid())
		{
			$RM = \obo\perms\RoleManager::getInstance();
			if($RM->isAdministrator())
			{
				$cmsDBM = \rocketD\db\DBManager::getConnection(new \rocketD\db\DBConnectData(\AppCfg::DB_MODX_HOST,\AppCfg::DB_MODX_USER,\AppCfg::DB_MODX_PASS,\AppCfg::DB_MODX_NAME,\AppCfg::DB_MODX_TYPE));
				$qstr = "SELECT * FROM modx_web_users AS U JOIN modx_web_user_attributes AS A ON U.id = A.internalKey  WHERE U.username = '?'";
				$q = $cmsDBM->querySafe($qstr, $username);
				$exists = false;
				$blocked = false;
				$loginCount = 0;
				$failedAttempts = false;
				$lastLogin = 0;
				if($r = $cmsDBM->fetch_assoc($q))
				{
					$exists = true;
					$blocked = $r['blocked'];
					$failedAttempts = $r['failedlogincount'];
					$loginCount = $r['logincount'];
					$lastLogin = $r['lastlogin'];
				}
				$result = array('exists' => $exists, 'blocked' => $blocked, 'failedAttempts' => $failedAttempts, 'loginCount' => $loginCount, 'lastLogin' => $lastLogin);
			}
		}
		else
		{
			$result = \rocketD\util\Error::getError(1);
		}
		return $result;
	}

	public function getUserInteractionLogs($userID)
	{
		if($this->getSessionValid())
		{
			$RM = \obo\perms\RoleManager::getInstance();
			if($RM->isAdministrator())
			{
				$TM = new \obo\log\LogManager();
				$result = $TM->getInteractionLogByUser($userID);
			}
			else
			{
				$result = \rocketD\util\Error::getError(1);
			}
			return $result;
		}
	}

	public function getUsersMatchingUsername($searchString)
	{
		if($this->getSessionValid())
		{
			$UM = \rocketD\auth\AuthManager::getInstance();
			$result = $UM->getUsersMatchingUsername($searchString);
		}
		else
		{
			$result = \rocketD\util\Error::getError(1);
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
		if(!\obo\util\Validator::isPosInt($loID))
		{
			return \rocketD\util\Error::getError(2);
		}

		if($this->getSessionValid())
		{
			$hasRole = $this->getSessionRoleValid(array(\cfg_obo_Role::CONTENT_CREATOR, \cfg_obo_Role::LIBRARY_USER));
			if(in_array(\cfg_obo_Role::LIBRARY_USER, $hasRole['hasRoles']) || in_array(\cfg_obo_Role::CONTENT_CREATOR, $hasRole['hasRoles']))
			{
				$this->DBM->startTransaction();
				$loman = \obo\lo\LOManager::getInstance();
				// if newest is true, get the newest draft that is related to the passed id
				$loObj = ($newest === true ? $loman->getLatestDraftByLOID($loID) /*newest*/ : $loman->getLO($loID, 'full') /*exact match*/);
				$this->DBM->commit();
			}
			else
			{
				$loObj = \obo\util\Error::getError(4);
			}
		}
		else
		{
			$loObj = \rocketD\util\Error::getError(1);
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
		if($this->getSessionValid())
		{
			$loman = \obo\lo\LOManager::getInstance();
			$loArr = $loman->getDrafts($rootid, 'min');
		}
		else
		{
			$loArr = \rocketD\util\Error::getError(1);
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
		$loMan = \obo\lo\LOManager::getInstance();
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
			$loMan = \obo\lo\LOManager::getInstance();
			$result = $loMan->getMyDrafts();
		}
		else
		{
			$result = \rocketD\util\Error::getError(1);
		}
		return $result;
	}

	/**
	 * Returns both drafts and masters.
	 * @author Zachary Berry
	 */
	public function getLOs($optLoIDArray=false, $getLOsForStats=false)
	{
		if($this->getSessionValid())
		{
			$loMan = \obo\lo\LOManager::getInstance();
			if(is_array($optLoIDArray))
			{
				$result = $loMan->getLO($optLoIDArray);
			}
			else if($getLOsForStats)
			{
				$AN = \obo\util\Analytics::getInstance();
				$result = $AN->getMyStatMasters();
			}
			else
			{
				$result = $loMan->getMyObjects();
			}
		}
		else
		{
			$result = \rocketD\util\Error::getError(1);
		}
		return $result;
	}

	public function getLibraryLOs()
	{
		if($this->getSessionValid())
		{
			$loMan = \obo\lo\LOManager::getInstance();
			$result = $loMan->getPublicMasters();
		}
		else
		{
			$result = \rocketD\util\Error::getError(1);
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
			$loman = \obo\lo\LOManager::getInstance();
			$loObj = $loman->newDraft($loObj);
			$this->DBM->commit();
		}
		else
		{
			$loObj = \rocketD\util\Error::getError(1);
		}
		return $loObj;
	}

	/**
	 * Makes the draft into the final LO, and removes all drafts previous to it
	 * @param $loID (number) learning object id
	 * @return (int) LOID if successful
	 * @return (bool) false on failure
	 * @return (error) on error
	 */
	public function createMaster($loID)
	{
		if($this->getSessionValid())
		{
			$this->DBM->startTransaction();
			$loman = \obo\lo\LOManager::getInstance();
			$result = $loman->createMaster($loID);
			$this->DBM->commit();
		}
		else
		{
			$result = \rocketD\util\Error::getError(1);
		}
		return $result;
	}

	//@TODO: Change the name of this to 'createCopy' or 'createDuplicate'
	public function createDerivative($loID)
	{
		if($this->getSessionValid())
		{
			$this->DBM->startTransaction();
			$loman = \obo\lo\LOManager::getInstance();
			$result = $loman->createDerivative($loID);
			$this->DBM->commit();
		}
		else
		{
			$result = \rocketD\util\Error::getError(1);
		}
		return $result;
	}

	public function removeLibraryLO($loID)
	{

		if($this->getSessionValid())
		{
			$this->DBM->startTransaction();
			$loman = \obo\lo\LOManager::getInstance();
			$result = $loman->removeFromLibrary($loID);
			$this->DBM->commit();
		}
		else
		{
			$result = \rocketD\util\Error::getError(1);
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
			$loman = \obo\lo\LOManager::getInstance();
			$result = $loman->deleteLO($loID);
			$this->DBM->commit();
		}
		else
		{
			$result = \rocketD\util\Error::getError(1);
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
			$lockMan = \obo\LockManager::getInstance();
			$loObj = $lockMan->lockLO($loID);
			$this->DBM->commit();
		}
		else
		{
			$loObj = \rocketD\util\Error::getError(1);
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
			$lockMan = \obo\LockManager::getInstance();
			$result = $lockMan->unlockLO($loID);
			$this->DBM->commit();
		}
		else
		{
			$result = \rocketD\util\Error::getError(1);
		}
		return $result;
	}

	// TODO: this should get all instances of an LO with permissions showing ownership optional param to only return the current user's instances
	public function getInstancesOfLO($loID)
	{
		if($this->getSessionValid())
		{
			$instMan = \obo\lo\InstanceManager::getInstance();
			$result = $instMan->getInstancesFromLOID($loID);
		}
		else
		{
			$result = \rocketD\util\Error::getError(1);
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
		if($this->getSessionValid())
		{
			$this->DBM->startTransaction();
			$instman = \obo\lo\InstanceManager::getInstance();
			$result = $instman->newInstance($name, $loID, $course, $startTime, $endTime, $attemptCount, $scoreMethod, $allowScoreImport);
			$this->DBM->commit();
		}
		else
		{
			$result = \rocketD\util\Error::getError(1);
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
			$instman = \obo\lo\InstanceManager::getInstance();
			//$result = \obo\util\Error::getError(4006);
			//@TODO
			$result = $instman->createInstanceVisit($instID);

		}
		else
		{
			$result = \rocketD\util\Error::getError(1);
		}

		return $result;
	}

	public function getInstanceData($instID)
	{
		$instman = \obo\lo\InstanceManager::getInstance();
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
			$instman = \obo\lo\InstanceManager::getInstance();
			$result = $instman->getAllInstances();
		}
		else
		{
			$result = \rocketD\util\Error::getError(1);
		}
		return $result;
	}

	/**
	 * Updates an instance of a learning object
	 * @param $instArr (Array) Array of information about the instance
	 * @param (Array) The instance Array
	 */
	public function editInstance($name, $instID, $course, $startTime, $endTime, $attemptCount, $scoreMethod, $allowScoreImport, $removeExternalLink = false)
	{
		// @TODO: For now these are added to work with the GET api.
		// @TODO: Find a better solution here
		$name = rawurldecode($name);
		$course = rawurldecode($course);
		$allowScoreImport = (bool)$allowScoreImport;

		if($this->getSessionValid())
		{
			$this->DBM->startTransaction();
			$instman = \obo\lo\InstanceManager::getInstance();
			$result = $instman->updateInstance($name, $instID, $course, $startTime, $endTime, $attemptCount, $scoreMethod, $allowScoreImport);

			// ONLY DELETE External link if start and end times are set properly
			if ($startTime > 0 && $endTime > $startTime && $removeExternalLink === true) $instman->updateInstanceExternalLink($instID, '');

			$this->DBM->commit();
		}
		else
		{
			$result = \rocketD\util\Error::getError(1);
		}
		return $result;
	}

	public function removeInstance($instID)
	{
		if($this->getSessionValid())
		{
			$this->DBM->startTransaction();
			$instMan = \obo\lo\InstanceManager::getInstance();
			$result = $instMan->deleteInstance($instID);
			$this->DBM->commit();
		}
		else
		{
			$result = \rocketD\util\Error::getError(1);
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
			$mediaMan = \obo\lo\MediaManager::getInstance();
			$result = $mediaMan->getAllMedia($optMediaIDArray);
		}
		else
		{
			$result = \rocketD\util\Error::getError(1);
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
		if( ! \obo\util\Validator::isPosInt($mediaObj->mediaID))
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
				$VM = \obo\VisitManager::getInstance();
				if(!$VM->registerCurrentViewKey($visitKey))
				{
					return \rocketD\util\Error::getError(5);
				}
			}
			$this->DBM->startTransaction();
			$mediaMan = \obo\lo\MediaManager::getInstance();
			$result = $mediaMan->saveMedia(new \obo\lo\Media((array) $mediaObj));
			$this->DBM->commit();
		}
		else
		{
			$result = \rocketD\util\Error::getError(1);
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
		if(!\obo\util\Validator::isPosInt($mid))
		{
			return false;
		}

		if($this->getSessionValid())
		{
			$this->DBM->startTransaction();
			$mediaMan = \obo\lo\MediaManager::getInstance();
			$result = $mediaMan->deleteMedia($mid);
			$this->DBM->commit();
		}
		else
		{
			$result = \rocketD\util\Error::getError(1);
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
			$lom = \obo\lo\LOManager::getInstance();
			$result = $lom->addToLibrary($loID, $allowDerivative);
		}
		else
		{
			$result = \rocketD\util\Error::getError(1);
		}
		return $result;
	}

	public function editUsersPerms($permObjects, $itemID = 0, $itemType = 'l', $removePerms = 0)
	{
		if( ! \obo\util\Validator::isPosInt($itemID))
		{
			return \rocketD\util\Error::getError(2);
		}

		if($this->getSessionValid())
		{
			// Switch used temporarily to allow us to use 2 permission systems
			switch($itemType)
			{
				case \cfg_core_Perm::TYPE_INSTANCE:
					$PMan = \obo\perms\PermManager::getInstance();
					// add perms
					if(is_array($permObjects) && count($permObjects) > 0 )
					{
						foreach($permObjects AS $value)
						{
							$result = $PMan->setPermsForUserToItem($value->userID, \cfg_core_Perm::TYPE_INSTANCE, $itemID, $value->perm, array() );
						}
					}
					// remove perms
					if(is_array($removePerms) && count($removePerms) > 0)
					{
						foreach($removePerms as $value)
						{
							$result = $PMan->setPermsForUserToItem($value->userID, \cfg_core_Perm::TYPE_INSTANCE, $itemID, array(), $value->perm );
						}
					}
					break;

				default:
					if( ! \obo\util\Validator::isItemType($itemType))
					{
						return \rocketD\util\Error::getError(2);
					}

					$PMan2 = \obo\perms\PermissionsManager::getInstance();

					foreach($permObjects as $permObj)
					{
						$permObj = (array) $permObj;
						if(!\obo\util\Validator::isPermObj($permObj))
						{
							return \rocketD\util\Error::getError(2);
						}

						$PMan2->setUserPerms($itemID, $itemType, new \obo\perms\Permissions($permObj));
					}
					$result = true;
					break;
			}
		}
		else
		{
			$result = \rocketD\util\Error::getError(1);
		}
		return $result;
	}

	public function removeUsersPerms($users, $itemID, $itemType)
	{
		if(!\obo\util\Validator::isUserArray($users) || !\obo\util\Validator::isPosInt($itemID) || !\obo\util\Validator::isItemType($itemType))
		{
			return \rocketD\util\Error::getError(2);
		}
		if($this->getSessionValid())
		{
			$this->DBM->startTransaction();
			$permman = \obo\perms\PermissionsManager::getInstance();
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
			$result = \rocketD\util\Error::getError(1);
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
		if( ! \obo\util\Validator::isPosInt($itemID))
		{
			return \rocketD\util\Error::getError(2);
		}

		if($this->getSessionValid())
		{

			switch($itemType)
			{
				case \cfg_core_Perm::TYPE_INSTANCE:
					$PMan = \obo\perms\PermManager::getInstance();
					$result = $PMan->getAllUsersIDsForItem(\cfg_core_Perm::TYPE_INSTANCE, $itemID);
					break;

				default:
					if(!\obo\util\Validator::isItemType($itemType))
					{
						return \rocketD\util\Error::getError(2);
					}
					$permman = \obo\perms\PermissionsManager::getInstance();
					$result = $permman->getPermsForItem($itemID, $itemType);
					break;
			}
		}
		else
		{
			$result = \rocketD\util\Error::getError(1);
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
		if(\obo\util\Validator::isPosInt($qGroupID))
		{

			if($this->getSessionValid())
			{
				$VM = \obo\VisitManager::getInstance();
				if(!$VM->registerCurrentViewKey($visitKey))
				{
					return \rocketD\util\Error::getError(5);
				}
				$this->DBM->startTransaction();
				$attemptMan = \obo\AttemptsManager::getInstance();
				$ret = $attemptMan->startAttempt($qGroupID);
				$this->DBM->commit();
				return $ret;
			}
			else
			{
				return \rocketD\util\Error::getError(1);
			}

		}
		return \rocketD\util\Error::getError(2);
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
		if(\obo\util\Validator::isPosInt($qGroupID) && !empty($questionID))
		{

			if($this->getSessionValid())
			{
				$VM = \obo\VisitManager::getInstance();
				if(!$VM->registerCurrentViewKey($visitKey))
				{
					return \rocketD\util\Error::getError(5);
				}
				$this->DBM->startTransaction();
				$scoreman = \obo\ScoreManager::getInstance();
				$result = $scoreman->submitQuestion($qGroupID, $questionID, $answer);
				$this->DBM->commit();
				return $result;
			}
			else
			{
				return \rocketD\util\Error::getError(1);
			}

		}
		return \rocketD\util\Error::getError(2);

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

		if(\obo\util\Validator::isPosInt($qGroupID) && \obo\util\Validator::isPosInt($questionID) && \obo\util\Validator::isScore($score))
		{

			if($this->getSessionValid())
			{
				$VM = \obo\VisitManager::getInstance();
				if(!$VM->registerCurrentViewKey($visitKey))
				{
					return \rocketD\util\Error::getError(5);
				}
				$this->DBM->startTransaction();
				$scoreman = \obo\ScoreManager::getInstance();
				$result = $scoreman->submitQuestion($qGroupID, $questionID, $score);
				$this->DBM->commit();
				return $result;
			}
			else
			{
				return \rocketD\util\Error::getError(1);
			}

		}
		return \rocketD\util\Error::getError(2);
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
		if(\obo\util\Validator::isPosInt($qGroupID))
		{
			if($this->getSessionValid())
			{
				$VM = \obo\VisitManager::getInstance();
				if(!$VM->registerCurrentViewKey($visitKey))
				{
					return \rocketD\util\Error::getError(5);
				}
				$this->DBM->startTransaction();
				$attemptMan = \obo\AttemptsManager::getInstance();
				$result = $attemptMan->endAttempt($qGroupID);
				$this->DBM->commit();
			}
			else
			{
				$result = \rocketD\util\Error::getError(1);
			}
			return $result;
		}
		return \rocketD\util\Error::getError(2);
	}

	/**
	 * Gets a listing of all final scores for all users of a learning object instance (for faculty)
	 * @param $instid (number) instance id
	 * @return (Array<Array>) An array of final score entries, with fields 'id', 'qGroupID', 'score', 'userID', 'user_name'
	 * @return (bool) False if error or no login
	 */
	public function getScoresForInstance($instid)
	{
		if(\obo\util\Validator::isPosInt($instid))
		{
			if($this->getSessionValid())
			{
				$scoreman = \obo\ScoreManager::getInstance();
				$result = $scoreman->getScoresForAllUsers($instid);
			}
			else
			{
				$result = \rocketD\util\Error::getError(1);
			}
			return $result;
		}
		return \rocketD\util\Error::getError(2);
	}

	/**
	 * Gets a listing of each question response (qscores data) for an instance.
	 * @param $instid (number) instance id
	 * @param $offset (number) the first record to return
	 * @param $amount (number) the number of records to return
	 * @return (Array<Object>) an array of qscore record objects for this instance.
	 */
	public function getResponsesForInstance($instid, $offset, $amount)
	{
		if(\obo\util\Validator::isPosInt($instid) && \obo\util\Validator::isInt($offset) && \obo\util\Validator::isPosInt($amount))
		{
			if($this->getSessionValid())
			{
				$scoreman = \obo\ScoreManager::getInstance();
				$result = $scoreman->getResponsesForAllUsers($instid, $offset, $amount);
			}
			else
			{
				$result = \rocketD\util\Error::getError(1);
			}
			return $result;
		}
		return \rocketD\util\Error::getError(2);
	}

	public function getVisitTrackingData($userID, $instid)
	{
		if(\obo\util\Validator::isPosInt($instid) && \obo\util\Validator::isPosInt($userID))
		{
			if($this->getSessionValid())
			{
				$TM = \obo\log\LogManager::getInstance();
				return $TM->getInteractionLogByUserAndInstance($instid, $userID);
			}
			else
			{
				$result = \rocketD\util\Error::getError(1);
			}
		}
		return false;
	}

	public function getInstanceTrackingData($instID)
	{
		if(\obo\util\Validator::isPosInt($instID) && \obo\util\Validator::isPosInt($instID))
		{
			if($this->getSessionValid())
			{
				$TM = \obo\log\LogManager::getInstance();
				return $TM->getInteractionLogByInstance($instID);
			}
			else
			{
				$result = \rocketD\util\Error::getError(1);
			}

		}
		return false;
	}


	/********* Misc Functions *********/
	/**
	 * Gets all available languages
	 * @return (Array<Array>) Array of languages, containing 'id' and 'name' values
	 * @return (number) -1 if error or no login
	 */
	public function getLanguages()
	{
		$langman = \obo\lo\LanguageManager::getInstance();
		$result = $langman->getAllLanguages();
		return $result;
	}

	public function getSession()
	{
		if($this->getSessionValid())
		{
			$UM = \rocketD\auth\AuthManager::getInstance();
			$result = $UM->getSessionID();
		}
		else
		{
			$result = \rocketD\util\Error::getError(1);
		}
		return $result;
	}

	/****  Roles Functions ****/
	public function getRoles()
	{
		if($this->getSessionValid())
		{
			$roleMan = \obo\perms\RoleManager::getInstance();
			$result = $roleMan->getAllRoles();
		}
		else
		{
			$result = \rocketD\util\Error::getError(1);
		}
		return $result;
	}

	public function getUserRoles($userID = 0)
	{
		if(\obo\util\Validator::isPosInt($userID, true))
		{
			if($this->getSessionValid())
			{
				$roleMan = \obo\perms\RoleManager::getInstance();
				$result = $roleMan->getUserRoles($userID);
			}
			else
			{
				$result = \rocketD\util\Error::getError(1);
			}
			return $result;
		}
		return \rocketD\util\Error::getError(2);
	}

	// TODO: this is quite similar to getUserInRole, either rename or redundent
	// Function accepts RoleID as a positive int, or a stringRoleName
	public function getUsersInRole($roleNames)
	{
		if($this->getSessionValid())
		{
			$roleMan = \obo\perms\RoleManager::getInstance();
			$roleIDs = $roleMan->getRoleIDsFromNames($roleNames);
			if($roleIDs == false || $roleIDs instanceof \rocketD\util\Error)
			{
				return false;
			}

			$result = $roleMan->getUsersInRole($roleIDs);
		}
		else
		{
			$result = \rocketD\util\Error::getError(1);
		}
		return $result;
	}

	public function createRole($roleName)
	{
		if(\obo\util\Validator::isRoleName($roleName))
		{
			if($this->getSessionValid())
			{
				$roleMan = \obo\perms\RoleManager::getInstance();
				$result = $roleMan->createRole($roleName);
			}
			else
			{
				$result = \rocketD\util\Error::getError(1);
			}
			return $result;
		}
		return \rocketD\util\Error::getError(2);
	}

	public function createExternalMediaLink($mediaObj)
	{
		if($this->getSessionValid())
		{
			$this->DBM->startTransaction();
			$mediaMan = \obo\lo\MediaManager::getInstance();
			$result = $mediaMan->newMedia(new \obo\lo\Media($mediaObj));
			$this->DBM->commit();
		}
		else
		{
			$result = \rocketD\util\Error::getError(1);
		}
		return $result;
	}

	//@TODO: Consider renaming to uploadMedia and funnel all media uploads through here.
	public function uploadMedia($fileData, $filename, $title, $description, $copyright, $length=0)
	{
		if($this->getSessionValid())
		{
			$this->DBM->startTransaction();
			$mediaMan = \obo\lo\MediaManager::getInstance();
			$result = $mediaMan->handleFileDataUpload($fileData, $filename, $title, $description, $copyright, $length);
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
		if(\obo\util\Validator::isRoleName($roleName))
		{
			if($this->getSessionValid())
			{
				$roleMan = \obo\perms\RoleManager::getInstance();
				$result = $roleMan->deleteRole($roleName);
			}
			else
			{
				$result = \rocketD\util\Error::getError(1);
			}
			return $result;
		}
		return \rocketD\util\Error::getError(2);
	}

	public function removeUsersRoles($users, $roles)
	{
		if(\obo\util\Validator::isUserArray($users) && \obo\util\Validator::isRoleArray($roles))
		{
			if($this->getSessionValid())
			{
				$this->DBM->startTransaction();
				$roleMan = \obo\perms\RoleManager::getInstance();
				$result = $roleMan->removeUsersFromRoles($users, $roles);
				$this->DBM->commit();
			}
			else
			{
				$result = \rocketD\util\Error::getError(1);
			}
			return $result;
		}
		return \rocketD\util\Error::getError(2);
	}

	/**
	 * @author Zachary Berry
	 */
	public function editUsersRoles($users, $roles)
	{
		if(\obo\util\Validator::isUserArray($users) && \obo\util\Validator::isRoleArray($roles))
		{
			if($this->getSessionValid())
			{
				$this->DBM->startTransaction();
				$roleMan = \obo\perms\RoleManager::getInstance();
				$result = $roleMan->addUsersToRoles($users, $roles);
				$this->DBM->commit();
			}
			else
			{
				$result = \rocketD\util\Error::getError(1);
			}
			return $result;
		}
		return \rocketD\util\Error::getError(2);
	}

	/****	Tracking Functions ***/

	public function trackPageChanged($visitKey, $pageID, $section)
	{
		if(!empty($pageID) && \obo\util\Validator::isSection($section))
		{

			if($this->getSessionValid())
			{
				$VM = \obo\VisitManager::getInstance();
				if(!$VM->registerCurrentViewKey($visitKey))
				{
					return \rocketD\util\Error::getError(5);
				}

				if($VM->getCurrentViewKeyInstID() > 0)
				{
					$this->DBM->startTransaction();
					$trackingMan = \obo\log\LogManager::getInstance();
					$result = $trackingMan->trackPageChanged($pageID, $section);
					$this->DBM->commit();
				}
				else
				{
					$result = \rocketD\util\Error::getError(4003);
				}
			}
			else
			{
				$result = \rocketD\util\Error::getError(1);
			}
			return $result;
		}
		return \rocketD\util\Error::getError(2);
	}

	public function trackSectionChanged($visitKey, $section)
	{
		if(\obo\util\Validator::isSection($section) )
		{

			if($this->getSessionValid())
			{
				$VM = \obo\VisitManager::getInstance();
				if(!$VM->registerCurrentViewKey($visitKey))
				{
					return \rocketD\util\Error::getError(5);
				}

				if( $VM->getCurrentViewKeyInstID() > 0 )
				{
					$this->DBM->startTransaction();
					$trackingMan = \obo\log\LogManager::getInstance();
					$result = $trackingMan->trackSectionChanged($section);
					$this->DBM->commit();
				}
				else
				{
					$result = \rocketD\util\Error::getError(4003);
				}
			}
			else
			{
				$result = \rocketD\util\Error::getError(1);
			}
			return $result;
		}
		return \rocketD\util\Error::getError(2);
	}


	// TODO: remove, no longer used
	public function trackComputerData($data)
	{
		return true;
	}


		public function trackVisitResume($visitKey, $instID)
		{
		// register visitKey first

		if(\obo\util\Validator::isPosInt($instID) )
		{
			if($this->getSessionValid())
			{
				$VM = \obo\VisitManager::getInstance();
				if(!$VM->registerCurrentViewKey($visitKey))
				{
					return \rocketD\util\Error::getError(5);
				}

				$this->DBM->startTransaction();
				$visitMan = \obo\VisitManager::getInstance();
				$result = $visitMan->resumeVisit($instID);
				$this->DBM->commit();
			}
			else
			{
				$result = \rocketD\util\Error::getError(1);
			}
			return $result;
		}
		return \rocketD\util\Error::getError(2);
		}

	public function getPasswordReset($username, $email, $returnURL)
	{
		// needs to be exposed to non-logged in users
		if(\obo\util\Validator::isString($username) && \obo\util\Validator::isString($email) && \obo\util\Validator::isString($returnURL) )
		{
			$UM = \rocketD\auth\AuthManager::getInstance();
			return $UM->requestPasswordReset($username, $email, $returnURL);
		}
		return \rocketD\util\Error::getError(2);
	}

	public function editPassword($oldPassword, $newPassword)
	{
		if(\obo\util\Validator::isString($oldPassword) && \obo\util\Validator::isString($newPassword) )
		{
			// session wont verify, so can't do it here
			$AM = \rocketD\auth\AuthManager::getInstance();
			return $AM->changePassword($oldPassword, $newPassword);
		}
		return \rocketD\util\Error::getError(2);
	}

	public function editPasswordWithKey($username, $key, $newpass)
	{
		if(\obo\util\Validator::isString($username) && \obo\util\Validator::isSHA1($key) && \obo\util\Validator::isString($newpass))
		{
			$UM = \rocketD\auth\AuthManager::getInstance();
			if($UM->changePasswordWithKey($username, $key, $newpass) === true)
			{
				//try to automatically log them in if the reset was successful
				if(!$UM->login($username, $newpass))
				{
					\rocketD\util\Error::getError(1007);
				}
				return true;
			}
			return false;
		}
		return \rocketD\util\Error::getError(2);
	}

	public function editExtraAttempts($userID, $instID, $count)
	{
		if(\obo\util\Validator::isPosInt($userID) && \obo\util\Validator::isPosInt($instID) && \obo\util\Validator::isInt($count))
		{
			if($this->getSessionValid())
			{
				$this->DBM->startTransaction();
				$attemptMan = \obo\AttemptsManager::getInstance();
				$result = $attemptMan->setAdditionalAttempts($userID, $instID, $count);
				$this->DBM->commit();
			}
			else
			{
				$result = \rocketD\util\Error::getError(1);
			}
		}
		else
		{
			$result = \rocketD\util\Error::getError(2);
		}

		return $result;
	}

	public function removeExtraAttempts($userID, $instID)
	{
		if(\obo\util\Validator::isPosInt($userID) && \obo\util\Validator::isPosInt($instID))
		{
			if($this->getSessionValid())
			{
				$this->DBM->startTransaction();
				$attemptMan = \obo\AttemptsManager::getInstance();
				$result = $attemptMan->removeAdditionalAttempts($userID, $instID, $count);
				$this->DBM->commit();
			}
			else
			{
				$result = \rocketD\util\Error::getError(1);
			}
		}
		else
		{
			$result = \rocketD\util\Error::getError(2);
		}

		return $result;
	}

	/* @author: Zachary Berry */
	public function trackClientError($client, $message, $data)
	{
		trace('trackClientError');
		trace($client);
		trace($message);
		trace($data);

		if($this->getSessionValid())
		{
			if(\obo\util\Validator::isClientType($client) && \obo\util\Validator::isString($message) && \obo\util\Validator::isString($data))
			{
				$clientError = new \stdClass();
				$clientError->client = $client;
				$clientError->message = $message;
				$clientError->data = $data;
				$result = \rocketD\util\Error::getError(101, $message, $clientError);
				return true;
			}
			else
			{
				$result = \rocketD\util\Error::getError(2);
			}
		}
		else
		{
			$result = \rocketD\util\Error::getError(1);
		}

		return $result;

	}

	public function getLOsWithMedia($mid)
	{
		if($this->getSessionValid())
		{
			$mediaMan = \obo\lo\MediaManager::getInstance();
			$loMan = \obo\lo\LOManager::getInstance();
			$result = $mediaMan->locateLOsWithMedia($mid);
			if( !($result instanceof \rocketD\util\Error) )
			{
				$result = $loMan->getLO($result);
			}
		}
		else
		{
			$result = \rocketD\util\Error::getError(1);
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
			'passwordHelp' => 'Your NID password is shared for several systems on campus including the Main Computer Labs, Webcourses@UCF, Obojobo, and several others.  Visit the central reset form to <u><a href="http://mynid.ucf.edu/" target="_blank">change your password</a></u>.   If you are a guests without a UCF ID Card will need to call the Service Desk (407-823-5117) to reset your password.',
			'overviewHelp' => 'Overview help here!',
			'md5Password' => false,
			'canActivate' => false,
			'activateAction' => '',
			'canReset' => true,
			'resetAction' => 'http://mynid.ucf.edu/',
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
			$LOS = new \obo\LOSystem();
			$result =  $LOS->mergeUsers($userIDFrom, $userIDTo);
		}
		else
		{
			$result = \rocketD\util\Error::getError(1);
		}
		return $result;
	}

	public function doImportEquivalentAttempt($visitKey)
	{
		if($this->getSessionValid())
		{
			$AM = new \obo\AttemptsManager();
			$result =  $AM->useEquivalentAttempt($visitKey);
		}
		else
		{
			$result = \rocketD\util\Error::getError(1);
		}
		return $result;
	}

	public function getLOStats($los, $stats, $start, $end, $resolution, $preview=true)
	{
		if($this->getSessionValid())
		{
			$AM = \obo\util\Analytics::getInstance();
			$result = $AM->getLOStat($los, $stats, $start, $end, $resolution, $preview);
		}
		else
		{
			$result = \rocketD\util\Error::getError(1);
		}
		return $result;
	}

	/**
	 * Build a set of arguments needed to create an LTI Post request for an outcomes module
	 *
	 * 3 Modes are possible with this
	 * Play Mode:  getLTIParams($widgetId, $pageId, $loID, $visitKey), requires login and active visitKey
	 * Preview Mode:  getLTIParams($widgetId, $pageId, $loID) - you dont have a visitKey, requires lib user
	 * Creator Mode: if you know your itemID - getLTIParams($widgetId) or if you dont - getLTIParams(), requires lib user
	 *
	 * @param string Optional - Id of the object you want from the tool (in Materia this is a widgetID)
	 * @param string Optional - Id of the current page that item is shown on
	 * @param string Optional - LOid of the lo that the item is sown in
	 * @param string Optional - Visit key of the current visit
	 * @return array ['url'] is the outcomes url to send the request to, ['params'] are the params to post to that url
	 */
	public function getLTIParams($mode, $itemID=null, $loID=null, $pageOrQuestionID=null, $pageItemIndex=null, $visitKey=null)
	{
		// must be logged in
		if($this->getSessionValid() !== true) return \rocketD\util\Error::getError(1);

		// ============================= CHOOSE PARAMS BASED ON MODE ============================
		switch($mode)
		{

			case 'select': // select a widget in creator mode
				if(!empty($loID) || !empty($pageOrQuestionID) || !empty($pageItemIndex) || !empty($visitKey) ) return \rocketD\util\Error::getError(2); // nothing but the itemID
				$roleMan = \obo\perms\RoleManager::getInstance();
				if(!$roleMan->isLibraryUser()) return \rocketD\util\Error::getError(1);

				$instID     = '';
				$visitID    = '';
				$attemptID  = '';
				$role       = 'Instructor';
				$passback   = false;
				$url        = \AppCfg::MATERIA_LTI_PICKER_URL;
				break;

			case 'preview': // show a widget in lo previews
				if(empty($itemID) || empty($loID) || empty($pageOrQuestionID) || !\obo\util\Validator::isPosInt($pageItemIndex, true) || !empty($visitKey)) return \rocketD\util\Error::getError(2); // no visit key
				$roleMan = \obo\perms\RoleManager::getInstance();
				if(!$roleMan->isLibraryUser()) return \rocketD\util\Error::getError(1);

				$instID     = '';
				$visitID    = '';
				$attemptID  = '';
				$role       = 'Learner';
				$passback   = false;
				$url        = \AppCfg::MATERIA_LTI_URL;
				break;

			case 'content': // show a widget in content in instance mode
				if(empty($itemID) || empty($loID) || empty($pageOrQuestionID) || !\obo\util\Validator::isPosInt($pageItemIndex, true) || empty($visitKey) ) return \rocketD\util\Error::getError(2); // everything
				$vm = \obo\VisitManager::getInstance();
				if(!$vm->registerCurrentViewKey($visitKey)) return \rocketD\util\Error::getError(5);

				$instID     = $vm->getCurrentViewKeyInstID();
				$visitID    = $vm->getCurrentVisitID();
				$attemptID  = '';
				$role       = 'Learner';
				$passback   = false;
				$url        = \AppCfg::MATERIA_LTI_URL;
				break;

			case 'question': // show a widget in practice/assessment in instance mode
				if(empty($itemID) || empty($loID) || empty($pageOrQuestionID) || !\obo\util\Validator::isPosInt($pageItemIndex, true) || empty($visitKey) ) return \rocketD\util\Error::getError(2); // everything
				$vm = \obo\VisitManager::getInstance();
				if(!$vm->registerCurrentViewKey($visitKey)) return \rocketD\util\Error::getError(5);

				$am = \obo\AttemptsManager::getInstance();

				$instID    = $vm->getCurrentViewKeyInstID();
				$visitID   = $vm->getCurrentVisitID();
				$attemptID = $am->getCurrentAttemptID();
				$role      = 'Learner';
				$passback  = true;
				$url       = \AppCfg::MATERIA_LTI_URL;

				if(!\obo\util\Validator::isPosInt($attemptID)) return \rocketD\util\Error::getError(2);
				break;

			default:
				return \rocketD\util\Error::getError(2);
				break;

		}

		if($passback)
		{
			$passback = \AppCfg::URL_WEB . 'lti/grade-passback.php';
		}

		$params = array(
			"resource_link_id"           => $loID .'-'. $instID .'-'. $pageItemIndex .'-'. $pageOrQuestionID, // unique placement in obojobo
			"context_id"                 => $instID,
			"lis_result_sourcedid"       => $loID .'-'. $instID .'-'. $pageItemIndex .'-'. $pageOrQuestionID .'-'. $attemptID .'-'. $visitID,
			"custom_widget_instance_id"  => $itemID,
			"roles"                      => $role,
		);

		$params =  \lti\OAuth::buildPostArgs($this->getUser(), $url, $params, \AppCfg::MATERIA_LTI_KEY, \AppCfg::MATERIA_LTI_SECRET, $passback);

		return array('url' => $url, 'params' => $params);
	}

	public function getAttemptQuestionScore($visitKey, $questionID)
	{
		// must be logged in
		if($this->getSessionValid() !== true) return \rocketD\util\Error::getError(1);

		// must be nice input
		if(!\obo\util\Validator::isPosInt($questionID) || empty($visitKey)) return \rocketD\util\Error::getError(2);

		// visit key must be valid
		$vm = \obo\VisitManager::getInstance();
		if(!$vm->registerCurrentViewKey($visitKey)) return \rocketD\util\Error::getError(5);

		$am = \obo\AttemptsManager::getInstance();
		$attemptID = $am->getCurrentAttemptID();

		$sm = \obo\ScoreManager::getInstance();
		return $sm->getQuestionScoreForAttempt($attemptID, $questionID);
	}

}
