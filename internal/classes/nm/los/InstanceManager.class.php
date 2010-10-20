<?php
/**
 * This class handles all database calls and logic pertaining to Instances
 * @author Jacob Bates <jbates@mail.ucf.edu>
 * @author Luis Estrada <lestrada@mail.ucf.edu>
 */

/**
 * This class handles all database calls and logic pertaining to Instances
 * This includes creating, retrieving, and deleting of data.
 */
class nm_los_InstanceManager extends core_db_dbEnabled
{
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
	
	/**
	 * Creates a new instance of a learning object (this is what the lo viewer will call)
	 * @param $instArr (Array) Array of information about the new instance (needs name, lo_id, courseID, startTime, and endTime)
	 * @return (number) new instance id
	 */
	public function newInstance($name, $loID, $course, $startTime, $endTime, $attemptCount, $scoreMethod = 'h', $allowScoreImport = true)
	{
		$roleMan = nm_los_RoleManager::getInstance();
		if(!$roleMan->isSuperUser()) // if the current user is not SuperUser
		{
			if(!$roleMan->isLibraryUser())
			{
				
				
				return core_util_Error::getError(4);
			}
			
			$permman = nm_los_PermissionsManager::getInstance();
			if( ! $permman->getMergedPerm($loID, cfg_obo_Perm::TYPE_LO, cfg_obo_Perm::PUBLISH, $_SESSION['userID']) )
			{
				
				
				return core_util_Error::getError(4);
			}
		}
		
		if(!nm_los_Validator::isString($name))
		{
			
			
			return core_util_Error::getError(2);
		}

		if(!nm_los_Validator::isPosInt($loID))
		{
			
			
			return core_util_Error::getError(2);
		}

		if(!nm_los_Validator::isPosInt($startTime))
		{
			
			
			return core_util_Error::getError(2);
		}
		
		if(!nm_los_Validator::isPosInt($endTime))
		{
			
			
			return core_util_Error::getError(2);
		}
		
		if($startTime > $endTime || $endTime < time())
		{
			
			
			return core_util_Error::getError(2);
		}
		
		if(!nm_los_Validator::isPosInt($attemptCount))
		{
			
			
			return core_util_Error::getError(2);
		}
		
		if(!nm_los_Validator::isScoreMethod($scoreMethod))
		{
			
			
			return core_util_Error::getError(2);
		}
		
		if(!nm_los_Validator::isBoolean($allowScoreImport))
		{
			
			
			return core_util_Error::getError(2);
		}
		
		$lo = new nm_los_LO();
		if( ! $lo->dbGetFull($this->DBM, $loID))
		{
			
			
			return core_util_Error::getError(2);
		}
		if($lo->subVersion > 0)
		{
			
			
			return core_util_Error::getError(2);
		}

		$system = new nm_los_LOSystem();
		$system->cleanOrphanData();
		
		$userID = $_SESSION['userID'];
	    
		//check if user is a Super User
		if(!$roleMan->isSuperUser())
		{	
		    //if the user is not a Super User
			//check if the user has permissions to do this
			$permMan = nm_los_PermissionsManager::getInstance();
			if(!$permMan->getMergedPerm($loID, cfg_obo_Perm::TYPE_LO, cfg_obo_Perm::PUBLISH, $userID))
			{
				
				
				return core_util_Error::getError(4);
			}
		}
		// TODO: future course code
		// if no courseID is sent, assume its a new course
		// if(!is_object($course))
		// {
		// 	$course = (object) $course;
		// 	
		// }
		// trace($course);
		// if( !($course->courseID > 0) )
		// {
		// 	$CM = nm_los_CourseManager::getInstance();
		// 	$CM->saveCourse($course);
		// }
		
		//Generate query string
		// TODO: future course code
		// $qstr = "INSERT INTO `".cfg_obo_Instance::TABLE."`
		// 		SET 
		// 			`".cfg_obo_Instance::TITLE."`='?',
		// 			`".cfg_obo_LO::ID."`='?',
		// 			`".cfg_core_User::ID."`='?',
		// 			`".cfg_obo_Instance::TIME."`='?',
		// 			`".cfg_obo_Instance::COURSE."`='?',
		// 			`".cfg_obo_Instance::START_TIME."`='?',
		// 			`".cfg_obo_Instance::END_TIME."`='?',
		// 			`".cfg_obo_Instance::ATTEMPT_COUNT."`='?',
		// 			`".cfg_obo_Instance::SCORE_METHOD."`='?',
		// 			`".cfg_obo_Instance::SCORE_IMPORT."`='?',
		// 			`".cfg_obo_Course::ID."`='?'";
		$qstr = "INSERT INTO `".cfg_obo_Instance::TABLE."`
				SET 
					`".cfg_obo_Instance::TITLE."`='?',
					`".cfg_obo_LO::ID."`='?',
					`".cfg_core_User::ID."`='?',
					`".cfg_obo_Instance::TIME."`='?',
					`".cfg_obo_Instance::COURSE."`='?',
					`".cfg_obo_Instance::START_TIME."`='?',
					`".cfg_obo_Instance::END_TIME."`='?',
					`".cfg_obo_Instance::ATTEMPT_COUNT."`='?',
					`".cfg_obo_Instance::SCORE_METHOD."`='?',
					`".cfg_obo_Instance::SCORE_IMPORT."`='?'";
		
		//Default scoreMethod (highest)
		if(empty($scoreMethod)) $scoreMethod = 'h';
		
		//Send query to DB, checking for errors
		//TODO: future course code: if(!($this->DBM->querySafe($qstr, $name, $loID, $userID, time(), $course->title, $startTime, $endTime, $attemptCount, $scoreMethod, (int)$allowScoreImport, $course->courseID)))
		if(!($this->DBM->querySafe($qstr, $name, $loID, $userID, time(), $course, $startTime, $endTime, $attemptCount, $scoreMethod, (int)$allowScoreImport)))
		{
			$this->DBM->rollback();
			trace(mysql_error(), true);
			return false;
		}
		$instID = $this->DBM->insertID;
		
		
		
		//Give the current user permissions to view and edit the instance
		// TODO: move permission sql statments to permMan
		$qstr = "INSERT 
					INTO `".cfg_obo_Perm::TABLE."`
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
						('?', '?', 'i', '1', '1', '0', '0', '1', '1', '0', '0', '0');";
		if(!($this->DBM->querySafe($qstr, $userID, $instID)))
		{
			$this->DBM->rollback();
			erro_log("ERROR: newInstance query 2  ".mysql_error());
			return false;
		}
		
		// give them permissions using the new perms system
		
		$pMan = nm_los_PermManager::getInstance();
		$setperms = $pMan->setPermsForUserToItem($userID, cfg_core_Perm::TYPE_INSTANCE, $instID, cfg_core_Perm::P_OWN, array());
		if($setperms instanceof core_util_Error)
		{
			return false;
		}
				
		return $instID;
	}
	
