<?php
/**
 * This class contains all logic pertaining to Permissions
 * @author Jacob Bates <jbates@mail.ucf.edu>
 * @author Luis Estrada <lestrada@mail.ucf.edu>
 */

/**
 * This class contains all logic pertaining to Permissions
 * This includes creating, retrieving, and deleting of data.
 *
 * Item types:
 * 'l' = learning object
 * 'm' = media
 * 'q' = question
 * 'i' = instance
 * ---'lay' = layout
 *
 * Permission types:
 * 'read' = user has the ability to read or view item
 * 'write' = user has ability to write or modify item
 * 'copy' = user has ability to make a complete copy of item
 * 'publish' = user has ability to use item in their own works or courses
 * 'giveRead' = user has ability to give other users read access
 * 'giveWrite' = user has ability to give other users write access
 * 'giveCopy' = user has ability to give other users copy access
 * 'givePublish' = user has ability to give other users use access
 * 'giveGlobal' = user has ability to give other users global access
 */
namespace obo\perms;
class PermissionsManager extends \rocketD\db\DBEnabled
{
	private static $instance;

	function __construct()
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

	// TODO: FIX RETURN FOR DB ABSTRACTION
	public function getPermsForItem($itemID = 0, $itemType = 'l')
	{
		if(!is_numeric($itemID) || $itemID == 0)
		{
			return false;
		}
		$qstr = "SELECT * FROM `".\cfg_obo_Perm::TABLE."` WHERE ".\cfg_obo_Perm::ITEM."='?' AND `".\cfg_obo_Perm::TYPE."`='?'";

		if(!($q = $this->DBM->querySafe($qstr, $itemID, $itemType)))
		{
			trace($this->DBM->error(), true);
			$this->DBM->rollback();
			return false;
		}

		$perms = array();
		while($r = $this->DBM->fetch_obj($q))
		{
			$perms[] = $r;
		}
		return $perms;
	}
	/**
	 * Performs validation specific to getting the global permissions for an item
	 * @param $itemID (number) Database item id
	 * @param $itemType (string) Item type.  Refer to table at top of source.
	 * @return (Permissions) global permissions object for item requested
	 * @return (bool) False if invalid Item ID
	 */
	public function getGlobalPerms($itemID=0, $itemType='l')
	{
		if($itemID == 0)
		{
			return false;
		}
		else
		{
			return $this->getPerms($itemID, $itemType);
		}
	}

	/**
	 * Performs validation specific to getting the user permissions for an item
	 * @param $itemID (number) Database item id
	 * @param $itemType (string) Item type.  Refer to table at top of source.
	 * @param $optUserID (number) ID of user to find permissions for
	 * @return (Permissions) permissions object for user for item requested
	 * @return (bool) False if invalid Item ID or user ID
	 */
	public function getUserPerms($itemID=0, $itemType='l', $optUserID=0)
	{
		if($optUserID == 0 || $itemID == 0)
		{
			return false;
		}
		else
		{
			return $this->getPerms($itemID, $itemType, $optUserID);
		}
	}

	/**
	 * Reuses existing permissions, assigning them to a new item.  The permissions will no longer be associated with the previous item
	 *
	 * @param string $oldItemID
	 * @param string $newItemID
	 * @param string $itemType
	 * @return void
	 * @author Ian Turgeon
	 */
	public function movePermsToItem($oldItemID, $newItemID, $itemType)
	{
		if(!\obo\util\Validator::isPosInt($oldItemID))
		{
			return false;
		}
		if(!\obo\util\Validator::isPosInt($newItemID))
		{
			return false;
		}
		$qstr = "UPDATE ".\cfg_obo_Perm::TABLE." SET ".\cfg_obo_Perm::ITEM." = '?' WHERE ".\cfg_obo_Perm::ITEM." = '?' AND ".\cfg_obo_Perm::TYPE." = '?' ";
		return $this->DBM->querySafe($qstr, $newItemID, $oldItemID, $itemType);
	}

