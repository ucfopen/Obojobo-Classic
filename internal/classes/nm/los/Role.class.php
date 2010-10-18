<?php
/**
 * This is the class that defines the Role data type
 * @author Luis Estrada <lestrada@mail.ucf.edu>
 */
/**
 * This is the class that defines the Role data type.
 * It is used simply for representing data in memory, and has no methods.
 */
class nm_los_Role
{
	public $roleID;			//Number:
	public $name;		//String:  the name of the role

	const SUPER_USER = "SuperUser";
	const SUPER_VIEWER = "SuperViewer";
	const CONTENT_CREATOR = "ContentCreator";
	const ADMINISTRATOR = "Administrator";
	const LIBRARY_USER = "LibraryUser";
	
	function __construct($roleID = 0, $name = "")
	{
		$this->roleID = $roleID;
		$this->name = $name;
	}
}
?>