	/**
	 * Retrieves an instance from the database  ONLY USE WHEN VIEWING A LO FROM THE VIEWER INCLUDING TRACKING
	 * @param $instID (number) ID of instance to retrieve
	 * @return (LO) learning object
	 * @return (bool) False if error
	 */
	public function createInstanceVisit($instID = 0)
	{
		if( ! nm_los_Validator::isPosInt($instID) )
		{
			
			
			return core_util_Error::getError(2);
		}
		
		$qstr = "SELECT * FROM ".cfg_obo_Instance::TABLE." WHERE `".cfg_obo_Instance::ID."`='?' LIMIT 1";
		if(!($q = $this->DBM->querySafe($qstr, $instID)))
		{
			
			
			return core_util_Error::getError(2);
		}
;
		if($r = $this->DBM->fetch_obj($q))
		{
			$curtime = time();
			//Verify that the instance is currently active
			if($r->{cfg_obo_Instance::START_TIME} <= $curtime)
			{
				$lom = nm_los_LOManager::getInstance();
				$rootID = $lom->getRootId($r->{cfg_obo_LO::ID});
				$permman = nm_los_PermissionsManager::getInstance();
				$roleMan = nm_los_RoleManager::getInstance();
				
				$visitMan = nm_los_VisitManager::getInstance();
				$visitMan->startInstanceView($instID);
				$visitMan->createVisit($instID);

				// getinstance, only get content if its past the assessment end time
				$trackMan = nm_los_TrackingManager::getInstance();
				if($curtime >= $r->{cfg_obo_Instance::END_TIME})
				{
					$lo = $lom->getLO($r->{cfg_obo_LO::ID}, 'content', false);
                    $lo->tracking =  $trackMan->getInstanceTrackingData($_SESSION['userID'], $instID);
				}
				else
				{
					$lo = $lom->getLO($r->{cfg_obo_LO::ID}, 'instance', false);
					$AM = nm_los_AttemptsManager::getInstance();
					$lo->equivalentAttempt = $AM->getEquivalentAttempt($_SESSION['userID'], $instID, $r->{cfg_obo_LO::ID});
					$lo->tracking =  $trackMan->getInstanceTrackingData($_SESSION['userID'], $instID);
					$lo->tracking->isInAttempt = $AM->getUnfinishedAttempt($lo->aGroup->qGroupID) != false;
				}
				
				// Add in instance viewing variables
				$lo->viewID = $visitMan->getInstanceViewKey($instID);
				$lo->instanceData = $this->getInstanceData($instID);
				$attemptMan = nm_los_AttemptsManager::getInstance();
				$lo->instanceData->attemptCount = $attemptMan->getTotalAttempts($instID);
				unset($lo->pGroup->kids);
				//unset($lo->aGroup->kids);
				
				return $lo;

			}
			else
			{
				
				
				return core_util_Error::getError(4003);
			}
		}
		else
		{
			
			
			return core_util_Error::getError(4002);
		}
	}
	

