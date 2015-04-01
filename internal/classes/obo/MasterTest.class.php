<?php
namespace obo;
require_once('PHPUnit/Framework.php');
require_once(dirname(__FILE__)."/../../../app.php");

class MasterTest extends PHPUnit_Framework_TestCase
{
	public  $MAS;
	const ADMIN_USER = '~su';
	const ADMIN_PW = 'mindshare01';

	protected function setUp()
	{

		$config->writeErrorsToLog = true;
		$config->dbConnData = new \rocketD\db\DBConnectData('localhost' , 'root', 'root', 'los_UnitTest');

	}


	public function testSetupDB()
	{

		$DBSchema = file_get_contents(dirname(__FILE__)."/../../../DELETEME/tests/dbStructure.sql");
		@mysql_connect($config->dbConnData->host, $config->dbConnData->user, $config->dbConnData->pass);
		@mysql_query('DROP DATABASE ' . $config->dbConnData->db);
		@mysql_query('CREATE DATABASE '.$config->dbConnData->db);
		@mysql_select_db($config->dbConnData->db);
		@mysql_close();

		@$mysqli = new mysqli($config->dbConnData->host, $config->dbConnData->user, $config->dbConnData->pass, $config->dbConnData->db);
		@$mysqli->multi_query($DBSchema);
		$mysqli->close();
		sleep(1);
	}


 	public function testverifySessionEmpty()
 	{
		$this->MAS = new nm_los_Master();
 		@$this->assertEquals(false, $this->MAS->verifySession());
 	}

 	public function testverifySessionSet()
 	{
		$this->MAS = new nm_los_Master();
 		@$this->MAS->login(self::ADMIN_USER, md5(self::ADMIN_PW));
 		@$this->assertTrue($this->MAS->verifySession());
 		$this->MAS->logout();
 	}

	public function testVerifySessionWithRoleName()
	{
		$this->MAS = new nm_los_Master();
		@$this->assertFalse($this->MAS->verifySession('SuperUser'));
		@$this->assertFalse($this->MAS->verifySession('Administrator'));
		@$this->assertFalse($this->MAS->verifySession('ContentCreator'));
		@$this->assertFalse($this->MAS->verifySession('SuperViewer'));
		@$this->assertFalse($this->MAS->verifySession('WikiEditor'));
		@$this->assertFalse( $this->MAS->verifySession('LibraryUser'));

		@$this->MAS->login(self::ADMIN_USER, md5(self::ADMIN_PW));

		@$this->assertTrue($this->MAS->verifySession('SuperUser'));
		@$this->assertTrue($this->MAS->verifySession('Administrator'));
		@$this->assertTrue( $this->MAS->verifySession('ContentCreator'));
		$error = new \obo\util\Error(4004);
		@$this->assertEquals($error, $this->MAS->verifySession('SuperViewer'));
		@$this->assertEquals($error, $this->MAS->verifySession('WikiEditor'));
		@$this->assertEquals($error, $this->MAS->verifySession('LibraryUser'));
		@$this->assertEquals(null, $this->MAS->logout());

	}

	public function testVerifySessionRole()
	{
		$this->MAS = new nm_los_Master();
		$error = new \obo\util\Error(2);
		$this->assertEquals($error, $this->MAS->verifySessionRole('SuperUser'));
		@$this->assertEquals(array('validSession' => null, 'roleNames' => array('SuperUser'), 'hasRoles' => array()), $this->MAS->verifySessionRole(array('SuperUser')));
		@$this->assertEquals(array('validSession' => null, 'roleNames' => array('Administrator'), 'hasRoles' => array()), $this->MAS->verifySessionRole(array('Administrator')));
		@$this->assertEquals(array('validSession' => null, 'roleNames' => array('ContentCreator'), 'hasRoles' => array()), $this->MAS->verifySessionRole(array('ContentCreator')));
		@$this->assertEquals(array('validSession' => null, 'roleNames' => array('SuperViewer'), 'hasRoles' => array()), $this->MAS->verifySessionRole(array('SuperViewer')));
		@$this->assertEquals(array('validSession' => null, 'roleNames' => array('WikiEditor'), 'hasRoles' => array()), $this->MAS->verifySessionRole(array('WikiEditor')));
		@$this->assertEquals(array('validSession' => null, 'roleNames' => array('LibraryUser'), 'hasRoles' => array()), $this->MAS->verifySessionRole(array('LibraryUser')));
		@$this->assertEquals(array('validSession' => null, 'roleNames' => array('LibraryUser', 'WikiEditor'), 'hasRoles' => array()), $this->MAS->verifySessionRole(array('LibraryUser', 'WikiEditor')));


		@$this->MAS->login(self::ADMIN_USER, md5(self::ADMIN_PW));
		$this->assertEquals(array('validSession' => true, 'roleNames' => array('SuperUser'), 'hasRoles' => array('SuperUser')), $this->MAS->verifySessionRole(array('SuperUser')));
		$this->assertEquals(array('validSession' => true, 'roleNames' => array('Administrator'), 'hasRoles' => array('Administrator')), $this->MAS->verifySessionRole(array('Administrator')));
		$this->assertEquals(array('validSession' => true, 'roleNames' => array('ContentCreator'), 'hasRoles' => array('ContentCreator')), $this->MAS->verifySessionRole(array('ContentCreator')));
		$this->assertEquals(array('validSession' => true, 'roleNames' => array('SuperViewer'), 'hasRoles' => array()), $this->MAS->verifySessionRole(array('SuperViewer')));


		$this->assertEquals($error, $this->MAS->verifySessionRole('SuperViewer'));

		$this->assertEquals(array('validSession' => true, 'roleNames' => array('WikiEditor'), 'hasRoles' => array()), $this->MAS->verifySessionRole(array('WikiEditor')));
		$this->assertEquals(array('validSession' => true, 'roleNames' => array('LibraryUser'), 'hasRoles' => array()), $this->MAS->verifySessionRole(array('LibraryUser')));
		$this->assertEquals(array('validSession' => true, 'roleNames' => array('LibraryUser', 'WikiEditor'), 'hasRoles' => array()), $this->MAS->verifySessionRole(array('LibraryUser', 'WikiEditor')));
		$this->assertEquals(null, $this->MAS->logout());
	}