	/**
	 * Duplicate permissions from one object to another.  The original item's permissions will be unchanged.
	 *
	 * @param string $oldItemID
	 * @param string $newItemID
	 * @param string $itemType
	 * @return void
	 * @author Ian Turgeon
	 */
	public function copyPermsToItem($oldItemID, $newItemID, $itemType)
	{
		if(!\obo\util\Validator::isPosInt($oldItemID))
		{
			return false;
		}
		if(!\obo\util\Validator::isPosInt($newItemID))
		{
			return false;
		}
		$qstr = "INSERT	IGNORE INTO
					".\cfg_obo_Perm::TABLE."
					(".\cfg_core_User::ID.",
						".\cfg_obo_Perm::ITEM.",
						".\cfg_obo_Perm::TYPE.",
						`".\cfg_obo_Perm::READ."`,
						`".\cfg_obo_Perm::WRITE."`,
						`".\cfg_obo_Perm::COPY."`,
						".\cfg_obo_Perm::PUBLISH.",
						".\cfg_obo_Perm::G_READ.",
						".\cfg_obo_Perm::G_WRITE.",
						".\cfg_obo_Perm::G_COPY.",
						".\cfg_obo_Perm::G_USE.",
						".\cfg_obo_Perm::G_GLOBAL.")
					SELECT	".\cfg_core_User::ID.",
						'?' AS ".\cfg_obo_Perm::ITEM.",
						".\cfg_obo_Perm::TYPE.",
						`".\cfg_obo_Perm::READ."`,
						`".\cfg_obo_Perm::WRITE."`,
						`".\cfg_obo_Perm::COPY."`,
						".\cfg_obo_Perm::PUBLISH.",
						".\cfg_obo_Perm::G_READ.",
						".\cfg_obo_Perm::G_WRITE.",
						".\cfg_obo_Perm::G_COPY.",
						".\cfg_obo_Perm::G_USE.",
						".\cfg_obo_Perm::G_GLOBAL."
					  FROM ".\cfg_obo_Perm::TABLE."
					  WHERE ".\cfg_obo_Perm::ITEM." = '?'
					  AND ".\cfg_obo_Perm::TYPE." = '?'";
		if(!$q = $this->DBM->querySafe($qstr, $newItemID, $oldItemID, $itemType))
		{
			return false;
		}
		return true;
	}

