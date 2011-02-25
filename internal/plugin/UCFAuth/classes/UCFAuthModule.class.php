<?php
class plg_UCFAuth_UCFAuthModule extends \rocketD\auth\AuthModule
{
	
	protected $oDBM;
	protected static $instance;

	const CONFIG = '\cfg_plugin_AuthModUCF';
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
	// security check: Ian Turgeon 2008-05-06 - PASS
	protected function defaultDBM()
	{
		if(!$this->oDBM) // if DBM isnt set use the default
		{ 
			// load this module's config
			$this->oDBM = \rocketD\db\DBManager::getConnection(new \rocketD\db\dbConnectData(\AppCfg::UCF_DB_HOST, \AppCfg::UCF_DB_USER, \AppCfg::UCF_DB_PASS, \AppCfg::UCF_DB_NAME, \AppCfg::UCF_DB_TYPE));
			if(!$this->oDBM->connected)
			{
				$NM = \obo\util\NotificationManager::getInstance();
				$NM->sendCriticalError('Oracle DB Connection Failure', 'Failed to connect to Lerxst on ' . date("F j, Y, g:i a"));
				
			}
		}
		parent::defaultDBM(); // build default dbm still for use with internal db
	}
	// security check: Ian Turgeon 2008-05-07 - FAIL (need to make sure this is an administrator/system only function, client should never have a list of all users)
	public function getAllUsers()
	{
		return parent::getAllUsers('\cfg_plugin_AuthModUCF');
	}

	// security check: Ian Turgeon 2008-05-08 - PASS
	public function recordExistsForID($userID=0)
	{
		return parent::recordExistsForID($userID, '\cfg_plugin_AuthModUCF');
	}
	
	// security check: Ian Turgeon 2008-05-06 - PASS
	public function fetchUserByID($userID = 0)
	{
		return parent::fetchUserByID($userID, '\cfg_plugin_AuthModUCF');
	}

