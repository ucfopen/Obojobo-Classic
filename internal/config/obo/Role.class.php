<?php
class cfg_obo_Role
{
	const TABLE = 'lo_roles';
	
	const MAP_USER_TABLE = 'lo_map_roles'; // maps users to roles
	const ID = 'roleID';
	const ROLE = 'name';
	const DESC = 'description';
	
	// define Roles
	const SU = 'SuperUser';
	const ADMIN = 'Administrator';
	const GUEST = 'Guest';
	const EMPLOYEE_ROLE = 'LibraryUser';
		
	// Custom Roles
	const CONTENT_CREATOR = 'ContentCreator';
	const SUPER_VIEWER = 'SuperViewer';
	const LIBRARY_USER = 'LibraryUser';

}
?>