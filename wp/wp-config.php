<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */
 
// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */

// OBOJOBO configuration 
require_once(__DIR__.'/../internal/config/cfgLocal.php'); // local config
define('DB_NAME', AppCfg::DB_WP_NAME);

/** MySQL database username */
define('DB_USER', AppCfg::DB_WP_USER);

/** MySQL database password */
define('DB_PASSWORD', AppCfg::DB_WP_PASS);

/** MySQL hostname */
define('DB_HOST', AppCfg::DB_WP_HOST);

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         ':UkH*J)D_ke}Nw< bKhW*Gk6OJA-CT]3i/9.exT1R55`y;f5wT3x*:!b7LO`RX8,');
define('SECURE_AUTH_KEY',  'DdYeK7qPXvt:x}Hf`I](p8p~VQ{N})}A3eSNj2.,Db7rN{7@y`* N|}b;vbF%P9i');
define('LOGGED_IN_KEY',    'wfK[Uz%!J1XJ4@pg(JA<i_Typ> M/7IPi5 VX_/^lRpkJ0e=`+=_kLw|-za^;3y@');
define('NONCE_KEY',        '/]=*>HLHqGwow%zQP#AFFhlCHvMl}UIoD]iX*<c4jvWcCE} nzm[+[7 {uKwwvmu');
define('AUTH_SALT',        's]Pk`epML[MeFU7r21*M/ NRbus(EM)5qccznyEVVSqQ4^0dR?+Zc(fw`JGr{O]C');
define('SECURE_AUTH_SALT', 'x63o?V2t<h5RM~)3&AINQ4}|ic3|by9ZxUY?W4V(i.{s4?0=bC 2~3.F8_P=a;.|');
define('LOGGED_IN_SALT',   'lNz|zi 3z3xgRU;? |,u*sG1dhKReF9;=4;rKVt<Ql$y{o}#o3j7e%9vPPc{-]e|');
define('NONCE_SALT',       'q4oI@:*nJ>2OLx@Cs(o)xFsRCWlog/@&)@fO+=&g!:sm]doP9GiW.Lshec]L>*lF');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
