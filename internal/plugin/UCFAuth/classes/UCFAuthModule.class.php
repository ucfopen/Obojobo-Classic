<?php

class plg_UCFAuth_UCFAuthModule extends \rocketD\auth\AuthModule
{
	use \rocketD\Singleton;

	public function fetchUserByID($userID = 0)
	{
		$user = parent::fetchUserByID($userID);
		if ($user) $user->ucfID = $this->getMetaField($user->userID, 'ucfID');

		return $user;
	}

	public function createNewUser($userName, $fName, $lName, $mName, $email, $optionalVars=[])
	{
		$userID = null;
		$error = null;

		$optionalVars['MD5Pass'] = $this->createSalt(); // create a random password that is unguessable
		$success = $this->checkRegisterPossible($userName, $fName, $lName, $mName, $email, $optionalVars);

		$this->defaultDBM();
		$this->DBM->startTransaction();

		// create user
		if ($success === true)
		{
			$res = parent::createNewUser($userName, $fName, $lName, $mName, $email, $optionalVars);
			$success = $res['success'];
			$userID = $res['userID'];
		}

		// update with ucf data
		if ($success === true)
		{
			$success = $this->addRecord($userID, $userName, $optionalVars['MD5Pass']);
		}

		// set metadata for ucfid
		if ($success === true && ! empty($optionalVars['ucfID']))
		{
			$success = $this->setMetaField($userID, 'ucfID', $optionalVars['ucfID']);
		}

		// we good?
		if ($success === true)
		{
			$this->DBM->commit();
		}
		else
		{
			$this->DBM->rollBack();
			trace($success, true);
			$error = 'Unable to create user.';
		}

		return array('success' => $success, 'userID' => $userID, 'error' => $error);
	}

	public function checkRegisterPossible($userName, $fName, $lName, $mName, $email, $optionalVars=0)
	{
		$validUsername = $this->validateUsername($userName);
		if($validUsername !== true)
		{
			trace($validUsername, true);
			return $validUsername;
		}
		if(!$this->validateFirstName($fName))
		{
			trace('Invalid first name', true);
			return 'Invalid first name';
		}
		if(!$this->validateLastName($lName))
		{
			trace('Invalid last name', true);
			return 'Invalid last name';
		}
		if(!$this->validateEmail($email))
		{
			trace('Invalid email address', true);
			return 'Invalid email address';
		}
		// registration requires a password
		$vPass = $this->validatePassword($optionalVars['MD5Pass']);
		if($vPass !== true)
		{
			return $vPass;
		}

		$this->defaultDBM();
		if(!$this->DBM->connected)
		{
			trace('not connected', true);
			return false;
		}
		// check for exising username
		$q = $this->DBM->querySafe("SELECT * FROM ".\cfg_core_User::TABLE." WHERE ".\cfg_core_User::LOGIN."='?' LIMIT 1", $userName);
		if($this->DBM->fetch_num($q) > 0 )
		{
			trace('username already exists', true);
			return false;
		}

		return true;
	}

	public function updateUser($userID, $userName, $fName, $lName, $mName, $email, $optionalVars=0)
	{
		// validate arguments
		if(!$this->validateUID($userID))
		{
			return array('success' => false, 'error' => 'Invalid User Id.');
		}

		$this->defaultDBM();
		if(!$this->DBM->connected)
		{
			trace('not connected', true);
			array('success' => false, 'error' => 'Database connection error.');
		}

		$user = $this->fetchUserByID($userID);
		if($user != false)
		{
			$this->DBM->startTransaction();
			$result = parent::updateUser($userID, $userName, $fName, $lName, $mName, $email, $optionalVars);
			if($result['success']==true)
			{
				// update with md5 pass
				if(!$this->updateRecord($userID, $userName, false))
				{
					$this->DBM->rollBack();
					trace('Unable to update user.', true);
					return array('success' => false, 'error' => 'Unable to update user.');
				}

				// Update the ucfID if it's set
				if(isset($optionalVars['ucfID']))
				{
					if(!$this->setMetaField($userID, 'ucfID', $optionalVars['ucfID']))
					{
						$this->DBM->rollBack();
						trace('Unable to update user.', true);
						return array('success' => false, 'error' => 'Unable to update user.');
					}
				}

				$this->DBM->commit();
				return array('success' => true);
			}
			$this->DBM->rollBack();
			trace('Unable to update user.', true);
			return array('success' => false, 'error' => 'Unable to update user.');
		}
		trace('Unable to locate user.', true);
		return array('success' => false, 'error' => 'Unable to locate user.');
	}