	public function testLogin()
	{
		$this->MAS = new nm_los_Master();
		@$this->assertTrue($this->MAS->login(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$this->assertEquals(null, $this->MAS->logout());

		@$this->assertTrue($this->MAS->login(self::ADMIN_USER, md5(self::ADMIN_PW) . ' '));
		$this->assertEquals(null, $this->MAS->logout());

		@$this->assertTrue($this->MAS->login(self::ADMIN_USER, ' '.md5(self::ADMIN_PW)));
		$this->assertEquals(null, $this->MAS->logout());
		@$this->assertTrue($this->MAS->login(self::ADMIN_USER, ' ' .md5(self::ADMIN_PW) . ' '));
		$this->assertEquals(null, $this->MAS->logout());
		@$this->assertTrue($this->MAS->login(' '.self::ADMIN_USER, md5(self::ADMIN_PW)));
		$this->assertEquals(null, $this->MAS->logout());
		@$this->assertTrue($this->MAS->login(self::ADMIN_USER.' ', md5(self::ADMIN_PW)));
		$this->assertEquals(null, $this->MAS->logout());
		@$this->assertTrue($this->MAS->login(' '.self::ADMIN_USER.' ', md5(self::ADMIN_PW)));
		$this->assertEquals(null, $this->MAS->logout());

		$error = new \obo\util\Error(1003);
		@$this->assertEquals($error, $this->MAS->login(self::ADMIN_USER, md5('adsfadsfadsfdasf')));
		@$this->assertEquals(null, $this->MAS->logout());
	}

	public function testGetUserInfo()
	{
		$this->MAS = new nm_los_Master();
		$error = new \obo\util\Error(1);
		@$this->assertEquals($error, $this->MAS->getUserInfo());

		@$this->assertTrue($this->MAS->login(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$user =  $this->MAS->getUserInfo();

		$this->assertObjectHasAttribute('id', $user);
		$this->assertObjectHasAttribute('login', $user);
		$this->assertObjectHasAttribute('first', $user);
		$this->assertObjectHasAttribute('last', $user);
		$this->assertObjectHasAttribute('mi', $user);
		$this->assertObjectHasAttribute('email', $user);
		$this->assertObjectHasAttribute('creation_date', $user);
		$this->assertObjectHasAttribute('last_login', $user);
		$this->assertEquals(1, $user->id);
		$this->assertEquals(self::ADMIN_USER, $user->login);
		$this->assertEquals(null, $this->MAS->logout());
		@$this->assertEquals($error, $this->MAS->getUserInfo());
	}

	public function testGetUserName()
	{
		$this->MAS = new nm_los_Master();
		$error = new \obo\util\Error(2);

		$this->assertEquals($error, $this->MAS->getUserName(-1));
		$this->assertEquals($error, $this->MAS->getUserName(0));
		$this->assertEquals($error, $this->MAS->getUserName('adsfdfs'));
		$this->assertEquals($error, $this->MAS->getUserName(array(3)));

		@$this->assertEquals(new \obo\util\Error(1), $this->MAS->getUserName(1));

		@$this->assertTrue($this->MAS->login(self::ADMIN_USER, md5(self::ADMIN_PW)));

		@$this->assertEquals('New Media', $this->MAS->getUserName(1));
		@$this->assertFalse($this->MAS->getUserName(999999999999999999999999)); // get a user that doesnt exist
		$this->assertEquals(null, $this->MAS->logout());
		// TODO: test someone with a middle name
	}

	public function testGetAllUsers()
	{
		$this->MAS = new nm_los_Master();
		$error = new \obo\util\Error(1);
		@$this->assertEquals($error, $this->MAS->getAllUsers());

		@$this->assertTrue( $this->MAS->login(self::ADMIN_USER, md5(self::ADMIN_PW)));

		@$allUsers = $this->MAS->getAllUsers();
		$this->assertGreaterThan(0, count($allUsers));
		foreach($allUsers as &$user)
		{
			$this->assertType('nm_los_User', $user);
		}
		// add and remove a user to test
	}

   	public function testGetAllContentCreators()
   	{
		$this->MAS = new nm_los_Master();
		$error = new \obo\util\Error(1);
		@$this->assertEquals($error, $this->MAS->getAllContentCreators());
		@$this->assertTrue( $this->MAS->login(self::ADMIN_USER, md5(self::ADMIN_PW)));
		@$users =  $this->MAS->getAllContentCreators();
		$this->assertGreaterThan(0, count($users));
		foreach($users as &$user)
		{
			$this->assertType('nm_los_User' ,$user);
		}
 		// TODO: add and remove a content creator to test
   	}

   	public function testdeleteLO()
   	{
		$this->MAS = new nm_los_Master();
   		@$this->assertEquals(new \obo\util\Error(2), $this->MAS->deleteLO());
   		@$this->assertEquals(new \obo\util\Error(1), $this->MAS->deleteLO(1));
   		@$this->assertEquals(new \obo\util\Error(2), $this->MAS->deleteLO('asdfadsfadsf'));
   		@$this->assertTrue($this->MAS->login(self::ADMIN_USER, md5(self::ADMIN_PW)));

   		$lo = $this->makeBasicLO();
   		@$newLO = $this->MAS->newLODraft($lo);
   		@$this->assertTrue($this->MAS->deleteLO($newLO->id));

   		$this->assertEquals(null, $this->MAS->logout());

   	}

    public function testGetLOFull()
 	{
		$this->MAS = new nm_los_Master();
 		@$this->assertEquals(new \obo\util\Error(2), $this->MAS->getLOFull());
 		@$this->assertEquals(new \obo\util\Error(2), $this->MAS->getLOFull('afadfs'));
 		@$this->assertEquals(new \obo\util\Error(2), $this->MAS->getLOFull(0));
 		@$this->assertEquals(new \obo\util\Error(2), $this->MAS->getLOFull(-1));


 		@$this->assertTrue($this->MAS->login(self::ADMIN_USER, md5(self::ADMIN_PW)));

 		$lo = $this->makeBasicLO();
    	@$newLO = $this->MAS->newLODraft($lo);
 		$LOID = $newLO->id;

 		@$this->assertEquals(new \obo\util\Error(2), $this->MAS->getLOFull());
 		@$this->assertEquals(new \obo\util\Error(2), $this->MAS->getLOFull('afadfs'));
 		@$this->assertEquals(new \obo\util\Error(2), $this->MAS->getLOFull(0));
 		@$this->assertEquals(new \obo\util\Error(2), $this->MAS->getLOFull(-1));

 		$lo = $this->MAS->getLOFull($LOID);
 		$this->doTestLO($LOID, $lo);
 		$this->assertEquals(null, $this->MAS->logout());
 	}

	public function testGetLatestLODraft(){}
	public function testGetLODrafts(){}
	public function testGetLOMeta()
	{
		$this->MAS = new nm_los_Master();
		@$this->assertTrue($this->MAS->login(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$lo = $this->makeBasicLO();
		@$newLO = $this->MAS->newLODraft($lo);
		$this->assertEquals(null, $this->MAS->logout());

		$LOID = $newLO->id;
		@$this->assertEquals(new \obo\util\Error(2), $this->MAS->getLOMeta());
		@$this->assertEquals(new \obo\util\Error(2), $this->MAS->getLOMeta('afadfs'));
		@$this->assertEquals(new \obo\util\Error(2), $this->MAS->getLOMeta(0));
		@$this->assertEquals(new \obo\util\Error(2), $this->MAS->getLOMeta(-1));
		@$lo = $this->MAS->getLOMeta($LOID);
		$this->assertType('\obo\lo\LO', $lo);
		$this->doTestLO($LOID, $lo, true);
		//TODO: test the newest argument of getLOMeta
	}
	public function testgetMyObjects()
	{
		$this->MAS = new nm_los_Master();
		@$this->assertEquals(new \obo\util\Error(1), $this->MAS->getMyObjects());

		@$this->assertTrue($this->MAS->login(self::ADMIN_USER, md5(self::ADMIN_PW)));
		$los = $this->MAS->getMyObjects();
		$this->assertType('array', $los);
		$this->assertGreaterThan(0, count($los));
		foreach($los AS &$lo)
		{
			$this->assertType('\obo\lo\LO', $lo);
			// TODO: test as non su - need to make sure they only get back thins with their userID in perms and read = 1
			//$this->assertTrue($lo->perms->user_id == $_SESSION['UID']);
			//$this->doTestLO($lo->id, $lo, true);
		}
		$this->assertEquals(null, $this->MAS->logout());

	}
	public function testgetPublicMasters()
	{
		// $this->MAS = new nm_los_Master();
		// @$this->assertEquals(new \obo\util\Error(1), $this->MAS->getPublicMasters());
		//
		// @$this->assertTrue($this->MAS->login(self::ADMIN_USER, md5(self::ADMIN_PW)));
		// $los = $this->MAS->getPublicMasters();
		// $this->assertType('array', $los);
		// $this->assertGreaterThan(0, count($los));
		// foreach($los AS &$lo)
		// {
		// 	$this->assertType('\obo\lo\LO', $lo);
		// 	$this->assertTrue($lo->perms->read == 1 ); // we should have read to everything here
		// 	//$this->doTestLO($lo->id, $lo, true);
		// }
		// $this->assertEquals(null, $this->MAS->logout());
	}
   //
   // 	public function testmakeDerivative(){}//!
   // 	public function testremoveFromLibrary(){}//!

   // 	public function testlockLO(){}
   // 	public function testunlockLO(){}
   // 	public function testgetInstancesFromLOID(){}//!
   // 	public function testnewInstance(){}//!
   // 	public function testgetInstance(){}//!
   // 	public function testgetInstanceMeta(){} //!
   // 	public function testgetAllInstances(){}//!
   // 	public function testupdateInstance(){}//!
   // 	public function testdeleteInstance(){}//!
   // 	public function testgetReadableMedia(){}//!
   // 	public function testnewMedia(){}//!
   // 	public function testsaveMedia(){}//!
   // 	public function testdeleteMedia(){}//!
   // 	public function testsetGlobalPerms(){}//!
   // 	public function testsetUsersPerms(){}
   // 	public function testremoveUserPerms(){}
   // 	public function testremoveUsersPerms(){}
   // 	public function testgetPermsForItem(){}
   // 	public function testgetAllLayouts(){}
   // 	public function teststartAttempt(){}//!
   // 	public function testAssessment()
   // 	{
   // 		$INSTID = 309;
   // 		@$this->assertEquals(new \obo\util\Error(2), $this->MAS->submitQuestion());
   // 		@$this->assertTrue($this->MAS->login(self::ADMIN_USER, md5(self::ADMIN_PW)));
   // 		// start the instance view
   // 		@$lo = $this->MAS->getInstance($INSTID);
   // 		$this->assertType('\obo\lo\LO', $lo);
   // 		// switch to the assessment tab
   // 		@$this->assertEquals(null,  $this->MAS->trackSectionChanged($lo->viewID, 3));
   //
   // 		// start the attempt view
   // 		@$attempt = $this->MAS->startAttempt($lo->viewID, $lo->agroup->id);
   // 		$this->assertGreaterThan(0, count($attempt));
   //
   // 		@$submit = $this->MAS->trackPageChanged($lo->viewID, $attempt[0]->id, 3);
   // 		$this->assertEquals(null, $submit);
   //
   // 		@$submit = $this->MAS->submitMedia($lo->viewID, $lo->agroup->id, $attempt[0]->id, -1);
   // 		$this->assertEquals(new \obo\util\Error(2), $submit);
   //
   // 		@$submit = $this->MAS->submitMedia($lo->viewID, $lo->agroup->id, $attempt[0]->id, 101);
   // 		$this->assertEquals(new \obo\util\Error(2), $submit);
   //
   // 		@$submit = $this->MAS->submitMedia($lo->viewID, $lo->agroup->id, $attempt[0]->id, 0);
   // 		$this->assertType('array', $submit);
   // 		$this->assertEquals(0, $submit['weight']);
   //
   // 		@$submit = $this->MAS->submitMedia($lo->viewID, $lo->agroup->id, $attempt[0]->id, 40);
   // 		$this->assertType('array', $submit);
   // 		$this->assertEquals(40, $submit['weight']);
   //
   // 		@$submit = $this->MAS->submitMedia($lo->viewID, $lo->agroup->id, $attempt[0]->id, 100);
   // 		$this->assertType('array', $submit);
   // 		$this->assertEquals(100, $submit['weight']);
   //
   // 		@$submit = $this->MAS->trackPageChanged($lo->viewID, $attempt[1]->id, 3);
   // 		$this->assertEquals(null, $submit);
   //
   // 		@$submit = $this->MAS->submitQuestion($lo->viewID, $lo->agroup->id, $attempt[1]->id, $attempt[1]->answers[0]->id);
   // 		$this->assertTrue($submit);
   //
   // 		@$submit = $this->MAS->submitQuestion($lo->viewID, $lo->agroup->id, $attempt[1]->id, $attempt[1]->answers[1]->id);
   // 		$this->assertTrue($submit);
   //
   // 		@$submit = $this->MAS->submitQuestion($lo->viewID, $lo->agroup->id, $attempt[1]->id, $attempt[1]->answers[2]->id);
   // 		$this->assertTrue($submit);
   //
   // 		@$submit = $this->MAS->trackPageChanged($lo->viewID, $attempt[2]->id, 3);
   // 		$this->assertEquals(null, $submit);
   //
   // 		@$submit = $this->MAS->submitQuestion($lo->viewID, $lo->agroup->id, $attempt[2]->id, $attempt[1]->answers[0]->id);
   // 		$this->assertTrue($submit);
   //
   // 		@$submit = $this->MAS->submitQuestion($lo->viewID, $lo->agroup->id, $attempt[2]->id, $attempt[1]->answers[1]->id);
   // 		$this->assertTrue($submit);
   //
   // 		@$end = $this->MAS->endAttempt($lo->viewID, $attempt[0]->id);
   // 		$this->assertGreaterThanOrEqual(0, $end);
   //
   // 		@$this->assertEquals(null, $this->MAS->logout());
   // 	}
   // // 	public function testsubmitMedia(){}//!
   // // 	public function testendAttempt(){}//!
   // // 	public function testgetScores(){}//!
   // // 	public function testgetVisitTrackingData(){}
   // // //	public function testgetQuestionResponses(){}
   // // 	public function testgetAllLanguages(){}
   // // 	public function testgetSessionID(){}//!
   // // 	public function testgetAllRoles(){}
   // // 	public function testgetUserRoles(){}
   // // 	public function testgetUsersInRole(){}
   // // 	public function testcreateRole(){}
   // // 	public function testdeleteRole(){}
   // // 	public function testremoveUsersFromRoles(){}
   // // 	public function testaddUsersToRoles(){}
   // // 	public function testtrackPageChanged(){}//!
   // // 	public function testtrackSectionChanged(){}//!
   // // 	public function testtrackComputerData(){}//!
   // // 	public function testresumeVisit(){}//!
   // // 	public function testuser_RequestPasswordReset(){}//!
   // // 	public function testuser_ChangePassword(){}//!
   // // 	public function testuser_ChangePasswordWithKey(){}//!
   // // 	public function testsetAdditionalAttempts(){}//!
   // // 	public function testremoveAdditionalAttempts(){}//!
   // // 	public function testlogClientError(){}
   // // 	public function testauth_getLoginOptions(){}
   //
	public function testGetAllLatestLODrafts()
	{
		$this->MAS = new nm_los_Master();
		@$this->assertEquals(new \obo\util\Error(1), $this->MAS->getAllLatestLODrafts());

		@$this->assertTrue($this->MAS->login(self::ADMIN_USER, md5(self::ADMIN_PW)));
		@$drafts = $this->MAS->getAllLatestLODrafts();
		$this->assertGreaterThan(0, $drafts );
		foreach($drafts AS &$draft)
		{
			//$this->doTestLO($draft->id, $draft);  // not all learning object conform
			$this->assertType('\obo\lo\LO', $draft);
		}
		$this->assertEquals(null, $this->MAS->logout());
	}
   //
   // 	public function testDeleteUserByID()
   // 	{
   // 		$error = new \obo\util\Error(2);
   //
   // 		$this->assertEquals($error, $this->MAS->deleteUserByID('dsafadsf'));
   // 		$this->assertEquals($error, $this->MAS->deleteUserByID(-1));
   // 		$this->assertEquals($error, $this->MAS->deleteUserByID(0));
   // 		//
   // 		// @$this->assertEquals(true, $this->MAS->login(self::ADMIN_USER, md5(self::ADMIN_PW)));
   // 		// TODO NEED a fake dataset to test this
   // 		//$this->assertEquals(null, $this->MAS->logout());
   // 	}

	protected function doTestLO($LOID, $lo, $testMeta=false, $testInstance=false)
	{

		$qTypes = array('MC', 'QA', 'Media');
		$pItemNames = array('TextArea', 'TextArea2', 'MediaView', 'MediaView1', 'MediaView2');
		$boolStringValues = array('1', '0');
		$bool = array(true, false);

		$this->assertType('\obo\lo\LO', $lo);

		$this->assertObjectHasAttribute('vers_whole', $lo);
		$this->assertObjectHasAttribute('vers_part', $lo);
		$isMaster = ($lo->vers_whole > 0 && $lo->vers_part == 0);

		$this->assertObjectHasAttribute('id', $lo);
		$this->assertTrue(\obo\util\Validator::isPosInt($lo->id));
		$this->assertEquals($LOID, $lo->id);
		$this->assertObjectHasAttribute('title', $lo);
		if($isMaster) $this->assertTrue(\obo\util\Validator::isString($lo->title));
		$this->assertObjectHasAttribute('auth', $lo);
		$this->assertObjectHasAttribute('lang', $lo);
		$this->assertTrue(\obo\util\Validator::isPosInt($lo->lang));
		$this->assertObjectHasAttribute('desc', $lo);
		if(!$testMeta) $this->assertObjectHasAttribute('id', $lo->desc);
		else $this->assertFalse(isset($lo->desc->id));
		if(!$testMeta) $this->assertTrue(\obo\util\Validator::isPosInt($lo->desc->id));
		$this->assertObjectHasAttribute('text', $lo->desc);
		if($isMaster) $this->assertTrue(\obo\util\Validator::isString($lo->desc->text));
		$this->assertObjectHasAttribute('objective', $lo);
		if(!$testMeta) $this->assertObjectHasAttribute('id', $lo->objective);
		else $this->assertFalse(isset($lo->objective->id));
		$this->assertObjectHasAttribute('text', $lo->objective);
		if(!$testMeta) $this->assertTrue(\obo\util\Validator::isPosInt($lo->objective->id));
		if($isMaster) $this->assertTrue(\obo\util\Validator::isString($lo->objective->text));
		$this->assertObjectHasAttribute('learn_time', $lo);

		//$this->assertGreaterThanOrEqual(0, $lo->learn_time);
		$this->assertTrue(\obo\util\Validator::isInt($lo->learn_time));
		$this->assertTrue(\obo\util\Validator::isInt($lo->vers_part));
		$this->assertTrue(\obo\util\Validator::isInt($lo->vers_whole));

		$this->assertObjectHasAttribute('root', $lo);
		$this->assertTrue(\obo\util\Validator::isPosInt($lo->root));
		$this->assertObjectHasAttribute('parent', $lo);
		$this->assertTrue(\obo\util\Validator::isInt($lo->parent));
		// master objects root = self, parent = self only if it is a 1.0, if it is a 2.0 parent points to the 1.0
		if($isMaster)
		{
			$this->assertEquals($lo->id, $lo->root);
			if($lo->vers_whole == 1)
			{
				$this->assertEquals($lo->id, $lo->parent);
			}
			else
			{
				$this->assertFalse($lo->id == $lo->parent);
			}

		}
		$this->assertObjectHasAttribute('datec', $lo);
		$this->assertTrue(\obo\util\Validator::isPosInt($lo->datec));
		$this->assertObjectHasAttribute('copyright', $lo);
		$this->assertObjectHasAttribute('pages', $lo);
		if(!$testMeta)
		{
			$this->assertGreaterThan(0, count($lo->pages));
			// loop through pages
			foreach($lo->pages AS &$page)
			{
				$this->assertObjectHasAttribute('id', $page);
				$this->assertTrue(\obo\util\Validator::isPosInt($page->id));
				$this->assertObjectHasAttribute('title', $page);
				if($isMaster) $this->assertTrue(\obo\util\Validator::isString($page->title));
				$this->assertObjectHasAttribute('author', $page);
				$this->assertTrue(\obo\util\Validator::isInt($page->author));
				$this->assertObjectHasAttribute('layout', $page);
				$this->assertTrue(\obo\util\Validator::isPosInt($page->layout));
				$this->assertObjectHasAttribute('datec', $page);
				$this->assertTrue(\obo\util\Validator::isPosInt($page->datec));
				$this->assertObjectHasAttribute('items', $page);
				$this->assertGreaterThan(0, count($page->items));
				// loop through page items
				foreach($page->items as &$item)
				{
					$this->assertObjectHasAttribute('id', $item);
					$this->assertTrue(\obo\util\Validator::isPosInt($item->id));
					$this->assertObjectHasAttribute('name', $item);
					$this->assertTrue(\obo\util\Validator::isString($item->name));
					$this->assertContains($item->name, $pItemNames);
					$this->assertObjectHasAttribute('layoutItemID', $item);
					$this->assertTrue(\obo\util\Validator::isPosInt($item->layoutItemID));
					$this->assertObjectHasAttribute('cdata', $item);
					$this->assertObjectHasAttribute('media', $item);
				}
				$this->assertObjectHasAttribute('q_id', $page);
			}
			$this->assertObjectHasAttribute('pgroup', $lo);
			$this->assertObjectHasAttribute('id', $lo->pgroup);
			$this->assertObjectHasAttribute('author', $lo->pgroup);
			$this->assertObjectHasAttribute('is_master', $lo->pgroup);
			$this->assertContains($lo->pgroup->is_master, array('0', '1'));
			$this->assertObjectHasAttribute('rand', $lo->pgroup);
			$this->assertContains($lo->pgroup->rand, array('0', '1'));
			$this->assertObjectHasAttribute('alts', $lo->pgroup);
			$this->assertObjectHasAttribute('alt_type', $lo->pgroup);
			$this->assertContains($lo->pgroup->alt_type, array('r', 'k'));
			$this->assertObjectHasAttribute('kids', $lo->pgroup);
			$this->assertGreaterThan(0, count($lo->pgroup->kids));
			// practice questions
			foreach($lo->pgroup->kids AS &$pQ)
			{
				$this->assertObjectHasAttribute('id', $pQ);
				$this->assertTrue(\obo\util\Validator::isPosInt($pQ->id));
				$this->assertObjectHasAttribute('author', $pQ);
				$this->assertTrue(\obo\util\Validator::isPosInt($pQ->author));
				$this->assertObjectHasAttribute('type', $pQ);
				$this->assertContains($pQ->type, $qTypes);
				$this->assertObjectHasAttribute('answers', $pQ);
				$this->assertGreaterThan(0, count($pQ->answers));
				$this->assertObjectHasAttribute('perms', $pQ);
				$this->assertObjectHasAttribute('items', $pQ);
				$this->assertGreaterThan(0, count($pQ->items));
				// question page items
				foreach($pQ->items as $pQItem)
				{
					$this->assertObjectHasAttribute('id', $pQItem);
					$this->assertTrue(\obo\util\Validator::isPosInt($pQItem->id));
					$this->assertObjectHasAttribute('name', $pQItem);
					$this->assertContains($item->name, $pItemNames);
					$this->assertTrue(\obo\util\Validator::isString($pQItem->name));
					$this->assertObjectHasAttribute('layout_item_id', $pQItem);
					$this->assertObjectHasAttribute('cdata', $pQItem);
					if($pQItem->layout_item_id == 'MediaView')
					{
						$this->assertObjectHasAttribute('media', $pQItem);
						$this->assertGreaterThan(0, count($pQItem->media));
						// media items
						foreach($pQItem->media as $media)
						{
							$this->assertObjectHasAttribute('id', $media);
							$this->assertTrue(\obo\util\Validator::isPosInt($media->id));
							$this->assertObjectHasAttribute('author', $media);
							$this->assertTrue(\obo\util\Validator::isPosInt($media->author));
							$this->assertObjectHasAttribute('title', $media);
							$this->assertObjectHasAttribute('type', $media);
							$this->assertObjectHasAttribute('desc', $media);
							$this->assertObjectHasAttribute('url', $media);
							$this->assertObjectHasAttribute('date_created', $media);
							$this->assertTrue(\obo\util\Validator::isPosInt($media->date_created));
							$this->assertObjectHasAttribute('copyright', $media);
							$this->assertObjectHasAttribute('height', $media);
							$this->assertObjectHasAttribute('width', $media);
							$this->assertObjectHasAttribute('length', $media);
							$this->assertObjectHasAttribute('version', $media);
						}
					}
				}
				$this->assertObjectHasAttribute('alt_group', $pQ);
				$this->assertObjectHasAttribute('feedback', $pQ);
				$this->assertArrayHasKey('correct', $pQ->feedback);
				$this->assertArrayHasKey('incorrect', $pQ->feedback);
			}
			$this->assertObjectHasAttribute('quizSize', $lo->pgroup);
			$this->assertObjectHasAttribute('agroup', $lo);
			$this->assertObjectHasAttribute('id', $lo->agroup);
			$this->assertObjectHasAttribute('author', $lo->agroup);
			$this->assertObjectHasAttribute('is_master', $lo->agroup);
			$this->assertContains($lo->agroup->is_master, array('0', '1'));
			$this->assertObjectHasAttribute('rand', $lo->agroup);
			$this->assertContains($lo->agroup->rand, array('0', '1'));
			$this->assertObjectHasAttribute('alts', $lo->agroup);
			$this->assertObjectHasAttribute('alt_type', $lo->agroup);
			$this->assertContains($lo->agroup->alt_type, array('r', 'k'));
			$this->assertObjectHasAttribute('kids', $lo->agroup);
			$this->assertGreaterThan(0, count($lo->agroup->kids));

			// Questions
			foreach($lo->agroup->kids AS &$pQ)
			{
				$this->assertObjectHasAttribute('id', $pQ);
				$this->assertTrue(\obo\util\Validator::isPosInt($pQ->id));
				$this->assertObjectHasAttribute('author', $pQ);
				$this->assertTrue(\obo\util\Validator::isPosInt($pQ->author));
				$this->assertObjectHasAttribute('type', $pQ);
				$this->assertContains($pQ->type, $qTypes);
				$this->assertObjectHasAttribute('answers', $pQ);
				$this->assertGreaterThan(0, count($pQ->answers));
				$this->assertObjectHasAttribute('perms', $pQ);
				$this->assertObjectHasAttribute('items', $pQ);
				$this->assertGreaterThan(0, count($pQ->items));
				// Items
				foreach($pQ->items as $pQItem)
				{
					$this->assertObjectHasAttribute('id', $pQItem);
					$this->assertTrue(\obo\util\Validator::isPosInt($pQItem->id));
					$this->assertObjectHasAttribute('name', $pQItem);
					$this->assertContains($item->name, $pItemNames);
					$this->assertTrue(\obo\util\Validator::isString($pQItem->name));
					$this->assertObjectHasAttribute('layout_item_id', $pQItem);
					$this->assertObjectHasAttribute('cdata', $pQItem);
					if($pQItem->layout_item_id == 'MediaView')
					{
						$this->assertObjectHasAttribute('media', $pQItem);
						$this->assertGreaterThan(0, count($pQItem->media));
						// Media Objects
						foreach($pQItem->media as $media)
						{
							$this->assertObjectHasAttribute('id', $media);
							$this->assertTrue(\obo\util\Validator::isPosInt($media->id));
							$this->assertObjectHasAttribute('author', $media);
							$this->assertTrue(\obo\util\Validator::isPosInt($media->author));
							$this->assertObjectHasAttribute('title', $media);
							$this->assertObjectHasAttribute('type', $media);
							$this->assertObjectHasAttribute('desc', $media);
							$this->assertObjectHasAttribute('url', $media);
							$this->assertObjectHasAttribute('date_created', $media);
							$this->assertTrue(\obo\util\Validator::isPosInt($media->date_created));
							$this->assertObjectHasAttribute('copyright', $media);
							$this->assertObjectHasAttribute('height', $media);
							$this->assertObjectHasAttribute('width', $media);
							$this->assertObjectHasAttribute('length', $media);
							$this->assertObjectHasAttribute('version', $media);
						}
					}
				}
				$this->assertObjectHasAttribute('alt_group', $pQ);
				$this->assertObjectHasAttribute('feedback', $pQ);
				$this->assertArrayHasKey('correct', $pQ->feedback);
				$this->assertArrayHasKey('incorrect', $pQ->feedback);
			}
			$this->assertObjectHasAttribute('quizSize', $lo->agroup);

			$this->assertObjectHasAttribute('layouts', $lo);
			//$this->assertEquals(7, count($lo->layouts));
			// Layouts

			foreach($lo->layouts AS $layout)
			{
				$this->assertObjectHasAttribute('id', $layout);
				$this->assertTrue(\obo\util\Validator::isPosInt($layout->id));
				$this->assertObjectHasAttribute('name', $layout);
				$this->assertObjectHasAttribute('thumb', $layout);
				$this->assertObjectHasAttribute('items', $layout);
				$this->assertObjectHasAttribute('tags', $layout);
			}
		}
		else
		{
			$this->assertFalse(isset($lo->pages));
			$this->assertFalse(isset($lo->agroup));
			$this->assertFalse(isset($lo->pgroup));
			$this->assertFalse(isset($lo->layouts));
		}
		$this->assertObjectHasAttribute('keywords', $lo);
		$this->assertTrue(is_array($lo->keywords));
		$this->assertObjectHasAttribute('perms', $lo);
		$this->assertGreaterThan(0, count($lo->perms));
		//$this->assertObjectHasAttribute('user_id', $lo->perms);// not sure why this is missing sometimes?
		$this->assertObjectHasAttribute('read', $lo->perms);
		//$this->assertContains($lo->perms->read, $boolStringValues); // TODO: put these tests back in
		$this->assertObjectHasAttribute('write', $lo->perms);
		//$this->assertContains($lo->perms->write, $boolStringValues);
		$this->assertObjectHasAttribute('copy', $lo->perms);
		//$this->assertContains($lo->perms->copy, $boolStringValues);
		$this->assertObjectHasAttribute('use', $lo->perms);
		//$this->assertContains($lo->perms->use, $boolStringValues);
		$this->assertObjectHasAttribute('give_read', $lo->perms);
		//$this->assertContains($lo->perms->give_read, $boolStringValues);
		$this->assertObjectHasAttribute('give_write', $lo->perms);
		//$this->assertContains($lo->perms->give_write, $boolStringValues);
		$this->assertObjectHasAttribute('give_copy', $lo->perms);
		//$this->assertContains($lo->perms->give_copy, $boolStringValues);
		$this->assertObjectHasAttribute('give_use', $lo->perms);
		//$this->assertContains($lo->perms->give_use, $boolStringValues);
		$this->assertObjectHasAttribute('give_global', $lo->perms);
		//$this->assertContains($lo->perms->give_global, $boolStringValues);
		$this->assertObjectHasAttribute('allow_req', $lo->perms);
		//$this->assertContains($lo->perms->allow_req, $boolStringValues);
		$this->assertObjectHasAttribute('summary', $lo);
		$this->assertArrayHasKey('content_size', $lo->summary);
		if(!$testMeta) $this->assertEquals(count($lo->pages), $lo->summary['content_size']);
		$this->assertTrue(\obo\util\Validator::isPosInt($lo->summary['content_size']));
		$this->assertArrayHasKey('practice_size', $lo->summary);
		if(!$testMeta) $this->assertEquals(count($lo->pgroup->kids), $lo->summary['practice_size']);
		$this->assertTrue(\obo\util\Validator::isPosInt($lo->summary['practice_size']));
		$this->assertArrayHasKey('assessment_size', $lo->summary);
		$this->assertTrue(\obo\util\Validator::isPosInt($lo->summary['assessment_size']));
	}

	protected function makeBasicLO()
	{
		$lo = array();
		$lo['pgroup'] = array(
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
		$lo['agroup'] = array(
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
