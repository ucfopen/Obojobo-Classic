<?php
class nm_los_Cache extends core_util_Memcache
{

	static private $instance = NULL;
	private $ns = 'oBo_';
	private $memEnabled = false;
	private $dbCache = false;
	private $DBM;

	static public function getInstance()
	{
		if(!isset(self::$instance))
		{
			$selfClass = __CLASS__;
			self::$instance = new $selfClass();
		}
		return self::$instance;
	}

	public function __construct()
	{
		parent::__construct();
		
		$this->memEnabled = AppCfg::CACHE_MEMCACHE;

	}

	public function clearAllCache()
	{
		
		$this->connectMemCache();
		// dump any profiler data to file
		core_util_Log::dumpProfile('amfphp_Filters');
		core_util_Log::dumpProfile('amfphp_Methods');
		core_util_Log::dumpProfile('memcache_missed');
		$this->flush();
		
	}
	
	public function setModUCFExternalUser($username, $userData)
	{
		if($this->memEnabled)
		{
			// expire tomorrow before the next update
			if(date('G') < 4)
			{
				// expire in 30 min
				$expire = 1800;
			}
			else
			{
				// expire tomorrow
				$expire = mktime(2, 0, 0, date('m'), date('d') + 1, date('Y'));
			}
			$this->set($this->ns.'nm_auth_ModUCFAuth:getUserFromExternalDB:'.$username, $userData, false, $expire) or core_util_Log::trace('Memcache Failed to write', true);
		}
	}
	public function getModUCFExternalUser($username)
	{
		if($this->memEnabled)
		{		
			return $this->get($this->ns.'nm_auth_ModUCFAuth:getUserFromExternalDB:'.$username);
		}
		return false;
	}	
	
	public function getAuthModClassForUser($userID)
	{
		if($this->memEnabled)
		{
			if($authModClass = $this->get($this->ns.'core_auth_AuthManager:getAuthModuleForUserID:'.$userID))
			{
				return $authModClass;
			}
		}
		return false;
	}

	public function setAuthModClassForUser($userID, $class)
	{
		if($this->memEnabled)
		{
			$this->set($this->ns.'core_auth_AuthManager:getAuthModuleForUserID:'.$userID, $class, false, 0) or core_util_Log::trace('Memcache Failed to write', true);
		}
	}
	
	public function setInstanceData($instData)
	{
		if($this->memEnabled)
		{
			$this->set($this->ns.'nm_los_InstanceData:'.$instData->instID, $instData, false, 0) or core_util_Log::trace('Memcache Failed to write', true);
		}		
	}
	
	public function getInstanceData($instID)
	{
		if($this->memEnabled)
		{
			return $this->get($this->ns.'nm_los_InstanceData:'.$instID);
		}		
	}
	
	public function clearInstanceData($instID)
	{
		if($this->memEnabled)
		{
			$this->delete($this->ns.'nm_los_InstanceData:'.$instID);
		}
	}
	
	public function setPerms($itemID, $itemType, $optUserID, $perms)
	{
		if($this->memEnabled)
		{
			$this->set($this->ns.'nm_los_PermItem:'.$itemID.$itemType.$optUserID, $perms, false, 0) or core_util_Log::trace('Memcache Failed to write', true);
		}
	}
	
	public function getPerms($itemID, $itemType, $optUserID)
	{
		if($this->memEnabled)
		{
			return $this->get($this->ns.'nm_los_PermItem:'.$itemID.$itemType.$optUserID);
		}
	}
	
	public function clearPerms($itemID, $itemType, $optUserID)
	{
		if($this->memEnabled)
		{
			$this->delete($this->ns.'nm_los_PermItem:'.$itemID.$itemType.$optUserID);
		}
	}

	public function setLock($lock)
	{
		if($this->memEnabled)
		{
			$this->set($this->ns.'nm_los_Lock:'.$lock->lockID, $lock, false, $lock->unlockTime-time() ) or core_util_Log::trace('Memcache Failed to write', true);
		}
	}
	