	/**
	 * Authenticates the user.  This module checks an external data source, and will create internal users based on the external data if they don't already exist
	 * Parent doc: Main Authentication function. This function will verify the user's crudentials and log them in. Must be extended to return a nm_los_User upon success, and false on failure.
	 *
	 * @return true/false
	 * @author Ian Turgeon
	 **/
	public function authenticate($requestVars)
	{
		$success = false;

		// filter the username to be all lowercase
		$username = strtolower($requestVars['userName']);

		if ($this->validateUsername($username) !== true) return false;
		if ($this->validatePassword($requestVars['password']) !== true) return false;

		// search external database
		// must be before fetch below
		$this->syncExternalUser($username);

		$user = $this->fetchUserByLogin($username);

		if ($user instanceof \rocketD\auth\User)
		{
			$success = $this->verifyPassword($user->login, $requestVars['password']);

			if ($success) $this->storeLogin($user);
		}

		\rocketD\util\Log::profile('login', "'{$username}','ucf-nid','0','".time().",'".($success?'1':'0')."'");
		return $success;
	}

	public function verifyPassword($userName, $password)
	{
		// for local testing, ldap access may not be possible, if in local test mode just return an ok
		if(\AppCfg::UCF_AUTH_BYPASS_PASSWORDS === true)
		{
			trace('WARNING LOCAL AUTHENTICATION TEST MODE ENABLED', true);
			return true;
		}

		return $this->bindLDAP($userName, $password);
	}

	protected function bindLDAP($username, $password)
	{
		$success = false;
		try
		{
			$ldap = ldap_connect(\AppCfg::UCF_LDAP);
			$rdn = "cn={$username},ou=people,dc=net,dc=ucf,dc=edu";
			ldap_set_option($ldap , LDAP_OPT_PROTOCOL_VERSION, 3);
			$success = @ldap_bind($ldap, $rdn, $password); // true if LDAP says password and username match
		}
		catch(Exception $e)
		{
			\obo\util\NotificationManager::getInstance()->sendCriticalError('LDAP Error', $e->getMessage());
			trace('ldap threw and exception', true);
			trace($e, true);
		}

		return $success === true;
	}

	protected function addRecord($userID, $userName, $password)
	{
		if(!$this->validateUID($userID)) return false;
		if($this->validateUsername($userName) !== true) return false;

		$this->defaultDBM();
		if(!$this->DBM->connected)
		{
			trace('not connected', true);
			return false;
		}
		// save the NID and set the auth_module to this one
		return (bool) $this->DBM->querySafe("UPDATE ".\cfg_core_User::TABLE." SET ".\cfg_core_User::LOGIN." = '?', ".\cfg_core_User::AUTH_MODULE." = '?' WHERE ".\cfg_core_User::ID." = '?' ", $userName, get_class($this), $userID);
	}

	public function updateRecord($userID, $userName, $password)
	{
		if(!$this->validateUID($userID)) return false;

		$this->defaultDBM();
		if(!$this->DBM->connected)
		{
			trace('not connected', true);
			return false;
		}

		// update login
		if($this->validateUsername($userName) === true)
		{
			\rocketD\util\Cache::getInstance()->clearUserByID($userID);

			// update username
			 return $this->DBM->querySafe("UPDATE ".\cfg_core_User::TABLE." SET ".\cfg_core_User::LOGIN." = '?' WHERE ".\cfg_core_User::ID." = '?' ", $userName, $userID);

		}
		return true;

	}

	public function validateUsername($userName)
	{
		// make sure the string length is less then 255, our usernames aren't that long
		if(strlen($userName) > \cfg_plugin_AuthModUCF::MAX_USERNAME_LENGTH)
		{
			return false;
		}
		// make sure the username is atleast 2 characters
		if(strlen($userName) < \cfg_plugin_AuthModUCF::MIN_USERNAME_LENGTH)
		{
			return false;
		}
		if(empty($userName))
		{
			return false;
		}
		if(preg_match("/^[[:alnum:]_]{".\cfg_plugin_AuthModUCF::MIN_USERNAME_LENGTH.",".\cfg_plugin_AuthModUCF::MAX_USERNAME_LENGTH."}$/i", $userName) == false)
		{
			return false;
		}
		return true;
	}

	public function validatePassword($pass)
	{
		if(empty($pass))
		{
			trace('password is empty');
			return 'Password is an empty string';
		}
		if(\obo\util\Validator::isMD5($pass) && $pass == 'd41d8cd98f00b204e9800998ecf8427e')
		{
			trace('md5 password is empty');
			return 'Password is an empty string';
		}
		return true;
	}

	public function syncExternalUser($userName)
	{
		if($this->validateUsername($userName) !== true) return false;

		// Ask UCF for this user
		// note* if they don't exist, they don't have access!
		if($eUser = $this->getUCFUserData($userName))
		{
			// UPDATE EXISTING
			if($user = $this->fetchUserByLogin($userName))
			{
				$ucfID = $this->getMetaField($user->userID, 'ucfID');
				if($eUser->first != $user->first || $eUser->last != $user->last || $eUser->email != $user->email || $ucfID != $eUser->ucfID)
				{
					$user->first = $eUser->first;
					$user->last  = $eUser->last;
					$user->email = $eUser->email;
					$this->updateUser($user->userID, $userName, $user->first, $user->last, '', $user->email, array('ucfID' => $eUser->ucfID));
				}
			}
			// CREATE NEW
			else
			{
				$this->createNewUser($userName, $eUser->first, $eUser->last, '', $eUser->email, array('ucfID' => $eUser->ucfID));
				$user = $this->fetchUserByLogin($userName);
			}
		}

		if ($user instanceof \rocketD\auth\User)
		{
			$this->updateRole($user->userID, $eUser->isCreator);
			return true;
		}

		return false;
	}

