<?php
class cfg_core_DBRole
{
	const TABLE = 'lo_roles';
	
	const MAP_PERM = '';  // maps a user role to a certain permission
	const MAP_USER = 'lo_map_roles'; // maps users to roles
	
	const UID = 'userID'; // the user ids
	const ID = 'roleID';	
	const ROLE = 'name';

}
?>