	public function getLock($loid)
	{
		if($this->memEnabled)
		{
			return $this->set($this->ns.'nm_los_Lock:'.$loid);
		}
	}
	
	public function clearLock($loid)
	{
		if($this->memEnabled)
		{
			$this->delete($this->ns.'nm_los_Lock:'.$lock->lockID);
		}		
	}
	
	public function setInstanceScores($instID, $scores)
	{
		if($this->memEnabled)
		{
			$this->set($this->ns.'nm_los_ScoreManager:getScores:'.$instID, $scores, false, 0) or core_util_Log::trace('Memcache Failed to write', true);
		}
	}
	
	public function getInstanceScores($instID)
	{
		if($this->memEnabled)
		{
			return $this->get($this->ns.'nm_los_ScoreManager:getScores:'.$instID);
		}
	}
	
	public function clearInstanceScores($instID)
	{
		if($this->memEnabled)
		{
			$this->delete($this->ns.'nm_los_ScoreManager:getScores:'.$instID);
		}
	}
	
	public function getAllUsers()
	{
		if($this->memEnabled)
		{
			return $this->get($this->ns.'core_auth_AuthManager:getAllUsers');
		}
	}
	
	public function setAllUsers($allUsers)
	{
		if($this->memEnabled)
		{
			$this->set($this->ns.'core_auth_AuthManager:getAllUsers', $allUsers, false, 0) or core_util_Log::trace('Memcache Failed to write', true);
		}
	}
	public function clearAllUsers()
	{
		if($this->memEnabled)
		{
			$this->delete($this->ns.'core_auth_AuthManager:getAllUsers');
		}
	}
	
	public function setAuthModForUser($userName, $authClass)
	{
		if($this->memEnabled)
		{
			$this->set($this->ns.'core_auth_AuthManager:getAuthModuleForUsername:'.$userName, $authClass, false, 0) or core_util_Log::trace('Memcache Failed to write', true);
		}
	}
	
	public function getAuthModForUser($userName)
	{
		if($this->memEnabled)
		{
			return $this->get($this->ns.'core_auth_AuthManager:getAuthModuleForUsername:'.$userName);
		}
	}
	
	public function setUserByID($userID, $user)
	{
		if($this->memEnabled)
		{
			$this->set($this->ns.'core_auth_AuthModule:fetchUserByID:'.$userID, $user, false, 0)  or core_util_Log::trace('Memcache Failed to write', true);
		}
	}
	
	public function getUserByID($userID)
	{
		if($this->memEnabled)
		{
			return $this->get($this->ns.'core_auth_AuthModule:fetchUserByID:'.$userID);
		}
		
	}
	
	public function clearUserByID($userID)
	{
		if($this->memEnabled)
		{
			$this->delete($this->ns.'core_auth_AuthModule:fetchUserByID:'.$userID);
		}		
	}
	
	public function getAllLangs()
	{
		if($this->memEnabled)
		{
			return $this->get($this->ns.'nm_los_LanguageManager:getAllLanguages');
		}		
	}
	
	public function setAllLangs($langs)
	{
		if($this->memEnabled)
		{
			$this->set($this->ns.'nm_los_LanguageManager:getAllLanguages', $langs, false, 0)  or core_util_Log::trace('Memcache Failed to write', true);
		}		
	}

	
	public function setMedia($media)
	{
		if($this->memEnabled)
		{
			$this->set($this->ns.'nm_los_Media:ID'.$media->mediaID, $media, false, 0)  or core_util_Log::trace('Memcache Failed to write', true);
		}		
	}
	
	public function getMedia($mediaID)
	{
		if($this->memEnabled)
		{
			return $this->get($this->ns.'nm_los_Media:ID'.$mediaID);
		}		
	}
	
