<?php
namespace rocketD\auth;
class ModTestUser extends AuthModule {

	protected static $instance;
	const USERNAME = '*teststudent*';
	const CAN_CHANGE_PW = false; // override this!

	static public function getInstance()
	{
		if(!isset(self::$instance))
		{
			$selfClass = __CLASS__;
			self::$instance = new $selfClass();
		}
		return self::$instance;
	}

	public function createNewUser($userName, $fName, $lName, $mName, $email, $optionalVars=0)
	{
		return false;
	}

	// security check: Ian Turgeon 2008-05-08 - PASS
	public function checkRegisterPossible($userName, $fName, $lName, $mName, $email, $optionalVars=0){
		// validate username
		$validUsername = $this->validateUsername($userName);
		if($validUsername !== true) return 'Invalid user name';
		if(!$this->validateFirstName($fName)) return 'Invalid first name';
		if(!$this->validateLastName($lName)) return 'Invalid last name';
		if(!$this->validateEmail($email)) return 'Invalid email address';
		if($this->getUIDforUsername($userName) !== false) return 'UserName not available.';
		return true;
	}

	// security check: Ian Turgeon 2008-05-08 - PASS
	public function updateUser($userID, $userName, $fName, $lName, $mName, $email, $optionalVars=0)
	{
		return array('success' => false, 'error' => 'Unable to locate user.');
	}

	public function authenticate($requestVars)
	{
		if(!isset($requestVars['isTestUser']) || $requestVars['isTestUser'] !== true) return false;

		if($userID = $this->getUIDforUsername(self::USERNAME))
		{
			$user = $this->fetchUserByID($userID);
		}
		else
		{
			$created = $this->_createTestUser();
			$user = $this->fetchUserByID($this->getUIDforUsername(self::USERNAME));
		}

		if ($user instanceof rocketD\auth\User)
		{
			// login
			$this->storeLogin($user->userID);
			$this->internalUser = $user;
			$_SESSION['isTestUser'] = true;
			return true;
		}
		else
		{
			return false;
		}
	}

	public function verifyPassword($user, $password)
	{
		return false;
	}

	protected function _createTestUser()
	{
		$valid = $this->checkRegisterPossible(self::USERNAME, 'Test', 'Student', 'E', 'test@test.com', array());
		if($valid === true)
		{
			$this->defaultDBM();
			if(!$this->DBM->connected) return false;
			$this->DBM->startTransaction();
			$result = parent::createNewUser(self::USERNAME, 'Test', 'Student', 'E', 'test@test.com', array());
			if($result['success'] == true)
			{
				$success = $this->DBM->querySafe("UPDATE ".\cfg_core_User::TABLE." SET ".\cfg_core_User::AUTH_MODULE." = '?' WHERE ".\cfg_core_User::ID." = '?' ", get_class($this), $result['userID']);
				if(!$success)
				{
					$this->DBM->rollBack();
				}
				$this->DBM->commit();
				return $success;
			}
			else
			{
				$this->DBM->rollBack();
				trace($result, true);
				return $result;
			}
		}
		return false;
	}

	// security check: Ian Turgeon 2008-05-08 - PASS
	protected function addRecord($userID, $userName, $password)
	{
		return false;
	}

	// security check: Ian Turgeon 2008-05-07 - PASS
	public function updateRecord($userID, $userName, $password)
	{
		return false;
	}

	// security check: Ian Turgeon 2008-05-06 - PASS
	public function validateUsername($username)
	{
		return $username == self::USERNAME;
	}

	// security check: Ian Turgeon 2008-05-06 - PASS
	public function validatePassword($pass)
	{
		return false;
	}


	public function isPasswordCurrent($userID)
	{
		return true;
	}
}
