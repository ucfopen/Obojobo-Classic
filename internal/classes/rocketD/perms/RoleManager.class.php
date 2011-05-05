<?php
/**
 * This class handles all logic dealing with Roles
 * @author Luis Estrada <lestrada@mail.ucf.edu>
 */

/**
 * This class handles all logic dealing with Roles
 */
namespace rocketD\perms;
class RoleManager extends \rocketD\db\DBEnabled
{
	private static $instance; // singleton instance reference
	
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
		if($roleName == "" || !is_string($roleName))
		{
			return false;
		}
		if($this->roleExists($roleName))
		{
			return false;
		}
		if(strlen($roleName) > 255 )
		{
			return false;
		}
		$qstr = "INSERT INTO ".\cfg_core_Role::TABLE." SET ".\cfg_core_Role::ROLE."='?'";
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
		// delete role
		$qstr = "DELETE FROM ".\cfg_core_Role::TABLE." WHERE ".\cfg_core_Role::ID." = '?'";
		if( !($q = $this->DBM->querySafe($qstr, $roleID)))
		{
			$this->DBM->rollback();
			trace(mysql_error(), true);
			return false;
		}
		// delete user <-> role mapping
		$qstr = "DELETE FROM ".\cfg_core_Role::MAP_USER_TABLE." WHERE ".\cfg_core_Role::ID." = '?'";
		if( !($q = $this->DBM->querySafe($qstr, $roleID)))
		{
			$this->DBM->rollback();
			trace(mysql_error(), true);
			return false;
		}
		// delete role <-> permission mapping
		$qstr = "DELETE FROM ".\cfg_core_Role::MAP_PERM." WHERE ".\cfg_core_Role::ID." = '?'";
		if( !($q = $this->DBM->querySafe($qstr, $roleID)))
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
	public function addUsersToRole($userIDs = "", $roleName = "")
	{
		if(!$this->isAdministrator())
		{
			return false;
		}
		return $this->addUsersToRole_SystemOnly($userIDs, $roleName);
	}
	
