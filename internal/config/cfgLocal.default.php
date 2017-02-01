<?php

// VIEW cfg.php TO SEE ALL OPTIONS YOU CAN OVERRIDE!

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
	const DB_TYPE = ''; // either mysql, mysqli, or oci8

	// --- Wordpress DB Connection ---
	const DB_WP_HOST = '';
	const DB_WP_USER = '';
	const DB_WP_PASS = '';
	const DB_WP_NAME = '';
	const DB_WP_TYPE = ''; // either mysql, mysqli, or oci8

	// --- WORDPRESS keys ---
	// GET NEW KEYS HERE https://api.wordpress.org/secret-key/1.1/salt/

	const WP_AUTH_KEY         = '';
	const WP_SECURE_AUTH_KEY  = '';
	const WP_LOGGED_IN_KEY    = '';
	const WP_NONCE_KEY        = '';
	const WP_AUTH_SALT        = '';
	const WP_SECURE_AUTH_SALT = '';
	const WP_LOGGED_IN_SALT   = '';
	const WP_NONCE_SALT       = '';
}