	// security check: Ian Turgeon 2008-05-08 - PASS
	public function createNewUser($userName, $fName, $lName, $mName, $email, $optionalVars=0)
	{
		$optionalVars['MD5Pass'] = md5(microtime() . $email . $userName . $fName); // create a random password that is unguessable
		$valid = $this->checkRegisterPossible($userName, $fName, $lName, $mName, $email, $optionalVars);
		if($valid === true)
		{
			$this->defaultDBM();
			if(!$this->oDBM->connected || !$this->DBM->connected)
			{
				trace('not connected', true);
				return false;
			}
			$this->DBM->startTransaction();
			$result = parent::createNewUser( $fName, $lName, $mName, $email, $optionalVars);
			if($result['success'] === true)
			{
				if(!$this->addRecord($result['userID'], $userName, $optionalVars['MD5Pass']))
				{
					$this->DBM->rollBack();
					return array('success' => false, 'error' => 'Unable to create user.');
				}
				//
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
		
	// security check: Ian Turgeon 2008-05-08 - PASS	
	public function checkRegisterPossible($userName, $fName, $lName, $mName, $email, $optionalVars=0)
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
		// check local db for username		
		$this->defaultDBM();
		if(!$this->oDBM->connected || !$this->DBM->connected)
		{
			trace('not connected', true);
			return false;
		}
		// not using getUIDforUsername to prevent de-provisioned user conflicts
		$q = $this->DBM->querySafe("SELECT ".\cfg_plugin_AuthModUCF::USER_NAME." FROM ".\cfg_plugin_AuthModUCF::TABLE." WHERE ".\cfg_plugin_AuthModUCF::USER_NAME."='?' LIMIT 1", $userName);
		if($this->DBM->fetch_num($q) > 0 )
		{
			trace('username already exists', true);
			return false;
		}
		
		return true;
	}
	
	// security check: Ian Turgeon 2008-05-08 - PASS
	public function getUIDforUsername($userName)
	{
		return parent::getUIDforUsername($userName, '\cfg_plugin_AuthModUCF');
	}
	// security check: Ian Turgeon 2008-05-08 - PASS
	public function updateUser($userID, $userName, $fName, $lName, $mName, $email, $optionalVars=0)
	{
		// validate arguments
		if(!$this->validateUID($userID))
		{
			return array('success' => false, 'error' => 'Invalid User Id.');
		}

		$this->defaultDBM();
		if(!$this->oDBM->connected || !$this->DBM->connected)
		{
			trace('not connected', true);
			return false;
		}		
		
		$user = $this->fetchUserByID($userID);
		if($user != false)
		{
			$this->DBM->startTransaction();
			$result = parent::updateUser($userID, $fName, $lName, $mName, $email, $optionalVars);
			if($result['success']==true)
			{
				// update with md5 pass
				if($this->updateRecord($userID, $userName, $optionalVars['MD5Pass']))
				{
					return array('success' => true);
				}		
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
	// security check: Ian Turgeon 2008-05-06 - PASS
	public function authenticate($requestVars)
	{
		$validSSO = false;
		// required stuff not sent, 
		if(empty($requestVars['userName']) && empty($requestVars['password']))
		{
			
			if(isset($_REQUEST[plg_UCFAuth_SsoHash::SSO_USERID]) && isset($_REQUEST[plg_UCFAuth_SsoHash::SSO_TIMESTAMP]) && isset($_REQUEST[plg_UCFAuth_SsoHash::SSO_HASH]))
			{
				$time = microtime(true);
				$sso = new plg_UCFAuth_SsoHash(\AppCfg::SSO_SECRET);
				$sso_req = $sso->getSsoInParametersFromRequest();
				trace($sso_req);
				try
				{
					if($sso->validateSSOHash($sso_req))
					{
						trace('sso validated', true);
						$validSSO = true;
						$requestVars['userName'] = $sso_req[plg_UCFAuth_SsoHash::SSO_USERID];
					}
				}
				//catch exception
				catch(Exception $e)
				{
					trace($e, true);
				}
				\rocketD\util\Log::profile('login', "'".$requestVars['userName']."','func_SSOAuthentication','".round((microtime(true) - $time),5)."','".time().",'".($validSSO?'1':'0')."'\n");
			}
		}
		
		if($this->validateUsername($requestVars['userName']) !== true) return false;
		if($validSSO == false && $this->validatePassword($requestVars['password']) !== true) return false;
		
		// begin authentication
		
		// create/update the user with the external database
		$user = $this->syncExternalUser($requestVars['userName']);
		
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
				trace("$user->login logged in", true);
				$this->storeLogin($user->userID);
				$this->internalUser = $user;
				return true;
			}
		}
		return false;
	}

	public function verifyPassword($userName, $password)
	{
		
		
		// for local testing, ldap access may not be possible, if in local test mode just return an ok
		if(\AppCfg::UCF_AUTH_BYPASS_PASSWORDS && $_SERVER['SERVER_ADDR'] != \AppCfg::PRODUCTION_IP)
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
				\rocketD\util\Log::profile('login', "'$userName','func_LDAPAuthenticate','".round((microtime(true) - $time),5)."','".time().",'".($success?'1':'0')."'\n");
			}
		}
		catch(Exception $e)
		{
			$NM = \obo\util\NotificationManager::getInstance();
			$NM->sendCriticalError('Oracle DB Connection Failure', 'LDAP Threw an Error ' . date("F j, Y, g:i a") . "\r\n" . print_r($e, true));
			trace('ldap threw and exception', true);
			trace($e);
		}

		
		//  on failure, attempt to find out reason for failure by asking our web service if its enabled
		if($success != true && \AppCfg::UCF_USE_WS_AUTH == true)
		{
			try
			{
				$soapClient = new SoapClient(\AppCfg::UCF_WSDL);
				$time = microtime(true); // time the soap call
				$soapRes = $soapClient->AuthenticateNid(array('sNid' => $userName,'sPassword' => $password,'sAppID' => \AppCfg::UCF_APP_ID));
				\rocketD\util\Log::profile('login', "'$userName','func_WSSOAPAuthenticate','".round((microtime(true) - $time),5)."','".time().",'{$soapRes->AuthenticateNidResult}'\n");

				// if the responce is valid but is not an error
				if(isset($soapRes->AuthenticateNidResult))
				{
					// set the return values
					$success = ($soapRes->AuthenticateNidResult == \cfg_plugin_AuthModUCF::WS_SUCCESS); // true if the result is the same string
					$code = $soapRes->AuthenticateNidResult;
				}
			}
			catch(Exception $e)
			{
				$NM = \obo\util\NotificationManager::getInstance();
				$NM->sendCriticalError('AD Web Service Failure', 'Failed to connect to the AD Web Service via soap on ' . date("F j, Y, g:i a") . "\r\n" . print_r($e, true));
				trace('webservice threw an exception', true);
				trace($e, true);
			}

		}
		
		return array('success' => $success, 'code' => $code);
	}

	// security check: Ian Turgeon 2008-05-08 - PASS
	protected function createSalt()
	{
		return md5(uniqid(rand(), true));
	}

	// security check: Ian Turgeon 2008-05-08 - PASS
	protected function addRecord($userID, $userName, $password)
	{
		// TODO: this should not be setting any passwords, if its not used, look into using this funciton 
		if(!$this->validateUID($userID)) return false;
		if($this->validateUsername($userName) !== true) return false;
		if($this->validatePassword($password) !== true) return false;

		$this->defaultDBM();
		if(!$this->oDBM->connected || !$this->DBM->connected)
		{
			trace('not connected', true);
			return false;
		}
		$salt = $this->createSalt();
		// MERGE OLD USERS 
		//\rocketD\util\Log::profile('NIDConversionSQL', "INSERT into ".\cfg_plugin_AuthModUCF::TABLE." set ".\cfg_core_User::ID."='$userID', ".\cfg_plugin_AuthModUCF::USER_NAME." = '$userName', ".\cfg_plugin_AuthModUCF::PASS."=MD5(CONCAT('$salt', '$password')), ".\cfg_plugin_AuthModUCF::SALT."='$salt';\n");
		return (bool) $this->DBM->querySafe("INSERT into ".\cfg_plugin_AuthModUCF::TABLE." set ".\cfg_core_User::ID."='?', ".\cfg_plugin_AuthModUCF::USER_NAME." = '?', ".\cfg_plugin_AuthModUCF::PASS."=MD5(CONCAT('?', '?')), ".\cfg_plugin_AuthModUCF::SALT."='?'", $userID, $userName, $salt, $password, $salt);
	}

	// security check: Ian Turgeon 2008-05-08 - PASS
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
		$successCheck1 = true;
		if($this->validateUsername($userName) === true)
		{
			// update username
			$successCheck1 = $this->DBM->querySafe("UPDATE ".\cfg_plugin_AuthModUCF::TABLE." set ".\cfg_plugin_AuthModUCF::USER_NAME."='?' WHERE ".\cfg_core_User::ID."='?' LIMIT 1", $userName, $userID);
			
			\rocketD\util\Cache::getInstance()->clearUserByID($userID);
		}
		// update password
		$successCheck2 = true;
		if($this->validatePassword($password) === true)
		{
			// update password
			$salt = $this->createSalt();
			$successCheck2 =  $this->DBM->querySafe("UPDATE ".\cfg_plugin_AuthModUCF::TABLE." set ".\cfg_plugin_AuthModUCF::PASS."=MD5(CONCAT('?', '?')), ".\cfg_plugin_AuthModUCF::SALT."='?' WHERE ".\cfg_core_User::ID."='?' LIMIT 1", $salt, $password, $salt, $userID);
			$this->DBM->querySafe("UPDATE ".\cfg_core_User::TABLE." set ".self::COL_PW_CHANGE_DATE."='".time()."' WHERE ".\cfg_core_User::ID."='?'", $userID);
		}
		return $successCheck1 && $successCheck2;
	
	}
	
