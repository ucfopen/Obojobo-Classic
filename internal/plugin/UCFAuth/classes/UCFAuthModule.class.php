<?php
class plg_UCFAuth_UCFAuthModule extends \rocketD\auth\AuthModule
{

	protected static $instance;

	static public function getInstance()
	{
		if(!isset(self::$instance))
		{
			$selfClass = __CLASS__;
			self::$instance = new $selfClass();
		}
		return self::$instance;
	}

	public function getAllUsers()
	{
		return parent::getAllUsers();
	}

	public function recordExistsForID($userID=0)
	{
		return parent::recordExistsForID($userID);
	}

	public function createNewUser($userName, $fName, $lName, $mName, $email, $optionalVars=[])
	{
		$optionalVars['MD5Pass'] = $this->createSalt(); // create a random password that is unguessable
		$valid = $this->checkRegisterPossible($userName, $fName, $lName, $mName, $email, $optionalVars);
		if($valid === true)
		{
			$this->defaultDBM();
			$this->DBM->startTransaction();
			$result = parent::createNewUser($userName, $fName, $lName, $mName, $email, $optionalVars);
			if($result['success'] === true)
			{
				if(!$this->addRecord($result['userID'], $userName, $optionalVars['MD5Pass']))
				{
					$this->DBM->rollBack();
					return array('success' => false, 'error' => 'Unable to create user.');
				}

				$this->DBM->commit();
				return array('success' => true, 'userID' => $result['userID']);
			}
			else
			{
				$this->DBM->rollBack();
				trace($result, true);
				return $result;
			}
		}
		else{
			trace($valid, true);
			return array('success' => false, 'error' => $valid);
		}
	}

