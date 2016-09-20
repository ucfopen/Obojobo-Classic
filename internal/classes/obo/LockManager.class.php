<?php

namespace obo;
class LockManager extends \rocketD\db\DBEnabled
{
	// TODO: move lockTime to $config
	const lockTime = 300;
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
	 * Checks to see if a lock exists for the specific LO
	 * @param $loID (Number)
	 * @return (Lock) The existing lock
	 * @return bool FALSE if there is an error
	 */
	public function lockExists($loID = 0)
	{
		if(!\obo\util\Validator::isPosInt($loID))
		{
			return \rocketD\util\Error::getError(2);
		}

		// get from cache
		if($lock = \rocketD\util\Cache::getInstance()->getLock($loID))
		{
			return $lock;
		}

		$qstr = "SELECT * FROM ".\cfg_obo_Lock::TABLE." WHERE ".\cfg_obo_LO::ID." = '?' LIMIT 1";
		$q = $this->DBM->querySafe($qstr, $loID);

		if( !$r = $this->DBM->fetch_obj($q) )
		{
			return false; // no lock
		}

		$userMan = \rocketD\auth\AuthManager::getInstance();
		$lockObj = new \obo\Lock($r->{\cfg_obo_Lock::ID}, $r->{\cfg_obo_LO::ID}, $userMan->fetchUserByID($r->{\cfg_core_User::ID}), $r->{\cfg_obo_Lock::UNLOCK_TIME});
		\rocketD\util\Cache::getInstance()->setLock($lockObj);
		return $lockObj;
	}

	/**
	 * Locks the LO, updates the lock if the same user is lockng it
	 * If another user try to lock it, if the unlock time has passed then the
	 * lock will be updated with the new user, otherwise it returns the lock
	 *
	 * @param $loID (Number)
	 * @return (Lock) the new lock object
	 * @return (bool) FALSE if loID is not valid
	 */
	public function lockLO($loID = 0)
	{
		if(!\obo\util\Validator::isPosInt($loID))
		{
			return \rocketD\util\Error::getError(2);
		}

		//check if user is a Super User
		$roleMan = \obo\perms\RoleManager::getInstance();
		if(!$roleMan->isSuperUser())
		{
			if(!$roleMan->isLibraryUser())
			{
				return \rocketD\util\Error::getError(4);
			}

			$permMan = \obo\perms\PermissionsManager::getInstance();
			if(!$permMan->getMergedPerm($loID, \cfg_obo_Perm::TYPE_LO, \cfg_obo_Perm::WRITE, $_SESSION['userID']))
			{
				return \rocketD\util\Error::getError(4);
			}
		}

		$userID = $_SESSION['userID'];
		$makeNewLock = false;
		$updateLock = false;

		// if lock exists
		if($lockObj = $this->lockExists($loID))
		{
			if($lockObj->user->userID == $userID) $updateLock = true; // lock belongs to user, update timeout
			if($lockObj->user->userID != $userID && $lockObj->unlockTime < time()) $updateLock = true; // lock doesnt belong to user, but is timed out
		}
		else
		{
			$makeNewLock = true; // no current locks, make one
		}

		// store changes
		if($updateLock)
		{
			$userMan = \rocketD\auth\AuthManager::getInstance();
			$lockObj->unlockTime = time()+self::lockTime; //update the time
			$lockObj->user = $userMan->fetchUserByID($userID);

			//update the lock
			$qstr = "UPDATE ".\cfg_obo_Lock::TABLE." SET ".\cfg_core_User::ID."='?', ".\cfg_obo_Lock::UNLOCK_TIME."='?' WHERE ".\cfg_obo_Lock::ID."='?' LIMIT 1";
			if( !($q = $this->DBM->querySafe($qstr, $userID, $lockObj->unlockTime, $lockObj->lockID)) )
			{
				$this->DBM->rollback();
				return false;
			}

			\rocketD\util\Cache::getInstance()->setLock($lockObj);
		}
		else if($makeNewLock)
		{
			$userMan = \rocketD\auth\AuthManager::getInstance();
			$lockObj = new \obo\Lock(0, $loID, $userMan->fetchUserByID($userID), time()+self::lockTime);

			$qstr = "INSERT INTO ".\cfg_obo_Lock::TABLE." SET ".\cfg_obo_LO::ID."='?', ".\cfg_obo_Lock::UNLOCK_TIME."='{$lockObj->unlockTime}', ".\cfg_core_User::ID."='?'";
			if( !($q = $this->DBM->querySafe($qstr, $loID, $userID)) )
			{
				$this->DBM->rollback();
				return false;
			}
			$lockObj->lockID = $this->DBM->insertID;

			\rocketD\util\Cache::getInstance()->setLock($lockObj);
		}

		return $lockObj; //return the new lock
	}

	/**
	 * Unlocks an LO
	 *
	 * @param $loID (Number)
	 * @return bool False if the loID is not valid, true if the lo was unlocked
	 */
	public function unlockLO($loID = 0)
	{

		$roleMan = \obo\perms\RoleManager::getInstance();
		//check if user is a Super User
		if(!$roleMan->isSuperUser())
		{
			if(!$roleMan->isLibraryUser())
			{


				return \rocketD\util\Error::getError(4);
			}
			$permMan = \obo\perms\PermissionsManager::getInstance();
			if(!$permMan->getMergedPerm($loID, \cfg_obo_Perm::TYPE_LO, \cfg_obo_Perm::WRITE, $_SESSION['userID']))
			{


				return \rocketD\util\Error::getError(4);
			}
		}

		$userID = $_SESSION['userID'];

		$lock = $this->lockExists($loID);

		if($lock instanceof \obo\Lock)
		{
			// its a lock, delete it
			$this->DBM->querySafe("DELETE FROM ".\cfg_obo_Lock::TABLE." WHERE ".\cfg_obo_Lock::ID."='?' LIMIT 1", $lock->lockID);

			\rocketD\util\Cache::getInstance()->clearLock($lock->lockID);
			return true;
		}
		// its false - which just meands it didnt exist, return true cause theres no lock anyway
		if($lock === false)
		{
			return true;
		}

		return $lock; // if $lock throws error, return that
	}

	public function cleanLocks()
	{
		$qstr = "DELETE FROM ".\cfg_obo_Lock::TABLE." WHERE ".\cfg_obo_Lock::UNLOCK_TIME." < ".time();

		if(!($q = $this->DBM->query($qstr)))
		{
			$this->DBM->rollback();
			trace(mysql_error(), true);
			//exit;
			return false;
		}
	}
}
