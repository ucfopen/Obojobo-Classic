<?php
class cfg_plugin_AuthModUCF
{
	const UCF_TEST_MODE = true;
	
	// Authentication Web Service AppID
	const UCF_APP_ID = "8a1b4076-3b1b-48ef-a3b1-57161a6aef39";
	const UCF_USE_WSDL = false;
	
	// LDAP url for ldap authentication
	const LDAP = "ldaps://net.ucf.edu";
	
	// Obojobo DB Connection
	const DB_HOST = 'localhost';
	const DB_USER = 'root';
	const DB_PASS = 'root';
	const DB_NAME = 'los_OracleStandin';
	const DB_TYPE = 'mysql';
	
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
	
	const WS_INVALID_PW = 'Invalid Password'; // NID exists, but Password is incorrect
	const WS_SUCCESS = 'Success'; // NID and Password correct
	const WS_LOCKED = 'Locked'; // User tried to log in unsuccesfully too many times, password reset required
	const WS_INVALID_NID = 'Invalid NID'; // NID does not exist
	const WS_ERROR = 'Error'; // Unknown Error - malformed request or some internal web service error, double check request and notify CST 
	const WS_EXPIRED = 'Expired'; // NID exists, password may be correct or not, but the password has expired and must be reset
	const WS_DISABLED = 'Disabled'; // Account is not Enabled - NID exists, but has not been enabled yet - ex: pre-employment 
	const WS_AD_DISABLED = 'UCFADRoleDisabled'; // Account's AD Role is not Enabled - NID exists, but user is no longer current - ex: alumni, previous employees
	const WS_INVALID_APPID = 'Invalid Consumer'; // APP ID is the right format, but not valid
	
	const LDAP_CANT_CONNECT = 'Cannot connect to LDAP';
}
?>