	protected function getUcfDb()
	{
		static $ucfDB;
		if ( ! isset($ucfDB) || ! $ucfDB->connected)
		{
			$con = new \rocketD\db\DBConnectData(\AppCfg::UCF_DB_HOST, \AppCfg::UCF_DB_USER, \AppCfg::UCF_DB_PASS, \AppCfg::UCF_DB_NAME, \AppCfg::UCF_DB_TYPE);
			$ucfDB = \rocketD\db\DBManager::getConnection($con);
		}
		return $ucfDB;
	}

	protected function getUCFUserData($username)
	{
		$ucfDB = $this->getUcfDb();
		if ( ! $ucfDB->connected)
		{
			trace('not connected', true);
			return false;
		}

		$userTable = \cfg_plugin_AuthModUCF::TABLE_PEOPLE;
		$userId    = \cfg_plugin_AuthModUCF::NID;
		$ucfID     = \cfg_plugin_AuthModUCF::PPS_NUMBER;
		$first     = \cfg_plugin_AuthModUCF::FIRST;
		$last      = \cfg_plugin_AuthModUCF::LAST;
		$email     = \cfg_plugin_AuthModUCF::EMAIL;
		$isStaff   = \cfg_plugin_AuthModUCF::IS_STAFF;
		$isStudent = \cfg_plugin_AuthModUCF::IS_STUDENT;

		$q = $ucfDB->querySafe("SELECT * FROM {$userTable} WHERE {$userId} = '?' ", $username);

		if ( !($result = $ucfDB->fetch_array($q)))
		{
			\rocketD\util\Log::profile('login', "'".$username."','not_in_external_db','0','".time().",'0'");
			return false;
		}

		$result = $this->trimArray($result);

		// provide a default email if needed
		if ( ! isset($result[$email]))
		{
			trace("$username has no email from cerebro, using default", true);
			$result[$email] = "$username@ucf.edu";
		}

		// Build a standardized result
		$user = (object) [
			'ucfID'     => $result[$ucfID],
			'first'     => $result[$first],
			'last'      => $result[$last],
			'email'     => $result[$email],
			'isCreator' => ((int) $result[$isStaff] === 1),
		];

		return $user;
	}

	protected function trimArray($array)
	{
		if(count($array) > 0 )
		{
			foreach($array as $value)
			{
				$value = trim($value);
			}
		}
		return $array;
	}

	public function updateRole($UIDorUser, $isLibraryUser = 0)
	{
		// if UIDorUser is a UID
		if (\obo\util\Validator::isPosInt($UIDorUser))
		{
			$user = $this->fetchUserByID($UIDorUser);
		}
		else
		{
			$user = $UIDorUser;
		}

		if ( ! ($user instanceof \rocketD\auth\User)) return false;

		// grab our user first to see if overrrideRoll has been set to 1
		// override hasnt been engaged, let external db dictate the role
		if (isset($user->overrideRole) && $user->overrideRole == '1') return true;

		$RM  = \rocketD\perms\RoleManager::getInstance();
		// GIVE ROLES
		if ($isLibraryUser)
		{
			$RM->addUsersToRoles_SystemOnly([$user->userID], [\cfg_core_Role::EMPLOYEE_ROLE, \cfg_obo_Role::CONTENT_CREATOR]);
			return true;
		}
		// REMOVE ROLES
		else
		{
			if ($RM->doesUserHaveARole(array(\cfg_core_Role::EMPLOYEE_ROLE), $user->userID))
			{
				$RM->removeUsersFromRoles_SystemOnly(array($user->userID), array(\cfg_core_Role::EMPLOYEE_ROLE));
			}

			if ($RM->doesUserHaveARole(array(\cfg_obo_Role::CONTENT_CREATOR), $user->userID))
			{
				$RM->removeUsersFromRoles_SystemOnly(array($user->userID), array(\cfg_obo_Role::CONTENT_CREATOR));
			}
			return true;
		}
	}

	public function isPasswordCurrent($userID)
	{
		return $this->validateUID($userID);
	}

	public function dbSetPassword($userID, $newPassword) { return false; }
	public function requestPasswordReset($userName, $email, $returnURL) { return false; }
	protected function sendPasswordResetEmail($sendTo, $returnURL, $resetKey) { return false; }
	public function changePasswordWithKey($userName, $key, $newpass) { return false; }
}