	// security check: Ian Turgeon 2008-05-06 - PASS
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
		if(preg_match("/^[[:alnum:]]{".\cfg_plugin_AuthModUCF::MIN_USERNAME_LENGTH.",".\cfg_plugin_AuthModUCF::MAX_USERNAME_LENGTH."}$/i", $userName) == false)
		{
			trace('User name can only contain alpha numeric characters. ' . $userName, true);
			return 'User name can only contain alpha numeric characters.';
		}		
		return true;
	}

	// security check: Ian Turgeon 2008-05-06 - PASS
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
	
	public function updateNetworkID($userID, $networkID)
	{
		$this->defaultDBM();
		if(!$this->oDBM->connected || !$this->DBM->connected)
		{
			trace('not connected', true);
			return false;
		}
		
		\rocketD\util\Cache::getInstance()->clearUserByID($userID);
		return (bool)$this->DBM->querySafe("UPDATE ".\cfg_plugin_AuthModUCF::TABLE." set ".\cfg_plugin_AuthModUCF::USER_NAME."='?' WHERE ".\cfg_core_User::ID."='?' LIMIT 1", $networkID, $userID);		
	}
	
	public function syncExternalUser($userName)
	{
		if($this->validateUsername($userName) !== true) return false;

		// look for user in external data
		if($externalUser = $this->getUCFUserData($userName))
		{
			if($userID = $this->getUIDforUsername($userName))
			{
				// update internal record
				if($user = $this->fetchUserByID($userID))
				{
					// update user data changes
					if($externalUser->{\cfg_plugin_AuthModUCF::FIRST} != $user->first || substr(trim($externalUser->{\cfg_plugin_AuthModUCF::MIDDLE}), 0, 1) != trim($user->mi) || $externalUser->{\cfg_plugin_AuthModUCF::LAST} != $user->last || $externalUser->{\cfg_plugin_AuthModUCF::EMAIL} != $user->email){
						trace('updating user info: ' . $user->mi .'='. $externalUser->{\cfg_plugin_AuthModUCF::MIDDLE} .','. $user->first .'='. $externalUser->{\cfg_plugin_AuthModUCF::FIRST}.','.$user->last .'='. $externalUser->{\cfg_plugin_AuthModUCF::LAST}.','.$user->email .'='. $externalUser->{\cfg_plugin_AuthModUCF::EMAIL});
						// external record differs from ours, update ours to match the external data
						$user->mi = $externalUser->{\cfg_plugin_AuthModUCF::MIDDLE};
						$user->first = $externalUser->{\cfg_plugin_AuthModUCF::FIRST};
						$user->last = $externalUser->{\cfg_plugin_AuthModUCF::LAST};
						$user->email = $externalUser->{\cfg_plugin_AuthModUCF::EMAIL};
						parent::updateUser($user->userID, $user->first, $user->last, $user->mi, $user->email);
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
				$created = $this->createNewUser($userName, $externalUser->{\cfg_plugin_AuthModUCF::FIRST}, $externalUser->{\cfg_plugin_AuthModUCF::LAST}, $externalUser->{\cfg_plugin_AuthModUCF::MIDDLE}, $externalUser->{\cfg_plugin_AuthModUCF::EMAIL}, array());
				if(!$created['success'])
				{
					trace('createNewUser Failed', true);
					return false;
				}
				else
				{
					trace("creating new internal user $userName" , true);
					// load user data from db
					if( !( $user = $this->fetchUserByID( $this->getUIDforUsername($userName) ) ) )
					{
						trace('fetchUserByID failed', true);
						return false;
					}
				}
			}
			// update roles
			$this->updateRole($user->userID, $externalUser->isCreator);
			
			return $user;
		}
		return false;
	}
	
	// TODO: FIX RETURN FOR DB ABSTRACTION
	protected function getUCFUserData($userName)
	{
		//check memcache
	
		$return = false;
		
		// TODO: need to filter sql variables to prevent sql injection
		$this->defaultDBM();
		if(!$this->oDBM->connected || !$this->DBM->connected)
		{
			trace('not connected', true);
			return false;
		}
		$this->updateNIDChanges(); //  update internal nids if needed
		
		// try faculty first
		$q = $this->oDBM->querySafe("Select * FROM ".\cfg_plugin_AuthModUCF::TABLE_EMPLOYEE." WHERE ".\cfg_plugin_AuthModUCF::NID." = '?'", $userName);
		if($r = $this->oDBM->fetch_obj($q))
		{
			$r->isCreator = true;
			$return = $this->trimArray($r);
		}
		else
		{
			// try students second
			$q = $this->oDBM->querySafe("Select * FROM ".\cfg_plugin_AuthModUCF::TABLE_STUDENT." WHERE ".\cfg_plugin_AuthModUCF::NID." = '?'", $userName);
			if($r = $this->oDBM->fetch_obj($q))
			{
				$r->isCreator = false;
				$return =  $this->trimArray($r);
			}
		}
		
		if($return)
		{
			
			//store in memcache
			\rocketD\util\Cache::getInstance()->setModUCFExternalUser($userName, $return);
		}
		trace($return ? "$userName found, employee:$r->isCreator" : "$userName not found");
		return $return;
	}
	
	protected function trimArray($array){
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
		if(!$this->oDBM->connected || !$this->DBM->connected)
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
			if($user->overrideRole != '1')
			{
				$RM  = \rocketD\perms\RoleManager::getInstance();
				if($isLibraryUser)
				{
					if(! $RM->doesUserHaveARole(array(\cfg_core_Role::EMPLOYEE_ROLE), $user->userID))
					{
						// user should be Library User, but isnt, add
						return $RM->addUsersToRole_SystemOnly(array($user->userID), \cfg_core_Role::EMPLOYEE_ROLE);
					}
				}
				// not marked as content creator
				else
				{
					if($RM->doesUserHaveARole(array(\cfg_core_Role::EMPLOYEE_ROLE), $user->userID))
					{
						// user shouldnt be LibraryUser, but is, remove
						return $RM->removeUsersFromRoles_SystemOnly(array($user->userID), array(\cfg_core_Role::EMPLOYEE_ROLE));
					}
				}
			}
		}
	}
	
	public function removeRecord($userID)
	{
		if(!$this->validateUID($userID)) return false;
		$return = parent::removeRecord($userID); // remove user
		$this->defaultDBM();
		if( !$this->DBM->connected)
		{
			trace('not connected', true);
			return false;
		}
		return $this->removeRecordInternal($userID);
	}
	
	public function removeRecordInternal($userID)
	{
		trace('deleting record '. $userID, true);
		return $return && $this->DBM->querySafe("DELETE FROM ".\cfg_plugin_AuthModUCF::TABLE." WHERE ".\cfg_core_User::ID."='?' LIMIT 1", $userID);
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

	public function updateNIDChanges($force=false)
	{
		$total = 0;
		$updated = 0;
		// TODO: remove memcache for any updated users
		$this->defaultDBM();
		if(!$this->oDBM->connected || !$this->DBM->connected)
		{
			trace('not connected', true);
			return false;
		}
		$lastUpdate = -1;
		// get last successful update date
		$q = $this->DBM->query("SELECT ".\cfg_core_Temp::VALUE." FROM ".\cfg_core_Temp::TABLE." WHERE ".\cfg_core_Temp::ID."='".\cfg_plugin_AuthModUCF::COL_EXTERNAL_SYNC_NAME."' ");
		if($r = $this->DBM->fetch_obj($q))
		{
			// if the last time we checked was anytime after the first second of today, skip updates unless overrided
			$now = getdate();
			if($r->{\cfg_core_Temp::VALUE} > mktime(0,0, 0, $now['mon'], $now['mday'], $now['year']) && $force == false)
			{
				return array('updated' => $updated, 'total' => $total);
			}
			// convert to string for comparison with oracle
			$lastUpdate = strftime("%d-%b-%y", $r->{\cfg_core_Temp::VALUE});
			trace('looking for updates after '. $lastUpdate, true);
		}
		// get all updates since last update
		if($q = $this->oDBM->query("SELECT * FROM ".\cfg_plugin_AuthModUCF::TABLE_NID." WHERE ".\cfg_plugin_AuthModUCF::NID_CHANGE_DATE." >= '$lastUpdate'")) // no need for querySafe
		{ 

			while($r = $this->oDBM->fetch_obj($q))
			{
				$total++;
				// the latest update date will be first, lets keep track of it in case the EFFDT doesn't match up with every day
				// update each NID
				if($q2 = $this->DBM->querySafe("UPDATE ".\cfg_plugin_AuthModUCF::TABLE." SET ".\cfg_plugin_AuthModUCF::USER_NAME."='?' WHERE ".\cfg_plugin_AuthModUCF::USER_NAME."='?' LIMIT 1", $r->{\cfg_plugin_AuthModUCF::NEW_NID}, $r->{\cfg_plugin_AuthModUCF::OLD_NID}))
				{
					if($this->DBM->affected_rows($q2) != 0)
					{
						$updated++;
						\rocketD\util\Cache::getInstance()->clearUserByID($userID);
						trace('NID changed: ' . $r->{\cfg_plugin_AuthModUCF::OLD_NID} .'->'. $r->{\cfg_plugin_AuthModUCF::NEW_NID}, true);
					}  
					else
					{
						trace('NID change may not be needed: ' . $r->{\cfg_plugin_AuthModUCF::OLD_NID} .'->'. $r->{\cfg_plugin_AuthModUCF::NEW_NID}, true);
					}
				}
				// double check to make sure the old NID isnt in our db anymore
				$saftyQ = $this->DBM->querySafe("SELECT * FROM ".\cfg_plugin_AuthModUCF::TABLE." WHERE ".\cfg_plugin_AuthModUCF::USER_NAME."='?'", $r->{\cfg_plugin_AuthModUCF::OLD_NID});
				if($this->DBM->fetch_num($saftyQ) > 0 )
				{
					trace('NID change failed, record for old NID still exists in Obojobo: '. $r->{\cfg_plugin_AuthModUCF::OLD_NID} .'->'. $r->{\cfg_plugin_AuthModUCF::NEW_NID}, true);
				}
			}
		}
		// update last log
		if($lastUpdate == -1)
		{
			$q = $this->DBM->query("INSERT INTO ".\cfg_core_Temp::TABLE." SET ".\cfg_core_Temp::ID."='".\cfg_plugin_AuthModUCF::COL_EXTERNAL_SYNC_NAME."', ".\cfg_core_Temp::VALUE."='". time() ."'");
		}
		else
		{
			$q = $this->DBM->query("UPDATE ".\cfg_core_Temp::TABLE." SET ".\cfg_core_Temp::VALUE."='". time() ."' WHERE ".\cfg_core_Temp::ID."='".\cfg_plugin_AuthModUCF::COL_EXTERNAL_SYNC_NAME."' ");
		}
		return array('updated' => $updated, 'total' => $total);
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
?>