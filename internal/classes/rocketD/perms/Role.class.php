<?php
/**
 * This is the class that defines the Role data type
 * @author Luis Estrada <lestrada@mail.ucf.edu>
 */
/**
 * This is the class that defines the Role data type.
 * It is used simply for representing data in memory, and has no methods.
 */
namespace rocketD\perms;
class Role
{
	public $roleID;			//Number:
	public $name;		//String:  the name of the role
	
	function __construct($id = 0, $name = "")
	{
		$this->roleID = $roleID;
		$this->name = $name;
	}
}
?>