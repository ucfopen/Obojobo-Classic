<?php
require_once(dirname(__FILE__).'/../packages/php-saml/_toolkit_loader.php');

class plg_UCFAuth_UCFAuthModule extends \rocketD\auth\AuthModule
{
	protected $oDBM;
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
	// security check: Ian Turgeon 2008-05-06 - PASS
	protected function defaultDBM()
	{
		if(!$this->oDBM) // if DBM isnt set use the default
		{ 
			// load this module's config
			$this->oDBM = \rocketD\db\DBManager::getConnection(new \rocketD\db\DBConnectData(\AppCfg::UCF_DB_HOST, \AppCfg::UCF_DB_USER, \AppCfg::UCF_DB_PASS, \AppCfg::UCF_DB_NAME, \AppCfg::UCF_DB_TYPE));
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
		return parent::getAllUsers();
	}

	// security check: Ian Turgeon 2008-05-08 - PASS
	public function recordExistsForID($userID=0)
	{
		return parent::recordExistsForID($userID);
	}
	
	// security check: Ian Turgeon 2008-05-06 - PASS
	public function fetchUserByID($userID = 0)
	{
		return parent::fetchUserByID($userID);
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
			$result = parent::createNewUser($userName, $fName, $lName, $mName, $email, $optionalVars);
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
		// check for exising username
		$q = $this->DBM->querySafe("SELECT * FROM ".\cfg_core_User::TABLE." WHERE ".\cfg_core_User::LOGIN."='?' LIMIT 1", $userName);
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
		return parent::getUIDforUsername($userName);
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
			$result = parent::updateUser($userID, $userName, $fName, $lName, $mName, $email, $optionalVars);
			if($result['success']==true)
			{
				// update with md5 pass
				if($this->updateRecord($userID, $userName))
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
		$auth = new OneLogin_Saml2_Auth(\AppCfg::$SAML_SETTINGS['saml']['config']);

		if (isset($_POST['SAMLResponse'])) {
			$auth->processResponse();
			$attributes = $auth->getAttributes();

			if (!$auth->isAuthenticated()) {
				trace("Authentication failed after returning from IdP");
				return false;
			}
		}

		$validSSO = false; // flag to indicate a SSO authentication is assumed
		$weakExternalSync = false; // allows the external sync to fail in case the user isn't present there

		if (!$auth->isAuthenticated() && !$this->checkForValidLTILogin($requestVars, $validSSO, $weakExternalSync)) {
			$auth->login();
			return false;
		}
		// AUTHENTICATED

		header("Location: " . $_SESSION['redirect']);

		// create/update the user with the external database
		$user = $this->syncSamlUser($attributes[$attr[\cfg_plugin_AuthModUCF::USERNAME]], $weakExternalSync);
		
		if($user instanceof \rocketD\auth\User)
		{
			$this->storeLogin($user->userID);
			$this->internalUser = $user;
			return true;
		}
		else
		{
			\rocketD\util\Log::profile('login', "'".$requestVars['userName']."','not_in_external_db','0','".time().",'0'");
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
		// save the NID and set the auth_module to this one
		return (bool) $this->DBM->querySafe("UPDATE ".\cfg_core_User::TABLE." SET ".\cfg_core_User::LOGIN." = '?', ".\cfg_core_User::AUTH_MODULE." = '?' WHERE ".\cfg_core_User::ID." = '?' ", $userName, get_class($this), $userID);
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
		if($this->validateUsername($userName) === true)
		{
			\rocketD\util\Cache::getInstance()->clearUserByID($userID);
			
			// update username
			 return $this->DBM->querySafe("UPDATE ".\cfg_core_User::TABLE." SET ".\cfg_core_User::LOGIN." = '?' WHERE ".\cfg_core_User::ID." = '?' ", $userName, $userID);
			
		}
		return true;
	
	}
	
	public function syncSamlUser($userName, $attributes)
	{
		$email = "notreal@not.real";
		$first = $attributes[\cfg_plugin_AuthModUCF::FIRST];
		$last = $attributes[\cfg_plugin_AuthModUCF::LAST] ;
		
		// determine user's role
		$isCreator    = false;
		$author_roles = ["faculty", "staff", "employee", "CF_STAFF"];

		foreach ($attributes[$attr['roles']] as $role)
		{
			if (in_array($role, $author_roles))
			{
				$isCreator = true;
				break;
			}
		}

		if($userID = $this->getUIDforUsername($userName))
		{
			// update internal record
			if($user = $this->fetchUserByID($userID))
			{
				// update user data changes
				if($first != $user->first || $last != $user->last || $email != $user->email){
					trace('updating user info: ' . $user->mi .'=,'. $user->first .'='. $first.','.$user->last .'='. $last.','.$user->email .'='. $email);
					// external record differs from ours, update ours to match the external data
					$user->first = $first;
					$user->last = $last;
					$user->email = $email;
					parent::updateUser($user->userID, $userName, $user->first, $user->last, $user->mi, $user->email);
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
			$created = $this->createNewUser($userName, $first, $last, "", $email, array());
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
		$this->updateRole($user->userID, $isCreator);
		
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

	public function verifyPassword($userName, $password)
	{
		return false;
	}
}
?>
