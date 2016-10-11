<?php
namespace obo\util;
class Cache extends \rocketD\util\RDMemcache
{
	use \rocketD\Singleton;

	private $ns = 'oBo_';
	private $dbCache = false;
	private $DBM;

	public function clearAllCache()
	{

		$this->connectMemCache();
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
			$this->set($this->ns.'nm_auth_ModUCFAuth:getUserFromExternalDB:'.$username, $userData, false, $expire) or trace('Memcache Failed to write', true);
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
			if($authModClass = $this->get($this->ns.'\rocketD\auth\AuthManager:getAuthModuleForUserID:'.$userID))
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
			$this->set($this->ns.'\rocketD\auth\AuthManager:getAuthModuleForUserID:'.$userID, $class, false, 0) or trace('Memcache Failed to write', true);
		}
	}

	public function setInstanceData(\obo\lo\InstanceData $instData)
	{
		if($this->memEnabled)
		{
			$this->set($this->ns.'\obo\lo\InstanceData:'.$instData->instID, $instData, false, 0) or trace('Memcache Failed to write', true);
		}
	}

	public function getInstanceData($instID)
	{
		if($this->memEnabled)
		{
			 return $this->get($this->ns.'\obo\lo\InstanceData:'.$instID);
		}
	}

	public function clearInstanceData($instID)
	{
		if($this->memEnabled)
		{
			$this->delete($this->ns.'\obo\lo\InstanceData:'.$instID);
		}
	}

	public function setPerms($itemID, $itemType, $optUserID, $perms)
	{
		if($this->memEnabled)
		{
			$this->set($this->ns.'nm_los_PermItem:'.$itemID.$itemType.$optUserID, $perms, false, 0) or trace('Memcache Failed to write', true);
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
			$this->set($this->ns.'\obo\Lock:'.$lock->lockID, $lock, false, $lock->unlockTime-time() ) or trace('Memcache Failed to write', true);
		}
	}

	public function getLock($loid)
	{
		if($this->memEnabled)
		{
			return $this->get($this->ns.'\obo\Lock:'.$loid);
		}
	}

	public function clearLock($loid)
	{
		if($this->memEnabled)
		{
			$this->delete($this->ns.'\obo\Lock:'.$lock->lockID);
		}
	}

	public function setScoresForAllUsers($instID, $scores)
	{
		if($this->memEnabled)
		{
			$this->set($this->ns.'\obo\ScoreManager:getScores:'.$instID, $scores, false, 0) or trace('Memcache Failed to write', true);
		}
	}

	public function getScoresForAllUsers($instID)
	{
		if($this->memEnabled)
		{
			return $this->get($this->ns.'\obo\ScoreManager:getScores:'.$instID);
		}
	}

	public function clearScoresForAllUsers($instID)
	{
		if($this->memEnabled)
		{
			$this->delete($this->ns.'\obo\ScoreManager:getScores:'.$instID);
		}
	}

	public function setScoresForUser($instID, $userID, $scores)
	{
		if($this->memEnabled)
		{
			$this->set($this->ns.'\obo\ScoreManager:getUserScores:'.$instID . ':' . $userID, $scores, false, 0) or trace('Memcache Failed to write', true);
		}
	}

	public function getScoresForUser($instID, $userID)
	{
		if($this->memEnabled)
		{
			return $this->get($this->ns.'\obo\ScoreManager:getUserScores:'.$instID . ':' . $userID);
		}
	}

	public function clearScoresForUser($instID, $userID)
	{
		if($this->memEnabled)
		{
			$this->delete($this->ns.'\obo\ScoreManager:getUserScores:'.$instID . ':' . $userID);
		}
	}



	public function getAllUsers()
	{
		if($this->memEnabled)
		{
			return $this->get($this->ns.'\rocketD\auth\AuthManager:getAllUsers');
		}
	}

	public function setAllUsers($allUsers)
	{
		if($this->memEnabled)
		{
			$this->set($this->ns.'\rocketD\auth\AuthManager:getAllUsers', $allUsers, false, 0) or trace('Memcache Failed to write', true);
		}
	}
	public function clearAllUsers()
	{
		if($this->memEnabled)
		{
			$this->delete($this->ns.'\rocketD\auth\AuthManager:getAllUsers');
		}
	}

	public function setAuthModForUser($userName, $authClass)
	{
		if($this->memEnabled)
		{
			$this->set($this->ns.'\rocketD\auth\AuthManager:getAuthModuleForUsername:'.$userName, $authClass, false, 0) or trace('Memcache Failed to write', true);
		}
	}

	public function getAuthModForUser($userName)
	{
		if($this->memEnabled)
		{
			return $this->get($this->ns.'\rocketD\auth\AuthManager:getAuthModuleForUsername:'.$userName);
		}
	}

	public function setUserByID($userID, $user)
	{
		if($this->memEnabled)
		{
			$this->set($this->ns.'\rocketD\auth\AuthModule:fetchUserByID:'.$userID, $user, false, 0)  or trace('Memcache Failed to write', true);
		}
	}

	public function getUserByID($userID)
	{
		if($this->memEnabled)
		{
			return $this->get($this->ns.'\rocketD\auth\AuthModule:fetchUserByID:'.$userID);
		}

	}

	public function clearUserByID($userID)
	{
		if($this->memEnabled)
		{
			$this->delete($this->ns.'\rocketD\auth\AuthModule:fetchUserByID:'.$userID);
		}
	}

	public function getAllLangs()
	{
		if($this->memEnabled)
		{
			return $this->get($this->ns.'\obo\lo\LanguageManager:getAllLanguages');
		}
	}

	public function setAllLangs($langs)
	{
		if($this->memEnabled)
		{
			$this->set($this->ns.'\obo\lo\LanguageManager:getAllLanguages', $langs, false, 0)  or trace('Memcache Failed to write', true);
		}
	}


	public function setMedia($media)
	{
		if($this->memEnabled)
		{
			$this->set($this->ns.'\obo\lo\Media:ID'.$media->mediaID, $media, false, 0)  or trace('Memcache Failed to write', true);
		}
	}

	public function getMedia($mediaID)
	{
		if($this->memEnabled)
		{
			return $this->get($this->ns.'\obo\lo\Media:ID'.$mediaID);
		}
	}

	public function clearMedia($mediaID)
	{
		if($this->memEnabled)
		{
			return $this->delete($this->ns.'\obo\lo\Media:ID'.$mediaID);
		}
	}

	public function setLO($loID, $LO)
	{
		if($this->memEnabled)
		{
			// set lo in memcache
			$this->set($this->ns.'\obo\lo\LO'.$loID, $LO, false, 0) or trace('Memcache Failed to write', true);
		}
	}

	public function getLO($loID)
	{

		if($this->memEnabled)
		{

			// get from memcache
			if($lo = $this->get($this->ns.'\obo\lo\LO'.$loID))
			{
				return $lo;
			}
		}

		return false;
	}

	public function clearLO($loID)
	{
		if($this->memEnabled)
		{
			$this->clearLOMeta($loID);
			return $this->delete($this->ns.'\obo\lo\LO'.$loID);
		}

	}

	public function setLOMeta($loID, $LO)
	{
		if($this->memEnabled)
		{
			// set lo in memcache
			$this->set($this->ns.'\obo\lo\LO:Meta'.$loID, $LO, false, 0) or trace('Memcache Failed to write', true);
		}
	}

	public function getLOMeta($loID)
	{

		if($this->memEnabled)
		{
			// get from memcache
			if($lo = $this->get($this->ns.'\obo\lo\LO:Meta'.$loID))
			{
				return $lo;
			}
		}
		return false;
	}

	public function clearLOMeta($loID)
	{
		if($this->memEnabled)
		{
			return $this->delete($this->ns.'\obo\lo\LO:Meta'.$loID);
		}
	}


	public function getUsersInRole($roleID)
	{
		if($this->memEnabled)
		{
			return $this->get($this->ns.'\obo\perms\RoleManager:getUsersInRole:'.$roleID);
		}
	}

	public function clearUsersInRole($roleID)
	{
		if($this->memEnabled)
		{
			$this->delete($this->ns.'\obo\perms\RoleManager:getUsersInRole:'.$roleID);
		}
	}

	public function setUsersInRole($roleID, $usersIndexes)
	{
		if($this->memEnabled)
		{
			$this->set($this->ns.'\obo\perms\RoleManager:getUsersInRole:'.$roleID, $usersIndexes, false, 900) or trace('failure writing memcache', true);
		}
	}

	public function getInteractionsByInstanceAndUser($instID, $userID)
	{
		if($this->memEnabled)
		{
			return $this->get($this->ns.'\obo\log\LogManager:getInteractionLogByUserAndInstance:'.$instID.':'.$userID);
		}

	}

	public function setInteractionsByInstanceAndUser($instID, $userID, $interactions)
	{
		if($this->memEnabled)
		{
			$this->set($this->ns.'\obo\log\LogManager:getInteractionLogByUserAndInstance:'.$instID.':'.$userID, $interactions, false, 3600) or trace('failure writing memcache', true);
		}
	}

	public function clearInteractionsByInstanceAndUser($instID, $userID)
	{
		if($this->memEnabled)
		{
			$this->delete($this->ns.'\obo\log\LogManager:getInteractionLogByUserAndInstance:'.$instID.':'.$userID);
		}
	}

	// public function getInteractionsByVisit($visitID)
	// {
	// 	if($this->memEnabled)
	// 	{
	// 		return $this->get($this->ns.'\obo\log\LogManager:getInteractionLogByVisit:'.$visitID);
	// 	}
	//
	// }
	//
	// public function setInteractionsByVisit($visitID, $interactions)
	// {
	// 	if($this->memEnabled)
	// 	{
	// 		$this->set($this->ns.'\obo\log\LogManager:getInteractionLogByVisit:'.$visitID, $interactions, false, 3600) or trace('failure writing memcache', true);
	// 	}
	// }
	//
	// public function clearInteractionsByVisit($visitID)
	// {
	// 	if($this->memEnabled)
	// 	{
	// 		$this->delete($this->ns.'\obo\log\LogManager:getInteractionLogByVisit:'.$visitID);
	// 	}
	// }

	public function setUIDForUserName($userID, $userName)
	{
		if($this->memEnabled)
		{
			$this->set($this->ns.'\rocketD\auth\AuthModule:getUIDforUsername'.$userName, $userID, false, 0) or trace('failure writing memcache', true);
		}

	}

	public function getUIDForUserName($userName)
	{
		if($this->memEnabled)
		{
			return $this->get($this->ns.'\rocketD\auth\AuthModule:getUIDforUsername'.$userName);
		}
	}

	public function getRoleIDFromName($roleName)
	{
		if($this->memEnabled)
		{
			return $this->get($this->ns.'\rocketD\auth\AuthModule:getUIDforUsername'.$roleName);
		}
	}

	public function setRoleIDFromName($roleName, $roleID)
	{
		if($this->memEnabled)
		{
			$this->set($this->ns.'\obo\perms\RoleManager:getRoleID:'.$roleName, $roleID, false, 0) or trace('failure writing memcache', true);
		}
	}

	public function getQGroup($qGroupID)
	{
		if($this->memEnabled)
		{
			if($result = $this->get($this->ns.'\obo\lo\QuestionGroup:getFromDB:'.$qGroupID))
			{
				return $result;
			}
		}

		return false;
	}

	public function setQGroup($qGroupID, $qGroup)
	{
		if($this->memEnabled)
		{
			$this->set($this->ns.'\obo\lo\QuestionGroup:getFromDB:'.$qGroupID, $qGroup, false, 0) or trace('Memcache Failed to write', true);
		}
	}

	public function clearQGroup($qGroupID)
	{
		if($this->memEnabled)
		{
			$this->delete($this->ns.'\obo\lo\QuestionGroup:getFromDB:'.$qGroupID);
		}
	}

	public function setPagesForLOID($loID, $pages)
	{
		if($this->memEnabled)
		{
			$this->set($this->ns.'\obo\lo\PageManager:getPagesForLOID:'.$loID, $pages, false, 0)  or trace('Memcache Failed to write', true);
		}
	}

	public function getPagesForLOID($loID)
	{
		if($this->memEnabled)
		{
			if($result = $this->get($this->ns.'\obo\lo\PageManager:getPagesForLOID:'.$loID))
			{
				return $result;
			}
		}

		return false;
	}

	public function setEquivalentAttempt($userID, $loID, $attempts)
	{
		if($this->memEnabled)
		{
			$this->set($this->ns.'\obo\AttemptsManager:getEquivalentAttempt:'.$userID.':'.$loID, $attempts, false, 0) or trace('Memcache Failed to write', true);
		}
	}

	public function getEquivalentAttempt($userID, $loID)
	{
		if($this->memEnabled)
		{
			if($result = $this->get($this->ns.'\obo\AttemptsManager:getEquivalentAttempt:'.$userID.':'.$loID))
			{
				return $result;
			}
		}
	}
	public function clearEquivalentAttempt($userID, $loID)
	{
		if($this->memEnabled)
		{
			$this->delete($this->ns.'\obo\AttemptsManager:getEquivalentAttempt:'.$userID.':'.$loID);
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
		}
	}

	public function setPermsForItem($itemType, $itemID, $perms)
	{
		if($this->memEnabled)
		{
			$this->set($this->ns.'nm_los_Perms:getPermsForItem:'.$itemType.':'.$itemID, $perms, false, 0) or trace('Memcache Failed to write', true);
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
			$this->set($this->ns.'nm_los_Perms:getPermsForGroup:'.$groupID, $perms, false, 0) or trace('Memcache Failed to write', true);
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
		}
	}


	public function  setPermsForUserToItem($userID, $itemType, $itemID, $perms)
	{
		if($this->memEnabled)
		{
			$this->set($this->ns.'nm_los_Perms:getPermsFOrUserToItem:'.$userID.':'.$itemType.':'.$itemID, $perms, false, 0) or trace('Memcache Failed to write', true);
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
			$this->set($this->ns.'plugin_UCFCourseDataAPI:getCourseStudents:'.$courseID, $students, false, 3600)  or trace('Memcache Failed to write', true);
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
				$this->set($this->ns.'rateLimit:'.$ip, 0, false, 60)  or trace('Memcache Failed to write', true);
				return;
			}
			if($rate > 30)
			{
				$this->set($this->ns.'rateLimit:'.$ip, $rate, false, 60) or trace('Memcache Failed to write', true); // extend the slow down for a minute
				\rocketD\util\Error::getError(6);
				usleep(10000000);
			}
			$this->increment($this->ns.'rateLimit:'.$ip, 1);
		}
		return;
	}
}
