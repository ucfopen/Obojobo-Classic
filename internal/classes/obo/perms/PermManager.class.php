<?php
/*

	ACL Model
	The perms system is additive, meaning perms can only be added, not removed by any given permisssion.  by default no permissions exist

	Items: Each Item of a specific item type has permissions, this would allow items to essentially have permissions for every user

	Groups: Each User Group or Role is given blanket permissions.  this allows certain roles to gain access to items

	Users Mapped to Groups: Each user can be given ther permissions from a group.

	Users Mapped to Items: Each user can have permissions for specific items.  This allows multiple people to own an item

*/
namespace obo\perms;
class PermManager extends \rocketD\perms\PermManager
{
	use \rocketD\Singleton;

	public function getAllItemIDs($itemType)
	{
		$ids = array();
		switch($itemType)
		{
			case \cfg_core_Perm::TYPE_INSTANCE:
				$query = "SELECT ".\cfg_obo_Instance::ID." FROM ". \cfg_obo_Instance::TABLE;
				$q = $this->DBM->querySafe($query);
				while($r = $this->DBM->fetch_obj($q))
				{
					$ids[] = $r->{\cfg_obo_Instance::ID};
				}
				break;
			case \cfg_core_Perm::TYPE_LO:
				$query = "SELECT ".\cfg_obo_LO::ID." FROM ". \cfg_obo_LO::TABLE;
				$q = $this->DBM->querySafe($query);
				while($r = $this->DBM->fetch_obj($q))
				{
					$ids[] = $r->{\cfg_obo_LO::ID};
				}
				break;
			case \cfg_core_Perm::TYPE_MEDIA:
				$query = "SELECT ".\cfg_obo_Media::ID." FROM ". \cfg_obo_Media::TABLE;
				$q = $this->DBM->querySafe($query);
				while($r = $this->DBM->fetch_obj($q))
				{
					$ids[] = $r->{\cfg_obo_Media::ID};
				}
				break;
		}
		return $ids;

	}

}