	public function clearMedia($mediaID)
	{
		if($this->memEnabled)
		{
			return $this->delete($this->ns.'nm_los_Media:ID'.$mediaID);
		}		
	}
	
	public function setLO($loID, $LO)
	{
		if($this->memEnabled)
		{
			// set lo in memcache
			if($this->set($this->ns.'nm_los_LO'.$loID, $LO, false, 0))
			{
				return; // memcache worked, skip the db cache even if it is on
			}
			core_util_Log::trace('Memcache Failed to write ');
		}
	}
	
	public function getLO($loID)
	{
		
		if($this->memEnabled)
		{
			
			// get from memcache
			if($lo = $this->get($this->ns.'nm_los_LO'.$loID))
			{
				return $lo;
			}
			core_util_Log::profile('memcache_missed', 'loID:'.$loID."\n");
		}

		return false;
	}
	
	public function clearLO($loID)
	{
		if($this->memEnabled)
		{
			$this->clearLOMeta($loID);
			return $this->delete($this->ns.'nm_los_LO'.$loID);
		}

	}

	public function setLOMeta($loID, $LO)
	{
		if($this->memEnabled)
		{
			// set lo in memcache
			if($this->set($this->ns.'nm_los_LO:Meta'.$loID, $LO, false, 0))
			{
				return; // memcache worked, skip the db cache even if it is on
			}
			core_util_Log::trace('Memcache Failed to write ');
		}
	}
	
	public function getLOMeta($loID)
	{
		
		if($this->memEnabled)
		{
			// get from memcache
			if($lo = $this->get($this->ns.'nm_los_LO:Meta'.$loID))
			{
				return $lo;
			}
			core_util_Log::profile('memcache_missed', 'loMeta:'.$loID."\n");
		}
		return false;
	}
	
	public function clearLOMeta($loID)
	{
		if($this->memEnabled)
		{
			return $this->delete($this->ns.'nm_los_LO:Meta'.$loID);
		}
	}

	
	public function getUsersInRole($roleID)
	{
		if($this->memEnabled)
		{
			return $this->get($this->ns.'nm_los_RoleManager:getUsersInRole:'.$roleID);
		}
	}
	
	public function clearUsersInRole($roleID)
	{
		if($this->memEnabled)
		{
			$this->delete($this->ns.'nm_los_RoleManager:getUsersInRole:'.$roleID);
		}
	}
	
	public function setUsersInRole($roleID, $usersIndexes)
	{
		if($this->memEnabled)
		{
			$this->set($this->ns.'nm_los_RoleManager:getUsersInRole:'.$roleID, $usersIndexes, false, 0) or core_util_Log::trace('failure writing memcache', true);
		}
	}
	
	public function getInteractionsByInstanceAndUser($instID, $userID)
	{
		if($this->memEnabled)
		{
			return $this->get($this->ns.'nm_los_TrackingManager:getInteractionLogByUserAndInstance:'.$instID.':'.$userID);
		}
		
	}
	
	public function setInteractionsByInstanceAndUser($instID, $userID, $interactions)
	{
		if($this->memEnabled)
		{
			$this->set($this->ns.'nm_los_TrackingManager:getInteractionLogByUserAndInstance:'.$instID.':'.$userID, $interactions, false, 3600) or core_util_Log::trace('failure writing memcache', true);
		}
	}
	
	public function clearInteractionsByInstanceAndUser($instID, $userID)
	{
		if($this->memEnabled)
		{
			$this->delete($this->ns.'nm_los_TrackingManager:getInteractionLogByUserAndInstance:'.$instID.':'.$userID);
		}		
	}
	
	public function setUIDForUserName($userID, $userName)
	{
		if($this->memEnabled)
		{
			$this->set($this->ns.'core_auth_AuthModule:getUIDforUsername'.$userName, $userID, false, 0) or core_util_Log::trace('failure writing memcache', true);
		}

	}
	
	public function getUIDForUserName($userName)
	{
		if($this->memEnabled)
		{
			return $this->get($this->ns.'core_auth_AuthModule:getUIDforUsername'.$userName);
		}
	}
	