	/**
	 * Sister function to getLOMeta, gets publicly available data about an instance.
	 *
	 * @param string $instID 
	 * @return (LO) Meta learning object or Error
	 * @author Ian Turgeon
	 */
	public function getInstanceData($instID=0)
	{
		
		if( ! (nm_los_Validator::isPosInt($instID) || is_array($instID)) )
		{
			
			return core_util_Error::getError(2);
		}
		
		$return = array();
		$permman = nm_los_PermissionsManager::getInstance();

		
		if(is_array($instID))
		{

			// remove non posInts from the array
			foreach($instID AS $key => $arrItem)
			{

				if( !nm_los_Validator::isPosInt($arrItem) )
				{

					unset($instID[$key]);
				}
				else
				{

					// TRY Retrieving from Cache
					if($curInstData = core_util_Cache::getInstance()->getInstanceData($arrItem))
					{

						$curInstData->perms = $permman->getMergedPerms($curInstData->instID, cfg_obo_Perm::TYPE_INSTANCE, $_SESSION['userID']);
						$return[] = $curInstData; // store in return
						unset($instID[$key]); // remove from list of keys to get
					}
				}
			}

			// no items left to look up
			if(count($instID) < 1)
			{

				if(count($return) > 0) // all items were found in cache or invalidated
				{

					return $return;
				}
				else // arg passed was empty array and or only contained non positive integers, return empty array
				{

					return $instID; 
				}
				
			}

			$instArr = implode(',', $instID);
		}
		else
		{

			// valid, attempt to get from cache since, special case for just one id
			if($curInstData = core_util_Cache::getInstance()->getInstanceData($instID))
			{

				$curInstData->perms = $permman->getMergedPerms($curInstData->instID, cfg_obo_Perm::TYPE_INSTANCE, $_SESSION['userID']);
				$return[] = $curInstData; // store in return
				return $curInstData; // store in return
				
			}

			$instArr = $instID;
		}

		// all cache attempts exhausted, get the remaining from the db
		$qstr = "SELECT * FROM ".cfg_obo_Instance::TABLE." WHERE ".cfg_obo_Instance::ID." IN (?)";
		if(!$q = $this->DBM->querySafe($qstr, $instArr))
		{

			return false;
		}

		$authMan = core_auth_AuthManager::getInstance();
		// TODO: future course code: $CM = nm_los_CourseManager::getInstance();

		while($r = $this->DBM->fetch_obj($q))
		{

			// TODO: future course code: $course = $CM->getCourse($r->{cfg_obo_Course::ID});
			$ownerName = $authMan->getName($r->{cfg_core_User::ID});



			// TODO: future course code: $iData = new nm_los_InstanceData($r->{cfg_obo_Instance::ID}, $r->{cfg_obo_LO::ID}, $r->{cfg_core_User::ID}, $ownerName, $r->{cfg_obo_Instance::TITLE}, $course, $r->{cfg_obo_Instance::TIME}, $r->{cfg_obo_Instance::START_TIME}, $r->{cfg_obo_Instance::END_TIME}, $r->{cfg_obo_Instance::ATTEMPT_COUNT}, $r->{cfg_obo_Instance::SCORE_METHOD}, $r->{cfg_obo_Instance::SCORE_IMPORT});
			$iData = new nm_los_InstanceData($r->{cfg_obo_Instance::ID}, $r->{cfg_obo_LO::ID}, $r->{cfg_core_User::ID}, $ownerName, $r->{cfg_obo_Instance::TITLE}, $r->{cfg_obo_Instance::COURSE}, $r->{cfg_obo_Instance::TIME}, $r->{cfg_obo_Instance::START_TIME}, $r->{cfg_obo_Instance::END_TIME}, $r->{cfg_obo_Instance::ATTEMPT_COUNT}, $r->{cfg_obo_Instance::SCORE_METHOD}, $r->{cfg_obo_Instance::SCORE_IMPORT});


			core_util_Cache::getInstance()->setInstanceData($iData);
			// get perms

			// OBOJOBO OMG FIX
			if($authMan->verifySession())
			{
				$iData->perms = $permman->getMergedPerms($r->{cfg_obo_Instance::ID}, cfg_obo_Perm::TYPE_INSTANCE, $_SESSION['userID']);
			}
			$return[] = $iData;

		}
		
		// only return one object if request was a single ID not an array
		if(!is_array($instID))
		{


			return $return[0];
		}
		


		return $return;
	}

