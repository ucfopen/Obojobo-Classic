<?php // Local config options set here, override settings from config_main here
require_once('cfg.php'); // default config

class AppCfg extends AppCfgDefault
{
	// --- DIRECTORYS & PATHS ---
	const DIR_BASE = '/var/www/obojobo/'; // Define Base Directory [ full path EX:  /www/obobjobo/ ]
	const URL_WEB = 'https://your/root/obojobourl/'; // Define the web directory for the base of GS [root inclusive at starting slash: /obojobo/]

	// --- Main App DB Connection ---
	const DB_HOST = '';
	const DB_USER = '';
	const DB_PASS = '';
	const DB_NAME = '';
	const DB_TYPE = ''; // either mysql or oci8

	// --- Wordpress DB Connection ---
	const DB_WP_HOST = '';
	const DB_WP_USER = '';
	const DB_WP_PASS = '';
	const DB_WP_NAME = '';
	const DB_WP_TYPE = ''; // either mysql or oci8

	// const GOOGLE_ANALYTICS_ID = '';

	// --- REQUIRED MATERIA LTI (using materia in obojobo via lti) ---
	// const MATERIA_LTI_URL = 'https://materia.school.edu/lti/assignment';
	// const MATERIA_LTI_PICKER_URL = 'https://materia.school.edu/lti/picker';
	// const MATERIA_LTI_SECRET  = 'secret';
	// const MATERIA_LTI_KEY  = 'key';
	// const MATERIA_LTI_TIMELIMIT = 3600; // OAUTH TIME LIMIT - 1 hr

	// --- REQUIRED MODLTI (using obojobo in something else as an lti) ---
	// const LTI_LAUNCH_PRESENTATION_RETURN_URL = 'lti/return.php';
	// const LTI_EXTERNAL_AUTHMOD = '\rocketD\auth\ModInternal';
	// const LTI_OAUTH_KEY = 'key';
	// const LTI_OAUTH_SECRET = 'secret';
	// const LTI_OAUTH_TIMEOUT = 3600;
	// const LTI_REMOTE_USERNAME_FIELD = 'lis_person_sourcedid';
	// const LTI_CREATE_USER_IF_MISSING = true;
	// const LTI_USE_ROLE = true;

	// --- REQUIRED CREDHUB @TODO - these shouldnt be required ---
	// const CREDHUB_KEY = 'qa-key';
	// const CREDHUB_SECRET = 'secret';
	// const CREDHUB_URL = 'https://badges.school.edu/api/badges/award';
	// const CREDHUB_TIMEOUT = 1800000;


	// --- WORDPRESS keys ---
	/* Change these to different unique phrases!
	* You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
	* You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
	*/
	const WP_AUTH_KEY         = '';
	const WP_SECURE_AUTH_KEY  = '';
	const WP_LOGGED_IN_KEY    = '';
	const WP_NONCE_KEY        = '';
	const WP_AUTH_SALT        = '';
	const WP_SECURE_AUTH_SALT = '';
	const WP_LOGGED_IN_SALT   = '';
	const WP_NONCE_SALT       = '';

	// SAML
	const SAML_USERNAME = '';
	const SAML_FIRST = '';
	const SAML_LAST = '';
	const SAML_ROLES = '';
}
