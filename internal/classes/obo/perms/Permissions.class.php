<?php
/**
 * This class defines the Permissions data type.
 * @author Jacob Bates <jbates@mail.ucf.edu>
 */

/**
 * This class defines the Permissions data type,
 * which is a set of fields reperesenting what a user can and cannot do.
 * It is used simply for representing data in memory, and has no methods.
 */
namespace obo\perms;
class Permissions
{
	public $userID;		// Number: User id this permission object pertains to
	public $read;			// Number: Whether the user can view the item
	public $write;			// Number: Whether the user can alter the item
	public $copy;			// Number: Whether the user can make a copy of the item
	public $publish;			// Number: Whether the user can use the item in their course
	public $giveRead;		// Number: Whether the user can give other users read access
	public $giveWrite;		// Number: Whether the user can give other users write access
	public $giveCopy;		// Number: Whether the user can give other users copy access
	public $givePublish;		// Number: Whether the user can give other users use access
	public $giveGlobal;	// Number: Whether the user can give global permissions

	public function __construct($userID=-1, $read=0, $write=0, $copy=0, $publish=0, $giveRead=0, $giveWrite=0, $giveCopy=0, $givePublish=0, $giveGlobal=0)
	{
		if(func_num_args() == 1)
        {
       		$permObj = func_get_arg(0);
       		$this->userID = (int)$permObj['userID'];
			$this->read = (int)$permObj['read'];
			$this->write = (int)$permObj['write'];
			$this->copy = (int)$permObj['copy'];
			$this->publish = (int)$permObj['publish'];
			$this->giveRead = (int)$permObj['giveRead'];
			$this->giveWrite = (int)$permObj['giveWrite'];
			$this->giveCopy = (int)$permObj['giveCopy'];
			$this->givePublish = (int)$permObj['givePublish'];
			$this->giveGlobal = (int)$permObj['giveGlobal'];
        }
        else
        {
			$this->userID = (int)$userID;
			$this->read = (int)$read;
			$this->write = (int)$write;
			$this->copy = (int)$copy;
			$this->publish = (int)$publish;
			$this->giveRead = (int)$giveRead;
			$this->giveWrite = (int)$giveWrite;
			$this->giveCopy = (int)$giveCopy;
			$this->givePublish = (int)$givePublish;
			$this->giveGlobal = (int)$giveGlobal;
        }
	}

	public function isOwner()
	{
		return $this->read + $this->write + $this->copy + $this->publish + $this->giveRead + $this->giveRead + $this->giveWrite + $this->giveCopy + $this->givePublish == 9 ;
	}
}
