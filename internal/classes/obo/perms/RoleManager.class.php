<?php

namespace obo\perms;
class RoleManager extends \rocketD\db\DBEnabled
{
	use \rocketD\Singleton;

	public function createRole($roleName = "")
	{
		if(!$this->isAdministrator())
		{
			return false;
		}
		if($roleName == "" || !is_string($roleName) )
		{
			return false;
		}
		if($this->roleExists($roleName))
		{
			return false;
		}
		if(strlen($roleName) > 255)
		{
			return false;
		}
		$qstr = "INSERT INTO ".\cfg_obo_Role::TABLE." SET ".\cfg_obo_Role::ROLE."='?'";
		if( !($q = $this->DBM->querySafe($qstr, $roleName)))
		{
			$this->DBM->rollback();
			trace($this->DBM->error(), true);
			return false;
		}

		return true;
	}

	public function deleteRole($roleName = "")
	{
		if(!$this->isAdministrator())
		{
			return false;
		}
		if($roleName == "" || !is_string($roleName))
		{
			return false;
		}
		if(($roleID = $this->getRoleID($roleName)) == 0)
		{
			return false;
		}
		$qstr = "DELETE FROM ".\cfg_obo_Role::TABLE." WHERE ".\cfg_obo_Role::ID." = '?'";
		if( !($q = $this->DBM->querySafe($qstr, $roleID)))
		{
			$this->DBM->rollback();
			trace($this->DBM->error(), true);
			return false;
		}

		$qstr = "DELETE FROM ".\cfg_obo_Role::MAP_USER_TABLE." WHERE ".\cfg_obo_Role::ID." = '?'";
		if( !($q = $this->DBM->querySafe($qstr, $roleID)))
		{
			$this->DBM->rollback();
			trace($this->DBM->error(), true);
			return false;
		}

		// clear memcache for all users in this role
		\rocketD\util\Cache::getInstance()->clearUsersInRole($roleID);

		return true;
	}

	public function addUsersToRole($userIDs = "", $roleName = "")
	{
		if(!$this->isAdministrator())
		{

			\rocketD\util\Error::getError(4); // log error
			//TODO: this should return an error?
			return false;
		}
		return $this->addUsersToRoles_SystemOnly($userIDs, [$roleName]);
	}

	public function addUserToRole($userID = 0, $roleName = "")
	{
		if($userID == 0 || !is_numeric($userID) || $roleName == "" || !is_string($roleName))
		{
			return false;
		}
		trace($this->DBM->error(), true);
		$userIDArr = array();
		$userIDArr[] = $userID;
		return $this->addUsersToRole($userIDArr, $roleName);
	}

	// TODO: FIX RETURN FOR DB ABSTRACTION
	public function getAllRoles()
	{
		if(!$this->isAdministrator())
		{
			return false;
		}
		$qstr = "SELECT * FROM ".\cfg_obo_Role::TABLE;

		if( !($q = $this->DBM->query($qstr)))
		{
			trace($this->DBM->error(), true);
			return false;
		}

		$roles = array();
		while($r = $this->DBM->fetch_obj($q))
		{
			$roles[] = $r;
		}
		return $roles;
	}

	public function getUsersInRole($roleid = 0)
	{
		if(!$this->isLibraryUser() && !$this->isAdministrator()) return false;

		if(!is_array($roleid)) $roleid = [$roleid];

		if(count($roleid) < 1) return false;

		foreach ($roleid as $key => $value)
		{
			if(!is_numeric($value)) return false;
		}

		$cacheKey = implode(',',  $roleid);
		$users = \rocketD\util\Cache::getInstance()->getUsersInRole($cacheKey);
		// no cache, get the list of indexes to cache
		if(!is_array($users))
		{
			$qstr = "SELECT U.userID, login, first, last, mi FROM obo_users AS U, obo_map_roles_to_user AS M WHERE U.userID = M.userID AND M.roleID IN (?) GROUP BY U.userID";
			if(!($q = $this->DBM->querySafe($qstr, $cacheKey)))
			{
				trace($this->DBM->error(), true);
				return false;
			}
			$users = $this->DBM->getAllRows($q, 'assoc');
			\rocketD\util\Cache::getInstance()->setUsersInRole($cacheKey, $users);
		}

		return $users;
	}

	// TODO: remove
	public function getAllContentCreators()
	{
		if(!$this->isContentCreator())
		{
			return false;
		}
		return $this->getUsersInRole($this->getRoleID(\obo\perms\Role::CONTENT_CREATOR), true);
	}

