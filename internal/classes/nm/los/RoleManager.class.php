<?php
/**
 * This class handles all logic dealing with Roles
 * @author Luis Estrada <lestrada@mail.ucf.edu>
 */

/**
 * This class handles all logic dealing with Roles
 */
class nm_los_RoleManager extends core_db_dbEnabled
{

	private static $instance;

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
	 * 
	 * 
	 */
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
		$qstr = "INSERT INTO ".cfg_obo_Role::TABLE." SET ".cfg_obo_Role::ROLE."='?'";
		if( !($q = $this->DBM->querySafe($qstr, $roleName)))
		{
			$this->DBM->rollback();
			trace(mysql_error(), true);
			return false;
		}

		return true;
	}
	
	/**
	 * 
	 * 
	 */
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
		$qstr = "DELETE FROM ".cfg_obo_Role::TABLE." WHERE ".cfg_obo_Role::ID." = '?'";
		if( !($q = $this->DBM->querySafe($qstr, $roleID)))
		{
			$this->DBM->rollback();
			//echo "ERROR: deleteRole query 1";
			trace(mysql_error(), true);
			return false;
		}
		
		$qstr = "DELETE FROM ".cfg_obo_Role::MAP_USER_TABLE." WHERE ".cfg_obo_Role::ID." = '?'";
		if( !($q = $this->DBM->querySafe($qstr, $roleID)))
		{
			$this->DBM->rollback();
			//echo "ERROR: deleteRole query 2";
			trace(mysql_error(), true);
			return false;
		}
		
		// clear memcache for all users in this role
		
		core_util_Cache::getInstance()->clearUsersInRole($roleID);
		
		return true;
	}

	/**
	 * 
	 * 
	 */
	public function addUsersToRole($userIDs = "", $roleName = "")
	{
		if(!$this->isAdministrator())
		{
			
			core_util_Error::getError(4); // log error
			//TODO: this should return an error?
			return false;
		}
		return $this->addUsersToRole_SystemOnly($userIDs, $roleName);
	}
	
	public function addUsersToRole_SystemOnly($userIDs = "", $roleName = "")
	{
		if($userIDs == "" || !is_array($userIDs) || $roleName == "" || !is_string($roleName))
		{
			trace('invalid input', true);
			return false;
		}
		if(($roleID = $this->getRoleID($roleName)) == 0)
		{
			trace('invalid role id', true);
			return false;
		}
		foreach($userIDs as $key => $userID)
		{
			if(!is_numeric($userID))
			{
				trace('invalid user id', true);
				return false;
			}
			//if($this->isUserInRole($userID, $roleName))
				//continue;
	
			$qstr = "INSERT IGNORE INTO ".cfg_obo_Role::MAP_USER_TABLE." SET ".cfg_core_User::ID."='?', ".cfg_obo_Role::ID."='?'";
			if( !($q = $this->DBM->querySafe($qstr, $userID, $roleID)))
			{
				$this->DBM->rollback();
				trace(mysql_error(), true);
				return false;
			}
		}
		return true;
	}
	/**
	 * 
	 */
	public function addUserToRole($userID = 0, $roleName = "")
	{
		if($userID == 0 || !is_numeric($userID) || $roleName == "" || !is_string($roleName))
		{
			return false;
		}
		trace(mysql_error(), true);
		$userIDArr = array();
		$userIDArr[] = $userID;
		return $this->addUsersToRole($userIDArr, $roleName);
	}
	
	/**
	 * 
	 * 
	 */
	// TODO: FIX RETURN FOR DB ABSTRACTION
	public function getAllRoles()
	{
		if(!$this->isAdministrator())
		{
			return false;
		}
		$qstr = "SELECT * FROM ".cfg_obo_Role::TABLE;
		
		if( !($q = $this->DBM->query($qstr)))
		{
			trace(mysql_error(), true);
			return false;
		}
		
		$roles = array();
		while($r = $this->DBM->fetch_obj($q))
		{
			$roles[] = $r;
		}
		return $roles;
	}

	/**
	 * 
	 */
	public function getUsersInRole($roleid = 0)
	{
	    if(!$this->isLibraryUser() && !$this->isAdministrator())
	    {
		    return false;
	    }
		// if the roleID ISNT an array of ids
		if(!is_array($roleid))
		{
			// check memcache for the list of user indexes
			
			$usersIndexes = core_util_Cache::getInstance()->getUsersInRole($roleid);

			// no cache, get the list of indexes to cache
			if(!is_array($usersIndexes))
			{
				$qstr = "SELECT ".cfg_core_User::ID." FROM ".cfg_obo_Role::MAP_USER_TABLE." WHERE ".cfg_obo_Role::ID."='?'";

				if(!($q = $this->DBM->querySafe($qstr, $roleid)))
				{
				    trace(mysql_error(), true);
					return false;
				}

				$usersIndexes = array();

				// build array so we can cache the indexes in memory instead of all of the user objects redundantly
				while($r = $this->DBM->fetch_obj($q))
				{
					$usersIndexes[] = $r->{cfg_core_User::ID};
				}
				// store in memcache
				core_util_Cache::getInstance()->setUsersInRole($roleid, $usersIndexes);
			}
			$users = array();
			if(is_array($usersIndexes))
			{
				$userMan = core_auth_AuthManager::getInstance();
				foreach($usersIndexes AS $userID)
				{
				    if($user = $userMan->fetchUserByID($userID))
					{
					    $users[] = $user; 
					}
				}
			}
			return $users;
		}
	
		if(is_array($roleid) && count($roleid) > 0)
		{
			$cacheKey = implode(',',  $roleid);
			// check memcache for the list of user indexes
			
			$usersIndexes = core_util_Cache::getInstance()->getUsersInRole($cacheKey);

			// no cache, get the list of indexes to cache
			if(!is_array($usersIndexes))
			{
				$qstr = "SELECT DISTINCT ".cfg_core_User::ID." FROM ".cfg_obo_Role::MAP_USER_TABLE." WHERE ".cfg_obo_Role::ID." IN (?)";
				if(!($q = $this->DBM->querySafe($qstr, $cacheKey)))
				{
				    trace(mysql_error(), true);
					return false;
				}

				$usersIndexes = array();

				// build array so we can cache the indexes in memory instead of all of the user objects redundantly
				while($r = $this->DBM->fetch_obj($q))
				{
					$usersIndexes[] = $r->{cfg_core_User::ID};
				}
				// store in memcache
				core_util_Cache::getInstance()->setUsersInRole($cacheKey, $usersIndexes);
			}
			$users = array();
			if(is_array($usersIndexes))
			{
				$userMan = core_auth_AuthManager::getInstance();
				foreach($usersIndexes AS $userID)
				{
				    if($user = $userMan->fetchUserByID($userID))
					{
					    $users[] = $user; 
					}
				}
			}

			return $users;
		}
		return false;
	}
	
	// TODO: remove
	public function getAllContentCreators()
	{
	    if(!$this->isContentCreator())
		{
			return false;
		}	
		return $this->getUsersInRole($this->getRoleID(nm_los_Role::CONTENT_CREATOR), true);
	}
	
	/**
	 * 
	 * 
	 */
	/*
	public function isUserInRole($userID = 0, $roleName = "")
	{
		if($userID <= 0)
			return false;
		if($roleName == "" || !is_string($roleName))
			return false;
			
		$qstr = "SELECT COUNT(*) as count FROM ".self::mapping." 
					WHERE userID='?' AND roleID='?'";
		
		if( !($q = $this->DBM->querySafe($qstr, $userID, $this->getRoleID($roleName))))
		{
			$this->DBM->rollback();
			//echo "ERROR: isUserInRole";
			trace("ERROR: isUserInRole".mysql_error());
			//exit;
			return false;
		}

		$r = $this->DBM->fetch_obj($q);
		if($r->count > 0)
			return true;
		else
			return false;
	}
	*/
	
	/**
	 * @author Zachary Berry
	 * @param $userID 
	 * 
	 */
	// TODO: FIX RETURN FOR DB ABSTRACTION
	public function getUserRoles($userID = 0)
	{
		$roles = array();
		$qstr = 	"SELECT R.".cfg_obo_Role::ID.", R.".cfg_obo_Role::ROLE." 
			 FROM ".cfg_obo_Role::MAP_USER_TABLE." AS M, ".cfg_obo_Role::TABLE." AS R
			WHERE M.".cfg_core_User::ID."='?' AND M.".cfg_obo_Role::ID." = R.".cfg_obo_Role::ID."";
		// return logged in user's roles if id is 0 or less, non su users can only use this method
		if($userID <= 0 || $userID == $_SESSION['userID'])
		{
			if(!($q = $this->DBM->querySafe($qstr, $_SESSION['userID'])))
			{
				trace(mysql_error(), true);
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
					trace(mysql_error(), true);
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
	
	/**
	 * 
	 *
	 */
	public function doesUserHaveRole($roles, $userID=0)
	{
		$length = count($roles);
		if($length == 0)
		{
			return false;
		}
		if($userID===0 || !nm_los_Validator::isPosInt($userID) )
		{
			$userID = $_SESSION['userID'];
		}
		
		$qstr = "SELECT COUNT(*) as count FROM ".cfg_obo_Role::MAP_USER_TABLE." WHERE ".cfg_core_User::ID."='?' AND (";

		for($i = 0; $i < $length; $i++)
		{
			if($i == $length - 1)
			{
				$qstr .= "".cfg_obo_Role::ID."='{$this->getRoleID($roles[$i])}'";
			}
			else
			{
				$qstr .= "".cfg_obo_Role::ID."='{$this->getRoleID($roles[$i])}' OR ";
			}
		}
		$qstr .= ")";
		
		if( !($q = $this->DBM->querySafe($qstr, $userID )))
		{
			trace(mysql_error(), true);
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
	 * 
	 * 
	 */
	public function getRoleID($roleName = "")
	{
		if($roleName == "" || !is_string($roleName))
		{
			return 0;
		}
		
		// check memcache
		
		if($roleID = core_util_Cache::getInstance()->getRoleIDFromName($roleName))
		{
			return $roleID;
		}

		$qstr = "SELECT ".cfg_obo_Role::ID." FROM ".cfg_obo_Role::TABLE." WHERE ".cfg_obo_Role::ROLE."='?'";

		if( !($q = $this->DBM->querySafe($qstr, $roleName)))
		{
			trace(mysql_error(), true);
			return false;
		}
		
		$r = $this->DBM->fetch_obj($q);
		
		// store in memcache
		core_util_Cache::getInstance()->setRoleIDFromName($roleName, $r->{cfg_obo_Role::ID});
				
		return $r->{cfg_obo_Role::ID};
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
	
	/**
	 * 
	 * 
	 */
	private function roleExists($roleName = "")
	{
		if($roleName == "" || !is_string($roleName))
		{
			//die("Role name cannot be empty");
		}

		$qstr = "SELECT COUNT(*) as count FROM ".cfg_obo_Role::TABLE." WHERE ".cfg_obo_Role::ROLE." = '?'";

		if( !($q = $this->DBM->querySafe($qstr, $roleName)))
		{
			trace(mysql_error(), true);
			//exit;
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

		
		
		foreach($users as $keyUser => $userID)
		{
			foreach($roles as $keyRole => $roleName)
			{
				//
				$qstr = "DELETE FROM ".cfg_obo_Role::MAP_USER_TABLE." WHERE ".cfg_core_User::ID."='?' AND ".cfg_obo_Role::ID."='?'";
			
				if(!($q = $this->DBM->querySafe($qstr, $userID, $this->getRoleID($roleName))))
				{
				    trace(mysql_error(), true);
					$this->DBM->rollback();
					$success = false;
				}
				else
				{
					
					// clear memcache for all users in this role
					core_util_Cache::getInstance()->clearUsersInRole($this->getRoleID($roleName));
				}
			}
		}
		
		return $success;
	}
	
	/**
	 * @author Zachary Berry
	 */
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
	 *
	 * @author Zachary Berry
	 */
	public function addUsersToRoles_SystemOnly($users = "", $roles ="")
	{
		if($users == "" || $roles == "" || !is_array($users) || !is_array($roles))
		{
			return false;
		}
		$success = true;
		

		

		
		foreach($users as $keyUser => $userID)
		{
			foreach($roles as $keyRole => $roleName)
			{
				$qstr = "INSERT IGNORE INTO ".cfg_obo_Role::MAP_USER_TABLE." SET ".cfg_core_User::ID."='?', ".cfg_obo_Role::ID."='?'";
			
				if(!($q = $this->DBM->querySafe($qstr, $userID, $this->getRoleID($roleName))))
				{
				    trace(mysql_error(), true);
					$this->DBM->rollback();
					$success = false;
				}
				else
				{
					// clear memcache for all users in this role
					core_util_Cache::getInstance()->clearUsersInRole($this->getRoleID($roleName));
				}
			}
		}
		
		return $success;
	}
	
	/**
	 * 
	 * 
	 */
	public function isAdministrator()
	{
		if(!isset($_SESSION['isAdministrator']))
		{
			$_SESSION['isAdministrator'] = $this->doesUserHaveRole(array(nm_los_Role::ADMINISTRATOR, nm_los_Role::SUPER_USER));
		}
		return $_SESSION['isAdministrator'];
	}

	public function isLibraryUser()
	{
		if(!isset($_SESSION['isLibraryUser']))
		{
			$_SESSION['isLibraryUser'] = $this->doesUserHaveRole(array(nm_los_Role::LIBRARY_USER, nm_los_Role::CONTENT_CREATOR, nm_los_Role::SUPER_VIEWER, nm_los_Role::ADMINISTRATOR, nm_los_Role::SUPER_USER));
		}
		return $_SESSION['isLibraryUser'];
	}

	/**
	 * 
	 * 
	 */
	public function isSuperUser()
	{
		if(!isset($_SESSION['isSuperUser']))
		{
			$_SESSION['isSuperUser'] = $this->doesUserHaveRole(array(nm_los_Role::SUPER_USER));
		}
		return $_SESSION['isSuperUser'];
	}
	
	/**
	 * 
	 * 
	 */
	public function isSuperViewer()
	{
		if(!isset($_SESSION['isSuperViewer']))
		{
			$_SESSION['isSuperViewer'] = $this->doesUserHaveRole(array(nm_los_Role::SUPER_VIEWER, nm_los_Role::SUPER_USER));
		}
		return $_SESSION['isSuperViewer'];
	}
	
	/**
	 * 
	 *
	 */
	public function isContentCreator()
	{
		if(!isset($_SESSION['isContentCreator']))
		{
			$_SESSION['isContentCreator'] = $this->doesUserHaveRole(array(nm_los_Role::CONTENT_CREATOR, nm_los_Role::SUPER_USER));
		}
		return $_SESSION['isContentCreator'];
	}
}
?>
