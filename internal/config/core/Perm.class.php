<?php
class cfg_core_Perm
{

	const TABLE = 'obo_map_perms_to_item';
	const ID = 'permID';
	const ITEM = 'itemID';
	const TYPE = 'itemType';
	const PERM = 'perm';

	// Item Types
	const TYPE_INSTANCE = '1';
	const TYPE_LO = '2';
	const TYPE_MEDIA = '3';
	
	// permission Types
	const P_READ = 1; // user can see item
	const P_WRITE = 2; // user can change item
	const P_DERIVE = 3; // user can make derivatives
	const P_INSTANTIATE = 4; // user can create instances
	const P_DISTRIBUTE = 5; // allowed to place in public library
	
	const P_GIVE_READ = 6; // user can give read perms
	const P_GIVE_WRITE = 7; // user can give write perms
	const P_GIVE_DERIVE = 8; // user can give copy perms
	const P_GIVE_INSTANTIATE = 9; // user can give use perms
	const P_GIVE_DISTRIBUTE = 10; // allowed to place in public library

	const P_OWN = 20; // ownership gives all rights over specified item
	
	// system access permissions  - usually applied via role perms
	const MIN_GROUP_VALUE = 100;
	const G_SU = 100; // su gets rights to do anything in the system, beyond item permissions
	const G_REPOSITORY = 101; // can access the repository
	const G_AUTHOR = 102; // can create content in the repository

	// Kogneato uses these
	// group rights only
	// const AUTHORACCESS = 80; // has rights to access manger interface
	// const ADMINISTRATOR = 85; // has rights to administer users
	// const SUPERUSER = 90; // has super user rights to do anything
}
?>