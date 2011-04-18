<?php
namespace obo;
class LOSystem extends \rocketD\db\DBEnabled
{

	public function cleanOrphanData($forceExecute=false, $forceStackID=false){}

	protected function _mergeUsersUpdate($tableName, $qSuffex)
	{
		$return = $this->DBM->querySafe("UPDATE IGNORE $tableName $qSuffex");
		return $return;
	}
	
	public function mergeUsers($userIDFrom, $userIDTo)
	{
		
		$RM = \obo\perms\RoleManager::getInstance();
		if($RM->isSuperUser())
		{
			$this->defaultDBM();
			// TODO: make sure they are su
			
			$AM = \rocketD\auth\AuthManager::getInstance();
		
			$fromUser = $AM->fetchUserByID($userIDFrom);
			$toUser = $AM->fetchUserByID($userIDTo);
			
			if( !($fromUser instanceof \rocketD\auth\User) || !($toUser instanceof \rocketD\auth\User) )
			{
				return \rocketD\util\Error::getError(2);
			}
			$this->DBM->startTransaction();
			$q2 = "SET ".\cfg_core_User::ID." = '$userIDTo' WHERE ".\cfg_core_User::ID." = '$userIDFrom'";
			$success = true;
			$success = $success && $this->_mergeUsersUpdate(\cfg_obo_Answer::TABLE, $q2);
			$success = $success && $this->_mergeUsersUpdate(\cfg_obo_Attempt::TABLE, $q2);
			$success = $success && $this->_mergeUsersUpdate(\cfg_obo_ExtraAttempt::TABLE, $q2);
			$success = $success && $this->_mergeUsersUpdate(\cfg_obo_ComputerData::TABLE, $q2);
			$success = $success && $this->_mergeUsersUpdate(\cfg_obo_Instance::TABLE, $q2);
			$success = $success && $this->_mergeUsersUpdate(\cfg_obo_Lock::TABLE, $q2);
			$success = $success && $this->_mergeUsersUpdate(\cfg_obo_LO::MAP_AUTH_TABLE, $q2);
			$success = $success && $this->_mergeUsersUpdate(\cfg_obo_Perm::TABLE, $q2);
			$success = $success && $this->_mergeUsersUpdate(\cfg_obo_Role::MAP_USER_TABLE, $q2);
			$success = $success && $this->_mergeUsersUpdate(\cfg_obo_Media::TABLE, $q2);
			$success = $success && $this->_mergeUsersUpdate(\cfg_obo_Question::TABLE, $q2);
			$success = $success && $this->_mergeUsersUpdate(\cfg_obo_Track::TABLE, $q2);
			$success = $success && $this->_mergeUsersUpdate(\cfg_obo_Visit::TABLE, $q2);
			$success = $success && $this->_mergeUsersUpdate(\cfg_obo_Perm::TABLE, $q2);

			
			if(!$success)
			{
				$this->DBM->rollBack();
			}
			else
			{
				$this->DBM->commit();
				// clear all cache
				
				\rocketD\util\Cache::getInstance()->clearAllCache();
				// remove old user
				$AM->removeUser($userIDFrom);
				$TM = \obo\log\LogManager::getInstance();
				$TM->trackMergeUser($userIDFrom, $userIDTo);
			}
			return $success;
		}
       
        return \rocketD\util\Error::getError(4);
	}
	
}
?>