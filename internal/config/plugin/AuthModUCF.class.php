<?php

class cfg_plugin_AuthModUCF
{
	const UCF_TEST_MODE = true;
	
	// External Employee Table
	const TABLE_PEOPLE = 'CDLPS_PEOPLE';
	const NID          = 'network_id';
	const FIRST        = 'first_name';
	const LAST         = 'last_name';
	const MIDDLE       = 'middle_name';
	const EMAIL        = 'email';
	const IS_STAFF     = 'staff';
	const IS_STUDENT   = 'student';
	
	const COL_EXTERNAL_SYNC_NAME  = 'AuthMod_PeopleSoft_LastNIDUpdate';
	const MAX_USERNAME_LENGTH = '255';
	const MIN_USERNAME_LENGTH = '2';

}