	// TODO: FIX RETURN FOR DB ABSTRACTION
	public function getUserRoles($userID = 0)
	{
		$roles = array();
		$qstr = "SELECT R.".\cfg_obo_Role::ID.", R.".\cfg_obo_Role::ROLE."
			FROM ".\cfg_obo_Role::MAP_USER_TABLE." AS M, ".\cfg_obo_Role::TABLE." AS R
			WHERE M.".\cfg_core_User::ID."='?' AND M.".\cfg_obo_Role::ID." = R.".\cfg_obo_Role::ID."";
		// return logged in user's roles if id is 0 or less, non su users can only use this method
		if($userID <= 0 || $userID == $_SESSION['userID'])
		{
			if(!($q = $this->DBM->querySafe($qstr, $_SESSION['userID'])))
			{
				trace($this->DBM->error(), true);
				return false;
			}

			while($r = $this->DBM->fetch_obj($q))
			{
				$roles[] = $r;
			}
		}
		// su can return a anyone's roles
		else
		{
				//unset( $_SESSION['isSuperUser']);
			if($this->isSuperUser())
			{
				if(!($q = $this->DBM->querySafe($qstr, $userID)))
				{
					trace($this->DBM->error(), true);
					return false;
				}

				while($r = $this->DBM->fetch_obj($q))
				{
					$roles[] = $r;
				}
			}
			else
			{
				return false;
			}
		}
		return $roles;
	}