	/**
	 * Gets a list of all instances this user has write access to
	 * @return (Array<Instance>) array of instance objects
	 * @return (bool) False if error
	 */
	// TODO: FIX RETURN FOR DB ABSTRACTION
	public function getAllInstances()
	{
		//$roleMan = nm_los_RoleManager::getInstance();
		//$permman = nm_los_PermissionsManager::getInstance();
		//$myInstances = $permman->getItemsWithPerm(cfg_obo_Perm::TYPE_INSTANCE, cfg_obo_Perm::WRITE);
		
		$PMan = nm_los_PermManager::getInstance();

		$itemPerms = $PMan->getAllItemsForUser($_SESSION['userID'], cfg_core_Perm::TYPE_INSTANCE, true);

		// TODO: limit what is returned based on what perm they have
		$myInstances = array_keys($itemPerms);
		return $this->getInstanceData($myInstances);
	}
		
	/**
	 * Updates an instance
	 * @param $instArr (Array) Array of information about the new instance (needs name, lo_id, courseID, startTime, and endTime)
	 * @return (Array<Instance>) instance array
	 */
	public function updateInstance($name, $instID, $course, $startTime, $endTime, $attemptCount, $scoreMethod, $allowScoreImport)
	{
		if(!nm_los_Validator::isString($name))
		{
			
			
			return core_util_Error::getError(2);
		}

		if(!nm_los_Validator::isPosInt($instID))
		{
			
			
			return core_util_Error::getError(2);
		}

		if(!nm_los_Validator::isPosInt($startTime))
		{
			
			
			return core_util_Error::getError(2);
		}
		
		if(!nm_los_Validator::isPosInt($endTime))
		{
			
			
			return core_util_Error::getError(2);
		}
		
		if($startTime > $endTime)
		{
			
			
			return core_util_Error::getError(2);
		}
		
		if(!nm_los_Validator::isPosInt($attemptCount))
		{
			
			
			return core_util_Error::getError(2);
		}
		
		if(!nm_los_Validator::isScoreMethod($scoreMethod))
		{
			
			
			return core_util_Error::getError(2);
		}
		
		if(!nm_los_Validator::isBoolean($allowScoreImport))
		{
			
			
			return core_util_Error::getError(2);
		}
		// TODO: future course code
		// if no courseID is sent, assume its a new course
		// if(!is_object($course))
		// {
		// 	$course = (object) $course;
		// 	
		// }
		// // if no courseID is sent, assume its a new course
		// $CM = nm_los_CourseManager::getInstance();
		// $CM->saveCourse($course);

		// TODO: future course code
		//Generate query string
		// $qstr = "UPDATE ".cfg_obo_Instance::TABLE."
		// 	SET 
		// 		`".cfg_obo_Instance::TITLE."` = '?', 
		// 		`".cfg_obo_Instance::COURSE."` = '?', 
		// 		`".cfg_obo_Instance::START_TIME."` = '?', 
		// 		`".cfg_obo_Instance::END_TIME."` = '?', 
		// 		`".cfg_obo_Instance::ATTEMPT_COUNT."` = '?', 
		// 		`".cfg_obo_Instance::SCORE_METHOD."` = '?',
		// 		`".cfg_obo_Instance::SCORE_IMPORT."` = '?',
		// 		`".cfg_obo_Course::ID."`='?'
		// 	WHERE 
		// 		`".cfg_obo_Instance::ID."` = '?'";
				
		$qstr = "UPDATE ".cfg_obo_Instance::TABLE."
			SET 
				`".cfg_obo_Instance::TITLE."` = '?', 
				`".cfg_obo_Instance::COURSE."` = '?', 
				`".cfg_obo_Instance::START_TIME."` = '?', 
				`".cfg_obo_Instance::END_TIME."` = '?', 
				`".cfg_obo_Instance::ATTEMPT_COUNT."` = '?', 
				`".cfg_obo_Instance::SCORE_METHOD."` = '?',
				`".cfg_obo_Instance::SCORE_IMPORT."` = '?'
			WHERE 
				`".cfg_obo_Instance::ID."` = '?'";
				
		
		core_util_Cache::getInstance()->clearInstanceData($instID);
		//Send query to DB, checking for errors
		// TODO:future course code: if( !($q = $this->DBM->querySafe($qstr, $name, $course->title, $startTime, $endTime, $attemptCount, $scoreMethod, (int)$allowScoreImport, $course->courseID, $instID)) )
		if( !($q = $this->DBM->querySafe($qstr, $name, $course, $startTime, $endTime, $attemptCount, $scoreMethod, (int)$allowScoreImport, $instID)) )
		{
			$this->DBM->rollback();
			trace(mysql_error(), true);
			return false;
		}
		
		return true;
	}
	
