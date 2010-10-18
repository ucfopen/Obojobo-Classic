<?php
require_once('PHPUnit/Framework.php');

// make our own config

$config = new stdClass();
$config->databaseHost = 'localhost';
$config->databaseName = 'los';
$config->databaseUser = 'root';
$config->databasePass = 'root';
$config->tablePrefix = "lo_";
$config->systemEmail = 'noReply@obojobo.ucf.edu';
$config->systemName = 'Obojobo';
$config->baseDir = dirname(__FILE__) . '/../../../';
$config->webDir = '/';
$config->debug = true;
$config->backTraceDepth = 2;
$config->writeErrorsToLog = f;	
$config->debugLog = $config->baseDir.'internal/logs/php_errors'. date('m_d_y', time()) .'.txt';
$config->debugLogCron = $config->baseDir.'internal/logs/php_errors'. date('m_d_y', time()) .'_cron.txt';
$config->remotingTraceDir = $config->baseDir . 'internal/logs/remoting/';
$config->namespace = $config->baseDir.'internal/classes/namespace.php';
$config->dirCreator = 'creator/';
$config->dirViewer = 'viewer/';
$config->dirRepository = 'repository/';
$config->dirMedia = $config->baseDir.'internal/media/';
$config->dirScripts = $config->baseDir.'internal/scripts/';
$config->dirAssets = 'assets/';
$config->AMFGateway = $config->webDir.'remoting/gateway.php';
$config->AMFDir = $config->baseDir. 'internal/amfphp/';
$config->configDir = 'internal/config/';
$config->wikiURL = 'https://obojobo.ucf.edu/index.php?id=help';
$config->statusURL = 'http://twitter.com/obojobo';
$config->updatesURL = 'https://obojobo.ucf.edu/index.php?id=updates_new_features';
$config->knownIssuesURL = 'https://obojobo.ucf.edu/index.php?id=known_issues';
$config->aboutURL = 'https://obojobo.ucf.edu/index.php?id=about_obojobo';
$config->homeURL = 'https://obojobo.ucf.edu/';
$config->twitterProxyURL = $config->webDir.$config->dirAssets.'twitterlog.php';
$config->saltLength = 16; //64-bit
$config->maxFileSize =  1024 * 1024 * 10; // 10MB
$config->timeLimit = 1800; // 30 minute timeout
$config->timeLimitRemoting = 240; // 4 minute timeout
$config->passwordLife = 5184000; // 60 days
$config->passwordResetLife = 864000; // 10 days
$config->cacheLOs = false;
$config->cacheLife = 43200; //24 hours
$config->cleanDBInterval = 900; //30 minutes
$config->defaultAuthModule = 1;
$config->memcacheEnabled = false;
$config->memcacheServers = array(array('host' => 'localhost', 'port' => '11211')	);
$config->nexMinFlashVersion = '10.0.12.36';
$config->proAccountFormURL = 'https://formmanager.ucf.edu/formsubmit.cfm';
$config->proAccountFormID = '27373';
$config->errorType = 'nm_los_Error';
ini_set('log_errors',1);
ini_set('error_log', $config->debugLog);
ini_set('display_errors',0);
$GLOBALS['amfphp']['hideClassNames'] = !$config->debug;
require_once($config->namespace);
$config->cache = core_util_Cache::getInstance();
$config->moduleConfigs = array();
$config->dbConnData = new core_db_dbConnectData($config->databaseHost , $config->databaseUser, $config->databasePass, $config->databaseName);
unset($config->databaseHost);
unset($config->databaseName);
unset($config->databaseUser);
unset($config->databasePass);
 
class APITest extends PHPUnit_Framework_TestCase
{
	const ADMIN_USER = '~su';
	const ADMIN_PW = 'testPassword';
	const ADMIN_PW2 = 'testPassword2';
	const ADMIN_EMAIL = 'iturgeon@gmail.com';
	const TEST_COUNT = 50;
	
	protected function setUp()
	{
		
		error_reporting(E_ERROR | E_WARNING | E_PARSE);
		$config->writeErrorsToLog = false;
		$config->memcacheEnabled = false; // force memcache off
		$config->cacheLOs = false; // force db cache off
		$config->dirMedia = dirname(__FILE__).'/media/';
		$config->isUnitTest = true;
		// clear cache
		$CM = core_util_Cache::getInstance();
		$CM->clearAllCache();
		$config->dbConnData = new core_db_dbConnectData('localhost' , 'root', 'root', 'los_UnitTest');
	}
	
	protected function getError($errorID)
	{
		
		$write = $config->writeErrorsToLog;
		$debug = $config->debug;
		$config->writeErrorsToLog = false;
		$config->debug = false;
		$storeError = new $config->errorType($errorID);
		$config->writeErrorsToLog = $write;
		$config->debug = $debug;
		return $storeError;
	}
	// TODO: test deleting an LO that has instances owned by the same user, and one that has instances that are owned by a different user
	// TODO: add test for getLOsWithMedia
	public function testSetupDB()
	{
		
		
		// clear database
		$DBSchema = file_get_contents(dirname(__FILE__)."/dbStructure.sql");

		mysql_connect($config->dbConnData->host, $config->dbConnData->user, $config->dbConnData->pass);
		mysql_query('DROP DATABASE ' . $config->dbConnData->db);
		mysql_query('CREATE DATABASE '.$config->dbConnData->db);
		mysql_select_db($config->dbConnData->db);
		mysql_close();
		
		$mysqli = new mysqli($config->dbConnData->host, $config->dbConnData->user, $config->dbConnData->pass, $config->dbConnData->db);
		$mysqli->multi_query($DBSchema);
		$mysqli->close();
		sleep(1);
		
		// insert default data
		$DBData = file_get_contents(dirname(__FILE__)."/dbDefaultData.php");
		eval("\$evaledData = $DBData;");
		
		$mysqli = new mysqli($config->dbConnData->host, $config->dbConnData->user, $config->dbConnData->pass, $config->dbConnData->db);
		$mysqli->multi_query($evaledData);
		$mysqli->close();
		sleep(1);
	}
	
 	public function testgetSessionValidEmpty()
 	{
		$API = nm_los_API::getInstance();
 		$this->assertFalse($API->getSessionValid());
 	}
    
