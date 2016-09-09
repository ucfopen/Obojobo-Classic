<?php // Local config options set here, override settings from config_main here
require_once('cfg.php'); // default config

class AppCfg extends AppCfgDefault
{
	// --- DIRECTORYS & PATHS ---
	const DIR_BASE = '/var/www/html/'; // Define Base Directory [ full path EX:  /www/obobjobo/ ]
	const URL_WEB = 'http://localhost/'; // Define the web directory for the base of GS [root inclusive at starting slash: http://obojobo.com/]

	// --- Main App DB Connection ---
	const DB_HOST = 'mysql';
	const DB_USER = 'obojobo_user';
	const DB_PASS = 'obojobo_pass';
	const DB_NAME = 'obojobo';
	const DB_TYPE = 'mysql';

	// --- Wordpress DB Connection ---
	const DB_WP_HOST = 'mysql';
	const DB_WP_USER = 'obojobo_user';
	const DB_WP_PASS = 'obojobo_pass';
	const DB_WP_NAME = 'obojobo_wordpress';
	const DB_WP_TYPE = 'mysql';


	// --- WORDPRESS keys ---
	/* Change these to different unique phrases!
	* You can generate new ones here https://api.wordpress.org/secret-key/1.1/salt/
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

	const MEMCACHE_HOSTS = 'memcached';

	// which login template from /assets/templates should we show on all login pages
	const LOGIN_TEMPLATE = 'login-default.php';

}
