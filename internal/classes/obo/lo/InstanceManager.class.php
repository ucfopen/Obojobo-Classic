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
namespace obo\lo;
class InstanceManager extends \rocketD\db\DBEnabled
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
	 *
	 *
	 * @param $instArr (Array) Array of information about the new instance (needs name, lo_id, courseID, startTime, and endTime)
	 * @return (number) new instance id
	 */
	public function newInstance($name, $loID, $course, $startTime, $endTime, $attemptCount, $scoreMethod = 'h', $allowScoreImport = true)
	{
		$roleMan = \obo\perms\RoleManager::getInstance();
		if(!$roleMan->isSuperUser()) // if the current user is not SuperUser
		{
			if(!$roleMan->isLibraryUser())
			{
				return \rocketD\util\Error::getError(4);
			}
			
			$permman = \obo\perms\PermissionsManager::getInstance();
			if( ! $permman->getMergedPerm($loID, \cfg_obo_Perm::TYPE_LO, \cfg_obo_Perm::PUBLISH, $_SESSION['userID']) )
			{
				return \rocketD\util\Error::getError(4);
			}
		}
		
		$valid = $this->validateInstanceValues($name, $loID, $course, $startTime, $endTime, $attemptCount, $scoreMethod, $allowScoreImport);
		if($valid !== true)
		{
			return $valid;
		}
		$valid = $this->validateLO($loID);
		if($valid !== true)
		{
			return $valid;
		}
		
		$userID = $_SESSION['userID'];

		//check if user is a Super User
		if(!$roleMan->isSuperUser())
		{
			//if the user is not a Super User
			//check if the user has permissions to do this
			$permMan = \obo\perms\PermissionsManager::getInstance();
			if(!$permMan->getMergedPerm($loID, \cfg_obo_Perm::TYPE_LO, \cfg_obo_Perm::PUBLISH, $userID))
			{
				return \rocketD\util\Error::getError(4);
			}
		}

		$instID = $this->insertNewInstance($userID, $name, $loID, $course, $startTime, $endTime, $attemptCount, $scoreMethod, $allowScoreImport);
		if(!$instID)
		{
			return false;
		}

		//Give the current user permissions to view and edit the instance
		// TODO: move permission sql statments to permMan
		$qstr = "INSERT 
					INTO `".\cfg_obo_Perm::TABLE."`
						(
							`".\cfg_core_User::ID."`,
							`".\cfg_obo_Perm::ITEM."`,
							`".\cfg_obo_Perm::TYPE."`,
							`".\cfg_obo_Perm::READ."`,
							`".\cfg_obo_Perm::WRITE."`,
							`".\cfg_obo_Perm::COPY."`,
							`".\cfg_obo_Perm::PUBLISH."`,
							`".\cfg_obo_Perm::G_READ."`,
							`".\cfg_obo_Perm::G_WRITE."`,
							`".\cfg_obo_Perm::G_COPY."`,
							`".\cfg_obo_Perm::G_USE."`,
							`".\cfg_obo_Perm::G_GLOBAL."`
						)
                	VALUES
						('?', '?', 'i', '1', '1', '0', '0', '1', '1', '0', '0', '0');";
		if(!($this->DBM->querySafe($qstr, $userID, $instID)))
		{
			$this->DBM->rollback();
			//erro_log("ERROR: newInstance query 2  ".mysql_error());
			return false;
		}
		
		// give them permissions using the new perms system
		
		$pMan = \obo\perms\PermManager::getInstance();
		$setperms = $pMan->setPermsForUserToItem($userID, \cfg_core_Perm::TYPE_INSTANCE, $instID, \cfg_core_Perm::P_OWN, array());
		if($setperms instanceof \rocketD\util\Error)
		{
			return false;
		}
				
		return $instID;
	}

	public function duplicateInstance_SystemOnly($instID = 0, $overriddenProps = false)
	{
		if( ! \obo\util\Validator::isPosInt($instID) )
		{
			return \rocketD\util\Error::getError(2);
		}

		$source = new \obo\lo\InstanceData();
		$source->dbGet($this->DBM, $instID);

		// If our instID is zero then the instance doesn't exist!
		if($source->instID == 0)
		{
			return false;
		}

		// Override any properties with overriddenProps
		$source = get_object_vars($source);
		if(is_array($overriddenProps))
		{
			$source = array_merge($source, $overriddenProps);
		}

		if( ! $this->validateInstanceValues(
			$source['name'],
			$source['loID'],
			$source['courseID'],
			$source['startTime'],
			$source['endTime'],
			$source['attemptCount'],
			$source['scoreMethod'],
			(bool)$source['allowScoreImport']
		))
		{
			return false;
		}
		if( ! $this->validateLO($source['loID']) )
		{
			return false;
		}

		return $this->insertNewInstance(
			0,
			$source['name'],
			$source['loID'],
			$source['courseID'],
			$source['startTime'],
			$source['endTime'],
			$source['attemptCount'],
			$source['scoreMethod'],
			(bool)$source['allowScoreImport'],
			$instID
		);
	}

	/**
	 * Retrieves an instance from the database  ONLY USE WHEN VIEWING A LO FROM THE VIEWER INCLUDING TRACKING
	 * @param $instID (number) ID of instance to retrieve
	 * @return (LO) learning object
	 * @return (bool) False if error
	 */
	public function createInstanceVisit($instID = 0)
	{
		if( ! \obo\util\Validator::isPosInt($instID) )
		{
			return \rocketD\util\Error::getError(2);
		}
		
		$qstr = "SELECT * FROM ".\cfg_obo_Instance::TABLE." WHERE `".\cfg_obo_Instance::ID."`='?' LIMIT 1";
		if(!($q = $this->DBM->querySafe($qstr, $instID)))
		{
			return \rocketD\util\Error::getError(2);
		}

		if($r = $this->DBM->fetch_obj($q))
		{
			// Verify that this is not direct access to an externally linked instance
			if(!empty($r->{\cfg_obo_Instance::EXTERNAL_LINK}))
			{
				$ltiApi = \lti\API::getInstance();
				if(!$ltiApi->getAssessmentSessionData($instID))
				{
					return \rocketD\util\Error::getError(4006);
				}
			}

			$curtime = time();
			//Verify that the instance is currently active
			if($r->{\cfg_obo_Instance::START_TIME} <= $curtime)
			{
				$lom = \obo\lo\LOManager::getInstance();
				// $rootID = $lom->getRootId($r->{\cfg_obo_LO::ID});
				$permman = \obo\perms\PermissionsManager::getInstance();
				$roleMan = \obo\perms\RoleManager::getInstance();
				
				$visitMan = \obo\VisitManager::getInstance();
				$visitMan->startInstanceView($instID, $r->{\cfg_obo_LO::ID});
				$visitMan->createVisit($instID);

				// getinstance, only get content if its past the assessment end time
				$trackMan = \obo\log\LogManager::getInstance();
				if(empty($r->{\cfg_obo_Instance::EXTERNAL_LINK}) && $curtime >= $r->{\cfg_obo_Instance::END_TIME})
				{
					$lo = $lom->getLO($r->{\cfg_obo_LO::ID}, 'content', false);
                    $lo->tracking =  $trackMan->getInstanceTrackingData($_SESSION['userID'], $instID);
				}
				else
				{
					$lo = $lom->getLO($r->{\cfg_obo_LO::ID}, 'instance', false);
					$AM = \obo\AttemptsManager::getInstance();
					$lo->equivalentAttempt = $AM->getEquivalentAttempt($_SESSION['userID'], $instID, $r->{\cfg_obo_LO::ID});
					$lo->tracking =  $trackMan->getInstanceTrackingData($_SESSION['userID'], $instID);
					$lo->tracking->isInAttempt = $AM->getUnfinishedAttempt($lo->aGroup->qGroupID) != false;
				}
				
				// Add in instance viewing variables
				$lo->viewID = $visitMan->getInstanceViewKey($instID);
				$lo->instanceData = $this->getInstanceData($instID);
				$attemptMan = \obo\AttemptsManager::getInstance();
				$lo->instanceData->attemptCount = $attemptMan->getTotalAttempts($instID);
				unset($lo->pGroup->kids);
				//unset($lo->aGroup->kids);
				
				return $lo;

			}
			else
			{
				
				
				return \rocketD\util\Error::getError(4003);
			}
		}
		else
		{
			
			
			return \rocketD\util\Error::getError(4002);
		}
	}
	

	/**
	 * Sister function to getLOMeta, gets publicly available data about an instance.
	 *
	 * @param string $instID 
	 * @return (LO) Meta learning object or Error
	 * @author Ian Turgeon
	 */
	public function getInstanceData($instID=0, $includeDeleted=false)
	{
		
		if( ! (\obo\util\Validator::isPosInt($instID) || is_array($instID)) )
		{
			return \rocketD\util\Error::getError(2);
		}
		
		$return = array();
		$permman = \obo\perms\PermissionsManager::getInstance();
		
		$roleMan = \obo\perms\RoleManager::getInstance();
		
		//--------------------------- REQUESTED ARRAY OF INSTANCES ------------------------//
		// run through the array
		// remove any non integers
		// get any that we can from cache
		if(is_array($instID))
		{
			
			foreach($instID AS $key => $arrItem)
			{
				// remove non posInts from the array
				if( !\obo\util\Validator::isPosInt($arrItem) )
				{
					unset($instID[$key]);
				}
				// get instance data
				else
				{
					
					if($curInstData = \rocketD\util\Cache::getInstance()->getInstanceData($arrItem))
					{
						if($roleMan->isSuperUser())
						{
							$curInstData->perms = $permman->getMergedPerms($curInstData->instID, \cfg_obo_Perm::TYPE_INSTANCE, $_SESSION['userID']);
						}
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
		//--------------------------- REQUESTED SINGLE INSTANCES ------------------------//
		else
		{

			// get single instance
			if($curInstData = \rocketD\util\Cache::getInstance()->getInstanceData($instID))
			{
				if($roleMan->isSuperUser())
				{
					$curInstData->perms = $permman->getMergedPerms($curInstData->instID, \cfg_obo_Perm::TYPE_INSTANCE, $_SESSION['userID']);
				}
				$return[] = $curInstData; // store in return
				return $curInstData; // store in return
				
			}

			$instArr = $instID;
		}

		// all cache attempts exhausted, get the remaining from the db
		if($includeDeleted)
		{
			$qstr = "SELECT * FROM ".\cfg_obo_Instance::TABLE." WHERE ".\cfg_obo_Instance::ID." IN (?)";
		}
		else
		{
			$qstr = "SELECT * FROM ".\cfg_obo_Instance::TABLE." WHERE ".\cfg_obo_Instance::ID." IN (?) AND ".\cfg_obo_Instance::DELETED." = '0' ";
		}

		// Exit if theres an issue with the query
		if(!$q = $this->DBM->querySafe($qstr, $instArr))
		{
			return false;
		}
		$authMan = \rocketD\auth\AuthManager::getInstance();
		// Get all the instances from the database
		while($r = $this->DBM->fetch_obj($q))
		{
			$userID = $r->{\cfg_core_User::ID};
			$ownerName = $userID > 0 ? $authMan->getName($userID) : '';
			$iData = new \obo\lo\InstanceData($r->{\cfg_obo_Instance::ID}, $r->{\cfg_obo_LO::ID}, $r->{\cfg_core_User::ID}, $ownerName, $r->{\cfg_obo_Instance::TITLE}, $r->{\cfg_obo_Instance::COURSE}, $r->{\cfg_obo_Instance::TIME}, $r->{\cfg_obo_Instance::START_TIME}, $r->{\cfg_obo_Instance::END_TIME}, $r->{\cfg_obo_Instance::ATTEMPT_COUNT}, $r->{\cfg_obo_Instance::SCORE_METHOD}, $r->{\cfg_obo_Instance::SCORE_IMPORT}, 0, array(), $r->{\cfg_obo_Instance::EXTERNAL_LINK});
			$iData->dbGetCourseData();
			//\rocketD\util\Cache::getInstance()->setInstanceData($iData);
			// get perms
			
			// Fix if the user is SU, just get merged perms
			if($roleMan->isSuperUser())
			{
				$iData->perms = $permman->getMergedPerms($r->{\cfg_obo_Instance::ID}, \cfg_obo_Perm::TYPE_INSTANCE, $_SESSION['userID']);
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
		$PMan = \obo\perms\PermManager::getInstance();
		$itemPerms = $PMan->getAllItemsForUser($_SESSION['userID'], \cfg_core_Perm::TYPE_INSTANCE, true, true);

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
		$valid = $this->validateInstanceValues($name, $instID, $course, $startTime, $endTime, $attemptCount, $scoreMethod, $allowScoreImport);
		if($valid !== true)
		{
			return $valid;
		}

		$qstr = "UPDATE ".\cfg_obo_Instance::TABLE."
			SET
				`".\cfg_obo_Instance::TITLE."` = '?',
				`".\cfg_obo_Instance::COURSE."` = '?',
				`".\cfg_obo_Instance::START_TIME."` = '?',
				`".\cfg_obo_Instance::END_TIME."` = '?',
				`".\cfg_obo_Instance::ATTEMPT_COUNT."` = '?',
				`".\cfg_obo_Instance::SCORE_METHOD."` = '?',
				`".\cfg_obo_Instance::SCORE_IMPORT."` = '?'
			WHERE
				`".\cfg_obo_Instance::ID."` = '?'";
		
		\rocketD\util\Cache::getInstance()->clearInstanceData($instID);
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

	/**
	 * Updates and instance to be used in an external system.
	 * Setting externalLinkName to '' will remove the link (and destroy the start/end times).
	 * @param (int) instID
	 * @param (string) externalLinkName
	 * @return true if updated
	 */
	public function updateInstanceExternalLink($instID, $externalLinkName)
	{
		if(!is_string($externalLinkName)) return \rocketD\util\Error::getError(2);

		\rocketD\util\Cache::getInstance()->clearInstanceData($instID);

		if(strlen($externalLinkName) === 0)
		{
			// remove external link:
			$qstr = "UPDATE ".\cfg_obo_Instance::TABLE."
				SET
					`".\cfg_obo_Instance::EXTERNAL_LINK."` = '?'
				WHERE
					`".\cfg_obo_Instance::ID."` = '?'";
		}
		else
		{
			// add/change external link
			$qstr = "UPDATE ".\cfg_obo_Instance::TABLE."
				SET
					`".\cfg_obo_Instance::START_TIME."` = '0',
					`".\cfg_obo_Instance::END_TIME."` = '0',
					`".\cfg_obo_Instance::EXTERNAL_LINK."` = '?'
				WHERE
					`".\cfg_obo_Instance::ID."` = '?'";
		}

		if( !($q = $this->DBM->querySafe($qstr, $externalLinkName, $instID)) )
		{
			$this->DBM->rollback();
			trace(mysql_error(), true);
			return false;
		}

		return true;
	}

	public function userCanEditInstance($userID, $instID)
	{
		$roleMan = \obo\perms\RoleManager::getInstance();
		if(!$roleMan->isSuperUser()) // if the current user is not SuperUser
		{
			if(!$roleMan->isLibraryUser())
			{
				return false;
			}
			$permman = \obo\perms\PermissionsManager::getInstance();
			if( ! $permman->getUserPerm($instID, \cfg_obo_Perm::TYPE_INSTANCE, \cfg_obo_Perm::WRITE, $userID) )
			{
				// check 2nd Perms system to see if they have write or own
				$pMan = \obo\perms\PermManager::getInstance();
				$perms = $pMan->getPermsForUserToItem($userID, \cfg_core_Perm::TYPE_INSTANCE, $instID);
				if(!is_array($perms) || ( !in_array(\cfg_core_Perm::P_WRITE, $perms) && !in_array(\cfg_core_Perm::P_OWN, $perms)) )
				{
					return false;
				}
			}
		}
		return true;
	}
	
	public function deleteInstance($instID = 0)
	{
	    if(!\obo\util\Validator::isPosInt($instID))
		{
			return \rocketD\util\Error::getError(2);
		}
		// Can this user edit the instance?
		if(!$this->userCanEditInstance($_SESSION['userID'], $instID))
		{
			return \rocketD\util\Error::getError(4);
		}
	
		// Delete permission relating to that instance
		$permman = \obo\perms\PermissionsManager::getInstance();
		if(!$permman->removeAllPermsForItem($instID, \cfg_obo_Perm::TYPE_INSTANCE))
		{
			return false;
		}
		// clean secondary permissions (shared users)
		$pMan = \obo\perms\PermManager::getInstance();
		$pMan->clearPermsForItem(\cfg_core_Perm::TYPE_INSTANCE, $instID);
		
		// clear cache
		\rocketD\util\Cache::getInstance()->clearInstanceData($instID);
		\rocketD\util\Cache::getInstance()->clearScoresForAllUsers($instID);
		\rocketD\util\Cache::getInstance()->clearScoresForUser($instID, $_SESSION['userID']);
		
		$tracking = \obo\log\LogManager::getInstance();
		$tracking->trackDeleteInstance($instID);
		
		// mark the instance as deleted
		$this->DBM->querySafe("UPDATE ".\cfg_obo_Instance::TABLE." SET ".\cfg_obo_Instance::DELETED." = '1' WHERE ".\cfg_obo_Instance::ID." = '?'", $instID);
		return true;
	}

	public function getLOID($instID)
	{
		if(!is_numeric($instID) || $instID < 1)
		{
			return false; // error: invalid input
		}
		
		// try cache, instanceData can find the loid by the instid
		
		if($instData = \rocketD\util\Cache::getInstance()->getInstanceData($instID))
		{
			return $instData->loID;
		}
		
		$qstr = "SELECT `".\cfg_obo_LO::ID."` FROM `".\cfg_obo_Instance::TABLE."` WHERE `".\cfg_obo_Instance::ID."` = '?'";
		
		if(!($q = $this->DBM->querySafe($qstr,  $instID)))
		{
			return false;
		}
		if($r = $this->DBM->fetch_obj($q))
		{
			return $r->{\cfg_obo_LO::ID};
		}
		else
		{
			return false; // error: instance does not exist
		}
	}

	public function getInstancesFromLOID($loID)
	{
		if(!\obo\util\Validator::isPosInt($loID))
		{
			if(!is_array($loID) && count($loID) < 1)
			{
				return \rocketD\util\Error::getError(2);
			}
			else
			{
				$loID = implode(',', $loID);
			}
		}
		
		$qstr = "SELECT ".\cfg_obo_Instance::ID."  FROM `".\cfg_obo_Instance::TABLE."` WHERE `".\cfg_obo_LO::ID."` IN (?)";
		
		if( !($q = $this->DBM->querySafe($qstr, $loID)) )
		{
			return false;
		}
		$result = array();
		while($r = $this->DBM->fetch_obj($q))
		{
			$result[] = $r->{\cfg_obo_Instance::ID};
		}
		// return empty array if non found
		return count($result) > 0 ? $this->getInstanceData($result) : $result;
	}

	protected function validateInstanceValues($name, $instOrLoID, $course, $startTime, $endTime, $attemptCount, $scoreMethod = 'h', $allowScoreImport = true)
	{
		if(!\obo\util\Validator::isString($name))
		{
			return \rocketD\util\Error::getError(2);
		}

		if(!\obo\util\Validator::isPosInt($instOrLoID))
		{
			return \rocketD\util\Error::getError(2);
		}

		if(!\obo\util\Validator::isPosInt($attemptCount))
		{
			return \rocketD\util\Error::getError(2);
		}
		
		if(!\obo\util\Validator::isScoreMethod($scoreMethod))
		{
			return \rocketD\util\Error::getError(2);
		}
		
		if(!\obo\util\Validator::isBoolean($allowScoreImport))
		{
			return \rocketD\util\Error::getError(2);
		}

		return true;
	}

	protected function validateLO($loID)
	{
		$lo = new \obo\lo\LO();
		if( ! $lo->dbGetFull($this->DBM, $loID))
		{
			return \rocketD\util\Error::getError(2);
		}
		if($lo->subVersion > 0)
		{
			return \rocketD\util\Error::getError(2);
		}

		return true;
	}

	protected function insertNewInstance($userID, $name, $loID, $course, $startTime, $endTime, $attemptCount, $scoreMethod, $allowScoreImport, $originalID = 0)
	{
		$qstr = "INSERT INTO `".\cfg_obo_Instance::TABLE."`
				SET 
					`".\cfg_obo_Instance::TITLE."`='?',
					`".\cfg_obo_LO::ID."`='?',
					`".\cfg_core_User::ID."`='?',
					`".\cfg_obo_Instance::TIME."`='?',
					`".\cfg_obo_Instance::COURSE."`='?',
					`".\cfg_obo_Instance::START_TIME."`='?',
					`".\cfg_obo_Instance::END_TIME."`='?',
					`".\cfg_obo_Instance::ATTEMPT_COUNT."`='?',
					`".\cfg_obo_Instance::SCORE_METHOD."`='?',
					`".\cfg_obo_Instance::SCORE_IMPORT."`='?',
					`".\cfg_obo_Instance::ORIGINAL_ID."`='?'";
		
		//Default scoreMethod (highest)
		if(empty($scoreMethod)) $scoreMethod = 'h';
		
		//Send query to DB, checking for errors
		//TODO: future course code: if(!($this->DBM->querySafe($qstr, $name, $loID, $userID, time(), $course->title, $startTime, $endTime, $attemptCount, $scoreMethod, (int)$allowScoreImport, $course->courseID)))
		if(!($this->DBM->querySafe($qstr, $name, $loID, $userID, time(), $course, $startTime, $endTime, $attemptCount, $scoreMethod, (int)$allowScoreImport, (int)$originalID)))
		{
			$this->DBM->rollback();
			trace(mysql_error(), true);
			return false;
		}
		
		return $this->DBM->insertID;
	}
}
?>