	public function addUsersToRole_SystemOnly($userIDs = "", $roleName = "")
	{
		if($userIDs == "" || !is_array($userIDs) || $roleName == "" || !is_string($roleName))
		{
			return false;
		}
		if(($roleID = $this->getRoleID($roleName)) == 0)
		{
			return false;
		}
		foreach($userIDs as $key => $userID)
		{
			if(!is_numeric($userID))
			{
				return false;
			}
			//if($this->isUserInRole($userID, $roleName))
				//continue;
	
			$qstr = "INSERT IGNORE INTO ".\cfg_core_Role::MAP_USER_TABLE." SET ".\cfg_core_User::ID." ='?', ".\cfg_core_Role::ID."='?'";
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
		$qstr = "SELECT * FROM ".\cfg_core_Role::TABLE;
		
		if( !($q = $this->DBM->query($qstr)))
		{
			$this->DBM->rollback();
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
		// TODO: is this right? can only admins know this?
		/*
	    if(!$this->isAdministrator())
	    {
		    return false;
	    }
		*/
		
		
		if(! $this->isContentCreator())
		{
			return false;
		}
		
		
		if(!is_numeric($roleid))
		{
			// roleid might be a role name, convert it to an ID
			// used in API::remote_getManagersList
			$roleid = $this->getRoleID($roleid);
		}
		
		if($roleid < 1)
		{
			return false;
		}
		
		// TODO: we could do 1 query instead of a query then looping through it and calling more stuff
		//  would speed things up a bit
		
		$qstr = "SELECT ".\cfg_core_User::ID." FROM ".\cfg_core_Role::MAP_USER_TABLE." WHERE ".\cfg_core_Role::ID."='?'";
		
		if(!($q = $this->DBM->querySafe($qstr, $roleid)))
		{
		    trace(mysql_error(), true);
			$this->DBM->rollback();
			return false;
		}
		
		$users = array();
		$UM = \rocketD\auth\AuthManager::getInstance();
		while($r = $this->DBM->fetch_obj($q))
		{
		    if($user = $UM->fetchUserByID($r->{\cfg_core_User::ID}))
			{
			    $users[] = $user; 
			}
		}
		
		return $users;
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
					WHERE userID='?' AND a='?'";
		
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
		$qstr = 	"SELECT ".\cfg_core_Role::TABLE.".".\cfg_core_Role::ID.", ".\cfg_core_Role::TABLE.".".\cfg_core_Role::ROLE." 
			 FROM ".\cfg_core_Role::MAP_USER_TABLE.", ".\cfg_core_Role::TABLE.
			" WHERE ".\cfg_core_Role::MAP_USER_TABLE.".".\cfg_core_User::ID."='?' AND ".\cfg_core_Role::MAP_USER_TABLE.".".\cfg_core_Role::ID." = ".\cfg_core_Role::TABLE.".".\cfg_core_Role::ID;
		// return logged in user's roles if id is 0 or less, non su users can only use this method
		if($userID <= 0 || $userID == $_SESSION['userID'])
		{
			if(!($q = $this->DBM->querySafe($qstr, $_SESSION['userID'])))
			{
				$this->DBM->rollback();
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
					$this->DBM->rollback();
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
				trace(' not super user.', true);
				return false;
			}
		}
		return $roles;
	}
	
	/**
	 * Returns true if user has any 
	 *	If no uid is sent, default to the current user
	
	 */
	/**
	 * does a user have one of the given roles?
	 *
	 * @param $roles	Array of role names
	 * @param $UID		Optional UID, defaults to the current session uid
	 * @return Boolean	True if user has any of the passed roles, false if no roles match
	 * @author Ian Turgeon
	 **/
	public function doesUserHaveARole($roles, $userID=0)
	{
		$storeInSession = false;
		$length = count($roles);
		if($length == 0)
		{
			return false;
		}
		// if using current user
		if($userID === 0 || !\obo\util\Validator::isPosInt($userID) )
		{
			$userID = $_SESSION['userID'];
		
		}

		$qstr = "SELECT COUNT(*) as count FROM ".\cfg_core_Role::MAP_USER_TABLE." WHERE ".\cfg_core_User::ID."='?' AND (";
		for($i = 0; $i < $length; $i++)
		{
			if($i == $length - 1)
			{
				$qstr .= \cfg_core_Role::ID. "=". $this->getRoleID($roles[$i]);
			}
			else
			{
				$qstr .= \cfg_core_Role::ID. "=" . $this->getRoleID($roles[$i]) .' OR ';
			}
		}
		$qstr .= ")";
		if( !($q = $this->DBM->querySafe($qstr, $userID )))
		{
			$this->DBM->rollback();
			trace(mysql_error(), true);
			return false;
		}

		$r = $this->DBM->fetch_obj($q);
		if($r->count > 0)
		{
			return true;
		}
		
		return false;

	}
	
	/**
	 * 
	 * 
	 */
	private function getRoleID($roleName = "")
	{
		if($roleName == "" || !is_string($roleName))
		{
			return 0;
		}
		if(!$this->roleExists($roleName))
		{
			return 0;
		}
		$qstr = "SELECT ".\cfg_core_Role::ID." FROM ".\cfg_core_Role::TABLE." WHERE ".\cfg_core_Role::ROLE."='?'";

		if( !($q = $this->DBM->querySafe($qstr, $roleName)))
		{
			$this->DBM->rollback();
			trace(mysql_error(), true);
			return false;
		}
		
		$r = $this->DBM->fetch_obj($q);
		return $r->{\cfg_core_Role::ID};
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

		$qstr = "SELECT COUNT(*) as count FROM ".\cfg_core_Role::TABLE." WHERE ".\cfg_core_Role::ROLE." = '?'";

		if( !($q = $this->DBM->querySafe($qstr, $roleName)))
		{
			$this->DBM->rollback();
		//	echo "ERROR: roleExists";
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
		
		foreach($users as $userID)
		{
			foreach($roles as $keyRole => $roleName)
			{
				//
				$qstr = "DELETE FROM ".\cfg_core_Role::MAP_USER_TABLE." WHERE ".\cfg_core_User::ID."='?' AND ".\cfg_core_Role::ID."='?'";
			
				if(!($q = $this->DBM->querySafe($qstr, $userID, $this->getRoleID($roleName))))
				{
				    trace(mysql_error(), true);
					$this->DBM->rollback();
					$success = false;
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
		
		foreach($users as $userID)
		{
			foreach($roles as $keyRole => $roleName)
			{
				$qstr = "INSERT IGNORE INTO ".\cfg_core_Role::MAP_USER_TABLE." SET ".\cfg_core_User::ID."='?', ".\cfg_core_Role::ID."='?'";
			
				if(!($q = $this->DBM->querySafe($qstr, $userID, $this->getRoleID($roleName))))
				{
				    trace(mysql_error(), true);
					$this->DBM->rollback();
					$success = false;
				}
			}
		}
		
		return $success;
	}
	
	/**
	 * 
	 * 
	 */
	// TODO: REMOVE
	public function isAdministrator()
	{
		if(!isset($_SESSION['isAdministrator']))
		{
			$_SESSION['isAdministrator'] = $this->doesUserHaveARole(array(\cfg_core_Role::ADMIN, \cfg_core_Role::SU));
		}
		return $_SESSION['isAdministrator'];
	}

	/**
	 * 
	 * 
	 */
	public function isSuperUser()
	{
		if(!isset($_SESSION['isSuperUser']))
		{
			$_SESSION['isSuperUser'] = $this->doesUserHaveARole(array(\cfg_core_Role::SU));
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
			$_SESSION['isSuperViewer'] = $this->doesUserHaveARole(array(\cfg_core_Role::SUPER_VIEWER, \cfg_core_Role::SU));
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
			$_SESSION['isContentCreator'] = $this->doesUserHaveARole(array(\cfg_core_Role::EMPLOYEE_ROLE, \cfg_core_Role::SU));
		}
		return $_SESSION['isContentCreator'];
	}
}
?>