	public function doesUserHaveRole($roles, $userID=0)
	{
		$length = count($roles);
		if($length == 0)
		{
			return false;
		}
		if($userID===0 || !\obo\util\Validator::isPosInt($userID) )
		{
			if(!isset($_SESSION['userID']))
			{
				return false;
			}

			$userID = $_SESSION['userID'];
		}

		$qstr = "SELECT COUNT(*) as count FROM ".\cfg_obo_Role::MAP_USER_TABLE." WHERE ".\cfg_core_User::ID."='?' AND (";

		for($i = 0; $i < $length; $i++)
		{
			if($i == $length - 1)
			{
				$qstr .= "".\cfg_obo_Role::ID."='{$this->getRoleID($roles[$i])}'";
			}
			else
			{
				$qstr .= "".\cfg_obo_Role::ID."='{$this->getRoleID($roles[$i])}' OR ";
			}
		}
		$qstr .= ")";

		if( !($q = $this->DBM->querySafe($qstr, $userID )))
		{
			trace($this->DBM->error(), true);
			return false;
		}

		$r = $this->DBM->fetch_obj($q);
		if($r->count > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function getRoleID($roleName = "")
	{
		if($roleName == "" || !is_string($roleName))
		{
			return 0;
		}

		if($roleID = \rocketD\util\Cache::getInstance()->getRoleIDFromName($roleName))
		{
			return $roleID;
		}

		$qstr = "SELECT ".\cfg_obo_Role::ID." FROM ".\cfg_obo_Role::TABLE." WHERE ".\cfg_obo_Role::ROLE."='?'";

		if( !($q = $this->DBM->querySafe($qstr, $roleName)))
		{
			trace($this->DBM->error(), true);
			return false;
		}

		$r = $this->DBM->fetch_obj($q);

		\rocketD\util\Cache::getInstance()->setRoleIDFromName($roleName, $r->{\cfg_obo_Role::ID});

		return $r->{\cfg_obo_Role::ID};
	}

	public function getRoleIDsFromNames($names)
	{
		if(!is_array($names))
		{
			$t = $names;
			$names = array();
			$names[] = $t;
		}
		if(count($names) < 1)
		{
			return false;
		}

		$ids = array();
		foreach($names AS $name)
		{
			$ids[] = $this->getRoleID($name);
		}

		return array_unique($ids);
	}

	private function roleExists($roleName = "")
	{
		$qstr = "SELECT COUNT(*) as count FROM ".\cfg_obo_Role::TABLE." WHERE ".\cfg_obo_Role::ROLE." = '?'";

		if( !($q = $this->DBM->querySafe($qstr, $roleName)))
		{
			trace($this->DBM->error(), true);
			return false;
		}

		$r = $this->DBM->fetch_obj($q);
		if($r->count > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Enter description here...
	 *
	 * @param Array $users
	 * @param Array $roles
	 * @return boolean
	 */
	public function removeUsersFromRoles($users = "", $roles = "")
	{
		// This needs to be executable by the system
		if(!$this->isAdministrator())
		{
			return false;
		}
		return $this->removeUsersFromRoles_SystemOnly($users, $roles);
	}

	/**
	 * This function is only for the system to call, it ignores administrator rights
	 *
	 * @param Array $users
	 * @param Array $roles
	 * @return boolean
	 */
	public function removeUsersFromRoles_SystemOnly($users = "", $roles ="")
	{
		if($users == "" || $roles == "" || !is_array($users) || !is_array($roles))
		{
			return false;
		}
		$success = true;

		foreach($users as $userID)
		{
			foreach($roles as $roleName)
			{
				$qstr = "DELETE FROM ".\cfg_obo_Role::MAP_USER_TABLE." WHERE ".\cfg_core_User::ID."='?' AND ".\cfg_obo_Role::ID."='?'";

				if(!($q = $this->DBM->querySafe($qstr, $userID, $this->getRoleID($roleName))))
				{
					trace($this->DBM->error(), true);
					$this->DBM->rollback();
					$success = false;
				}
				else
				{
					\rocketD\util\Cache::getInstance()->clearUsersInRole($this->getRoleID($roleName));
				}
			}
		}

		return $success;
	}

	public function addUsersToRoles($users = "", $roles = "")
	{
		if(!$this->isAdministrator())
		{
			return false;
		}
		return $this->addUsersToRoles_SystemOnly($users, $roles);
	}

	/**
	 * This function is only for the system to call, it ignores administrator rights
	 */
	public function addUsersToRoles_SystemOnly($userIDs = "", $roles ="")
	{
		$rocketDRM = \rocketD\perms\RoleManager::getInstance();
		return $rocketDRM->addUsersToRoles_SystemOnly($userIDs, $roles);
	}

	public function setCronRole()
	{
		$_SESSION['oboCronRole'] = true;
	}

	public function isAdministrator()
	{
		if(!isset($_SESSION['isAdministrator']))
		{
			$_SESSION['isAdministrator'] = $this->doesUserHaveRole(array(\obo\perms\Role::ADMINISTRATOR, \obo\perms\Role::SUPER_USER));
		}
		return $_SESSION['isAdministrator'];
	}

	public function isLibraryUser()
	{
		if(!isset($_SESSION['isLibraryUser']))
		{
			$_SESSION['isLibraryUser'] = $this->doesUserHaveRole(array(\obo\perms\Role::LIBRARY_USER, \obo\perms\Role::CONTENT_CREATOR, \obo\perms\Role::SUPER_VIEWER, \obo\perms\Role::ADMINISTRATOR, \obo\perms\Role::SUPER_USER));
		}
		return $_SESSION['isLibraryUser'];
	}

	public function isSuperUser()
	{
		if(!isset($_SESSION['isSuperUser']))
		{
			$_SESSION['isSuperUser'] = $this->doesUserHaveRole(array(\obo\perms\Role::SUPER_USER));
		}
		return $_SESSION['isSuperUser'];
	}

	public function isStatsUser()
	{
		if(!isset($_SESSION['isStatsUser']))
		{
			$_SESSION['isStatsUser'] = $this->doesUserHaveRole(array(\obo\perms\Role::SUPER_STATS, \obo\perms\Role::SUPER_USER));
		}
		return $_SESSION['isStatsUser'];
	}

	public function isSuperStats()
	{
		if(!isset($_SESSION['isSuperStats']))
		{
			$_SESSION['isSuperStats'] = $this->doesUserHaveRole(array(\obo\perms\Role::SUPER_STATS, \obo\perms\Role::SUPER_USER));
		}
		return $_SESSION['isSuperStats'];
	}

	public function isSuperViewer()
	{
		if(!isset($_SESSION['isSuperViewer']))
		{
			$_SESSION['isSuperViewer'] = $this->doesUserHaveRole(array(\obo\perms\Role::SUPER_VIEWER, \obo\perms\Role::SUPER_USER));
		}
		return $_SESSION['isSuperViewer'];
	}

	public function isContentCreator()
	{
		if(!isset($_SESSION['isContentCreator']))
		{
			$_SESSION['isContentCreator'] = $this->doesUserHaveRole(array(\obo\perms\Role::CONTENT_CREATOR, \obo\perms\Role::SUPER_USER));
		}
		return $_SESSION['isContentCreator'];
	}
}