	public function getRoleIDFromName($roleName)
	{
		if($this->memEnabled)
		{
			return $this->get($this->ns.'core_auth_AuthModule:getUIDforUsername'.$roleName);
		}		
	}
	
	public function setRoleIDFromName($roleName, $roleID)
	{
		if($this->memEnabled)
		{
			$this->set($this->ns.'nm_los_RoleManager:getRoleID:'.$roleName, $roleID, false, 0) or core_util_Log::trace('failure writing memcache', true);
		}		
	}
	
	public function getQGroup($qGroupID)
	{
		if($this->memEnabled)
		{
			if($result = $this->get($this->ns.'nm_los_QuestionGroup:getFromDB:'.$qGroupID))
			{
				return $result;
			}
			core_util_Log::profile('memcache_missed', 'qGroupID:'.$qGroupID."\n");
		}

		return false;
	}
	
	public function setQGroup($qGroupID, $qGroup)
	{
		if($this->memEnabled)
		{
			if($this->set($this->ns.'nm_los_QuestionGroup:getFromDB:'.$qGroupID, $qGroup, false, 0))
			{
				return; // if memcache works, return, if it fails, failover to db caching
			}
			core_util_Log::trace('failure writing memcache', true);
		}
	}
	
	public function clearQGroup($qGroupID)
	{
		if($this->memEnabled)
		{
			$this->delete($this->ns.'nm_los_QuestionGroup:getFromDB:'.$qGroupID);
		}
	}
	
	public function setPagesForLOID($loID, $pages)
	{
		if($this->memEnabled)
		{
			if($this->set($this->ns.'nm_los_PageManager:getPagesForLOID:'.$loID, $pages, false, 0))
			{
				return; // if memcache works, return, if it fails, failover to db caching
			}
			core_util_Log::trace('failure writing memcache', true);
		}
	}
	
	public function getPagesForLOID($loID)
	{
		if($this->memEnabled)
		{
			if($result = $this->get($this->ns.'nm_los_PageManager:getPagesForLOID:'.$loID))
			{
				return $result;
			}
			core_util_Log::profile('memcache_missed', 'pagesForLOID:'.$loID."\n");
		}		

		return false;
	}
	
	public function setEquivalentAttempt($userID, $loID, $attempts)
	{
		if($this->memEnabled)
		{
			if($this->set($this->ns.'nm_los_AttemptsManager:getEquivalentAttempt:'.$userID.':'.$loID, $attempts, false, 0))
			{
				return;
			}
			core_util_Log::trace('failure writing memcache', true);
		}
	}
	
	public function getEquivalentAttempt($userID, $loID)
	{
		if($this->memEnabled)
		{
			if($result = $this->get($this->ns.'nm_los_AttemptsManager:getEquivalentAttempt:'.$userID.':'.$loID))
			{
				return $result;
			}
			core_util_Log::profile('memcache_missed', 'equivalentAttempts:'.$userID . ' ' .$loID."\n");
		}		
	}
	public function clearEquivalentAttempt($userID, $loID)
	{
		if($this->memEnabled)
		{
			$this->delete($this->ns.'nm_los_AttemptsManager:getEquivalentAttempt:'.$userID.':'.$loID);
		}
	}
	
	public function getPermsForItem($itemType, $itemID)
	{
		if($this->memEnabled)
		{
			if($result = $this->get($this->ns.'nm_los_Perms:getPermsForItem:'.$itemType.':'.$itemID))
			{
				return $result;
			}
			core_util_Log::profile('memcache_missed', 'PermsForItem:'.$itemType . ','. $itemID."\n");
		}
	}
	
	public function setPermsForItem($itemType, $itemID, $perms)
	{
		if($this->memEnabled)
		{
			if($this->set($this->ns.'nm_los_Perms:getPermsForItem:'.$itemType.':'.$itemID, $perms, false, 0))
			{
				return;
			}
			core_util_Log::trace('failure writing memcache', true);
		}
	}
	
