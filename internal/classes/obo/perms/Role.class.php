<?php
/**
 * This is the class that defines the Role data type
 * @author Luis Estrada <lestrada@mail.ucf.edu>
 */
/**
 * This is the class that defines the Role data type.
 * It is used simply for representing data in memory, and has no methods.
 */
namespace obo\perms;
class Role
{
	public $roleID;			//Number:
	public $name;		//String:  the name of the role

	const SUPER_USER = 'SuperUser';
	const SUPER_VIEWER = 'SuperViewer';
	const CONTENT_CREATOR = 'ContentCreator';
	const ADMINISTRATOR = 'Administrator';
	const LIBRARY_USER = 'LibraryUser';
	const SUPER_STATS = 'SuperStats';

	function __construct($roleID = 0, $name = "")
	{
		$this->roleID = $roleID;
		$this->name = $name;
	}
}