	/**
	 * Gets Global or User permissions for an item (use getUserPerms or getGlobalPerms instead)
	 * @param $itemID (number) Database item id
	 * @param $itemType (string) Item type.  Refer to table at top of source.
	 * @param $optUserID (number) ID of user to find permissions for (0 for global)
	 * @return (Permissions) permissions object for user for item requested
	 * @return (bool) False if invalid Item ID or user ID
	 */
	// TODO: FIX RETURN FOR DB ABSTRACTION
	private function getPerms($itemID=0, $itemType='l', $optUserID=0)
	{
		//Do type checking
		if(!\obo\util\Validator::isPosInt($itemID))
		{
			return false;
		}
		if(!\obo\util\Validator::isPosInt($optUserID, true))
		{
			$optUserID = 0;
		}

		$q = $this->DBM->querySafe("SELECT * FROM `".\cfg_obo_Perm::TABLE."` WHERE
			".\cfg_core_User::ID."='?' AND
			".\cfg_obo_Perm::ITEM."='?' AND
			`".\cfg_obo_Perm::TYPE."`='?' LIMIT 1", $optUserID, $itemID, $itemType);

		if($r = $this->DBM->fetch_obj($q))
		{
			$permObj = new \obo\perms\Permissions(
				$r->{\cfg_core_User::ID},
				$r->{\cfg_obo_Perm::READ},
				$r->{\cfg_obo_Perm::WRITE},
				$r->{\cfg_obo_Perm::COPY},
				$r->{\cfg_obo_Perm::PUBLISH},
				$r->{\cfg_obo_Perm::G_READ},
				$r->{\cfg_obo_Perm::G_WRITE},
				$r->{\cfg_obo_Perm::G_COPY},
				$r->{\cfg_obo_Perm::G_USE},
				$r->{\cfg_obo_Perm::G_GLOBAL});

			return $permObj;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Merges global and user permissions together for specified user
	 * @param $itemID (number) Database item id
	 * @param $itemType (string) Item type.  Refer to table at top of source.
	 * @param $optUserID (number) ID of user to find permissions for (0 for global)
	 * @return (Permissions) merged permissions object
	 * @return (bool) False if invalid Item ID or user ID
	 */
	public function getMergedPerms($itemID=0, $itemType='l', $optUserID=0)
	{
		if(!\obo\util\Validator::isPosInt($itemID))
		{
			trace('invalid input', true);
			return new \obo\perms\Permissions();
		}

		$userPerms = false;
		$permObj = $this->getPerms($itemID, $itemType, 0);			//Global perms
		// check if we need to merge global with other UID perms
		// if no other uid was sent, merge with the current users perms if they are logged in
		if($optUserID == 0 && \obo\util\Validator::isPosInt($_SESSION['userID']) )
		{
			$optUserID = $_SESSION['userID'];
		}
		if(\obo\util\Validator::isPosInt($optUserID))
		{
			$userPerms = $this->getPerms($itemID, $itemType, $optUserID);	//User perms
		}

		if($permObj == false && $userPerms == false) // both are false for some reason
		{
			$permObj = new \obo\perms\Permissions();
		}
		else if($permObj == false) // no global perms, return the user perms
		{
			$permObj = $userPerms;
		}
		else if($userPerms == false) // no user perms, return the global perms
		{
			// do nothing - just return
		}
		else // both perms exist, merge them
		{
			$permObj = $this->mergePermObjects($permObj, $userPerms);
		}

		return $permObj;
	}

	public function mergePermObjects($permA, $permB)
	{
		$permObj = new \obo\perms\Permissions(
			$permA->userID > $permB->userID ? $permA->userID : $permB->userID,
			($permA->read || $permB->read) ? 1 : 0,
			($permA->write || $permB->write) ? 1 : 0,
			($permA->copy || $permB->copy) ? 1 : 0,
			($permA->publish || $permB->publish) ? 1 : 0,
			($permA->giveRead || $permB->giveRead) ? 1 : 0,
			($permA->giveWrite || $permB->giveWrite) ? 1 : 0,
			($permA->giveCopy || $permB->giveCopy) ? 1 : 0,
			($permA->givePublish || $permB->givePublish) ? 1 : 0,
			($permA->giveGlobal || $permB->giveGlobal) ? 1 : 0
		);

		return $permObj;
	}

	/**
	 * Returns ids of all items of a certain type given certain permissions in order from the newest to oldest
	 * @param $itemType (string) Item type.  Refer to table at top of source.
	 * @param $perm (string) Perm type.  Refer to table at top of source.
	 * @return (Array<number>) Array of item IDs
	 *
	 */
	public function getItemsWithPerm($itemType='l', $perm='read', $ignoreGlobal=false, $ignoreCurrentUser=false)
	{
		$itemType = substr($itemType,0,1);	//Make sure $itemType is only one char long
		$itemArr = array();

		$userID = $ignoreCurrentUser ? 0 : $_SESSION['userID'];

		$roleMan = \obo\perms\RoleManager::getInstance();
		// super user will always have whatever right your asking for, do not run this if we're trying to ignore the current user
		if($ignoreCurrentUser == false && $roleMan->isSuperUser())
		{
			$qstr = "SELECT ".\cfg_obo_Perm::ITEM." FROM ".\cfg_obo_Perm::TABLE." WHERE `".\cfg_obo_Perm::TYPE."`='?' AND `?`='1'";
			$q = $this->DBM->querySafe($qstr, $itemType, $perm);
		}
		else
		{
			if($ignoreGlobal) $qstr = "SELECT ".\cfg_obo_Perm::ITEM." FROM `".\cfg_obo_Perm::TABLE."` WHERE ".\cfg_core_User::ID."='?' AND	`".\cfg_obo_Perm::TYPE."`='?' AND `?`='1'";
			else  $qstr = "SELECT ".\cfg_obo_Perm::ITEM." FROM `".\cfg_obo_Perm::TABLE."` WHERE ".\cfg_core_User::ID." IN ('?','0') AND	`".\cfg_obo_Perm::TYPE."`='?' AND `?`='1'";
			$q = $this->DBM->querySafe($qstr, $userID, $itemType, $perm);
		}

		while( $r = $this->DBM->fetch_obj($q))
		{
			$itemArr[] = $r->{\cfg_obo_Perm::ITEM};
		}
		return array_reverse(array_values(array_unique($itemArr)));
	}

	/**
	 * Gets the merged permission specified by $perm for an item
	 * @param $itemID (number) Database item id
	 * @param $itemType (string) Item type.  Refer to table at top of source.
	 * @param $perm (string) Perm type.  Refer to table at top of source.
	 * @param $optUserID (number) ID of user to find permissions for (0 for global)
	 * @return (bool) True if permission allowed, False if not allowed or error
	 */
	public function getMergedPerm($itemID=0, $itemType='l', $perm='read', $optUserID=0)
	{
		return ($this->getUserPerm($itemID, $itemType, $perm, $optUserID) || $this->getGlobalPerm($itemID, $itemType, $perm))  ? 1 : 0;
	}

	/**
	 * Gets only the user permission specified by $perm
	 * @param $itemID (number) Database item id
	 * @param $itemType (string) Item type.  Refer to table at top of source.
	 * @param $perm (string) Perm type.  Refer to table at top of source.
	 * @param $optUserID (number) ID of user to find permissions for
	 * @return (bool) True if permission allowed, False if not allowed or error
	 */
	public function getUserPerm($itemID=0, $itemType='l', $perm='read', $optUserID=0)
	{
		if($optUserID > 0)
		{
			return $this->getPerm($itemID, $itemType, $perm, $optUserID);
		}
		else
		{
			return false;
		}
	}

	/**
	 * Gets only the global permission specified by $perm
	 * @param $itemID (number) Database item id
	 * @param $itemType (string) Item type.  Refer to table at top of source.
	 * @param $perm (string) Perm type.  Refer to table at top of source.
	 * @return (bool) True if permission allowed, False if not allowed or error
	 */
	public function getGlobalPerm($itemID=0, $itemType='l', $perm='read')
	{
		return $this->getPerm($itemID, $itemType, $perm, 0);
	}

	/**
	 * Gets only the permissions specified by $perm (use getGlobalPerm or getUserPerm)
	 * @param $itemID (number) Database item id
	 * @param $itemType (string) Item type.  Refer to table at top of source.
	 * @param $perm (string) Perm type.  Refer to table at top of source.
	 * @param $optUserID (number) ID of user to find permissions for (0 for global)
	 * @return (bool) True if permission allowed, False if not allowed or error
	 */
	private function getPerm($itemID=0, $itemType='l', $perm='read', $optUserID=0)
	{
		$itemType = substr($itemType,0,1);	//Make sure $itemType is only one char long
		if(is_numeric($itemID) && is_numeric($optUserID) && $itemID > 0)
		{
			$q = $this->DBM->querySafe("SELECT `?` FROM ".\cfg_obo_Perm::TABLE." WHERE
				".\cfg_core_User::ID."='?' AND
				".\cfg_obo_Perm::ITEM."='?' AND
				`".\cfg_obo_Perm::TYPE."`='?' AND
				`?`='1'
				LIMIT 1", $perm, $optUserID, $itemID, $itemType, $perm);
			return ((bool) $r = $this->DBM->fetch_obj($q));
		}
		else
		{
			return false;
		}
	}


	/**
	 * Gets all the users with the specified perm, for an item
	 * @param $itemID (number) Database item id
	 * @param $itemType (string) Item type.  Refer to table at top of source.
	 * @param $perm (string) Perm type.  Refer to table at top of source.
	 * @return (Array) user id and user name
	 */
	// TODO: FIX RETURN FOR DB ABSTRACTION
	public function getUsersWithPerm($itemID=0, $itemType='i', $perm='read')
	{
		$itemType = substr($itemType,0,1);	//Make sure $itemType is only one char long
		if(is_numeric($itemID) && $itemID > 0)
		{
			if( !($q = $this->DBM->querySafe("SELECT ".\cfg_core_User::ID." FROM ".\cfg_obo_Perm::TABLE." WHERE ".\cfg_obo_Perm::ITEM."='?' AND `".\cfg_obo_Perm::TYPE."`='?' AND `?`='1'", $itemID, $itemType, $perm)) )
			{
				trace($this->DBM->error(), true);
				$this->DBM->rollback();
				return false;
			}

			$userArr = array();
			$userMan = \rocketD\auth\AuthManager::getInstance();
			while( $r = $this->DBM->fetch_obj($q) )
			{
				array_push($userArr, array(
					'userID' => $r->{\cfg_core_User::ID},
					'user_name' => $userMan->getName($r->{\cfg_core_User::ID})
				));
			}

			return $userArr;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Sets global permissions for a specified item
	 * @param $itemID (number) Database item id
	 * @param $itemType (string) Item type.  Refer to table at top of source.
	 * @param $permObj (Permissions) new global permissions for item
	 * @return (bool) True if successful, False if not successful or error
	 */
	public function setGlobalPerms($itemID=0, $itemType='l', $permObj)
	{
		$userID = $_SESSION['userID'];

		if(\obo\perms\RoleManager::getInstance()->isSuperUser() || ($itemID != 0 && $this->getUserPerm($itemID, $itemType, 'giveGlobal', $userID)))
		{
			return $this->setPerms($itemID, $itemType, $permObj);
		}
		else
		{
			return false;
		}
	}

	/**
	 * Sets user permissions for a specified item
	 * @param $itemID (number) Database item id
	 * @param $itemType (string) Item type.  Refer to table at top of source.
	 * @param $permObj (Permissions) new global permissions for item
	 * @return (bool) True if successful, False if not successful or error
	 */
	public function setUserPerms($itemID=0, $itemType='l', $permObj)
	{
		if(!is_numeric($itemID) || $itemID < 1)
		{
			return false; // error: invalid input
		}
		return $this->setPerms($itemID, $itemType, $permObj);
	}

	public function setUsersPerms($permObjects, $itemID = 0, $itemType = 'l')
	{
		if(!\obo\util\Validator::isPosInt($itemID))
		{


			return \rocketD\util\Error::getError(2);
		}

		foreach($permObjects as $permObj)
		{
			$res = $this->setPerms($itemID, $itemType, new \obo\perms\Permissions($permObj));
			if(! ($res === true) ) return $res; // something didn't work
		}
		return true;
	}

	// TODO: remove this OR removeUsersPerms....
	public function removeUserPerms($itemID=0, $itemType='l', $userID = -1)
	{
		if(! \obo\util\Validator::isPosInt($itemID, true) )
		{


			return \rocketD\util\Error::getError(2);
		}
		//check if user had permissions to this first.
		$qstr = "SELECT * FROM ".\cfg_obo_Perm::TABLE." WHERE ".\cfg_obo_Perm::ITEM."='?' AND `".\cfg_obo_Perm::TYPE."`='?' AND ".\cfg_core_User::ID."!='?' AND
					`".\cfg_obo_Perm::READ."`='1' AND `".\cfg_obo_Perm::WRITE."`='1' AND ".\cfg_obo_Perm::COPY."='1' AND ".\cfg_obo_Perm::PUBLISH."='1' AND ".\cfg_obo_Perm::G_READ."='1' AND
					".\cfg_obo_Perm::G_WRITE."='1' AND ".\cfg_obo_Perm::G_COPY."='1' AND ".\cfg_obo_Perm::G_USE."='1'";
		if(!($q = $this->DBM->querySafe($qstr, $itemID, $itemType, $userID)))
		{
			return false;
		}
		if(!($r = $this->DBM->fetch_obj($q)))
		{


			return \rocketD\util\Error::getError(5003);
		}
		$qstr = "DELETE FROM ".\cfg_obo_Perm::TABLE." WHERE ".\cfg_obo_Perm::ITEM."='?' AND `".\cfg_obo_Perm::TYPE."`='?' AND ".\cfg_core_User::ID."='?'";
		if(!($q = $this->DBM->querySafe($qstr, $itemID, $itemType, $userID)))
		{
			$this->DBM->rollback();
			return false;
		}
		return true;
	}

	public function removeAllPermsForItem($itemID, $itemType)
	{
		if(! \obo\util\Validator::isPosInt($itemID) )
		{
			return \rocketD\util\Error::getError(2);
		}

		$q = "DELETE FROM ".\cfg_obo_Perm::TABLE." WHERE `".\cfg_obo_Perm::ITEM."`='?' AND `".\cfg_obo_Perm::TYPE."`='?'";
		if(!($q = $this->DBM->querySafe($q, $itemID, $itemType)))
		{
			$this->DBM->rollback();
			return false;
		}
		return true;
	}

	public function removeAllPermsForUser($userID = -1)
	{
		if($userID == 0 || $userID == -1)
		{
			return false;
		}
		$qstr = "DELETE FROM ".\cfg_obo_Perm::TABLE." WHERE ".\cfg_core_User::ID."='?'";
		if(!($q = $this->DBM->querySafe($qstr, $userID)))
		{
			$this->DBM->rollback();
			return false;
		}
		return true;
	}

	public function removeUsersPerms($userIDs, $itemID=0, $itemType='l')
	{
		if($itemID == 0 || !is_array($userIDs))
		{
			return false;
		}
		foreach($userIDs as $key => $userID)
		{
			$this->removeUserPerms($itemID, $itemType, $userID);
		}
		return true;
	}

	/**
	 * Sets user permissions for a specified item
	 * @param $itemID (number) Database item id
	 * @param $itemType (string) Item type.  Refer to table at top of source.
	 * @param $permObj (Permissions) new user permissions for item
	 * @return (bool) True if successful, False if not successful or error
	 */

	// TODO: make sure the user can never call this directly
	public function setFullPermsForItem($itemID, $itemType)
	{
		if($itemID==0)
		{
			return false;
		}
		else
		{
			$userID = $_SESSION['userID'];

			$qstr = "INSERT INTO
			 `".\cfg_obo_Perm::TABLE."`
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
				VALUES ('?', '?', '?', '1', '1', '1', '1', '1', '1', '1', '1', '1');";
			if(!($q = $this->DBM->querySafe($qstr, $userID, $itemID, $itemType)) )
			{
				trace($this->DBM->error(), true);
				$this->DBM->rollback();
				return false;
			}

			return true;
		}
	}

	/**
	 * Adds/changes permissions for $permObj->userID for the $itmid specified
	 * @param $itemID (number) Database item id
	 * @param $itemType (string) Item type.  Refer to table at top of source.
	 * @param $permObj (Permissions) new global permissions for item
	 * @return (bool) True if successful, False if not successful or error
	 *
	 * @todo update the permissions if it exists
	 */
	private function setPerms($itemID=0, $itemType='l', $permObj)
	{
		$userID = $_SESSION['userID'];
		// TODO: make this in one db call isntead of 5?
		$giveRead = $this->getMergedPerm($itemID, $itemType, 'giveRead', $userID);
		$giveWrite = $this->getMergedPerm($itemID, $itemType, 'giveWrite', $userID);
		$giveCopy = $this->getMergedPerm($itemID, $itemType, 'giveCopy', $userID);
		$giveUse = $this->getMergedPerm($itemID, $itemType, 'givePublish', $userID);
		$giveGlobal = $this->getMergedPerm($itemID, $itemType, 'giveGlobal', $userID);

		$roleMan = \obo\perms\RoleManager::getInstance();
		if($roleMan->isSuperUser())
		{
			$giveRead = 1;
			$giveWrite = 1;
			$giveCopy = 1;
			$giveUse = 1;
			$giveGlobal = 1;
		}

		//See if the user has ANY permissions to be giving permissions
		if(!($giveRead || $giveWrite || $giveCopy || $giveUse))
		{
			return \rocketD\util\Error::getError(4);
		}

		$qstr = "DELETE FROM ".\cfg_obo_Perm::TABLE." WHERE ".\cfg_core_User::ID."='?' AND ".\cfg_obo_Perm::ITEM."='?' AND `".\cfg_obo_Perm::TYPE."`='?'";

		if(!($q = $this->DBM->querySafe($qstr, $permObj->userID, $itemID, $itemType)))
		{
			$this->DBM->rollback();
			return false;
		}

		// TODO: need to do querySafe all all this randomness :(
		//For each permission, test to see if the user has permission to change each permission
		$qstr = "INSERT INTO ".\cfg_obo_Perm::TABLE." SET ".\cfg_core_User::ID."='?', ".\cfg_obo_Perm::ITEM."='?', `".\cfg_obo_Perm::TYPE."`='?', ";
		if($giveRead){ $qstr .= "`".\cfg_obo_Perm::READ."`='{$permObj->read}', "; }else{ $qstr .= "`".\cfg_obo_Perm::READ."`='0', "; }
		if($giveWrite){ $qstr .= "`".\cfg_obo_Perm::WRITE."`='{$permObj->write}', "; }else{ $qstr .= "`".\cfg_obo_Perm::WRITE."`='0', "; }
		if($giveCopy){ $qstr .= "`".\cfg_obo_Perm::COPY."`='{$permObj->copy}', "; }else{ $qstr .= "`".\cfg_obo_Perm::COPY."`='0', "; }
		if($giveUse){ $qstr .= "`".\cfg_obo_Perm::PUBLISH."`='{$permObj->publish}', "; }else{ $qstr .= "`".\cfg_obo_Perm::PUBLISH."`='0', "; }
		if($giveRead){ $qstr .= "".\cfg_obo_Perm::G_READ."='{$permObj->giveRead}', "; }else{ $qstr .= "".\cfg_obo_Perm::G_READ."='0', "; }
		if($giveWrite){ $qstr .= "".\cfg_obo_Perm::G_WRITE."='{$permObj->giveWrite}', "; }else{ $qstr .= "".\cfg_obo_Perm::G_WRITE."='0', "; }
		if($giveCopy){ $qstr .= "".\cfg_obo_Perm::G_COPY."='{$permObj->giveCopy}', "; }else{ $qstr .= "".\cfg_obo_Perm::G_COPY."='0', "; }
		if($giveUse){ $qstr .= "".\cfg_obo_Perm::G_USE."='{$permObj->givePublish}', "; }else{ $qstr .= "".\cfg_obo_Perm::G_USE."='0', "; }
		if($giveGlobal){ $qstr .= "".\cfg_obo_Perm::G_GLOBAL."='{$permObj->giveGlobal}' "; }else{ $qstr .= "".\cfg_obo_Perm::G_GLOBAL."='0' "; }

		if( !($q = $this->DBM->querySafe($qstr, $permObj->userID, $itemID, $itemType)) )
		{
			$this->DBM->rollback();
			return false;
		}

		return true;
	}

	/**
	 * Checks to see if perms exist
	 * @param $itemID (number) Database item id
	 * @param $itemType (string) Item type.  Refer to table at top of source.
	 * @param $permObj (Permissions) new global permissions for item
	 * @return (bool) true if perms exist false otherwise
	 *
	 */
	public function hasPerms($userID, $itemID, $itemType)
	{
		$qstr = "SELECT * FROM ".\cfg_obo_Perm::TABLE." WHERE
					".\cfg_obo_Perm::UIT."='?' AND
					".\cfg_obo_Perm::ITEM."='?' AND
					`".\cfg_obo_Perm::TYPE."`='?' LIMIT 1";

		if(!($q = $this->DBM->querySafe($qstr, $userID, $itemID, $itemType)))
		{
			trace($this->DBM->error(), true);
			$this->DBM->rollback();
			return false;
		}
		if(!($r = $this->DBM->fetch_obj($q)))
		{
			return false;
		}
		return true;
	}

	/**
	 * Updates user permissions for a specified item
	 * @param $itemID (number) Database item id
	 * @param $itemType (string) Item type.  Refer to table at top of source.
	 * @param $permObj (Permissions) new global permissions for item
	 * @return (bool) True if successful, False if not successful or error
	 */
	public function updateUserPerms($itemID = 0, $itemType = 'l', $permObj)
	{
		if($itemID == 0)
		{
			return false;
		}
		else
		{
			return $this->updatePerms($itemID, $itemType, $permObj);
		}
	}

	private function updatePerms($itemID = 0, $itemType = 'l', $permObj)
	{
		$userID = $_SESSION['userID'];

		$giveRead = 1;$this->getMergedPerm($itemID, $itemType, 'giveRead', $userID);
		$giveWrite = 1;$this->getMergedPerm($itemID, $itemType, 'giveWrite', $userID);
		$giveCopy = 1;$this->getMergedPerm($itemID, $itemType, 'giveCopy', $userID);
		$giveUse = 1;$this->getMergedPerm($itemID, $itemType, 'givePublish', $userID);
		$giveGlobal = 1;$this->getMergedPerm($itemID, $itemType, 'giveGlobal', $userID);

		//See if the user has ANY permissions to be giving permissions
		if(!($giveRead || $giveWrite || $giveCopy || $giveUse))
		{
			return false;
		}
		// TODO: need to querySafe all the randomness here :(
		//For each permission, test to see if the user has permission to change each permission
		$qstr = "UPDATE ".self::mapping." SET ";
		if($giveRead){ $qstr .= "`read`='{$permObj->read}', "; }else{ $qstr .= "`read`='0', "; }
		if($giveWrite){ $qstr .= "`write`='{$permObj->write}', "; }else{ $qstr .= "`write`='0', "; }
		if($giveCopy){ $qstr .= "`copy`='{$permObj->copy}', "; }else{ $qstr .= "`copy`='0', "; }
		if($giveUse){ $qstr .= "`publish`='{$permObj->publish}', "; }else{ $qstr .= "`publish`='0', "; }
		if($giveRead){ $qstr .= "`giveRead`='{$permObj->giveRead}', "; }else{ $qstr .= "`giveRead`='0', "; }
		if($giveWrite){ $qstr .= "`giveWrite`='{$permObj->giveWrite}', "; }else{ $qstr .= "`giveWrite`='0', "; }
		if($giveCopy){ $qstr .= "`giveCopy`='{$permObj->giveCopy}', "; }else{ $qstr .= "`giveCopy`='0', "; }
		if($giveUse){ $qstr .= "`givePublish`='{$permObj->givePublish}', "; }else{ $qstr .= "`givePublish`='0', "; }
		if($giveGlobal){ $qstr .= "`giveGlobal`='{$permObj->giveGlobal}', "; }else{ $qstr .= "`giveGlobal`='0', "; }
		$qstr .= "`allow_req`='0' WHERE `userID`='?' AND `itemID`='?' AND `itemType`='?' LIMIT 1";

		if(!($q = $this->DBM->querySafe($qstr, $permObj->userID, $itemID, $itemType)))
		{
			$this->DBM->rollback();
			return false;
		}

		return true;
	}

}
