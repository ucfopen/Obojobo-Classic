<?php
class cfg_plugin_AuthModUCF
{
	const UCF_TEST_MODE = true;
	
	// External Employee Table
	const TABLE_EMPLOYEE = 'NM_EMPLOYEE';
	const NID = 'NETWORK_ID';
	const FIRST = 'FIRST_NAME';
	const LAST = 'LAST_NAME';
	const MIDDLE = 'MIDDLE_NAME';
	const EMAIL = 'EMAIL';
	
	// External Student Table
	const TABLE_STUDENT = 'NM_STUDENT';
	
	// EXTERNAL NID CHANGES TABLE
	const TABLE_NID = 'NM_NID_CHANGE';
	const NID_CHANGE_DATE = 'EFFDT';
	const OLD_NID = 'OLD_NID';
	const NEW_NID = 'NEW_NID';
	
	const COL_EXTERNAL_SYNC_NAME  = 'AuthMod_PeopleSoft_LastNIDUpdate';
	const MAX_USERNAME_LENGTH = '255';
	const MIN_USERNAME_LENGTH = '2';

}
?>