	public function checkRegisterPossible($userName, $fName, $lName, $mName, $email, $optionalVars=[])
	{
		$validUsername = $this->validateUsername($userName);
		if($validUsername !== true){
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

	public function getUIDforUsername($userName)
	{
		return parent::getUIDforUsername($userName);
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
		$validSSO = false; // flag to indicate a SSO authentication is assumed
		$weakExternalSync = false; // allows the external sync to fail in case the user isn't present there

		// filter the username to be all lowercase
		$requestVars['userName'] = strtolower($requestVars['userName']);

		// Portal SSO - session vars set in portal pagelet /sso/porta/orientation-academic-integrity.php ********************//
		if(empty($requestVars['userName']) && empty($requestVars['password']))
		{
			$this->checkForValidPortalSession($requestVars, $validSSO, $weakExternalSync);
		}
		// LTI SSO
		else
		{
			$this->checkForValidLTILogin($requestVars, $validSSO, $weakExternalSync);
		}

		if($this->validateUsername($requestVars['userName']) !== true) return false;
		if($validSSO == false && $this->validatePassword($requestVars['password']) !== true) return false;

		// create/update the user with the external database
		$user = $this->syncExternalUser($requestVars['userName'], $weakExternalSync);

		if($user instanceof \rocketD\auth\User)
		{
			// if the user is not signed in by SSO, authenticate using WebService/LDAP
			if($validSSO != true)
			{
				$checkPassword = $this->verifyPassword($user->login, $requestVars['password']);
			}

			// if they are valid, allow them in
			if($validSSO === true || $checkPassword['success'])
			{
				$this->storeLogin($user->userID);
				$this->internalUser = $user;
				return true;
			}
		}
		else
		{
			\rocketD\util\Log::profile('login', "'".$requestVars['userName']."','external_sync_fail','0','".time().",'0'");
		}
		return false;
	}

	protected function checkForValidLTILogin(&$requestVars, &$validSSO, &$weakExternalSync)
	{
		// handle an LTI SSO authentication request
		if(!empty($requestVars['userName']) && !empty($requestVars['validLti']))
		{
			if(!empty($requestVars['createIfMissing']) && $requestVars['createIfMissing'] === true)
			{
				$weakExternalSync = true;
			}
			$validSSO = true;
			\rocketD\util\Log::profile('login', "'".$requestVars['userName']."','LTI','0','".time().",'1'");
		}
	}

	protected function checkForValidPortalSession(&$requestVars, &$validSSO, &$weakExternalSync)
	{
		if(isset($_SESSION['PORTAL_SSO_NID']) && isset($_SESSION['PORTAL_SSO_EPOCH']) && $_SESSION['PORTAL_SSO_EPOCH'] >= time() - 1800)
		{
			$requestVars['userName'] = $_SESSION['PORTAL_SSO_NID'];
			// logged in, clear the session variables
			unset( $_SESSION['PORTAL_SSO_NID'],  $_SESSION['PORTAL_SSO_EPOCH'] );
			$validSSO = true;
			$weakExternalSync = true; // allow the user to not exist in external db
			\rocketD\util\Log::profile('login', "'".$requestVars['userName']."','PortalSSO','0','".time().",'1'");
		}
	}

	public function verifyPassword($userName, $password)
	{
		// for local testing, ldap access may not be possible, if in local test mode just return an ok
		if(\AppCfg::UCF_AUTH_BYPASS_PASSWORDS)
		{
			trace('WARNING LOCAL AUTHENTICATION TEST MODE ENABLED', true);
			return array('success' => true, 'code' => '');
		}

		// make the LDAP request
		$success = false;
		$code = '';
		$time = microtime(true); // timer for LDAP call
		try
		{
			$ds = @ldap_connect(\AppCfg::LDAP);
			if (!$ds)
			{
				$NM = \obo\util\NotificationManager::getInstance();
				$NM->sendCriticalError('LDAP Connection Failure', 'Failed to connect to LDAP on ' . date("F j, Y, g:i a"));
				trace('connecting to ldap failed', true);
			}
			else
			{
				ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
				$success = @ldap_bind($ds, "cn=".$userName.",ou=people,dc=net,dc=ucf,dc=edu", $password); // true if LDAP verifies
				\rocketD\util\Log::profile('login', "'$userName','LDAP','".round((microtime(true) - $time),5)."','".time().",'".($success?'1':'0')."'");
			}
		}
		catch(Exception $e)
		{
			$NM = \obo\util\NotificationManager::getInstance();
			$NM->sendCriticalError('Oracle DB Connection Failure', 'LDAP Threw an Error ' . date("F j, Y, g:i a") . "\r\n" . print_r($e, true));
			trace('ldap threw and exception', true);
			trace($e);
		}

		return array('success' => $success, 'code' => $code);
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
			trace('User name maximum length is '.\cfg_plugin_AuthModUCF::MAX_USERNAME_LENGTH.' characters. ' . $userName, true);
			return 'User name maximum length is 20 characters.';
		}
		// make sure the username is atleast 2 characters
		if(strlen($userName) < \cfg_plugin_AuthModUCF::MIN_USERNAME_LENGTH)
		{
			trace('User name minimum length is '.\cfg_plugin_AuthModUCF::MIN_USERNAME_LENGTH.' characters. ' . $userName, true);
			return 'User name minimum length is 2 characters.';
		}
		if(empty($userName))
		{
			trace('Username is empty', true);
			return 'Username is empty';
		}
		if(preg_match("/^[[:alnum:]_]{".\cfg_plugin_AuthModUCF::MIN_USERNAME_LENGTH.",".\cfg_plugin_AuthModUCF::MAX_USERNAME_LENGTH."}$/i", $userName) == false)
		{
			trace('User name can only contain alpha numeric characters. ' . $userName, true);
			return 'User name can only contain alpha numeric characters.';
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

	public function syncExternalUser($userName, $allowWeakSync=false, $createIfMissing = false)
	{
		if($this->validateUsername($userName) !== true) return false;

		// look for user in external data
		if($eUser = $this->getUCFUserData($userName))
		{
			if($userID = $this->getUIDforUsername($eUser->ucfID))
			{
				// update internal record
				if($user = $this->fetchUserByID($userID))
				{
					// update user data changes
					if($eUser->first != $user->first || $eUser->last != $user->last || $eUser->email != $user->email){

						trace("updating user info:  {$user->first} = {$eUser->first}, {$user->last} = {$eUser->last}, {$user->email} = {$eUser->email}, {$ucfID} = {$eUser->ucfID}", true);

						$user->first = $eUser->first;
						$user->last  = $eUser->last;
						$user->email = $eUser->email;

						$this->updateUser($user->userID, $eUser->ucfID, $user->first, $user->last, '', $user->email);
					}
				}
				else
				{
					trace('fetchUserByID failed', true);
					return false;
				}
			}
			else
			{
				// create internal record
				$created = $this->createNewUser($eUser->ucfID, $eUser->first, $eUser->last, '', $eUser->email);
				if(!$created['success'])
				{
					trace('createNewUser Failed', true);
					return false;
				}
				else
				{
					trace("creating new internal user $userName" , true);
					// load user data from db
					if( !( $user = $this->fetchUserByID( $this->getUIDforUsername($eUser->ucfID) ) ) )
					{
						trace('fetchUserByID failed', true);
						return false;
					}
				}
			}
			// update roles
			$this->updateRole($user->userID, $eUser->isCreator);

			return $user;
		}

		// user didn't exist externally, however weakSync assumes SSO as authoritative, create a placeholder user account
		if($allowWeakSync === true)
		{
			// if the placeholder already exists
			//@TODO - UCF ID PHASE 2
			if($userID = $this->getUIDforUsername($userName))
			{
				// load user data from db
				if($user = $this->fetchUserByID($userID) )
				{
					return $user;
				}
				else
				{
					return false;
				}
			}
			// no placeholder, create
			else
			{
				// create internal placeholder record
				$created = $this->createNewUser($userName, '', '', '', '', array());
				if(!$created['success'])
				{
					trace('createNewUser Failed', true);
					return false;
				}
				else
				{
					trace('weak Sync used for '. $userName, true);
					// load user data from db
					//@TODO - UCF ID PHASE 2
					if( !( $user = $this->fetchUserByID( $this->getUIDforUsername($userName) ) ) )
					{
						trace('fetchUserByID failed', true);
						return false;
					}

					$this->setMetaField($user->userID, 'portal_sso', '1');

					return $user;
				}
			}
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

	public function updateRole($UIDorUser, $isLibraryUser=0)
	{
		$this->defaultDBM();
		if(!$this->DBM->connected)
		{
			trace('not connected', true);
			return false;
		}

		// if UIDorUser is a UID
		if(\obo\util\Validator::isPosInt($UIDorUser))
		{
			if(! (	$user = $this->fetchUserByID($UIDorUser) ) )
			{
				trace('unable to find user ' . $UIDorUser, true);
				return false;
			}
		}
		else
		{
			$user = $UIDorUser;
		}

		// grab our user first to see if overrrideRoll has been set to 1
		if($user instanceof \rocketD\auth\User)
		{
			// override hasnt been engaged, let external db dictate the role
			if(!isset($user->overrideRole) || $user->overrideRole != '1')
			{
				$RM  = \rocketD\perms\RoleManager::getInstance();
				// GIVE ROLES
				if($isLibraryUser)
				{
					if(! $RM->doesUserHaveARole(array(\cfg_core_Role::EMPLOYEE_ROLE), $user->userID))
					{
						$RM->addUsersToRole_SystemOnly(array($user->userID), \cfg_core_Role::EMPLOYEE_ROLE);
					}

					if(! $RM->doesUserHaveARole(array(\cfg_obo_Role::CONTENT_CREATOR), $user->userID))
					{
						$RM->addUsersToRole_SystemOnly(array($user->userID), \cfg_obo_Role::CONTENT_CREATOR);
					}
					return true;
				}
				// REMOVE ROLES
				else
				{
					if($RM->doesUserHaveARole(array(\cfg_core_Role::EMPLOYEE_ROLE), $user->userID))
					{
						$RM->removeUsersFromRoles_SystemOnly(array($user->userID), array(\cfg_core_Role::EMPLOYEE_ROLE));
					}

					if($RM->doesUserHaveARole(array(\cfg_obo_Role::CONTENT_CREATOR), $user->userID))
					{
						$RM->removeUsersFromRoles_SystemOnly(array($user->userID), array(\cfg_obo_Role::CONTENT_CREATOR));
					}
					return true;
				}
			}
		}
	}

	public function removeRecord($userID)
	{
		return parent::removeRecord($userID); // remove user
	}

	public function isPasswordCurrent($userID)
	{
		if($this->validateUID($userID))
		{
			return true; // USER password is maintained in LDAP
		}
		return false;
	}

	public function dbSetPassword($userID, $newPassword)
	{
		return false;
	}

	public function requestPasswordReset($userName, $email, $returnURL)
	{
		return false;
	}

	protected function sendPasswordResetEmail($sendTo, $returnURL, $resetKey)
	{
		return false;
	}

	public function changePasswordWithKey($userName, $key, $newpass)
	{
		return false;
	}
}
