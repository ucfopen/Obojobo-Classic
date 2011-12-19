<?php // Local config options set here, override settings from config_main here
require_once('cfg.php'); // default config

class AppCfg extends AppCfgDefault
{
	//--------------------   DIRECTORYS & PATHS  -----------------------//
	const DIR_BASE = 'C:/full/disk/path/to/app/'; // Define Base Directory [ full path EX:  /www/obobjobo/ ]
	
	const URL_WEB = 'http://your/root/obojobourl/'; // Define the web directory for the base of GS [root inclusive at starting slash: /obojobo/]
	
	//--------------------   DEBUG   -----------------------//	
	//const DEBUG_MODE = true; // Define Debug [true, false]
	//const UCF_AUTH_BYPASS_PASSWORDS = false; // Never set true in production, [true, false]
	
	//--------------------   CACHE   -----------------------//	
	//const CACHE_DB = true; // cache to the db talbes? [true, false]
	//const CACHE_MEMCACHE = true; // cache to memcache? [true, false]
	
	//--------------------   DATABASE   -----------------------//
	// Main App DB Connection
	const DB_HOST = '';
	const DB_USER = '';
	const DB_PASS = '';
	const DB_NAME = '';
	const DB_TYPE = ''; // either mysql or oci8
	
	// ModX DB Connection
	const DB_MODX_HOST = '';
	const DB_MODX_USER = '';
	const DB_MODX_PASS = '';
	const DB_MODX_NAME = '';
	const DB_MODX_TYPE = ''; // either mysql or oci8
	
	// Wordpress DB Connection
	const DB_WP_HOST = '';
	const DB_WP_USER = '';
	const DB_WP_PASS = '';
	const DB_WP_NAME = '';
	const DB_WP_TYPE = ''; // either mysql or oci8
		
	// UCF AUTH DB Connection
	const UCF_DB_HOST = '';
	const UCF_DB_USER = '';
	const UCF_DB_PASS = '';
	const UCF_DB_NAME = '';
	const UCF_DB_TYPE = ''; // either mysql or oci8
	
	//--------------------   AUTHENTICATION   -----------------------//
	
	//const UCF_USE_WS_AUTH = false;  // use the ucf web service to check for reasons of password failure
	const UCF_WSDL = ''; // the web service wsdl needed for the ws query
	const UCF_APP_ID = ''; // the app id needed to connect to the web service
		
	const LDAP = ''; // The LDAP URL for accessing UCF Authentication
	
	const SSO_SECRET = ""; // SSO Secret key used for the single sign on script
	
	const KOGNEATO_JSON_URL = 'https://kogneato.ucf.edu/gs-amfphp/json.php/GSPlayer.';

}
?>