<?php
/**
 * This class defines the data type for Locks
 * @author Luis Estrada <lestrada@mail.ucf.edu>
 */

/**
 * This class defines the data type for Locks
 */
namespace obo;
class Lock
{
	public $lockID;		//Number:
	public $loID;	//Number:  The id of the LO
	public $user; //User: The User object
	public $unlockTime; //Unix Timestamp

	function __construct($lockID=0, $loID=0, $user=0, $unlockTime=0)
	{
		$this->lockID = $lockID;
		$this->loID = $loID;
		$this->user = $user;
		$this->unlockTime = $unlockTime;
	}
}
?>