	public function clearPermsForItem($itemType, $itemID)
	{
		if($this->memEnabled)
		{
			$this->delete($this->ns.'nm_los_Perms:getPermsForItem:'.$itemType.':'.$itemID);
		}
	}
	
	
	public function clearPermsForGroup($groupID)
	{
		if($this->memEnabled)
		{
			$this->delete($this->ns.'nm_los_Perms:getPermsForGroup:'.$groupID);
		}
	}
	
	public function setPermsForGroup($groupID, $perms)
	{
		if($this->memEnabled)
		{
			if($this->set($this->ns.'nm_los_Perms:getPermsForGroup:'.$groupID, $perms, false, 0))
			{
				return;
			}
			core_util_Log::trace('failure writing memcache', true);
		}
	}
	
	public function getPermsForGroup($groupID)
	{
		if($this->memEnabled)
		{
			if($result = $this->get($this->ns.'nm_los_Perms:getPermsForGroup:'.$groupID))
			{
				return $result;
			}
			core_util_Log::profile('memcache_missed', 'getPermsForGroup:'.$groupID."\n");
		}
	}
	
	
	public function  setPermsForUserToItem($userID, $itemType, $itemID, $perms)
	{
		if($this->memEnabled)
		{
			if($this->set($this->ns.'nm_los_Perms:getPermsFOrUserToItem:'.$userID.':'.$itemType.':'.$itemID, $perms, false, 0))
			{
				return;
			}
			core_util_Log::trace('failure writing memcache', true);
		}
	
	}
	public function  getPermsForUserToItem($userID, $itemType, $itemID)
	{
		if($this->memEnabled)
		{
			if($result = $this->get($this->ns.'nm_los_Perms:getPermsFOrUserToItem:'.$userID.':'.$itemType.':'.$itemID))
			{
				return $result;
			}
			core_util_Log::profile('memcache_missed', 'permsForUserToItem:'.$userID.','.$itemType.','.$itemID."\n");
		}		
	}
	public function  clearPermsForUserToItem($userID, $itemType, $itemID)
	{
		if($this->memEnabled)
		{
			$this->delete($this->ns.'nm_los_Perms:getPermsFOrUserToItem:'.$userID.':'.$itemType.':'.$itemID);
		}
	}
	
	public function setCourseStudents($courseID, $students)
	{
		if($this->memEnabled)
		{
			if($this->set($this->ns.'plugin_UCFCourseDataAPI:getCourseStudents:'.$courseID, $students, false, 3600))
			{
				return;
			}
			core_util_Log::trace('failure writing memcache', true);
		}
	}
	
	public function getCourseStudents($courseID)
	{
		if($this->memEnabled)
		{
			if($result = $this->get($this->ns.'plugin_UCFCourseDataAPI:getCourseStudents:'.$courseID))
			{
				return $result;
			}
			core_util_Log::profile('memcache_missed', 'courseStudents:'.$courseID."\n");
		}
	}
	
	public function clearCourseStudents($courseID)
	{
		if($this->memEnabled)
		{
			$this->delete($this->ns.'plugin_UCFCourseDataAPI:getCourseStudents:'.$courseID);
		}
	}
	
	public function doRateLimit($ip)
	{
		if($this->memEnabled)
		{
			$rate = $this->get($this->ns.'rateLimit:'.$ip);
			if($rate === false)
			{
				$this->set($this->ns.'rateLimit:'.$ip, 0, false, 60);
				return;
			}
			if($rate > 30)
			{
				$this->set($this->ns.'rateLimit:'.$ip, $rate, false, 60); // extend the slow down for a minute
				core_util_Error::getError(6);
				usleep(10000000);
			}
			$this->increment($this->ns.'rateLimit:'.$ip, 1);
		}
		return;
	}
	
	
}

?>