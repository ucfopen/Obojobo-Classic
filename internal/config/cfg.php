<?php
/**
 *******
 ****** DO NOT EDIT THIS CONFIG ::: edit cfgLocal.php to override these values or add custom values
 *******
*/
class AppCfgDefault
{
	//--------------------   GENERAL   -----------------------//
	// Define the system email address
	const SYS_EMAIL = 'noReply@obojobo.ucf.edu';

	// Define the name of the system (presented in emails and such)
	const SYS_NAME = 'Obojobo';

	// Define maximum file upload size in bytes
	const MAX_FILE_SIZE = 10485760; // 10MB

	// Minimum Flash Version to show a warning for
	const FLASH_VER_WARN = '10.0.12.36';

	// constants for testing the current environment
	const ENV_DEV = 'dev';
	const ENV_TEST = 'test';
	const ENV_PROD = 'prod';

	// set the default environment
	const ENVIRONMENT = 'prod';

	//--------------------   DIRECTORYS & PATHS  -----------------------//
	// Define output of all php errors
	const DIR_LOGS = 'internal/logs/';

	const DIR_ADMIN = 'internal/admin/';

	const DIR_CLASSES = 'internal/classes/';

	// Define the relative location for the media directory with trailing /
	const DIR_MEDIA = 'internal/media/';

	// Define the relative location for the scripts directory with trailing /
	const DIR_SCRIPTS = 'internal/includes/';

	const DIR_TEMPLATES = 'internal/templates/';

	// Define the relative location for the assets directory with trailing /
	const DIR_ASSETS = 'assets/';

	// Define working directory for amfphp
	const DIR_AMFPHP = 'internal/includes/amfphp/';

	// Plugin Directory
	const DIR_PLUGIN ='internal/plugin/';

	// Define the relative location for the creator directory with trailing /
	const URL_CREATOR = 'creator/';

	const URL_VIEWER = 'view/';

	const URL_PREVIEW = 'preview/';

	const URL_REPOSITORY = 'repository/';

	// Define Location of the wiki
	const URL_WIKI = 'help/';

	// Define location of the student quick start guide
	const URL_STUDENT_QSTART = '/help/view/Student-Quick-Start-Guide.html';

	// Define Location of the status (twitter) page
	const URL_STATUS = 'http://twitter.com/obojobo';

	// Define Location of the Updates & New Features page
	const URL_UPDATES = 'about/updatesAndFeatures.html';

	// Define location of the Known Issues page
	const URL_ISSUES = 'about/knownIssues.html';

	// Define Location of the about page
	const URL_ABOUT = 'about/aboutObojobo.html';

	// Define location of the twitter proxy
	const URL_TWITTER_PROXY = 'assets/twitterlog.php';

	// Define location of the Flickr API
	const URL_FLICKR_API = 'https://api.flickr.com/services/rest/';

	// Define location of the Known Issues page
	const URL_KNOWN_ISSUES = 'about/knownIssues.html';

	// Define the relative location for the remoting gateway
	const AMF_GATEWAY  = 'api/amf.php';

	// Define the json gateway location
	const JSON_GATEWAY = 'api/json.php';

	//--------------------   ERRORS   -----------------------//
	// Define Debug [true, false]
	const DEBUG_MODE = false;

	// Define depth of backtrace to print
	const DEBUG_BACKTRACE = 3;

	// Write Errors to log [true, false]
	const DEBUG_LOG_ERRORS = true;

	// Define the system's error class
	const ERROR_TYPE = 'obo\util\Error';

	// Enable/Disable Profiling code [true, false]
	const PROFILE_MODE = true;

	//--------------------   CACHE   -----------------------//
	// Should Obobjobo cache the learning objects
	const DB_CACHE_LO = true;

	// Maximum life of cache
	const CACHE_LIFE = 43200; //24 hours

	// Clean Database Interval
	const DB_CLEAN_INTERVAL = 900; //30 minutes

	// Memcache on?
	const CACHE_MEMCACHE = true;

	// to use multiple servers seperate them with comas: 'localhost,localhost' matching to ports '11211,11212'
	const MEMCACHE_HOSTS = 'localhost';
	const MEMCACHE_PORTS = '11211';

	// make cache class
	const CACHE_CLASS = 'obo\util\Cache';

	//--------------------- NOTIFICATION ---------------------------//
	// Send email score notifications to student
	const NOTIFY_SCORE = true;

	//--------------------   AUTHENTICATION   -----------------------//
	// Look at the PLUGINS section for auth plugin modules
	const SESSION_NAME = 'OBOSESSION';

	// Define Idle Time Logout in seconds
	const AUTH_TIMEOUT = 1800; // 30 minute timeout
	const AUTH_TIMEOUT_REMOTING = 240; // 4 minute timeout

	// add in camma seperated class names of authentication plugins to us
	const AUTH_PLUGINS = '\rocketD\auth\ModLTI,\rocketD\auth\ModInternal';

	// which login template from /assets/templates should we show on all login pages
	const LOGIN_TEMPLATE = 'login-default.php';

	// Define Password timelimit in seconds for a password to be valid before needing to be changed
	const AUTH_PW_LIFE = 5184000; // 60 days
	const AUTH_INTERNAL_USERNAME_MATCH = "/^[a-zA-Z0-9\~_]+$/i";

	//--------------------   PLUGINS   -----------------------//

	// add in camma seperated names of plugins from the internal/plugins/ directory
	const CORE_PLUGINS = '';
	const GOOGLE_ANALYTICS_ID = '';

	// REQUIRED MATERIA LTI (using materia in obojobo via lti)
	const MATERIA_LTI_URL = 'https://materia.school.edu/lti/assignment';
	const MATERIA_LTI_PICKER_URL = 'https://materia.school.edu/lti/picker';
	const MATERIA_LTI_SECRET  = 'secret';
	const MATERIA_LTI_KEY  = 'key';
	const MATERIA_LTI_TIMELIMIT = 3600; // OAUTH TIME LIMIT - 1 hr


	// REQUIRED MODLTI (using obojobo in something else as an lti)
	const LTI_LAUNCH_PRESENTATION_RETURN_URL = 'lti/return.php';
	const LTI_EXTERNAL_AUTHMOD = '\rocketD\auth\ModInternal';
	const LTI_OAUTH_KEY = 'key';
	const LTI_OAUTH_SECRET = 'secret';
	const LTI_OAUTH_TIMEOUT = 3600;
	const LTI_REMOTE_USERNAME_FIELD = 'lis_person_sourcedid';
	const LTI_CREATE_USER_IF_MISSING = true;
	const LTI_USE_ROLE = true;

	// REQUIRED CREDHUB @TODO - these shouldnt be required
	const CREDHUB_KEY = 'key';
	const CREDHUB_SECRET = 'secret';
	const CREDHUB_URL = 'https://badges.school.edu/api/badges/award';
	const CREDHUB_TIMEOUT = 1800000;

}