	public function deleteInstance($instID = 0)
	{
	    if(!nm_los_Validator::isPosInt($instID))
		{
			
	       
	        return core_util_Error::getError(2);
		}		
		$roleMan = nm_los_RoleManager::getInstance();
		if(!$roleMan->isSuperUser()) // if the current user is not SuperUser
		{
			if(!$roleMan->isLibraryUser())
			{
				
				
				return core_util_Error::getError(4);
			}
			$permman = nm_los_PermissionsManager::getInstance();
			if( ! $permman->getUserPerm($instID, cfg_obo_Perm::TYPE_INSTANCE, cfg_obo_Perm::WRITE, $_SESSION['userID']) )
			{
				// check 2nd Perms system to see if they have write or own
				$pMan = nm_los_PermManager::getInstance();
				$perms = $pMan->getPermsForUserToItem($_SESSION['userID'], cfg_core_Perm::TYPE_INSTANCE, $instID);
				if(!is_array($perms) && !in_array(cfg_core_Perm::P_WRITE, $perms) && !in_array(cfg_core_Perm::P_OWN, $perms) )
				{
					
					
					return core_util_Error::getError(4);
				}
			}
		}
	
		if(!nm_los_Validator::isPosInt($instID))
		{
			return false; // error invalid input
		}
		// Delete permission relating to that instance
		$permman = nm_los_PermissionsManager::getInstance();
		if(!$permman->removeAllPermsForItem($instID, cfg_obo_Perm::TYPE_INSTANCE))
		{
			return false;
		}
		// clean secondary permissions 
		$pMan = nm_los_PermManager::getInstance();
		$pMan->clearPermsForItem(cfg_core_Perm::TYPE_INSTANCE, $instID);
		
		
		core_util_Cache::getInstance()->clearInstanceData($instID);
		core_util_Cache::getInstance()->clearInstanceScores($instID);
		$tracking = nm_los_TrackingManager::getInstance();
		$tracking->trackDeleteInstance($instID);
		
		$system = new nm_los_LOSystem();
		$system->cleanInstances();
		return true;
	}

	public function getLOID($instID)
	{
		if(!is_numeric($instID) || $instID < 1)
		{
			return false; // error: invalid input
		}
		
		// try cache, instanceData can find the loid by the instid
		
		if($instData = core_util_Cache::getInstance()->getInstanceData($instID))
		{
			return $instData->loID;
		}
		
		$qstr = "SELECT `".cfg_obo_LO::ID."` FROM `".cfg_obo_Instance::TABLE."` WHERE `".cfg_obo_Instance::ID."` = '?'";
		
		if(!($q = $this->DBM->querySafe($qstr,  $instID)))
		{
			return false;
		}
		if($r = $this->DBM->fetch_obj($q))
		{
			return $r->{cfg_obo_LO::ID};
		}
		else
		{
			return false; // error: instance does not exist
		}
	}

	public function getInstancesFromLOID($loID)
	{
		if(!nm_los_Validator::isPosInt($loID))
		{
			
			
			return core_util_Error::getError(2);
		}
		
		$qstr = "SELECT ".cfg_obo_Instance::ID."  FROM `".cfg_obo_Instance::TABLE."` WHERE `".cfg_obo_LO::ID."` = '?'";
		
		if( !($q = $this->DBM->querySafe($qstr, $loID)) )
		{
			return false;
		}
		$result = array();
		while($r = $this->DBM->fetch_obj($q))
		{
			$result[] = $r->{cfg_obo_Instance::ID};
		}
		// return empty array if non found
		return count($result) > 0 ? $this->getInstanceData($result) : $result ;
	}
}
?>