	public function testExpiredPassword()
	{
		// default data starts with an expired password, lets make sure thats working
		$API = nm_los_API::getInstance();
		$this->assertEquals( $this->getError(1004), $API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW2)));
	}
	
	public function testPasswordResetKey()
	{
		// lets test bad data
		$API = nm_los_API::getInstance();
		$invalidInput = $this->getError(2);
		$this->assertEquals($invalidInput, $API->getPasswordReset(self::ADMIN_USER, self::ADMIN_EMAIL, ''));
		$this->assertEquals($invalidInput, $API->getPasswordReset(self::ADMIN_USER, 'bademail@mail.com', 'test'));
		$this->assertFalse($API->getPasswordReset('su', self::ADMIN_EMAIL, 'test'));

		// test good data
		$this->assertTrue($API->getPasswordReset(self::ADMIN_USER, self::ADMIN_EMAIL, 'test'));
		// now lets grab the reset key out of the database and make sure its a sha-1 key
		
		$DBM = $this->doGetDBM();
		$q = $DBM->query('SELECT '.cfg_core_AuthModInternal::RESET_KEY.' FROM '.cfg_core_AuthModInternal::TABLE.' WHERE '.cfg_core_User::ID.' = 1');
		$r = $DBM->fetch_obj($q);
		$this->assertEquals(40 , strlen($r->{cfg_core_AuthModInternal::RESET_KEY}));
		
		// calling again should generate the same key since it has not expired
		$this->assertTrue($API->getPasswordReset(self::ADMIN_USER, self::ADMIN_EMAIL, 'test'));
		// now lets grab the reset key out of the database
		$q2 = $DBM->query('SELECT '.cfg_core_AuthModInternal::RESET_KEY.' FROM '.cfg_core_AuthModInternal::TABLE.' WHERE '.cfg_core_User::ID.' = 1');
		$r2 = $DBM->fetch_obj($q2);
		$this->assertEquals($r->{cfg_core_AuthModInternal::RESET_KEY}, $r2->{cfg_core_AuthModInternal::RESET_KEY});
		
		// lets expire it and try again, we should get a new key
		$DBM->query('UPDATE '.cfg_core_AuthModInternal::TABLE.' SET '.cfg_core_AuthModInternal::RESET_TIME.' = '. (time() - $config->passwordResetLife - 1) .' WHERE '.cfg_core_User::ID.' = 1');
		$this->assertTrue($API->getPasswordReset(self::ADMIN_USER, self::ADMIN_EMAIL, 'test'));
		// now lets grab the reset key out of the database
		$q2 = $DBM->query('SELECT '.cfg_core_AuthModInternal::RESET_KEY.' FROM '.cfg_core_AuthModInternal::TABLE.' WHERE '.cfg_core_User::ID.' = 1');
		$r2 = $DBM->fetch_obj($q2);
		$this->assertFalse($r->{cfg_core_AuthModInternal::RESET_KEY} == $r2->{cfg_core_AuthModInternal::RESET_KEY});
		
		// now lets reset the password using bad data, then our key
		$this->assertEquals($invalidInput, $API->editPasswordWithKey(self::ADMIN_USER, 'bad key', self::ADMIN_PW));
		$this->assertEquals($invalidInput, $API->editPasswordWithKey(self::ADMIN_USER, '901e8f21b5f02a214b08bef8f32c1964f57b43db', self::ADMIN_PW));
		$this->assertEquals($invalidInput, $API->editPasswordWithKey(self::ADMIN_USER, $r2->{cfg_core_AuthModInternal::RESET_KEY}, self::ADMIN_PW));
		$this->assertTrue($API->editPasswordWithKey(self::ADMIN_USER, $r2->{cfg_core_AuthModInternal::RESET_KEY}, md5(self::ADMIN_PW)));
		
		// test to make sure our new password works
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$API->doLogout();
	}



 	public function testgetSessionValidSet()
 	{	
		$API = nm_los_API::getInstance();
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$this->assertTrue($API->getSessionValid());
		$API->doLogout();
 	}
   
	public function testgetSessionValidWithRoleName()
	{
		// test failures
		$API = nm_los_API::getInstance();
		$this->assertFalse($API->getSessionValid(cfg_obo_Role::SU));
		$this->assertFalse($API->getSessionValid(cfg_obo_Role::ADMIN));
		$this->assertFalse($API->getSessionValid(cfg_obo_Role::GUEST));
		$this->assertFalse($API->getSessionValid(cfg_obo_Role::EMPLOYEE_ROLE));
		$this->assertFalse($API->getSessionValid(cfg_obo_Role::CONTENT_CREATOR));
		$this->assertFalse( $API->getSessionValid(cfg_obo_Role::SUPER_VIEWER));
		
		// login and test valid requests
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$this->assertTrue($API->getSessionValid());
		$this->assertTrue($API->getSessionValid(cfg_obo_Role::SU));
		$this->assertTrue($API->getSessionValid(cfg_obo_Role::ADMIN));
		$this->assertTrue( $API->getSessionValid(cfg_obo_Role::CONTENT_CREATOR));
		
		// test logged in invalid requests
		
		$error = $this->getError(4004);
		$this->assertEquals($error, $API->getSessionValid(cfg_obo_Role::SUPER_VIEWER));
		$this->assertEquals($error, $API->getSessionValid(cfg_obo_Role::GUEST));
		$this->assertEquals($error, $API->getSessionValid(cfg_obo_Role::EMPLOYEE_ROLE));
		$this->assertEquals(null, $API->doLogout());
	}
    	
	public function testgetSessionRoleValid()
	{	
		$API = nm_los_API::getInstance();
		
		$error = $this->getError(2);
		$this->assertEquals($error, $API->getSessionRoleValid(cfg_obo_Role::SU));
		$this->assertEquals(array('validSession' => null, 'roleNames' => array(cfg_obo_Role::SU), 'hasRoles' => array()), $API->getSessionRoleValid(array(cfg_obo_Role::SU)));
		$this->assertEquals(array('validSession' => null, 'roleNames' => array(cfg_obo_Role::ADMIN), 'hasRoles' => array()), $API->getSessionRoleValid(array(cfg_obo_Role::ADMIN)));
		$this->assertEquals(array('validSession' => null, 'roleNames' => array(cfg_obo_Role::CONTENT_CREATOR), 'hasRoles' => array()), $API->getSessionRoleValid(array(cfg_obo_Role::CONTENT_CREATOR)));
		$this->assertEquals(array('validSession' => null, 'roleNames' => array(cfg_obo_Role::SUPER_VIEWER), 'hasRoles' => array()), $API->getSessionRoleValid(array(cfg_obo_Role::SUPER_VIEWER)));
		$this->assertEquals(array('validSession' => null, 'roleNames' => array('WikiEditor'), 'hasRoles' => array()), $API->getSessionRoleValid(array('WikiEditor')));
		$this->assertEquals(array('validSession' => null, 'roleNames' => array(cfg_obo_Role::EMPLOYEE_ROLE), 'hasRoles' => array()), $API->getSessionRoleValid(array(cfg_obo_Role::EMPLOYEE_ROLE)));
		$this->assertEquals(array('validSession' => null, 'roleNames' => array(cfg_obo_Role::EMPLOYEE_ROLE, 'WikiEditor'), 'hasRoles' => array()), $API->getSessionRoleValid(array(cfg_obo_Role::EMPLOYEE_ROLE, 'WikiEditor')));
			
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$this->assertEquals(array('validSession' => true, 'roleNames' => array(cfg_obo_Role::SU), 'hasRoles' => array(cfg_obo_Role::SU)), $API->getSessionRoleValid(array(cfg_obo_Role::SU)));
		$this->assertEquals(array('validSession' => true, 'roleNames' => array(cfg_obo_Role::ADMIN), 'hasRoles' => array(cfg_obo_Role::ADMIN)), $API->getSessionRoleValid(array(cfg_obo_Role::ADMIN)));
		$this->assertEquals(array('validSession' => true, 'roleNames' => array(cfg_obo_Role::CONTENT_CREATOR), 'hasRoles' => array(cfg_obo_Role::CONTENT_CREATOR)), $API->getSessionRoleValid(array(cfg_obo_Role::CONTENT_CREATOR)));
		$this->assertEquals(array('validSession' => true, 'roleNames' => array(cfg_obo_Role::SUPER_VIEWER), 'hasRoles' => array()), $API->getSessionRoleValid(array(cfg_obo_Role::SUPER_VIEWER)));
			
		$this->assertEquals($error, $API->getSessionRoleValid(cfg_obo_Role::SUPER_VIEWER));
			
		$this->assertEquals(array('validSession' => true, 'roleNames' => array('WikiEditor'), 'hasRoles' => array()), $API->getSessionRoleValid(array('WikiEditor')));
		$this->assertEquals(array('validSession' => true, 'roleNames' => array(cfg_obo_Role::EMPLOYEE_ROLE), 'hasRoles' => array()), $API->getSessionRoleValid(array(cfg_obo_Role::EMPLOYEE_ROLE)));
		$this->assertEquals(array('validSession' => true, 'roleNames' => array(cfg_obo_Role::EMPLOYEE_ROLE, 'WikiEditor'), 'hasRoles' => array()), $API->getSessionRoleValid(array(cfg_obo_Role::EMPLOYEE_ROLE, 'WikiEditor')));
		$this->assertEquals(null, $API->doLogout());
	}
    	
	public function testLogin()
	{
		$API = nm_los_API::getInstance();
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$this->assertEquals(null, $API->doLogout());
			
		// try adding spaces that should be trimed off
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW) . ' '));
		$this->assertEquals(null, $API->doLogout());		
		$this->assertTrue($API->doLogin(self::ADMIN_USER, ' '.md5(self::ADMIN_PW)));
		$this->assertEquals(null, $API->doLogout());
		$this->assertTrue($API->doLogin(self::ADMIN_USER, ' ' .md5(self::ADMIN_PW) . ' '));
		$this->assertEquals(null, $API->doLogout());
		$this->assertTrue($API->doLogin(' '.self::ADMIN_USER, md5(self::ADMIN_PW)));
		$this->assertEquals(null, $API->doLogout());
		$this->assertTrue($API->doLogin(self::ADMIN_USER.' ', md5(self::ADMIN_PW)));
		$this->assertEquals(null, $API->doLogout());
		$this->assertTrue($API->doLogin(' '.self::ADMIN_USER.' ', md5(self::ADMIN_PW)));
		$this->assertEquals(null, $API->doLogout());
		
		// try invalid password
		$error = $this->getError(1003);
		$this->assertEquals($error, $API->doLogin(self::ADMIN_USER, md5('adsfadsfadsfdasf')));
		//$this->assertEquals($error, $API->doLogin(self::ADMIN_USER, self::ADMIN_PW));
		$this->assertEquals(null, $API->doLogout());
	}
    

	public function testCreateInternalUser()
	{
		$API = nm_los_API::getInstance();
		require_once dirname(__FILE__)."/php-faker/faker.php";
		
		$error = $this->getError(2);
		$error2 = $this->getError(0);
		$error3 = $this->getError(4);
		// TODO: refine to test creating user API once its made
		
		// test failure
		$user = $this->doMakeFakeUserArray('~');
		$am = core_auth_AuthManager::getInstance();

		// invalid input
		@$this->assertEquals($error, $am->saveUser(5, 5));
		// without md5'd password
		$this->assertEquals($error3, $am->saveUser($user, array('MD5Pass' => self::ADMIN_PW)));
		// valid, but not logged in
		$this->assertEquals($error3, $am->saveUser($user, array('MD5Pass' => md5(self::ADMIN_PW))));
		
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		
		// invalid input
		@$this->assertEquals($error, $am->saveUser(5, 5));
		// without md5'd password
		$this->assertEquals($error2, $am->saveUser($user, array('MD5Pass' => self::ADMIN_PW)));
		// valid
		$this->assertTrue($am->saveUser($user, array('MD5Pass' => md5(self::ADMIN_PW))));
		
		
		// now lets make sure that authmod internal was used core_auth_ModInternal
		$authModInternal = $am->getAuthModuleForUsername($user['login']);
		$this->assertType('core_auth_ModInternal',$authModInternal);
		
		$userID = $authModInternal->getUIDforUsername($user['login']);
		$this->assertGreaterThan( 0, $userID);
		
		// lets make sure the user comes back as it was sent
		$createdUser = $authModInternal->fetchUserByID($userID);
		$this->doTestUser($createdUser);
		$this->assertEquals($userID, $createdUser->userID);
		$this->assertEquals($user['login'], $createdUser->login);
		$this->assertEquals(null, $API->doLogout());
		
	}
	
	public function testremoveInternalUser()
	{
		$API = nm_los_API::getInstance();
		$error0 = $this->getError(0);
		$error2 = $this->getError(2);
		$error4 = $this->getError(4);
		$am = core_auth_AuthManager::getInstance();
		// TODO: change this to use the api
		
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		
		$allUsers = $API->getUsers();
		$this->assertEquals(2, count($allUsers));

		$userID = $allUsers[1]->userID;
		$this->assertTrue($userID != 1); // make sure that the returned items are sorted so that our new user is 2nd
		$this->assertEquals(null, $API->doLogout());
		
		$authModInternal = new core_auth_ModInternal();
		// test logged out
		$result = $am->removeUser(0);
		$this->assertEquals($error2, $result);
		$this->assertEquals($error4, $am->removeUser(1));
		$this->assertEquals($error4, $am->removeUser($userID));
		
		// test logged in
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$this->assertEquals($error2, $am->removeUser(0));
		$this->assertEquals($error0, $am->removeUser(1)); // try to remove self, should fail
		$this->assertTrue($am->removeUser($userID));
		
		$userID = $authModInternal->getUIDforUsername('~test');
		$this->assertFalse($userID);
	}

	public function testgetUser()
	{
		$API = nm_los_API::getInstance();
		$error = $this->getError(1);
		// test failures
		$this->assertEquals($error, $API->getUser());
		
		// login and make sure the data is correct	
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$user =  $API->getUser();
		$this->doTestUser($user);
		$this->assertEquals(1, $user->userID);
		$this->assertEquals(self::ADMIN_USER, $user->login);
		$this->assertEquals(null, $API->doLogout());
	}
    	
	public function testGetUserName()
	{
		$API = nm_los_API::getInstance();
		$error2 = $this->getError(2);
		$error = $this->getError(1);
		// test failures
		$this->assertEquals($error, $API->getUserName(-1));
		$this->assertEquals($error, $API->getUserName(0));
		$this->assertEquals($error, $API->getUserName('adsfdfs'));
		$this->assertEquals($error, $API->getUserName(array(3)));		
		$this->assertEquals($error, $API->getUserName(1));
	
		// login and test
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));		
		$this->assertEquals('New Media', $API->getUserName(1));
		$this->assertFalse($API->getUserName(999999999999999999999999)); // get a user that doesnt exist
		$this->assertEquals(null, $API->doLogout());
		// TODO: test someone with a middle name
	}

	public function testGetUsers()
	{
		$API = nm_los_API::getInstance();
		$error = $this->getError(1);
		$this->assertEquals($error, $API->getUsers());
		
		$this->assertTrue( $API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		
		// make a batch of 50 users
		$am = core_auth_AuthManager::getInstance();
		$i = self::TEST_COUNT;
		while($i--)
		{
			$user = $this->doMakeFakeUserArray('~');
			$this->assertTrue($am->saveUser($user, array('MD5Pass' => md5(self::ADMIN_PW))));
		}
		
		$allUsers = $API->getUsers();
		$this->assertTrue(count($allUsers) == (self::TEST_COUNT + 1));
		foreach($allUsers as &$user)
		{
			$this->doTestUser($user);
		}
		$this->assertEquals(null, $API->doLogout());
	}
	
	public function testEditUsersRoles()
	{
		$API = nm_los_API::getInstance();
		
		//setup
		$this->assertTrue( $API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		// add contentcreator role to the last half of the users
		$allUsers = $API->getUsers();
		$this->assertTrue(count($allUsers) > 0);
		$users = array();
		for($i = round(count($allUsers)/2); $i < count($allUsers); $i++)
		{
			$users[] = $allUsers[$i]->userID;
		}
		$this->assertTrue($API->editUsersRoles($users, array(cfg_obo_Role::CONTENT_CREATOR,cfg_obo_Role::LIBRARY_USER)));
		
		// TODO: test not logged in, test with bad user id, test with bad roles
		// 
	}

   	public function testGetUsersInRole()
   	{
		$API = nm_los_API::getInstance();
		$error = $this->getError(1);
		$this->assertEquals($error, $API->getUsersInRole(cfg_obo_Role::CONTENT_CREATOR));
		$this->assertTrue( $API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$users =  $API->getUsersInRole(cfg_obo_Role::CONTENT_CREATOR);
		$this->assertTrue(count($users) == round(self::TEST_COUNT/2)+1);
		foreach($users as &$user)
		{
			$this->doTestUser($user);
		}
 		// TODO: add and remove a content creator to test
   	}

   	public function testdeleteLO()
   	{
		$API = nm_los_API::getInstance();
		$error = $this->getError(1);
		$error2 = $this->getError(2);
		@$this->assertEquals($error, $API->removeLO());
		$this->assertEquals($error, $API->removeLO(1));
		$this->assertEquals($error, $API->removeLO('asdfadsfadsf'));
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$lo = $this->makeBasicLO();
		$newLO = $API->createDraft($lo);
		$lo = $API->getLO($newLO->loID);
		$this->doTestLO($newLO->loID, $newLO);
		$this->assertTrue($API->removeLO($newLO->loID));
		$this->assertEquals(null, $API->doLogout());
   	}

    public function testgetLO()
 	{
		$API = nm_los_API::getInstance();
		$error = $this->getError(2);
		
		@$this->assertEquals($error, $API->getLO());
		$this->assertEquals($error, $API->getLO('afadfs'));
		$this->assertEquals($error, $API->getLO(0));
		$this->assertEquals($error, $API->getLO(-1));

		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));

		$lo = $this->makeBasicLO();
		$newLO = $API->createDraft($lo);
		$LOID = $newLO->loID;
		
		@$this->assertEquals($error, $API->getLO());
		$this->assertEquals($error, $API->getLO('afadfs'));
		$this->assertEquals($error, $API->getLO(0));
		$this->assertEquals($error, $API->getLO(-1));

		$lo = $API->getLO($LOID);
		$this->doTestLO($LOID, $lo);
		$this->assertEquals(null, $API->doLogout());
 	}

	public function testGetDraftOfLO()
	{
		$API = nm_los_API::getInstance();
		$error = $this->getError(2);
		
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
	
		$lo = $this->makeBasicLO();
		$newLO = $API->createDraft($lo);
		// TODO: finish
	}

	public function testGetLOMeta()
	{
		$API = nm_los_API::getInstance();
		$error = $this->getError(1);
		$error2 = $this->getError(2);
				
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$lo = $this->makeBasicLO();
		$newLO = $API->createDraft($lo);
		$this->assertEquals(null, $API->doLogout());
		
		$LOID = $newLO->loID;
		@$this->assertEquals($error2, $API->getLOMeta());
		$this->assertEquals($error2, $API->getLOMeta('afadfs'));
		$this->assertEquals($error2, $API->getLOMeta(0));
		$this->assertEquals($error2, $API->getLOMeta(-1));
		$lo = $API->getLOMeta($LOID);
		$this->doTestLO($LOID, $lo, true);
		//TODO: test the newest argument of getLOMeta
	}

	public function testgetLOs()
	{
		$API = nm_los_API::getInstance();
		$this->assertEquals($this->getError(1), $API->getLOs());
		
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$los = $API->getLOs();
		$this->assertType('array', $los);
		$this->assertGreaterThan(0, count($los));
		$loIDArr = array(); // for testing the optional argument of getLOs
		foreach($los AS &$lo)
		{
			$loIDArr[] = $lo->loID;
			$this->assertType('nm_los_LO', $lo);
		}
		
		// test sending an array to get LOs
		array_shift($loIDArr);
		$los2 = $API->getLOs($loIDArr);
		$this->assertType('array', $los2);
		$this->assertEquals( count($los2)+1, count($los) );
		foreach($los2 AS &$lo)
		{
			$this->assertType('nm_los_LO', $lo);
		}
		
		$this->assertEquals(null, $API->doLogout());
		
	}
	
	public function testcreateMaster()
	{
		$error = $this->getError(1);
		$error2 = $this->getError(2);
		$API = nm_los_API::getInstance();
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$lo = $this->makeBasicLO();
		
		$lo2 = $this->makeBasicLO();
		$lo2['title'] = 'Test Master';

		$lo3 = $this->makeBasicLO();
		$lo3['title'] = 'Test Master1';
		$lo3['objective'] = 'This is an Objective';

		$lo4 = $this->makeBasicLO();
		$lo4['title'] = 'Test Master2';
		$lo4['objective'] = 'This is an Objective';
		$lo4['keywords'][] = 'Test';
		
		$lo5 = $this->makeBasicLO();
		$lo5['title'] = 'Test Master3';
		$lo5['objective'] = 'This is an Objective';
		$lo5['keywords'][] = 'Test';
		$lo5['learnTime'] = 99;
				
		$newLO = $API->createDraft($lo);
		$newLO2 = $API->createDraft($lo2);
		$newLO3 = $API->createDraft($lo3);
		$newLO4 = $API->createDraft($lo4);
		$newLO5 = $API->createDraft($lo5);
		
		$newLO = $API->getLO($newLO->loID);
		$newLO2 = $API->getLO($newLO2->loID);
		$newLO3 = $API->getLO($newLO3->loID);
		$newLO4 = $API->getLO($newLO4->loID);
		$newLO5 = $API->getLO($newLO5->loID);
		
		$this->doTestLO($newLO->loID, $newLO);
		$this->doTestLO($newLO2->loID, $newLO2);
		$this->doTestLO($newLO3->loID, $newLO3);
		$this->doTestLO($newLO4->loID, $newLO4);
		$this->doTestLO($newLO5->loID, $newLO5);
		
		$this->assertEquals(null, $API->doLogout());
		$this->assertEquals($error, $API->createMaster($newLO->loID));
		$this->assertEquals($error, $API->createMaster($newLO2->loID));
		$this->assertEquals($error, $API->createMaster($newLO3->loID));
		$this->assertEquals($error, $API->createMaster($newLO4->loID));
		$this->assertEquals($error, $API->createMaster($newLO5->loID));
		
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$this->assertEquals($error2, $API->createMaster($newLO->loID)); // no title
		$this->assertEquals($error2, $API->createMaster($newLO2->loID)); // no objective
		$this->assertEquals($error2, $API->createMaster($newLO3->loID)); // no keywords
		$this->assertEquals($error2, $API->createMaster($newLO4->loID)); // no learning time
		$this->assertTrue( $API->createMaster($newLO5->loID)); // should work
		
		$this->assertEquals($error2, $API->createMaster($newLO5->loID)); // should fail, this loid is already a master
		
		// make another draft like lo5 to create a 2nd master
		$lo5['title'] = 'Test Master4';
		$newLO99 = $API->createDraft($lo5);
		$newLO99 = $API->getLO($newLO99->loID);
		$this->assertTrue($API->createMaster($newLO99->loID));
		
		$this->assertEquals(null, $API->doLogout());
	}
	
	public function testcreateLibraryLO()
	{
		$error = $this->getError(1);
		$error2 = $this->getError(2);
		$API = nm_los_API::getInstance();
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$loMan = nm_los_LOManager::getInstance();
		$masters = $loMan->getMyMasters();
		$drafts = $loMan->getMyDrafts();
		
		$this->assertEquals(null, $API->doLogout());
		// not logged in
		$this->assertEquals($error, $API->createLibraryLO($masters[0]->loID, false));
		// erronious input
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$this->assertEquals($error2, $API->createLibraryLO(0, false));
		$this->assertEquals($error2, $API->createLibraryLO($drafts[0]->loID, false)); // trying to place a draft in the library
		// success expected
		
		$this->assertTrue($API->createLibraryLO($masters[0]->loID, false)); 
		$this->assertTrue($API->createLibraryLO($masters[1]->loID, false)); 
		$this->assertEquals(null, $API->doLogout());
		
	}
	
	public function testgetLibraryLOs()
	{
		$API = nm_los_API::getInstance();
		$this->assertEquals($this->getError(1), $API->getLibraryLOs());
		
		//$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$this->logInAsRandomUser(cfg_obo_Role::LIBRARY_USER);
		$los = $API->getLibraryLOs();
		$this->assertType('array', $los);
		$this->assertEquals(2, count($los));
		foreach($los AS &$lo)
		{
			$this->assertType('nm_los_LO', $lo);
			$this->assertEquals(0, $lo->subVersion);
			$this->assertTrue($lo->perms->read == 1 ); // we should have read to everything here
			$this->doTestLO($lo->loID, $lo, true);	
		}
		$this->assertEquals(null, $API->doLogout());
	}

	// TODO: try this with different user permissions
   	public function testcreateDerivative()
	{
		$API = nm_los_API::getInstance();
		$error = $this->getError(1);
		$error2 = $this->getError(2);
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$loMan = nm_los_LOManager::getInstance();
		$masters = $loMan->getMyMasters();
		$drafts = $loMan->getMyDrafts();
		$this->assertEquals(null, $API->doLogout());
		
		foreach($drafts AS &$draft)
		{
			$this->assertEquals($error, $API->createDerivative($draft->loID));
		}
		foreach($masters AS &$master)
		{
			$this->assertEquals($error, $API->createDerivative($master->loID));
		}
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		foreach($drafts AS &$draft)
		{
			// fail, only masters are elegable to make derivatives
			$this->assertEquals($error2, $API->createDerivative($draft->loID));
		}
		foreach($masters AS &$master)
		{
			$newLO = $API->createDerivative($master->loID);
			$this->assertTrue(nm_los_Validator::isPosInt($newLO));
			$lo = $API->getLO($newLO);
			$this->doTestLO($lo->loID, $lo);
		}
		
		$this->assertEquals(null, $API->doLogout());
	}
	
	// TODO: try with different users & perms
   	public function testremoveLibraryLO()
	{
		$API = nm_los_API::getInstance();
		$error = $this->getError(1);
		$error2 = $this->getError(2);
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$libs = $API->getLibraryLOs();
		$this->assertGreaterThan(0, count($libs));
		$loMan = nm_los_LOManager::getInstance();
		$masters = $loMan->getMyMasters();
		$drafts = $loMan->getMyDrafts();
		$this->assertEquals(null, $API->doLogout());
		
		// fail
		@$this->assertEquals($error, $API->removeLibraryLO() );
		$this->assertEquals($error, $API->removeLibraryLO(0) );
		$this->assertEquals($error, $API->removeLibraryLO(-1) );
		$this->assertEquals($error, $API->removeLibraryLO('monkey') );
		$this->assertEquals($error, $API->removeLibraryLO(array($drafts[0]->loID, $drafts[0]->loID) ) );
		$this->assertEquals($error, $API->removeLibraryLO($drafts[0]->loID) );
		$this->assertEquals($error, $API->removeLibraryLO($masters[0]->loID) );
		$this->assertEquals($error, $API->removeLibraryLO($libs[0]->loID) );
		
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		@$this->assertEquals($error2, $API->removeLibraryLO() );
		$this->assertEquals($error2, $API->removeLibraryLO(0) );
		$this->assertEquals($error2, $API->removeLibraryLO(-1) );
		$this->assertEquals($error2, $API->removeLibraryLO('monkey') );
		$this->assertEquals($error2, $API->removeLibraryLO(array($drafts[0]->loID, $drafts[0]->loID) ) );
		$this->assertFalse($API->removeLibraryLO($drafts[0]->loID) ); // TODO: maybe... this should return and error2, but the function would have to run another query, for sake of speed, this will return true harmlessly
		$this->assertFalse($API->removeLibraryLO($masters[0]->loID) );
		$this->assertTrue( $API->removeLibraryLO($libs[0]->loID) );
		$this->assertTrue(count($API->getLibraryLOs()) == count($libs) - 1);
		
		$this->assertEquals(null, $API->doLogout());
		
	}
   	
	// TODO: should locks actually prevent saving drafts/masters/?
	public function testcreateLOLock()
	{
		$API = nm_los_API::getInstance();
		$error = $this->getError(1);
		$error2 = $this->getError(2);
		$error4 = $this->getError(4);
		
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$libs = $API->getLibraryLOs();
		$loMan = nm_los_LOManager::getInstance();
		$masters = $loMan->getMyMasters();
		$drafts = $loMan->getMyDrafts();
		$this->assertEquals(null, $API->doLogout());
		
		@$this->assertEquals($error, $API->createLOLock());
		$this->assertEquals($error, $API->createLOLock(-1));
		$this->assertEquals($error, $API->createLOLock(0));
		$this->assertEquals($error, $API->createLOLock($drafts[0]->loID));
		
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$me = $API->getUser();
		
		@$this->assertEquals($error2, $API->createLOLock());
		$this->assertEquals($error2, $API->createLOLock(-1));
		$this->assertEquals($error2, $API->createLOLock(0));
		$lock = $API->createLOLock($drafts[0]->loID);
		$this->assertType('nm_los_Lock' , $lock);
		$this->assertType('core_auth_User' , $lock->user);
		$this->assertEquals($me, $lock->user);
		$this->assertTrue(nm_los_Validator::isPosInt($lock->lockID));
		$this->assertTrue(nm_los_Validator::isPosInt($lock->unlockTime));
		$this->assertEquals($drafts[0]->loID, $lock->loID);

		$lock = $API->createLOLock($masters[0]->loID);
		$this->doTestUser($lock->user);
		$this->assertType('nm_los_Lock' , $lock);
		$this->assertEquals($me, $lock->user);
		$this->assertTrue(nm_los_Validator::isPosInt($lock->lockID));
		$this->assertTrue(nm_los_Validator::isPosInt($lock->unlockTime));
		$this->assertEquals($masters[0]->loID, $lock->loID);

		$lock = $API->createLOLock($libs[0]->loID);
		$this->assertType('nm_los_Lock' , $lock);
		$this->doTestUser($lock->user);
		$this->assertEquals($me, $lock->user);
		$this->assertTrue(nm_los_Validator::isPosInt($lock->lockID));
		$this->assertTrue(nm_los_Validator::isPosInt($lock->unlockTime));
		$this->assertEquals($libs[0]->loID, $lock->loID);
		
		$this->logInAsRandomUser(cfg_obo_Role::LIBRARY_USER);
		
		@$this->assertEquals($error2, $API->createLOLock());
		$this->assertEquals($error2, $API->createLOLock(-1));
		$this->assertEquals($error2, $API->createLOLock(0));
		$this->assertEquals($error4 , $API->createLOLock($drafts[0]->loID));
		// $this->doTestUser($lock->user);
		// $this->assertEquals($me, $lock->user);
		// $this->assertTrue(nm_los_Validator::isPosInt($lock->lockID));
		// $this->assertTrue(nm_los_Validator::isPosInt($lock->unlockTime));
		// $this->assertEquals($drafts[0]->loID, $lock->loID);

		$lock = $API->createLOLock($masters[0]->loID);
		$this->assertEquals($error4 , $lock);


		$lock = $API->createLOLock($libs[0]->loID);
		$this->assertEquals($error4 , $lock);

		
		$this->assertEquals(null, $API->doLogout());
		
	}
	
	// TODO: should removeLoLock return false if there is a lock and I cant unlock it?
	public function testremoveLOLock()
	{
		$API = nm_los_API::getInstance();
		$error = $this->getError(1);
		$error2 = $this->getError(2);
		$error4 = $this->getError(4);
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$me = $API->getUser();
		$libs = $API->getLibraryLOs();
		$loMan = nm_los_LOManager::getInstance();
		$masters = $loMan->getMyMasters();
		$drafts = $loMan->getMyDrafts();
		$this->assertEquals(null, $API->doLogout());
		
		@$this->assertEquals($error, $API->removeLOLock());
		$this->assertEquals($error, $API->removeLOLock(-1));
		$this->assertEquals($error, $API->removeLOLock(0));
		$this->assertEquals($error, $API->removeLOLock($drafts[0]->loID));
		
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$me = $API->getUser();
		
		@$this->assertEquals($error2, $API->removeLOLock());
		$this->assertEquals($error2, $API->removeLOLock(-1));
		$this->assertEquals($error2, $API->removeLOLock(0));
		$this->assertTrue($API->removeLOLock($drafts[0]->loID));
		$this->assertTrue($API->removeLOLock($masters[0]->loID));
		
		$this->logInAsRandomUser(cfg_obo_Role::LIBRARY_USER);
		
		@$this->assertEquals($error2, $API->createLOLock());
		$this->assertEquals($error2, $API->createLOLock(-1));
		$this->assertEquals($error2, $API->createLOLock(0));
		$this->assertEquals($error4 ,$API->removeLOLock($drafts[0]->loID));
		$this->assertEquals($error4, $API->removeLOLock($masters[0]->loID));
		$this->assertEquals($error4, $API->createLOLock($drafts[0]->loID));
		$this->assertEquals($error4, $API->removeLOLock($drafts[0]->loID));
		
		$lock = $API->createLOLock($libs[0]->loID);
		$this->assertEquals($error4, $lock);
		//$this->doTestUser($lock->user);
		//$this->assertEquals($me, $lock->user);
		//$this->assertTrue(nm_los_Validator::isPosInt($lock->lockID));
		//$this->assertTrue(nm_los_Validator::isPosInt($lock->unlockTime));
		//$this->assertEquals($libs[0]->loID, $lock->loID);
		
		$this->assertEquals(null, $API->doLogout());
		
	}
	public function testcreateInstance()
	{
		$API = nm_los_API::getInstance();
		$error = $this->getError(1);
		$error2 = $this->getError(2);
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$me = $API->getUser();
		$libs = $API->getLibraryLOs();
		$loMan = nm_los_LOManager::getInstance();
		$masters = $loMan->getMyMasters();
		$drafts = $loMan->getMyDrafts();
		$this->assertEquals(null, $API->doLogout());
		
		@$this->assertEquals($error, $API->createInstance(5, 'test', -55, array(), -34));
		$this->assertEquals($error, $API->createInstance('Test Instance', $masters[0]->loID, 'TestCourseID', time(), time()+1, 1, 'h'));
		$this->assertEquals($error, $API->createInstance('Test Instance', $libs[0]->loID, 'TestCourseID', time(), time()+1, 1, 'h'));
		$this->assertEquals($error, $API->createInstance('Test Instance', $drafts[0]->loID, 'TestCourseID', time(), time()+1, 1, 'h'));
		
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		@$this->assertEquals($error2, $API->createInstance(5, 'test', -55, array(), -34));
		$inst1 = $API->createInstance('Test Instance', $masters[0]->loID, 'TestCourseID', time(), time()+1, 1, 'h');
		$this->assertTrue(nm_los_Validator::isPosInt($inst1));
		$inst2 = $API->createInstance('Test Instance', $libs[0]->loID, 'TestCourseID', time(), time()+1, 1, 'h');
		$this->assertTrue(nm_los_Validator::isPosInt($inst2));
		$this->assertEquals($error2, $API->createInstance('Test Instance', $drafts[0]->loID, 'TestCourseID', time(), time()+1, 1, 'h'));
		
		$meta = $API->getInstanceData($inst1);
		$this->doTestInstanceData($meta);
		$meta = $API->getInstanceData($inst2);
		$this->doTestInstanceData($meta);
	}
	
	public function testgetInstancesOfLO()
	{
		$API = nm_los_API::getInstance();
		$error = $this->getError(1);
		$error2 = $this->getError(2);
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$libs = $API->getLibraryLOs();
		$loMan = nm_los_LOManager::getInstance();
		$masters = $loMan->getMyMasters();
		$this->assertEquals(null, $API->doLogout());
		
		$this->assertEquals($error, $API->getInstancesOfLO(-5));
		$this->assertEquals($error, $API->getInstancesOfLO('whoopths'));
		$this->assertEquals($error, $API->getInstancesOfLO(34));
		$this->assertEquals($error, $API->getInstancesOfLO($libs[0]->loID));
		$this->assertEquals($error, $API->getInstancesOfLO($masters[0]->loID));
		
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		
		$this->assertEquals($error2, $API->getInstancesOfLO(-5));
		$this->assertEquals($error2, $API->getInstancesOfLO('whoopths'));
		$this->assertEquals(array(), $API->getInstancesOfLO(34));
		$instances = $API->getInstancesOfLO($libs[0]->loID);
		foreach($instances AS &$instance)
		{
			$this->doTestInstanceData($instance);
		}
		$instances = $API->getInstancesOfLO($masters[0]->loID);
		foreach($instances AS $instance)
		{
			$this->doTestInstanceData($instance);
		}
		$this->assertEquals(null, $API->doLogout());
	}
	
	// TODO: finish
	public function testcreateInstanceVisit()
	{
		$API = nm_los_API::getInstance();
		$error = $this->getError(1);
		$error2 = $this->getError(2);
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		
		$instances = $API->getInstances();
		$this->assertEquals(null, $API->doLogout());
		
		$this->assertEquals($error, $API->createInstanceVisit(0));
		$this->assertEquals($error, $API->createInstanceVisit(-43));
		$this->assertEquals($error, $API->createInstanceVisit(1.42));
		$this->assertEquals($error, $API->createInstanceVisit('1'));
		$this->assertEquals($error, $API->createInstanceVisit('whoopths'));
		$this->assertEquals($error, $API->createInstanceVisit(array()));
		$this->assertEquals($error, $API->createInstanceVisit($API));
		foreach($instances AS $instance)
		{
			$this->assertEquals($error, $API->createInstanceVisit($instance->instID));
			
		}
		
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$this->assertEquals($error2, $API->createInstanceVisit(0));
		$this->assertEquals($error2, $API->createInstanceVisit(-43));
		$this->assertEquals($error2, $API->createInstanceVisit(1.42));
		$this->assertEquals($error2, $API->createInstanceVisit('whoopths'));
		$this->assertEquals($error2, $API->createInstanceVisit(array()));
		$this->assertEquals($error2, $API->createInstanceVisit($API));
		// try opening them all
		foreach($instances AS $instance)
		{
		//	$this->assertEquals($error2, $API->createInstanceVisit($instance->instID));
			// test return for all the visit data
		}
		$this->assertEquals(null, $API->doLogout());
	}
	
	public function testgetInstances()
	{
		$API = nm_los_API::getInstance();
		$error = $this->getError(1);
		$error2 = $this->getError(2);
		$this->assertEquals($error, $API->getInstances());
		
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$instances = $API->getInstances();
		$this->assertTrue(is_array($instances));
		foreach($instances AS $instance)
		{
			$this->doTestInstanceData($instance);
		}
		$this->assertEquals(null, $API->doLogout());
		// TODO: test other users visibility
	}
	public function testeditInstance(){}//!
	
	public function testremoveInstance()
	{
		$API = nm_los_API::getInstance();
		$error = $this->getError(1);
		$error2 = $this->getError(2);
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$instances = $API->getInstances();
		$this->assertTrue(is_array($instances));
		foreach($instances AS $instance)
		{
			$this->doTestInstanceData($instance);
		}
		$this->assertEquals(null, $API->doLogout());
		
		$this->assertEquals($error, $API->removeInstance(0));
		$this->assertEquals($error, $API->removeInstance(-43));
		$this->assertEquals($error, $API->removeInstance(1.42));
		$this->assertEquals($error, $API->removeInstance('whoopths'));
		$this->assertEquals($error, $API->removeInstance(array()));
		$this->assertEquals($error, $API->removeInstance($API));
		foreach($instances AS $instance)
		{
			$this->assertEquals($error, $API->removeInstance($instance));
		}
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$this->assertEquals($error2, $API->removeInstance(0));
		$this->assertEquals($error2, $API->removeInstance(-43));
		$this->assertEquals($error2, $API->removeInstance(1.42));
		$this->assertEquals($error2, $API->removeInstance('whoopths'));
		$this->assertEquals($error2, $API->removeInstance(array()));
		$this->assertEquals($error2, $API->removeInstance($API));
		
		foreach($instances AS $instance)
		{
			$this->assertTrue($API->removeInstance($instance->instID));
			$this->assertGreaterThan(count($API->getInstances()), count($instances) );
		}
		$this->assertEquals(count($API->getInstances()), 0 );
	}
	
	public function testcreateMedia()
	{
		$API = nm_los_API::getInstance();
		$error = $this->getError(1);
		$error2 = $this->getError(2);
		
		// $this->assertEquals($error2, $API->createMedia('a'));
		// $this->assertEquals($error2, $API->createMedia(0));
		// $this->assertEquals($error2, $API->createMedia(4.3));
		// $this->assertEquals($error2, $API->createMedia(array()));
		// $this->assertEquals($error2, $API->createMedia($API));
		// $this->assertEquals($error, $API->createMedia(new nm_los_Media()));
		$fileData = $this->doMakeFileData(dirname(__FILE__).'/media/image.gif');
		$mm = nm_los_MediaManager::getInstance();
		$return = $mm->handleMediaUpload($fileData, 'image title', 'image description', 'copyright', '5000');
		$this->assertFalse($return);
		
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		
		// $this->assertEquals($error2, $API->createMedia('a'));
		// $this->assertEquals($error2, $API->createMedia(0));
		// $this->assertEquals($error2, $API->createMedia(4.3));
		// $this->assertEquals($error2, $API->createMedia(array()));
		// $this->assertEquals($error2, $API->createMedia($API));
		
		// have to simulate what happens in upAsset
		// move a file to the media dir giving it a hash of it's name
	
		$fileData = $this->doMakeFileData(dirname(__FILE__).'/media/image.gif');
		$mm = nm_los_MediaManager::getInstance();
		$return = $mm->handleMediaUpload($fileData, 'image title', 'image description', 'copyright', '5000');
		$this->assertTrue($return);
		// test to make sure all the right media dimensions get set
		
		$fileData = $this->doMakeFileData(dirname(__FILE__).'/media/image.jpeg');
		$return = $mm->handleMediaUpload($fileData, 'image title', 'image description', 'copyright', '5000');
		$this->assertTrue($return);
		
		$fileData = $this->doMakeFileData(dirname(__FILE__).'/media/image.png');
		$return = $mm->handleMediaUpload($fileData, 'image title', 'image description', 'copyright', '5000');
		$this->assertTrue($return);
		
		$fileData = $this->doMakeFileData(dirname(__FILE__).'/media/flash6.swf');
		$return = $mm->handleMediaUpload($fileData, 'image title', 'image description', 'copyright', '5000');
		$this->assertTrue($return);
		
		$fileData = $this->doMakeFileData(dirname(__FILE__).'/media/flash6.swf');
		$return = $mm->handleMediaUpload($fileData, 'image title', 'image description', 'copyright', '5000');
		$this->assertTrue($return);
		
		$fileData = $this->doMakeFileData(dirname(__FILE__).'/media/flash7.swf');
		$return = $mm->handleMediaUpload($fileData, 'image title', 'image description', 'copyright', '5000');
		$this->assertTrue($return);
		
		$fileData = $this->doMakeFileData(dirname(__FILE__).'/media/flash8.swf');
		$return = $mm->handleMediaUpload($fileData, 'image title', 'image description', 'copyright', '5000');
		$this->assertTrue($return);
		
		$fileData = $this->doMakeFileData(dirname(__FILE__).'/media/flash9.swf');
		$return = $mm->handleMediaUpload($fileData, 'image title', 'image description', 'copyright', '5000');
		$this->assertTrue($return);		
		
		$fileData = $this->doMakeFileData(dirname(__FILE__).'/media/music.mp3');
		$return = $mm->handleMediaUpload($fileData, 'image title', 'image description', 'copyright', '5000');
		$this->assertTrue($return);
		
		$fileData = $this->doMakeFileData(dirname(__FILE__).'/media/flv.flv');
		$return = $mm->handleMediaUpload($fileData, 'image title', 'image description', 'copyright', '5000');
		$this->assertTrue($return);
		
		$medias = $API->getMedia();
		$this->assertEquals(10, count($medias));
		foreach($medias AS $media)
		{
			$this->doTestMediaObject($media);
		}
		//TODO: figure out how to test this in working condition
		$this->assertEquals(null, $API->doLogout());
	}
	
	public function testeditMedia()
	{
		$API = nm_los_API::getInstance();
		$error = $this->getError(1);
		$error2 = $this->getError(2);
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$medias = $API->getMedia();
		foreach($medias AS $media)
		{
			$this->doTestMediaObject($media);
			$media->title = "edit image title";
			$media->descText = "edit image description";
			$media->copyright = 'edit image copyright';
			$editObject = $this->doConvertMediaObjToArray($media);
			$API->editMedia($editObject);
		}
		
		$medias = $API->getMedia();
		
		foreach($medias AS $media)
		{
			$this->assertEquals($media->title, 'edit image title');
			$this->assertEquals($media->descText, "edit image description");
			$this->assertEquals($media->copyright, 'edit image copyright');
			$this->doTestMediaObject($media);
		}
	}
	public function testgetMedia()
	{
		$API = nm_los_API::getInstance();
		$error = $this->getError(1);
		$error2 = $this->getError(2);
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$medias = $API->getMedia();
		$this->assertTrue(count($medias) > 0);
		$mediaIDs = array();
		foreach($medias AS $media)
		{
			$this->doTestMediaObject($media);
			$mediaIDs[] = $media->mediaID;
		}
		
		// test getting specific items
		array_shift($mediaIDs);
		$medias2 = $API->getMedia($mediaIDs);
		$this->assertEquals(count($medias2)+1, count($medias) );
		foreach($medias2 AS $media)
		{
			$this->doTestMediaObject($media);
		}		
		
	}
	
	public function testremoveMedia()
	{
		$API = nm_los_API::getInstance();
		$error = $this->getError(1);
		$error2 = $this->getError(2);
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$medias = $API->getMedia();
		$this->assertTrue(count($medias) > 0);
		foreach($medias AS $media)
		{
			$this->doTestMediaObject($media);
			$this->assertTrue($API->removeMedia($media->mediaID));
		}
		$medias = $API->getMedia();
		$this->assertEquals(0,  count($medias));
	}

   	public function testSimpleAssessment()
   	{	
		$API = nm_los_API::getInstance();
		$error = $this->getError(1);
		$error2 = $this->getError(2);
		
		$INSTID = $this->makeLibraryLO();
		//$this->assertEquals($this->getError(2), $API->trackSubmitQuestion());
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		// start the instance view
		$lo = $API->createInstanceVisit($INSTID);
		$this->doTestLO($lo->loID, $lo, false, true);
		//$this->doTestInstanceLO($lo);
		$this->doTestInstanceData($lo->instanceData);
		$this->assertTrue( nm_los_Validator::isMD5($lo->viewID) );
		
		$this->assertEquals(null, $API->trackSectionChanged($lo->viewID, 2));
		
		// start the attempt view
		$attempt = $API->trackAttemptStart($lo->viewID, $lo->aGroup->qGroupID);
		// attempt start will return the questions since they are not included in the getInstance
		$this->assertGreaterThan(0, count($attempt));
		$score = 0;
		
		
		
		$this->doAnswerQuestion($lo->viewID, $lo->aGroup->qGroupID, $attempt[0],2);
		$this->doAnswerQuestion($lo->viewID, $lo->aGroup->qGroupID, $attempt[0],2);
		$this->doAnswerQuestion($lo->viewID, $lo->aGroup->qGroupID, $attempt[0],2);
		
		// check submitting a valid answer id from a different question
		$submit = $API->trackSubmitQuestion($lo->viewID, $lo->aGroup->qGroupID, $attempt[0]->questionID, $attempt[1]->answers[0]->answerID);
		$this->assertEquals($error2, $submit);
		
		$score += $this->doAnswerQuestion($lo->viewID, $lo->aGroup->qGroupID, $attempt[0],2);
		
		// test submit media for a MC question
		$submit = $API->trackSubmitMedia($lo->viewID, $lo->aGroup->qGroupID, $attempt[0]->questionID, 101);
		$this->assertEquals($error2, $submit);
		@$submit = $API->trackSubmitMedia($lo->viewID, $lo->aGroup->qGroupID, $attempt[0]->questionID, '50');
		$this->assertEquals($error2, $submit);
		@$submit = $API->trackSubmitMedia($lo->viewID, $lo->aGroup->qGroupID, $attempt[0]->questionID, 40);
		$this->assertEquals($error2, $submit);
		
		// test submit for a real media question
		@$submit = $API->trackSubmitMedia($lo->viewID, $lo->aGroup->qGroupID, $attempt[2]->questionID, -1);
		$this->assertEquals($error2, $submit);
		@$submit = $API->trackSubmitMedia($lo->viewID, $lo->aGroup->qGroupID, $attempt[2]->questionID, 101);
		$this->assertEquals($error2, $submit);
		@$submit = $API->trackSubmitMedia($lo->viewID, $lo->aGroup->qGroupID, $attempt[2]->questionID, '101');
		$this->assertEquals($error2, $submit);
	    @$submit = $API->trackSubmitMedia($lo->viewID, $lo->aGroup->qGroupID, $attempt[2]->questionID, '50');
		$this->assertTrue($submit); 
		$this->doAnswerQuestion($lo->viewID, $lo->aGroup->qGroupID, $attempt[2],2);
		$score += $this->doAnswerQuestion($lo->viewID, $lo->aGroup->qGroupID, $attempt[2],2);

		// submit the rest
		$score += $this->doAnswerQuestion($lo->viewID, $lo->aGroup->qGroupID, $attempt[1],2);
		$score += $this->doAnswerQuestion($lo->viewID, $lo->aGroup->qGroupID, $attempt[3],2);
		$score += $this->doAnswerQuestion($lo->viewID, $lo->aGroup->qGroupID, $attempt[4],2);
		$score += $this->doAnswerQuestion($lo->viewID, $lo->aGroup->qGroupID, $attempt[5],2);
		$score += $this->doAnswerQuestion($lo->viewID, $lo->aGroup->qGroupID, $attempt[6],2);
		$score += $this->doAnswerQuestion($lo->viewID, $lo->aGroup->qGroupID, $attempt[7],2);
		$score += $this->doAnswerQuestion($lo->viewID, $lo->aGroup->qGroupID, $attempt[8],2);
		$score += $this->doAnswerQuestion($lo->viewID, $lo->aGroup->qGroupID, $attempt[9],2);
		   
		@$end = $API->trackAttemptEnd($lo->viewID, $lo->aGroup->qGroupID);
		$this->assertGreaterThanOrEqual(0, $end);
		$this->assertEquals(round(($score/1000)*100), $end);
		trace($score);
		
		@$this->assertEquals(null, $API->doLogout());
   	}

	public function testSimultaneousAssessment()
	{
		// test 6 simultaneous attempts on different los
		$API = nm_los_API::getInstance();
		$instA = $this->makeLibraryLO();
		$instB = $this->makeLibraryLO();
		$instC = $this->makeLibraryLO();
		$instD = $this->makeLibraryLO();
		$instE = $this->makeLibraryLO();
		$instF = $this->makeLibraryLO();
		
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		// start the instance view
		$loA = $API->createInstanceVisit($instA);
		$this->doTestLO($loA->loID, $loA, false, true);
		$this->doTestInstanceData($loA->instanceData);
		$this->assertTrue( nm_los_Validator::isMD5($loA->viewID));
	
		$loB = $API->createInstanceVisit($instB);
		$this->doTestLO($loB->loID, $loB, false, true);
		$this->doTestInstanceData($loB->instanceData);
		$this->assertTrue( nm_los_Validator::isMD5($loB->viewID));
		
		$loC = $API->createInstanceVisit($instC);
		$this->doTestLO($loC->loID, $loC, false, true);
		$this->doTestInstanceData($loC->instanceData);
		$this->assertTrue( nm_los_Validator::isMD5($loC->viewID));
		
		$loD = $API->createInstanceVisit($instD);
		$this->doTestLO($loD->loID, $loD, false, true);
		$this->doTestInstanceData($loD->instanceData);
		$this->assertTrue( nm_los_Validator::isMD5($loD->viewID));
		
		$loE = $API->createInstanceVisit($instE);
		$this->doTestLO($loE->loID, $loE, false, true);
		$this->doTestInstanceData($loE->instanceData);
		$this->assertTrue( nm_los_Validator::isMD5($loE->viewID));
		
		$loF = $API->createInstanceVisit($instF);
		$this->doTestLO($loF->loID, $loF, false, true);
		$this->doTestInstanceData($loF->instanceData);
		$this->assertTrue( nm_los_Validator::isMD5($loF->viewID));
		
		@$this->assertEquals(null, $API->trackSectionChanged($loA->viewID, 2));
		@$this->assertEquals(null, $API->trackSectionChanged($loB->viewID, 2));
		@$this->assertEquals(null, $API->trackSectionChanged($loC->viewID, 2));
		@$this->assertEquals(null, $API->trackSectionChanged($loD->viewID, 2));
		@$this->assertEquals(null, $API->trackSectionChanged($loE->viewID, 2));
		@$this->assertEquals(null, $API->trackSectionChanged($loF->viewID, 2));
		
		// start the attempt view
		$attemptA = $API->trackAttemptStart($loA->viewID, $loA->aGroup->qGroupID);
		$attemptB = $API->trackAttemptStart($loB->viewID, $loB->aGroup->qGroupID);
		$attemptC = $API->trackAttemptStart($loC->viewID, $loC->aGroup->qGroupID);
		$attemptD = $API->trackAttemptStart($loD->viewID, $loD->aGroup->qGroupID);
		$attemptE = $API->trackAttemptStart($loE->viewID, $loE->aGroup->qGroupID);
		$attemptF = $API->trackAttemptStart($loF->viewID, $loF->aGroup->qGroupID);
		// attempt start will return the questions since they are not included in the getInstance
		$this->assertGreaterThan(0, count($attemptA));
		$this->assertGreaterThan(0, count($attemptB));
		$this->assertGreaterThan(0, count($attemptC));
		$this->assertGreaterThan(0, count($attemptD));
		$this->assertGreaterThan(0, count($attemptE));
		$this->assertGreaterThan(0, count($attemptF));
		
		$scoreA += $this->doAnswerQuestion($loA->viewID, $loA->aGroup->qGroupID, $attemptA[0],2);
		$scoreB += $this->doAnswerQuestion($loB->viewID, $loB->aGroup->qGroupID, $attemptB[0],2);
		$scoreC += $this->doAnswerQuestion($loC->viewID, $loC->aGroup->qGroupID, $attemptC[0],2);
		$scoreD += $this->doAnswerQuestion($loD->viewID, $loD->aGroup->qGroupID, $attemptD[0],2);
		$scoreE += $this->doAnswerQuestion($loE->viewID, $loE->aGroup->qGroupID, $attemptE[0],2);
		$scoreF += $this->doAnswerQuestion($loF->viewID, $loF->aGroup->qGroupID, $attemptF[0],2);

		$scoreA += $this->doAnswerQuestion($loA->viewID, $loA->aGroup->qGroupID, $attemptA[1],2);
		$scoreB += $this->doAnswerQuestion($loB->viewID, $loB->aGroup->qGroupID, $attemptB[1],2);
		$scoreC += $this->doAnswerQuestion($loC->viewID, $loC->aGroup->qGroupID, $attemptC[1],2);
		$scoreD += $this->doAnswerQuestion($loD->viewID, $loD->aGroup->qGroupID, $attemptD[1],2);
		$scoreE += $this->doAnswerQuestion($loE->viewID, $loE->aGroup->qGroupID, $attemptE[1],2);
		$scoreF += $this->doAnswerQuestion($loF->viewID, $loF->aGroup->qGroupID, $attemptF[1],2);
		
		$scoreA += $this->doAnswerQuestion($loA->viewID, $loA->aGroup->qGroupID, $attemptA[2],2);
		$scoreB += $this->doAnswerQuestion($loB->viewID, $loB->aGroup->qGroupID, $attemptB[2],2);
		$scoreC += $this->doAnswerQuestion($loC->viewID, $loC->aGroup->qGroupID, $attemptC[2],2);
		$scoreD += $this->doAnswerQuestion($loD->viewID, $loD->aGroup->qGroupID, $attemptD[2],2);
		$scoreE += $this->doAnswerQuestion($loE->viewID, $loE->aGroup->qGroupID, $attemptE[2],2);
		$scoreF += $this->doAnswerQuestion($loF->viewID, $loF->aGroup->qGroupID, $attemptF[2],2);
		
		$scoreA += $this->doAnswerQuestion($loA->viewID, $loA->aGroup->qGroupID, $attemptA[3],2);
		$scoreB += $this->doAnswerQuestion($loB->viewID, $loB->aGroup->qGroupID, $attemptB[3],2);
		$scoreC += $this->doAnswerQuestion($loC->viewID, $loC->aGroup->qGroupID, $attemptC[3],2);
		$scoreD += $this->doAnswerQuestion($loD->viewID, $loD->aGroup->qGroupID, $attemptD[3],2);
		$scoreE += $this->doAnswerQuestion($loE->viewID, $loE->aGroup->qGroupID, $attemptE[3],2);
		$scoreF += $this->doAnswerQuestion($loF->viewID, $loF->aGroup->qGroupID, $attemptF[3],2);
		
		$scoreA += $this->doAnswerQuestion($loA->viewID, $loA->aGroup->qGroupID, $attemptA[4],2);
		$scoreB += $this->doAnswerQuestion($loB->viewID, $loB->aGroup->qGroupID, $attemptB[4],2);
		$scoreC += $this->doAnswerQuestion($loC->viewID, $loC->aGroup->qGroupID, $attemptC[4],2);
		$scoreD += $this->doAnswerQuestion($loD->viewID, $loD->aGroup->qGroupID, $attemptD[4],2);
		$scoreE += $this->doAnswerQuestion($loE->viewID, $loE->aGroup->qGroupID, $attemptE[4],2);
		$scoreF += $this->doAnswerQuestion($loF->viewID, $loF->aGroup->qGroupID, $attemptF[4],2);
		
		$scoreA += $this->doAnswerQuestion($loA->viewID, $loA->aGroup->qGroupID, $attemptA[5],2);
		$scoreB += $this->doAnswerQuestion($loB->viewID, $loB->aGroup->qGroupID, $attemptB[5],2);
		$scoreC += $this->doAnswerQuestion($loC->viewID, $loC->aGroup->qGroupID, $attemptC[5],2);
		$scoreD += $this->doAnswerQuestion($loD->viewID, $loD->aGroup->qGroupID, $attemptD[5],2);
		$scoreE += $this->doAnswerQuestion($loE->viewID, $loE->aGroup->qGroupID, $attemptE[5],2);
		$scoreF += $this->doAnswerQuestion($loF->viewID, $loF->aGroup->qGroupID, $attemptF[5],2);
		
		$scoreA += $this->doAnswerQuestion($loA->viewID, $loA->aGroup->qGroupID, $attemptA[6],2);
		$scoreB += $this->doAnswerQuestion($loB->viewID, $loB->aGroup->qGroupID, $attemptB[6],2);
		$scoreC += $this->doAnswerQuestion($loC->viewID, $loC->aGroup->qGroupID, $attemptC[6],2);
		$scoreD += $this->doAnswerQuestion($loD->viewID, $loD->aGroup->qGroupID, $attemptD[6],2);
		$scoreE += $this->doAnswerQuestion($loE->viewID, $loE->aGroup->qGroupID, $attemptE[6],2);
		$scoreF += $this->doAnswerQuestion($loF->viewID, $loF->aGroup->qGroupID, $attemptF[6],2);
		
		$scoreA += $this->doAnswerQuestion($loA->viewID, $loA->aGroup->qGroupID, $attemptA[7],2);
		$scoreB += $this->doAnswerQuestion($loB->viewID, $loB->aGroup->qGroupID, $attemptB[7],2);
		$scoreC += $this->doAnswerQuestion($loC->viewID, $loC->aGroup->qGroupID, $attemptC[7],2);
		$scoreD += $this->doAnswerQuestion($loD->viewID, $loD->aGroup->qGroupID, $attemptD[7],2);
		$scoreE += $this->doAnswerQuestion($loE->viewID, $loE->aGroup->qGroupID, $attemptE[7],2);
		$scoreF += $this->doAnswerQuestion($loF->viewID, $loF->aGroup->qGroupID, $attemptF[7],2);
		
		$scoreA += $this->doAnswerQuestion($loA->viewID, $loA->aGroup->qGroupID, $attemptA[8],2);
		$scoreB += $this->doAnswerQuestion($loB->viewID, $loB->aGroup->qGroupID, $attemptB[8],2);
		$scoreC += $this->doAnswerQuestion($loC->viewID, $loC->aGroup->qGroupID, $attemptC[8],2);
		$scoreD += $this->doAnswerQuestion($loD->viewID, $loD->aGroup->qGroupID, $attemptD[8],2);
		$scoreE += $this->doAnswerQuestion($loE->viewID, $loE->aGroup->qGroupID, $attemptE[8],2);
		$scoreF += $this->doAnswerQuestion($loF->viewID, $loF->aGroup->qGroupID, $attemptF[8],2);
		
		$scoreA += $this->doAnswerQuestion($loA->viewID, $loA->aGroup->qGroupID, $attemptA[9],2);
		$scoreB += $this->doAnswerQuestion($loB->viewID, $loB->aGroup->qGroupID, $attemptB[9],2);
		$scoreC += $this->doAnswerQuestion($loC->viewID, $loC->aGroup->qGroupID, $attemptC[9],2);
		$scoreD += $this->doAnswerQuestion($loD->viewID, $loD->aGroup->qGroupID, $attemptD[9],2);
		$scoreE += $this->doAnswerQuestion($loE->viewID, $loE->aGroup->qGroupID, $attemptE[9],2);
		$scoreF += $this->doAnswerQuestion($loF->viewID, $loF->aGroup->qGroupID, $attemptF[9],2);
		
	
		@$end = $API->trackAttemptEnd($loB->viewID, $loB->aGroup->qGroupID);
		$this->assertEquals(round(($scoreB/1000)*100), $end);
		@$end = $API->trackAttemptEnd($loA->viewID, $loA->aGroup->qGroupID);
		$this->assertEquals(round(($scoreA/1000)*100), $end);
		@$end = $API->trackAttemptEnd($loC->viewID, $loC->aGroup->qGroupID);
		$this->assertEquals(round(($scoreC/1000)*100), $end);		
		@$end = $API->trackAttemptEnd($loD->viewID, $loD->aGroup->qGroupID);
		$this->assertEquals(round(($scoreD/1000)*100), $end);
		@$end = $API->trackAttemptEnd($loE->viewID, $loE->aGroup->qGroupID);
		$this->assertEquals(round(($scoreE/1000)*100), $end);
		@$end = $API->trackAttemptEnd($loF->viewID, $loF->aGroup->qGroupID);
		$this->assertEquals(round(($scoreF/1000)*100), $end);
		@$this->assertEquals(null, $API->doLogout());
	}

	public function testeditUsersPerms()
	{
		
		$API = nm_los_API::getInstance();
		$error = $this->getError(1);
		$error2 = $this->getError(2);
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		
		// make a user to give perms to
		$user = $this->doMakeFakeUserArray('~');
		$am = core_auth_AuthManager::getInstance();
		$this->assertTrue($am->saveUser($user, array('MD5Pass' => md5(self::ADMIN_PW))));
		
		// now lets make sure that authmod internal was used core_auth_ModInternal
		$authModInternal = $am->getAuthModuleForUsername($user['login']);
		$this->assertType('core_auth_ModInternal',$authModInternal);
		$userID = $authModInternal->getUIDforUsername($user['login']);
		$this->assertGreaterThan( 0, $userID);
		$user = $authModInternal->fetchUserByID($userID);
		$this->doUnexpirePassword($userID);
		// make a draft to give perms to
		$lo = $this->makeBasicLO();
		$newLO = $API->createDraft($lo);
		$lo = $API->getLO($newLO->loID);
		$this->doTestLO($newLO->loID, $newLO);
		
		// observer
		$perms = $this->doMakePerm($userID, 'observer');
		$this->assertTrue($API->editUsersPerms(array($perms), $newLO->loID, cfg_obo_Perm::TYPE_LO));
		@$this->assertEquals(null, $API->doLogout());
		$this->assertTrue($API->doLogin($user->login, md5(self::ADMIN_PW)));
		$loMan = nm_los_LOManager::getInstance();
		$drafts = $loMan->getMyDrafts();
		$myLO = $API->getLO($drafts[0]->loID);
		$this->doTestLO($myLO->loID, $myLO);
		$this->assertEquals(new nm_los_Permissions($perms), $myLO->perms);
		unset($myLO->perms);
		unset($lo->perms);
		$this->assertEquals($myLO, $lo);
		@$this->assertEquals(null, $API->doLogout());
		
		// observer + publish
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$perms = $this->doMakePerm($userID, 'observer', true);
		$this->assertTrue($API->editUsersPerms(array($perms), $newLO->loID, cfg_obo_Perm::TYPE_LO));
		@$this->assertEquals(null, $API->doLogout());
		$this->assertTrue($API->doLogin($user->login, md5(self::ADMIN_PW)));
		$loMan = nm_los_LOManager::getInstance();
		$drafts = $loMan->getMyDrafts();
		$myLO = $API->getLO($drafts[0]->loID);
		$this->doTestLO($myLO->loID, $myLO);
		$this->assertEquals(new nm_los_Permissions($perms), $myLO->perms);
		unset($myLO->perms);
		unset($lo->perms);
		$this->assertEquals($myLO, $lo);
		
		// coDev
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$perms = $this->doMakePerm($userID, 'coDev');
		$this->assertTrue($API->editUsersPerms(array($perms), $newLO->loID, cfg_obo_Perm::TYPE_LO));
		@$this->assertEquals(null, $API->doLogout());
		$this->assertTrue($API->doLogin($user->login, md5(self::ADMIN_PW)));
		$loMan = nm_los_LOManager::getInstance();
		$drafts = $loMan->getMyDrafts();
		$myLO = $API->getLO($drafts[0]->loID);
		$this->doTestLO($myLO->loID, $myLO);
		$this->assertEquals(new nm_los_Permissions($perms), $myLO->perms);
		unset($myLO->perms);
		unset($lo->perms);
		$this->assertEquals($myLO, $lo);

		// coDev + publish
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$perms = $this->doMakePerm($userID, 'coDev', true);
		$this->assertTrue($API->editUsersPerms(array($perms), $newLO->loID, cfg_obo_Perm::TYPE_LO));
		@$this->assertEquals(null, $API->doLogout());
		$this->assertTrue($API->doLogin($user->login, md5(self::ADMIN_PW)));
		$loMan = nm_los_LOManager::getInstance();
		$drafts = $loMan->getMyDrafts();
		$myLO = $API->getLO($drafts[0]->loID);
		$this->doTestLO($myLO->loID, $myLO);
		$this->assertEquals(new nm_los_Permissions($perms), $myLO->perms);
		unset($myLO->perms);
		unset($lo->perms);
		$this->assertEquals($myLO, $lo);
		
		// owner
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$perms = $this->doMakePerm($userID, 'owner');
		$this->assertTrue($API->editUsersPerms(array($perms), $newLO->loID, cfg_obo_Perm::TYPE_LO));
		@$this->assertEquals(null, $API->doLogout());
		$this->assertTrue($API->doLogin($user->login, md5(self::ADMIN_PW)));
		$loMan = nm_los_LOManager::getInstance();
		$drafts = $loMan->getMyDrafts();
		$myLO = $API->getLO($drafts[0]->loID);
		$this->doTestLO($myLO->loID, $myLO);
		$this->assertEquals(new nm_los_Permissions($perms), $myLO->perms);
		unset($myLO->perms);
		unset($lo->perms);
		$this->assertEquals($myLO, $lo);
		
		//$permObjects, $itemID = 0, $itemType = 'l';
		// TODO: change prms to media
		// TODO: change perms to instances
		

	}
	
	public function testPermRights()
	{
		/*
		-able to see it
		-be able to edit
		-able create instance
		-able to create a master lo
		-able to delete lo
		-able to put in library
		-able to assign others perms
		-remove last perms should throw error
		TODO: -getLO if im an instance owner, codev, owner, viewer preview (in pub lib, libraryUser, or have perms) getLOMeta too
		// TODO: test contentCreator vs LibraryUser
		*/
		
		$API = nm_los_API::getInstance();
		$error = $this->getError(1);
		$error2 = $this->getError(2);
		$error4 = $this->getError(4);
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		
		// make a user to give perms to
		$user = $this->doMakeFakeUserArray('~');
		$am = core_auth_AuthManager::getInstance();
		$this->assertTrue($am->saveUser($user, array('MD5Pass' => md5(self::ADMIN_PW))));
		
		// now lets make sure that authmod internal was used core_auth_ModInternal
		$authModInternal = $am->getAuthModuleForUsername($user['login']);
		$this->assertType('core_auth_ModInternal',$authModInternal);
		$userID = $authModInternal->getUIDforUsername($user['login']);
		//$this->assertTrue($API->editUsersRoles(array($userID), array(cfg_obo_Role::CONTENT_CREATOR, cfg_obo_Role::LIBRARY_USER)));
		$this->assertTrue($API->editUsersRoles(array($userID), array(cfg_obo_Role::LIBRARY_USER)));
		//$this->assertTrue($API->editUsersRoles(array($userID), array()));
		$this->assertGreaterThan( 0, $userID);
		$user = $authModInternal->fetchUserByID($userID);
		$this->doUnexpirePassword($userID);
		// make a draft to give perms to
		$lo = $this->makeFullLO();
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW))); // need to log in again because makefulllo logs out
		$newLO = $API->createDraft($lo);
		$newMaster = $API->createMaster($newLO->loID);
		$this->assertTrue($newMaster);
		$loMan = nm_los_LOManager::getInstance();
		$masters = $loMan->getMyMasters();
		$masterLO = $masters[count($masters)-1];
		$this->assertType('nm_los_LO', $newLO);
		$lo['rootID'] = $newLO->loID;
		$draftLO = $API->createDraft($lo);
		$lo['rootID'] = $draftLO->rootID;
		//$lo = $API->getLO($newLO->loID);
		//$this->doTestLO($newLO->loID, $newLO);
		
		$perms = $this->doMakePerm(1, 'owner');
		// no perms
		@$this->assertEquals(null, $API->doLogout());
		$this->assertTrue($API->doLogin($user->login, md5(self::ADMIN_PW)));
		$loMan = nm_los_LOManager::getInstance();
		$drafts = $loMan->getMyDrafts();
		$this->assertEquals(0, count($drafts));
		$this->assertEquals($error4, $API->createDraft($lo));
		$this->assertEquals($error4, $API->removeLO($draftLO->loID));
		$this->assertEquals($error4, $API->createMaster($draftLO->loID) );
		$this->assertEquals($error4, $API->createLibraryLO($masterLO->loID, false)); 
		$this->assertEquals($error4, $API->removeLibraryLO($masterLO->loID) );
		$this->assertEquals($error4, $API->createDerivative($masterLO->loID));
		$this->assertEquals($error4, $API->createLOLock($draftLO->loID));
		$this->assertEquals($error4, $API->createInstance('Test Instance', $master->loID, 'TestCourseID', time(), time()+1, 1, 'h'));
		$this->assertEquals($error4, $API->editUsersPerms(array($perms), $draftLO->loID, cfg_obo_Perm::TYPE_LO));
		@$this->assertEquals(null, $API->doLogout());
		
		// observer
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$this->assertTrue($API->editUsersPerms(array($this->doMakePerm($userID, 'observer')), $masterLO->rootID, cfg_obo_Perm::TYPE_LO));
		$this->assertTrue($API->editUsersPerms(array($this->doMakePerm($userID, 'observer')), $draftLO->rootID, cfg_obo_Perm::TYPE_LO));
		@$this->assertEquals(null, $API->doLogout());
		$this->assertTrue($API->doLogin($user->login, md5(self::ADMIN_PW)));
		$loMan = nm_los_LOManager::getInstance();
		$drafts = $loMan->getMyDrafts();
		$this->assertEquals(1, count($drafts));
		$this->assertEquals($error4, $API->createDraft($lo));
		$this->assertEquals($error4, $API->removeLO($draftLO->loID));
		$this->assertEquals($error4, $API->createMaster($draftLO->loID) ); // should work
		$this->assertEquals($error4, $API->createLibraryLO($masterLO->loID, false)); 
		$this->assertEquals($error4, $API->createDerivative($masterLO->loID));
		$this->assertEquals($error4, $API->createLOLock($draftLO->loID));
		$this->assertEquals($error4, $API->createInstance('Test Instance', $masterLO->loID, 'TestCourseID', time(), time()+1, 1, 'h'));
		$this->assertEquals($error4, $API->editUsersPerms(array($perms), $draftLO->loID, cfg_obo_Perm::TYPE_LO));
		@$this->assertEquals(null, $API->doLogout());
		
		// observer + publish
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$this->assertTrue($API->editUsersPerms(array($this->doMakePerm($userID, 'observer', true)), $masterLO->rootID, cfg_obo_Perm::TYPE_LO));
		$this->assertTrue($API->editUsersPerms(array($this->doMakePerm($userID, 'observer', true)), $draftLO->rootID, cfg_obo_Perm::TYPE_LO));
		@$this->assertEquals(null, $API->doLogout());
		$this->assertTrue($API->doLogin($user->login, md5(self::ADMIN_PW)));
		$loMan = nm_los_LOManager::getInstance();
		$drafts = $loMan->getMyDrafts();
		$this->assertEquals(1, count($drafts));
		$this->assertEquals($error4, $API->createDraft($lo));
		$this->assertEquals($error4, $API->removeLO($draftLO->loID));
		$this->assertEquals($error4, $API->createMaster($draftLO->loID) ); // should work
		$this->assertEquals($error4, $API->createLibraryLO($masterLO->loID, false)); 
		$this->assertEquals($error4, $API->removeLibraryLO($masterLO->loID) );
		$this->assertEquals($error4, $API->createDerivative($masterLO->loID));
		$this->assertEquals($error4, $API->createLOLock($draftLO->loID));
		$this->assertGreaterThan(0, $API->createInstance('Test Instance', $masterLO->loID, 'TestCourseID', time(), time()+1, 1, 'h'));
		$this->assertEquals($error4, $API->editUsersPerms(array($perms), $draftLO->loID, cfg_obo_Perm::TYPE_LO));
		@$this->assertEquals(null, $API->doLogout());
		
		$lo = $this->makeFullLO();
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW))); // need to log in again because makefulllo logs out
		// make a new master
		$newLO = $API->createDraft($lo);
		$newMaster = $API->createMaster($newLO->loID);
		$this->assertTrue($newMaster);
		$loMan = nm_los_LOManager::getInstance();
		$masters = $loMan->getMyMasters();
		$masterLO = $masters[count($masters)-1];
		$this->assertType('nm_los_LO', $newLO);
		
		// make a new draft of the master
		$lo['rootID'] = $newLO->loID;
		$draftLO = $API->createDraft($lo);
		$lo['rootID'] = $draftLO->rootID;
		
		// coDev
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$this->assertTrue($API->editUsersPerms(array($this->doMakePerm($userID, 'coDev')), $masterLO->rootID, cfg_obo_Perm::TYPE_LO));
		$this->assertTrue($API->editUsersPerms(array($this->doMakePerm($userID, 'coDev')), $draftLO->rootID, cfg_obo_Perm::TYPE_LO));
		@$this->assertEquals(null, $API->doLogout());
		$this->assertTrue($API->doLogin($user->login, md5(self::ADMIN_PW)));
		$loMan = nm_los_LOManager::getInstance();
		$drafts = $loMan->getMyDrafts();
		$this->assertEquals(2, count($drafts));
		$this->assertType('nm_los_LO', $API->createDraft($lo));
		$this->assertEquals($error4, $API->removeLO($draftLO->loID));
		$this->assertTrue($API->createMaster($draftLO->loID));
		$this->assertEquals($error4, $API->createLibraryLO($masterLO->loID, false)); 
		$this->assertEquals($error4, $API->removeLibraryLO($masterLO->loID) );
		$this->assertEquals($error4, $API->createDerivative($masterLO->loID));
		$this->assertType('nm_los_Lock', $API->createLOLock($draftLO->loID));
		$this->assertEquals($error4, $API->createInstance('Test Instance', $masterLO->loID, 'TestCourseID', time(), time()+1, 1, 'h'));
		$this->assertEquals($error4, $API->editUsersPerms(array($perms), $draftLO->loID, cfg_obo_Perm::TYPE_LO));
		@$this->assertEquals(null, $API->doLogout());
		
		$lo = $this->makeFullLO();
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW))); // need to log in again because makefulllo logs out
		$newLO = $API->createDraft($lo);
		$newMaster = $API->createMaster($newLO->loID);
		$this->assertTrue($newMaster);
		$loMan = nm_los_LOManager::getInstance();
		$masters = $loMan->getMyMasters();
		$masterLO = $masters[count($masters)-1];
		$this->assertType('nm_los_LO', $newLO);
		$lo['rootID'] = $masterLO->loID;
		$draftLO = $API->createDraft($lo);
		$lo['rootID'] = $draftLO->rootID;
		
		// TODO: reset the draft so i can try to make another master
		// coDev + publish
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$this->assertTrue($API->editUsersPerms(array($this->doMakePerm($userID, 'coDev', true)), $masterLO->rootID, cfg_obo_Perm::TYPE_LO));
		$this->assertTrue($API->editUsersPerms(array($this->doMakePerm($userID, 'coDev', true)), $draftLO->rootID, cfg_obo_Perm::TYPE_LO));
		@$this->assertEquals(null, $API->doLogout());
		$this->assertTrue($API->doLogin($user->login, md5(self::ADMIN_PW)));
		$loMan = nm_los_LOManager::getInstance();
		$drafts = $loMan->getMyDrafts();
		$this->assertEquals(2, count($drafts));
		$this->assertType('nm_los_LO', $API->createDraft($lo));
		$this->assertEquals($error4, $API->removeLO($draftLO->loID));
		//$this->assertTrue($API->createMaster($draftLO->loID) ); // should work
		$this->assertEquals($error4, $API->createLibraryLO($masterLO->loID, false)); 
		$this->assertEquals($error4, $API->removeLibraryLO($masterLO->loID) );
		$this->assertEquals($error4, $API->createDerivative($masterLO->loID));
		$this->assertType('nm_los_Lock', $API->createLOLock($draftLO->loID));
		$this->assertGreaterThan(0, $API->createInstance('Test Instance', $masterLO->loID, 'TestCourseID', time(), time()+1, 1, 'h'));
		$this->assertEquals($error4, $API->editUsersPerms(array($perms), $draftLO->loID, cfg_obo_Perm::TYPE_LO));
		@$this->assertEquals(null, $API->doLogout());

		$lo = $this->makeFullLO();
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW))); // need to log in again because makefulllo logs out
		$newLO = $API->createDraft($lo);
		$newMaster = $API->createMaster($newLO->loID);
		$this->assertTrue($newMaster);
		$loMan = nm_los_LOManager::getInstance();
		$masters = $loMan->getMyMasters();
		$masterLO = $masters[count($masters)-1];
		$this->assertType('nm_los_LO', $masterLO);
		$lo['rootID'] = $masterLO->loID;
		$draftLO = $API->createDraft($lo);
		$lo['rootID'] = $draftLO->rootID;
				
		// owner
		$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$this->assertTrue($API->editUsersPerms(array($this->doMakePerm($userID, 'owner')), $masterLO->rootID, cfg_obo_Perm::TYPE_LO));
		$this->assertTrue($API->editUsersPerms(array($this->doMakePerm($userID, 'owner')), $draftLO->rootID, cfg_obo_Perm::TYPE_LO));
		@$this->assertEquals(null, $API->doLogout());
		$this->assertTrue($API->doLogin($user->login, md5(self::ADMIN_PW)));
		$loMan = nm_los_LOManager::getInstance();
		$drafts = $loMan->getMyDrafts();
		$this->assertEquals(3, count($drafts));
		$this->assertType('nm_los_LO', $API->createDraft($lo));
		$this->assertEquals( $this->getError(6003), $API->removeLO($masterLO->loID));
		//$this->assertTrue( $API->createMaster($draftLO->loID) ); // should work
		//$this->assertTrue($API->createLibraryLO($masterLO->loID, false)); 
		//$this->assertTrue( $API->removeLibraryLO($masterLO->loID) );
		$this->assertGreaterThan(0, $API->createDerivative($masterLO->loID));
		$this->assertType('nm_los_Lock', $API->createLOLock($draftLO->loID));
		$this->assertGreaterThan(0, $API->createInstance('Test Instance', $masterLO->loID, 'TestCourseID', time(), time()+1, 1, 'h'));
		$this->assertTrue($API->editUsersPerms(array($perms), $draftLO->loID, cfg_obo_Perm::TYPE_LO));
		@$this->assertEquals(null, $API->doLogout());
	}


	public function testremoveUsersPerms(){}
	public function testgetItemPerms(){}
	public function testgetLayouts(){}
	public function testtrackAttemptStart(){}
   	public function testtrackSubmitMedia(){}
   	public function testTrackAttemptEnd(){}
   	public function testgetScoresForInstance(){}//!
   	public function testgetVisitTrackingData(){}//!
   	public function testgetLanguages(){}
   	public function testgetSession(){}//!
   	public function testgetRoles(){}
   	public function testgetUserRoles(){}
   	public function testcreateRole(){}
   	public function testremoveRole(){}
   	public function testremoveUsersRoles(){}
   	public function testtrackPageChanged(){}
   	public function testtrackSectionChanged(){}
   	public function testtrackComputerData(){}//!
   	public function testtrackVisitResume(){}//!
   	public function testgetPasswordReset(){}//!
   	public function testeditPassword(){}//!
   	public function testeditPasswordWithKey(){}//!
   	public function testeditExtraAttempts(){}//!
   	public function testremoveExtraAttempts(){}//!
   	public function testtrackClientError(){}
   	public function testgetLoginOptions(){}
/*
	public function testGetAllLatestLODrafts()
	{
		$API = nm_los_API::getInstance();
		@$this->assertEquals($this->getError(1), $API->getDrafts());
		
		@$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		@$drafts = $API->getDrafts();
		$this->assertGreaterThan(0, $drafts );
		foreach($drafts AS &$draft)
		{
			//$this->doTestLO($draft->id, $draft);  // not all learning object conform
			$this->assertType('nm_los_LO', $draft);
		}
		$this->assertEquals(null, $API->doLogout());
	}

   	public function testRemoveUser()
   	{
   		$error = $this->getError(2);
   		$API = nm_los_API::getInstance();
   		$this->assertEquals($error, $API->removeUser('dsafadsf'));
   		$this->assertEquals($error, $API->removeUser(-1));
   		$this->assertEquals($error, $API->removeUser(0));
   		// 
   		@$this->assertEquals(true, $API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
   		// TODO NEED a fake dataset to test this
   		$this->assertEquals(null, $API->doLogout());
   	}
	*/
	
	// owner
	// observer
	// observer w/publish
	// co-dev
	// co-dev w/publish
	protected function doMakePerm($userID, $type, $publish=false)
	{
		$perms = array();
		$perms['userID'] = $userID;
		$perms['read'] = 0;
		$perms['write'] = 0;
		$perms['copy'] = 0;
		$perms['publish'] = $publish ? 1 : 0;
		$perms['giveRead'] = 0;
		$perms['giveWrite'] = 0;
		$perms['giveCopy'] = 0;
		$perms['givePublish'] = 0;
		$perms['giveGlobal'] = 0;
		
		switch($type)
		{
			case "owner":
				$perms['read'] = 1;
				$perms['write'] = 1;
				$perms['copy'] = 1;
				$perms['publish'] = 1;
				$perms['giveRead'] = 1;
				$perms['giveWrite'] = 1;
				$perms['giveCopy'] = 1;
				$perms['givePublish'] = 1;
				break;
			case "observer":
				$perms['read'] = 1;
				break;
			case "coDev":
				$perms['read'] = 1;
				$perms['write'] = 1;
				break;
		}
		return $perms;
	}
	
	protected function doUnexpirePassword($userID)
	{
		$DBM = $this->doGetDBM();
		$DBM->query('UPDATE '.cfg_core_AuthModInternal::TABLE.' SET '.cfg_core_AuthModInternal::PW_CHANGE_TIME.' = '. (time() + $config->passwordResetLife) .' WHERE '.cfg_core_User::ID.' = '.$userID);
	}
	
	public function doAnswerQuestion($viewID, $qGroupID, $question, $section)
	{
		$API = nm_los_API::getInstance();
		
		@$this->assertEquals(null, $API->trackPageChanged($viewID, $question->questionID, $section));
		switch($question->itemType)
		{
			case cfg_obo_Question::QTYPE_MEDIA :
				$score = rand(0, 100);
				$res = $API->trackSubmitMedia($viewID, $qGroupID, $question->questionID, $score);
				$this->assertTrue($res);
				return $score;
			default:
				$QM = nm_los_QuestionManager::getInstance();
				$getQ = $QM->getQuestion($question->questionID, true);
				$ans = $getQ->answers[array_rand($getQ->answers)];
				$res = $API->trackSubmitQuestion($viewID, $qGroupID, $question->questionID, $ans->answerID);
				$this->assertTrue($res);
				return $ans->weight;
		}
	}
	
	
	protected function doConvertMediaObjToArray($media)
	{
		$arrMedia = array();
		$arrMedia['mediaID'] = $media->mediaID;
		$arrMedia['title'] = $media->title;
		$arrMedia['itemType'] = $media->itemType;
		$arrMedia['descText'] = $media->descText;
		$arrMedia['copyright'] = $media->copyright;
		$arrMedia['thumb'] = $media->thumb;
		$arrMedia['url'] = $media->url;
		$arrMedia['size'] = $media->size;
		$arrMedia['length'] = $media->length;
		$arrMedia['scorable'] = $media->scorable;
		$arrMedia['width'] = $media->width;
		$arrMedia['height'] = $media->height;
		$arrMedia['version'] = $media->version;
		
		return $arrMedia;

	}
	
	protected function doTestMediaObject($media)
	{
		$this->assertType('nm_los_Media', $media);
		$this->assertTrue(nm_los_Validator::isString('itemType'));
		
		
		$baseName = basename($media->url);
		$lastDot = strrpos($baseName, '.');
	    $fileName = substr($baseName, 0, $lastDot);
	    $extension = strtolower(substr($baseName, $lastDot+1));
		$file = $config->dirMedia.$media->mediaID.".".$extension;
		switch($media->itemType)
		{
			case 'pic':
				$data = getimagesize($file);
				$this->assertTrue(nm_los_Validator::isPosInt($media->width));
				$this->assertEquals($media->width, $data[0]);
				$this->assertTrue(nm_los_Validator::isPosInt($media->height));
				$this->assertEquals($media->height, $data[1]);
				break;
			case 'swf':
				require_once($config->dirScripts.'swfheader.class.php');
				$swfh = new swfheader();
				$this->assertEquals($media->version, $swfh->getVersion($file));
				$this->assertGreaterThan(0, $media->version);
				
				$dimensions = $swfh->getDimensions($file);
				$this->assertGreaterThan(0, $media->width);
				$this->assertEquals($media->width, $dimensions['width']);

				$this->assertGreaterThan(0, $media->height);
				$this->assertEquals($media->height, $dimensions['height']);

				break;
			case 'flv':
				// TODO: test this more
				break;
			case 'mp3':
				// TODO: test this more
				break;
		}
			
		$this->assertGreaterThan(0, $media->mediaID);
		$this->assertGreaterThan(0, $media->auth);
		$this->assertGreaterThan(0, $media->createTime);
		$this->assertGreaterThan(0, $media->size);
		$this->assertEquals($media->size, filesize($file));
	}
	
	protected function doMakeFileData($file)
	{
		// $fileData['Filedata']['name']
		// $fileData['Filedata']['tmp_name']
		// $fileData['Filedata']['size']
		if(!file_exists($file))
		{
			echo ' file doesnt exist';
		}
		$fileObj = array();
		$fileObj['name'] = $file;
		$fileObj['tmp_name'] = $file;
		$fileObj['size'] = filesize($file);
		return $fileObj;
	}
	
	private function logInAsRandomUser($role)
	{
		$API = nm_los_API::getInstance();
		@$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		@$users = $API->getUsersInRole($role);
		$user = $users[array_rand($users, 1)];
		// update the user so that we dont have to reset their password
		$DBM = $this->doGetDBM();
		$q = $DBM->query('UPDATE '.cfg_core_AuthModInternal::TABLE.' SET '.cfg_core_AuthModInternal::PW_CHANGE_TIME.' = '. time() .' WHERE '.cfg_core_User::ID.' = ' . $user->userID);
		@$this->assertTrue( $API->doLogin($user->login, md5(self::ADMIN_PW)));
	}
	
	protected function doMakeFakeUserArray($userNamePrefix='')
	{
		require_once dirname(__FILE__)."/php-faker/faker.php";
		$f = new Faker;
		$am = core_auth_AuthManager::getInstance();
		// look for a random user up to 50 times
		for($i = 0; $i < 100; $i++)
		{
			$user = array('userID' => 0, 'login'=> $userNamePrefix.$f->Internet->user_name, 'first'=> $f->Name->first_name, 'last'=>$f->Name->surname, 'mi'=> substr($f->Name->first_name, 0, 1), 'email'=> $f->Internet->email);
			if($am->getAuthModuleForUsername($user['login']) == false)
			{
				return $user;
			}
		}
		exit('couldnt find unused user in less then 100 tries');
	}
	
	protected function doGetDBM()
	{
		
		return core_db_DBManager::getConnection($config->dbConnData);
	}
	
	protected function doTestUser($user)
	{
		$this->assertType('core_auth_User', $user);
		$this->assertObjectHasAttribute('userID', $user);
		$this->assertObjectHasAttribute('login', $user);
		$this->assertObjectHasAttribute('first', $user);
		$this->assertObjectHasAttribute('last', $user);
		$this->assertObjectHasAttribute('mi', $user);
		$this->assertObjectHasAttribute('email', $user);
		$this->assertObjectHasAttribute('createTime', $user);
		$this->assertObjectHasAttribute('lastLogin', $user);
		$this->assertGreaterThan(0, $user->userID);
		$this->assertGreaterThan(0, strlen($user->login));
	}
	
	protected function doTestInstanceData(&$instData)
	{
		$scoreMethods = array(cfg_obo_Instance::SCORE_METHOD_HIGHEST , cfg_obo_Instance::SCORE_METHOD_MEAN, cfg_obo_Instance::SCORE_METHOD_RECENT);
		$this->assertType('nm_los_InstanceData', $instData);
		
		$this->assertObjectHasAttribute('instID', $instData);
		$this->assertTrue(nm_los_Validator::isPosInt($instData->instID));
		$this->assertObjectHasAttribute('loID', $instData);
		$this->assertTrue(nm_los_Validator::isPosInt($instData->loID));
		$this->assertObjectHasAttribute('userID', $instData);
		$this->assertTrue(nm_los_Validator::isString($instData->userID));
		$this->assertObjectHasAttribute('name', $instData);
		$this->assertTrue(nm_los_Validator::isString($instData->name));
		$this->assertObjectHasAttribute('courseID', $instData);
		$this->assertTrue(nm_los_Validator::isString($instData->courseID));
		$this->assertObjectHasAttribute('createTime', $instData);
		$this->assertTrue(nm_los_Validator::isString($instData->createTime));
		$this->assertObjectHasAttribute('startTime', $instData);
		$this->assertTrue(nm_los_Validator::isPosInt($instData->startTime));
		$this->assertObjectHasAttribute('endTime', $instData);
		$this->assertTrue(nm_los_Validator::isPosInt($instData->endTime));
		$this->assertObjectHasAttribute('attemptCount', $instData);
		$this->assertTrue(nm_los_Validator::isPosInt($instData->attemptCount));
		$this->assertObjectHasAttribute('scoreMethod', $instData);
		$this->assertContains($instData->scoreMethod, $scoreMethods);
		$this->assertObjectHasAttribute('perms', $instData);
		//$this->assertTrue(is_array($instData->perms));
	}
	
	protected function doTestLO($LOID, $lo, $testMeta=false, $testInstance=false)
	{
		
		$qTypes = array('MC', 'QA', 'Media');
		$pItemNames = array('TextArea', 'TextArea2', 'MediaView', 'MediaView1', 'MediaView2');
		
		$this->assertType('nm_los_LO', $lo);
		
		$this->assertObjectHasAttribute('version', $lo);
		$this->assertObjectHasAttribute('subVersion', $lo);
		$isMaster = ($lo->version > 0 && $lo->subVersion == 0);
		
		$this->assertObjectHasAttribute('loID', $lo);
		$this->assertTrue(nm_los_Validator::isPosInt($lo->loID));
		$this->assertEquals($LOID, $lo->loID);
		$this->assertObjectHasAttribute('title', $lo);
		if($isMaster) $this->assertTrue(nm_los_Validator::isString($lo->title));
		$this->assertObjectHasAttribute('languageID', $lo);
		$this->assertTrue(nm_los_Validator::isPosInt($lo->languageID));
		$this->assertObjectHasAttribute('notesID', $lo);
		$this->assertObjectHasAttribute('notes', $lo);
		//if($isMaster) $this->assertTrue(nm_los_Validator::isPosInt($lo->notesID));
		if($testMeta == false && nm_los_Validator::isString($lo->notes))
		{
			$this->assertTrue(nm_los_Validator::isPosInt($lo->notesID));
		}		
		
		$this->assertTrue(nm_los_Validator::isString($lo->notes));
		$this->assertObjectHasAttribute('objective', $lo);
		$this->assertObjectHasAttribute('objID', $lo);
		if($isMaster && !$testMeta) $this->assertTrue(nm_los_Validator::isPosInt($lo->objID));
		if($isMaster && !$testMeta) $this->assertTrue(nm_los_Validator::isString($lo->objective));
		if($testMeta == false &&  nm_los_Validator::isString($lo->objective))
		{
			$this->assertTrue(nm_los_Validator::isPosInt($lo->objID));
		}
		$this->assertObjectHasAttribute('learnTime', $lo);
		
		$this->assertTrue(nm_los_Validator::isInt($lo->learnTime));
		if($isMaster) $this->assertGreaterThanOrEqual(0, $lo->learnTime);
		$this->assertTrue(nm_los_Validator::isInt($lo->version));
		$this->assertTrue(nm_los_Validator::isInt($lo->subVersion));

		$this->assertObjectHasAttribute('rootID', $lo);
		$this->assertTrue(nm_los_Validator::isPosInt($lo->rootID));
		$this->assertObjectHasAttribute('parentID', $lo);
		$this->assertTrue(nm_los_Validator::isInt($lo->parentID));
		// Master objects root = self, parent = self only if it is a 1.0, if it is a 2.0 parent points to the 1.0
		if($isMaster)
		{
			$this->assertEquals($lo->loID, $lo->rootID);
			if($lo->version != 1)
			{
				$this->assertFalse($lo->loID == $lo->parentID);
			}
			
		}
		$this->assertObjectHasAttribute('createTime', $lo);
		$this->assertTrue(nm_los_Validator::isPosInt($lo->createTime));
		$this->assertObjectHasAttribute('copyright', $lo);
		$this->assertObjectHasAttribute('pages', $lo);
		if($testMeta == false) 
		{
			$this->assertGreaterThan(0, count($lo->pages));
			// loop through pages
			foreach($lo->pages AS $page)
			{
				$this->assertObjectHasAttribute('pageID', $page);
				$this->assertTrue(nm_los_Validator::isPosInt($page->pageID));
				$this->assertObjectHasAttribute('title', $page);
				if($isMaster) $this->assertTrue(nm_los_Validator::isString($page->title));
				$this->assertObjectHasAttribute('userID', $page);
				$this->assertTrue(nm_los_Validator::isInt($page->userID));
				$this->assertObjectHasAttribute('layoutID', $page);
				$this->assertTrue(nm_los_Validator::isPosInt($page->layoutID));
				$this->assertObjectHasAttribute('createTime', $page);
				$this->assertTrue(nm_los_Validator::isPosInt($page->createTime));
				$this->assertObjectHasAttribute('items', $page);
				$this->assertGreaterThan(0, count($page->items));
				// loop through page items
				foreach($page->items as $item)
				{
					$this->assertObjectHasAttribute('pageItemID', $item);
					$this->assertTrue(nm_los_Validator::isPosInt($item->pageItemID));
					$this->assertObjectHasAttribute('name', $item);
					$this->assertTrue(nm_los_Validator::isString($item->name));
					$this->assertContains($item->name, $pItemNames);
					$this->assertObjectHasAttribute('layoutItemID', $item);
					$this->assertTrue(nm_los_Validator::isPosInt($item->layoutItemID));
					$this->assertObjectHasAttribute('data', $item);
					$this->assertObjectHasAttribute('media', $item);
				}
				$this->assertObjectHasAttribute('questionID', $page);
			}
			$this->assertObjectHasAttribute('pGroup', $lo);
			$this->assertObjectHasAttribute('qGroupID', $lo->pGroup);
			$this->assertObjectHasAttribute('userID', $lo->pGroup);
			$this->assertObjectHasAttribute('rand', $lo->pGroup);
			$this->assertContains($lo->pGroup->rand, array('0', '1'));
			$this->assertObjectHasAttribute('allowAlts', $lo->pGroup);
			$this->assertObjectHasAttribute('altMethod', $lo->pGroup);
			$this->assertContains($lo->pGroup->altMethod, array('r', 'k'));
			if($testInstance == false)
			{
				$this->assertObjectHasAttribute('kids', $lo->pGroup);
				$this->assertGreaterThan(0, count($lo->pGroup->kids));
				// practice questions
				foreach($lo->pGroup->kids AS $pQ)
				{
					$this->assertObjectHasAttribute('questionID', $pQ);
					$this->assertTrue(nm_los_Validator::isPosInt($pQ->questionID));
					$this->assertObjectHasAttribute('userID', $pQ);
					$this->assertTrue(nm_los_Validator::isPosInt($pQ->userID));
					$this->assertObjectHasAttribute('itemType', $pQ);
					$this->assertContains($pQ->itemType, $qTypes);
					$this->assertObjectHasAttribute('answers', $pQ);
					$this->assertGreaterThan(0, count($pQ->answers));
					$this->assertObjectHasAttribute('perms', $pQ);
					$this->assertObjectHasAttribute('items', $pQ);
					$this->assertGreaterThan(0, count($pQ->items));
					// question page items
					foreach($pQ->items as $pQItem)
					{
						$this->assertObjectHasAttribute('pageItemID', $pQItem);
						$this->assertTrue(nm_los_Validator::isPosInt($pQItem->pageItemID));
						$this->assertObjectHasAttribute('name', $pQItem);
						$this->assertContains($item->name, $pItemNames);
						$this->assertTrue(nm_los_Validator::isString($pQItem->name));
						$this->assertObjectHasAttribute('layoutItemID', $pQItem);
						$this->assertObjectHasAttribute('data', $pQItem);
						if($pQItem->layoutItemID == 'MediaView')
						{
							$this->assertObjectHasAttribute('media', $pQItem);
							$this->assertGreaterThan(0, count($pQItem->media));
							// media items
							foreach($pQItem->media as $media)
							{
								$this->assertObjectHasAttribute('mediaID', $media);
								$this->assertTrue(nm_los_Validator::isPosInt($media->id));
								$this->assertObjectHasAttribute('userID', $media);
								$this->assertTrue(nm_los_Validator::isPosInt($media->author));
								$this->assertObjectHasAttribute('title', $media);
								$this->assertObjectHasAttribute('itemType', $media);
								$this->assertObjectHasAttribute('desc', $media);
								$this->assertObjectHasAttribute('url', $media);
								$this->assertObjectHasAttribute('createTime', $media);
								$this->assertTrue(nm_los_Validator::isPosInt($media->createTime));
								$this->assertObjectHasAttribute('copyright', $media);
								$this->assertObjectHasAttribute('height', $media);
								$this->assertObjectHasAttribute('width', $media);
								$this->assertObjectHasAttribute('length', $media);
								$this->assertObjectHasAttribute('version', $media);
							}
						}
					}
					$this->assertObjectHasAttribute('questionIndex', $pQ);
					$this->assertObjectHasAttribute('feedback', $pQ);
					$this->assertArrayHasKey('correct', $pQ->feedback);
					$this->assertArrayHasKey('incorrect', $pQ->feedback);
				}
			}
			$this->assertObjectHasAttribute('quizSize', $lo->pGroup);
			$this->assertObjectHasAttribute('aGroup', $lo);
			$this->assertObjectHasAttribute('qGroupID', $lo->aGroup);
			$this->assertObjectHasAttribute('userID', $lo->aGroup);
			$this->assertObjectHasAttribute('rand', $lo->aGroup);
			$this->assertContains($lo->aGroup->rand, array('0', '1'));		
			$this->assertObjectHasAttribute('allowAlts', $lo->aGroup);
			$this->assertObjectHasAttribute('altMethod', $lo->aGroup);
			$this->assertContains($lo->aGroup->altMethod, array('r', 'k'));
			$this->assertObjectHasAttribute('kids', $lo->aGroup);
			if($testInstance == false)
			{
				$this->assertGreaterThan(0, count($lo->aGroup->kids));

				// Questions
				foreach($lo->aGroup->kids AS $pQ)
				{
					$this->assertObjectHasAttribute('questionID', $pQ);
					$this->assertTrue(nm_los_Validator::isPosInt($pQ->questionID));
					$this->assertObjectHasAttribute('userID', $pQ);
					$this->assertTrue(nm_los_Validator::isPosInt($pQ->userID));
					$this->assertObjectHasAttribute('itemType', $pQ);
					$this->assertContains($pQ->itemType, $qTypes);
					$this->assertObjectHasAttribute('answers', $pQ);
					$this->assertGreaterThan(0, count($pQ->answers));
					$this->assertObjectHasAttribute('perms', $pQ);
					$this->assertObjectHasAttribute('items', $pQ);
					$this->assertGreaterThan(0, count($pQ->items));
					// Items
					foreach($pQ->items as $pQItem)
					{
						$this->assertObjectHasAttribute('pageItemID', $pQItem);
						$this->assertTrue(nm_los_Validator::isPosInt($pQItem->pageItemID));
						$this->assertObjectHasAttribute('name', $pQItem);
						$this->assertContains($item->name, $pItemNames);
						$this->assertTrue(nm_los_Validator::isString($pQItem->name));
						$this->assertObjectHasAttribute('layoutItemID', $pQItem);
						$this->assertObjectHasAttribute('data', $pQItem);
						if($pQItem->layoutItemID == 'MediaView')
						{
							$this->assertObjectHasAttribute('media', $pQItem);
							$this->assertGreaterThan(0, count($pQItem->media));
							// Media Objects
							foreach($pQItem->media as $media)
							{
								$this->assertObjectHasAttribute('mediaID', $media);
								$this->assertTrue(nm_los_Validator::isPosInt($media->mediaID));
								$this->assertObjectHasAttribute('userID', $media);
								$this->assertTrue(nm_los_Validator::isPosInt($media->author));
								$this->assertObjectHasAttribute('title', $media);
								$this->assertObjectHasAttribute('itemType', $media);
								$this->assertObjectHasAttribute('desc', $media);
								$this->assertObjectHasAttribute('url', $media);
								$this->assertObjectHasAttribute('createTime', $media);
								$this->assertTrue(nm_los_Validator::isPosInt($media->createTime));
								$this->assertObjectHasAttribute('copyright', $media);
								$this->assertObjectHasAttribute('height', $media);
								$this->assertObjectHasAttribute('width', $media);
								$this->assertObjectHasAttribute('length', $media);
								$this->assertObjectHasAttribute('version', $media);
							}
						}
					}
					$this->assertObjectHasAttribute('questionIndex', $pQ);
					$this->assertObjectHasAttribute('feedback', $pQ);
					$this->assertArrayHasKey('correct', $pQ->feedback);
					$this->assertArrayHasKey('incorrect', $pQ->feedback);
				}
			}
			$this->assertObjectHasAttribute('quizSize', $lo->aGroup);
		
			$this->assertObjectHasAttribute('layouts', $lo);
			
			//$this->assertEquals(7, count($lo->layouts));
			// Layouts
		
			foreach($lo->layouts AS $layout)
			{
				$this->assertObjectHasAttribute('layoutID', $layout);
				$this->assertTrue(nm_los_Validator::isPosInt($layout->layoutID));
				$this->assertObjectHasAttribute('name', $layout);
				$this->assertObjectHasAttribute('thumb', $layout);
				$this->assertObjectHasAttribute('items', $layout);
				$this->assertObjectHasAttribute('tags', $layout);
				// test layout items
				foreach($layout->items AS $item)
				{
					$this->assertObjectHasAttribute('layoutItemID', $item);
					$this->assertTrue(nm_los_Validator::isPosInt($item->layoutItemID));
					$this->assertObjectHasAttribute('name', $item);
					$this->assertObjectHasAttribute('component', $item);
					$this->assertObjectHasAttribute('x', $item);
					$this->assertObjectHasAttribute('y', $item);
					$this->assertObjectHasAttribute('width', $item);
					$this->assertObjectHasAttribute('height', $item);
					$this->assertObjectHasAttribute('data', $item);
				}
			}
		}
		else
		{
			$this->assertFalse(isset($lo->pages));
			$this->assertFalse(isset($lo->aGroup));
			$this->assertFalse(isset($lo->pGroup));
			$this->assertFalse(isset($lo->layouts));
		}
		$this->assertObjectHasAttribute('keywords', $lo);
		$this->assertTrue(is_array($lo->keywords));
		$this->assertObjectHasAttribute('perms', $lo);
		$this->assertGreaterThan(0, count($lo->perms));
		$this->doTestPerms($lo->perms);
		$this->assertObjectHasAttribute('summary', $lo);
		$this->assertArrayHasKey('contentSize', $lo->summary);
		if(!$testMeta) $this->assertEquals(count($lo->pages), $lo->summary['contentSize']);
		$this->assertTrue(nm_los_Validator::isPosInt($lo->summary['contentSize']));
		$this->assertArrayHasKey('practiceSize', $lo->summary);
		if($testInstance == false)
		{
			if(!$testMeta) $this->assertEquals(count($lo->pGroup->kids), $lo->summary['practiceSize']);
		}
		$this->assertTrue(nm_los_Validator::isPosInt($lo->summary['practiceSize']));
		$this->assertArrayHasKey('assessmentSize', $lo->summary);
		$this->assertTrue(nm_los_Validator::isPosInt($lo->summary['assessmentSize']));
	}
 
	protected function doTestPerms($perms)
	{
		$boolStringValues = array(1, 0);
		$this->assertObjectHasAttribute('userID', $perms);
		$this->assertObjectHasAttribute('read', $perms);
		$this->assertContains($perms->read, $boolStringValues);
		$this->assertObjectHasAttribute('write', $perms);
		$this->assertContains($perms->write, $boolStringValues);
		$this->assertObjectHasAttribute('copy', $perms);
		$this->assertContains($perms->copy, $boolStringValues);
		$this->assertObjectHasAttribute('publish', $perms);
		$this->assertContains($perms->publish, $boolStringValues);
		$this->assertObjectHasAttribute('giveRead', $perms);
		$this->assertContains($perms->giveRead, $boolStringValues);
		$this->assertObjectHasAttribute('giveWrite', $perms);
		$this->assertContains($perms->giveWrite, $boolStringValues);
		$this->assertObjectHasAttribute('giveCopy', $perms);
		$this->assertContains($perms->giveCopy, $boolStringValues);
		$this->assertObjectHasAttribute('givePublish', $perms);
		$this->assertContains($perms->givePublish, $boolStringValues);
		$this->assertObjectHasAttribute('giveGlobal', $perms);
		$this->assertContains($perms->giveGlobal, $boolStringValues);
	}

	protected function makeBasicLO()
	{
		$lo = array();
		$lo['pGroup'] = array(
							'altMethod' => 'r',
							'allowAlts' => '0',
							'userID' => 0,
							'kids' => array(
								array(
								'itemType' => 'MC',
							    'items' => array(array('data' => '', 'name' => 'TextArea', 'layoutItemID'=> 0, 'pageItemID'=> 0, 'media' => array())), 
								'questionID' => 0,
								'altIndex' => 0,
								'answers' => array(array('feedback' => '', 'weight' => 0, 'userID' => 0, 'answerID' => 0, 'answer' => ''), array('feedback' => '', 'weight' => 0, 'userID' => 0, 'answerID' => 0, 'answer' => '')),
								'feedback' => array('correct' => '', 'incorrect' => ''),
								'perms' => 0,
								'userID' => 0
								)
							),
							'rand'=> '0',
							'qGroupID' => 0
						);
		$lo['loID'] = 0 ;
		$lo['learnTime'] = 0 ;
		$lo['createTime'] = time();
		$lo['languageID'] = 1;
		$lo['objID'] = 0;
		$lo['objective'] = '';
		$lo['aGroup'] = array(
							'altMethod' => 'r',
							'allowAlts' => '0',
							'userID' => 0,
							'kids' => array(
								array(
								'itemType' => 'MC',
							    'items' => array(array('data' => '', 'name' => 'TextArea', 'layoutItemID'=> 0, 'pageItemID'=> 0, 'media' => array())), 
								'questionID' => 0,
								'alt_group' => 0,
								'answers' => array(array('feedback' => '', 'weight' => 0, 'userID' => 0, 'answerID' => 0, 'answer' => ''), array('feedback' => '', 'weight' => 0, 'userID' => 0, 'answerID' => 0, 'answer' => '')),
								'required' => false,
								'feedback' => array('correct' => '', 'incorrect' => ''),
								'perms' => 0,
								'userID' => 0
								)
							),
							'rand'=> '0',
							'qGroupID' => 0
						);
		
		$lo['parentID'] = 0;
		$lo['version'] = 0;
		$lo['notesID'] = 0;
		$lo['notes'] = 'notes!';
		$lo['subVersion'] = 0;
		$lo['rootID'] = 0;
		$lo['perms'] = array('read' => 0, 'write' => 0, 'giveWrite' => 0, 'givePublish' => 0, 'giveGlobal' => 0, 'giveRead' => 0 , 'giveCopy' => 0, 'userID' => 0, 'copy' => 0, 'publish' => 0);
		$lo['layouts'] = array();
		$lo['pages'] = array(
							array(
							'title' => 'PageTitle',
							'questionID' => -1,
							'createTime' => '',
							'userID' => 0,
							'items' => array(
									array(
										'data' => '',
										'name' => 'TextArea',
										'layoutItemID' => 1,
										'pageItemID' => 0,
										'media' => array()
									)
								
								),
							'layoutID' => 1,
							'pageID' => 0
							)
						);
		$lo['title'] = '';
		$lo['keywords'] = array();
		return $lo;
	}
	
	protected function makeFullLO()
	{
		$API = nm_los_API::getInstance();
		@$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		
		$fileData = $this->doMakeFileData(dirname(__FILE__).'/media/image.png');
		$mm = nm_los_MediaManager::getInstance();
		$return = $mm->handleMediaUpload($fileData, 'image title', 'image description', 'copyright', '5000');
		$this->assertTrue($return);
		
		$fileData = $this->doMakeFileData(dirname(__FILE__).'/media/flash6.swf');
		$return = $mm->handleMediaUpload($fileData, 'image title', 'image description', 'copyright', '5000');
		$this->assertTrue($return);
		
		
		$medias = $API->getMedia();
		$questionMediaID = $medias[count($medias)-1]->mediaID;
		$contentMediaID = $medias[count($medias)-2]->mediaID;
		// load json
		$json = file_get_contents(dirname(__FILE__).'/lo.json');
		$json = utf8_encode($json );
		$libraryLO = json_decode($json, true);
		
		$libraryLO['pages'][3]['items'][1]['media'][0]['mediaID'] = $contentMediaID;
		$libraryLO['pages'][4]['items'][1]['media'][0]['mediaID'] = $contentMediaID;
		$libraryLO['pages'][5]['items'][1]['media'][0]['mediaID'] = $contentMediaID;
		
		$libraryLO['pGroup']['kids'][2]['items'][0]['media'][0]['mediaID'] = $questionMediaID;
		$libraryLO['pGroup']['kids'][3]['items'][0]['media'][0]['mediaID'] = $questionMediaID;
		
		$libraryLO['aGroup']['kids'][2]['items'][0]['media'][0]['mediaID'] = $questionMediaID;
		$libraryLO['aGroup']['kids'][4]['items'][0]['media'][0]['mediaID'] = $questionMediaID;
		$libraryLO['aGroup']['kids'][8]['items'][0]['media'][0]['mediaID'] = $questionMediaID;
		$this->assertEquals(null, $API->doLogout());
		return $libraryLO;
	}
	
	protected function makeLibraryLO()
	{
		$libraryLO = $this->makeFullLO();
		$API = nm_los_API::getInstance();
		@$this->assertTrue($API->doLogin(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$newLO = $API->createDraft($libraryLO);
		$this->assertTrue( $API->createMaster($newLO->loID));
		$inst = $API->createInstance('Library Learning Object', $newLO->loID, 'Library Learning Object', time(), time()+6000, 2, 'h');
		$this->assertGreaterThan(0,  $inst);
		$this->assertEquals(null, $API->doLogout());
		return $inst;
	}

	protected function makeBadLO()
	{
		$lo = array();
		$lo['pGroup'] = array(
							'alt_type' => 'r',
							'alts' => '0',
							'author' => 0,
							'kids' => array(
								array(
								'type' => 'MC',
							    'items' => array(array('cdata' => '', 'name' => 'TextArea', 'layoutItemID'=> 0, 'id'=> 0, 'media' => array())), 
								'id' => 0,
								'alt_group' => 0,
								'answers' => array(array('feedback' => '', 'weight' => 0, 'author' => 0, 'id' => 0, 'atext' => ''), array('feedback' => '', 'weight' => 0, 'author' => 0, 'id' => 0, 'atext' => '')),
								'required' => false,
								'feedback' => array('correct' => '', 'incorrect' => ''),
								'perms' => 0,
								'author' => 0
								)
							),
							'rand'=> '0',
							'id' => 0
						);
		$lo['id'] = 0 ;
		$lo['learn_time'] = 0 ;
		$lo['datec'] = time();
		$lo['lang'] = 1;
		$lo['objective'] = array('id' => 0, 'text' => '');
		$lo['aGroup'] = array(
							'alt_type' => 'r',
							'alts' => '0',
							'author' => 0,
							'kids' => array(
								array(
								'type' => 'MC',
							    'items' => array(array('cdata' => '', 'name' => 'TextArea', 'layoutItemID'=> 0, 'id'=> 0, 'media' => array())), 
								'id' => 0,
								'alt_group' => 0,
								'answers' => array(array('feedback' => '', 'weight' => 0, 'author' => 0, 'id' => 0, 'atext' => ''), array('feedback' => '', 'weight' => 0, 'author' => 0, 'id' => 0, 'atext' => '')),
								'required' => false,
								'feedback' => array('correct' => '', 'incorrect' => ''),
								'perms' => 0,
								'author' => 0
								)
							),
							'rand'=> '0',
							'id' => 0
						);
		
		$lo['parent'] = 0;
		$lo['vers_whole'] = 0;
		$lo['desc'] = array('id' => 0, 'text' => 'a');
		$lo['vers_part'] = 0;
		$lo['root'] = 0;
		$lo['perms'] = array('p_read' => 0, 'p_write' => 0, 'give_write' => 0, 'give_use' => 0, 'give_global' => 0, 'give_read' => 0 , 'allow_req' => 0, 'give_copy' => 0, 'user_id' => 0, 'p_copy' => 0, 'p_use' => 0);
		$lo['layouts'] = array();
		$lo['pages'] = array(
							array(
							'title' => '',
							'q_id' => -1,
							'datec' => '',
							'author' => 0,
							'items' => array(
									array(
										'cdata' => '',
										'name' => 'TextArea',
										'layoutItemID' => 1,
										'id' => 0,
										'media' => array()
									)
								
								),
							'layout' => 1,
							'id' => 0
							)
						);
		$lo['auth'] = '';
		$lo['title'] = '';
		$lo['keywords'] = array();
		return $lo;
	}	
}
?>