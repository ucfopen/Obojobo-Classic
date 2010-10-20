<?php
abstract class core_auth_AuthModule extends core_db_dbEnabled
{
	
	protected $internalUser;	
	// TODO: Move reset to internal authmod
	const COL_PW_RESET_KEY = 'ResetPasswordKey';
	const COL_PW_RESET_DATE = 'resetPasswordDate';
	const CAN_CHANGE_PW = false; // override this!

	abstract public function authenticate($requestVars);
	abstract public function isPasswordCurrent($userID);
	abstract public function dbSetPassword($userID, $newPassword);
	abstract protected function addRecord($userID, $userName, $password);
	abstract public function updateRecord($userID, $username, $password);
	abstract public function verifyPassword($user, $password);
	abstract public function requestPasswordReset($username, $email, $returnURL);
	abstract protected function sendPasswordResetEmail($sendTo, $returnURL, $resetKey);
	abstract public function changePasswordWithKey($username, $key, $newpass);

	abstract static public function getInstance();
	/**
	 * Fetch the Obojobo user data by it's ID
	 *
	 * @return core_auth_User on success, false on failure
	 * @author /bin/bash: niutil: command not found
	 **/
	// security check: Ian Turgeon 2008-05-06 - PASS
	public function fetchUserByID($userID, $authModDBC)
	{
		if(!is_numeric($userID) || $userID < 1)
		{
			trace('userID not valid', true);
			return false;
		}
		//check memcache
		
		if($user = core_util_Cache::getInstance()->getUserByID($userID))
		{
			return $user;
		}
		
		$this->defaultDBM();
		//Fetch user data
		
		// TODO: change constant($authModDBC.'::TABLE') to $authModDBC::TABLE when PHP 5.3.0 is out
		$qstr = "SELECT
		 				U.*, AMOD.". constant($authModDBC.'::USER_NAME') ." 
					FROM ".cfg_core_User::TABLE." AS U
					LEFT JOIN ".constant($authModDBC.'::TABLE')." AS AMOD
						ON AMOD.".cfg_core_User::ID." = U.".cfg_core_User::ID." 
					WHERE AMOD.".cfg_core_User::ID."='?'";
		$q = $this->DBM->querySafe($qstr ,$userID);
		$return = $this->buildUserFromQueryResult($this->DBM->fetch_obj($q), $authModDBC);
		
		//store in memcache
		core_util_Cache::getInstance()->setUserByID($userID, $return);
		return $return;
	}

	// TODO: this needs to be the one function for this call, limitations in php 5.2 required the authmods to have their own copy of this function for the retrieving the constants
	// security check: Ian Turgeon 2008-05-07 - FAIL (need to make sure this is an administrator/system only function, client should never have a list of all users)
	public function getAllUsers($authModDBC)
	{
		$this->defaultDBM();
		$users = array();
		$q = $this->DBM->query("SELECT ". cfg_core_User::ID . " FROM ". constant($authModDBC.'::TABLE'));
		while($r = $this->DBM->fetch_obj($q))
		{
			if($newUser = $this->fetchUserByID($r->{cfg_core_User::ID}))
			{
				$users[] = $newUser;
			}
		}
		return $users;
	}
	
	// TODO: add password current info to user so that we can use memcache to determine if password is current
	protected function buildUserFromQueryResult($result, $authModDBC)
	{
		if($result)
		{
			return new core_auth_User($result->{cfg_core_User::ID}, $result->{constant($authModDBC.'::USER_NAME')}, $result->{cfg_core_User::FIRST}, $result->{cfg_core_User::LAST},
								   $result->{cfg_core_User::MIDDLE}, $result->{cfg_core_User::EMAIL},
								   $result->{cfg_core_User::CREATED_TIME}, $result->{cfg_core_User::LOGIN_TIME}/*, $result->{self::COL_UPLOAD_LIMIT}*/);
		}
		return false;
	}
	
	// security check: Ian Turgeon 2008-05-08 - PASS
	public function getUser()
	{
		return $this->internalUser;
	}
	/**
		Make sure all the conditions for this authentication module's use are met.  Conditions may limit referrers, information retrival methods, encryption, keys, or various other protections.
	**/
	protected function verifyAuthModuleAvail()
	{
		
	}
	
	// security check: Ian Turgeon 2008-05-08 - PASS				
	public function getUIDforUsername($username, $authModDBC)
	{
		if($this->validateUsername($username) === true){
			$this->defaultDBM();
			
			
			if($userID = core_util_Cache::getInstance()->getUIDForUserName($username))
			{
				return $userID;
			}
			
			if(!$this->DBM->connected)
			{
				trace('not connected', true);
				return false;
			}
		
			$q = $this->DBM->querySafe("SELECT ".cfg_core_User::ID." FROM ".constant($authModDBC.'::TABLE')." WHERE ".constant($authModDBC.'::USER_NAME')."='?' LIMIT 1", $username);
			if($r = $this->DBM->fetch_obj($q))
			{
				// store in memcache
				core_util_Cache::getInstance()->setUIDForUserName($username, $r->{cfg_core_User::ID});
				
				return $r->{cfg_core_User::ID}; // return found user id
			}
	
		}
		return false;
	}
	
	// security check: Ian Turgeon 2008-05-08 - PASS	
	public function createNewUser($fName, $lName, $mName, $email, $optionalVars=0)
	{
		// Only update if valid (empty keeps existing value)		
		if($this->validateFirstName($fName) && $this->validateLastName($lName) && $this->validateMiddleName($mName) && $this->validateEmail($email))
		{
			// Invalidating memcache that has a list of all users
			// TODO: may be better to just append to the list then delete it
			
			core_util_Cache::getInstance()->clearAllUsers();
						
			$this->defaultDBM();
			$qstr = "INSERT INTO ".cfg_core_User::TABLE."
			 SET ".cfg_core_User::FIRST."='?',
			 ".cfg_core_User::LAST."='?',
			 ".cfg_core_User::MIDDLE."='?',
			 ".cfg_core_User::EMAIL."='?',
			 ".cfg_core_User::CREATED_TIME."=UNIX_TIMESTAMP(),
			 ".cfg_core_User::LOGIN_TIME."=''";
			// MERGE OLD USERS 
			//	core_util_Log::profile('NIDConversionSQL', "INSERT INTO ".cfg_core_User::TABLE." SET ".cfg_core_User::FIRST."='$fName', ".cfg_core_User::LAST."='$lName', ".cfg_core_User::MIDDLE."='$mName', ".cfg_core_User::EMAIL."='$email', ".cfg_core_User::CREATED_TIME."=UNIX_TIMESTAMP(), ".cfg_core_User::LOGIN_TIME."='';\n");
			
			if($this->DBM->querySafe($qstr, $fName, $lName, $mName,  $email ))
			{
				return array('success' => true, 'userID' => $this->DBM->insertID);
			}
		}
		trace("cannot create user ", true);
		return array('success' => false, 'error' => 'Unable to create User.');
	}

	// security check: Ian Turgeon 2008-05-08 - PASS	
	public function updateUser($userID, $fName, $lName, $mName, $email, $optionalVars=0)
	{
		// require a valid UID
		if($this->validateUID($userID))
		{
			// get a user from the db to get the current values;
			$user = $this->fetchUserByID($userID);
			if($user)
			{

				// Only update if valid (empty keeps existing value)
				if(!$this->validateFirstName($fName)) $fName = $user->first; 
				if(!$this->validateLastName($lName)) $lName = $user->last;
				if(!$this->validateMiddleName($mName)) $mName = $user->mi;
				//if(!$this->validateTitle($title)) $title = $user->title;
				if(!$this->validateEmail($email)) $email = $user->email;

				$this->defaultDBM();
				$qstr = "UPDATE ".cfg_core_User::TABLE." 
				SET ".cfg_core_User::FIRST."='?',
				 ".cfg_core_User::LAST."='?',
				 ".cfg_core_User::MIDDLE."='?',
				 ".cfg_core_User::EMAIL."='?' WHERE ".cfg_core_User::ID."='?' LIMIT 1";
				if($q = $this->DBM->querySafe($qstr, $fName, $lName, $mName, $email, $userID))
				{
					
					core_util_Cache::getInstance()->clearUserByID($userID);
					return array('success' => true, 'userID' => $userID);
				}
				else
				{
					trace("unable to update user " . mysql_error(), true);
				}
			}
		}
		return array('success' => false, 'error' => 'Unable to update User.');
	}

	/**
	 * Not meant to be extended, this function will create a session with appropriate variables and update the database.
	 *
	 * @return void
	 * @author Ian Turgeon
	 **/
	// security check: Ian Turgeon 2008-05-06 - PASS
	protected function storeLogin($userID)
	{
		// validate arguments
		if(!$this->validateUID($userID))
		{
			trace('userID not valid', true);
			return void;
		}
		else
		{
			
			$this->defaultDBM();
			if(!session_id()) @session_start();
			@session_regenerate_id(false);
			$_SESSION = array();// force a fresh start on the session variables
			$_SESSION['userID'] = $userID;
			$_SESSION['passed'] = true;
			$_SESSION['timestamp'] = time() + AppCfg::AUTH_TIMEOUT;
			$this->DBM->querySafe("UPDATE ".cfg_core_User::TABLE." SET ".cfg_core_User::SID." = '".session_id()."',  ".cfg_core_User::LOGIN_TIME." = UNIX_TIMESTAMP() WHERE ".cfg_core_User::ID."='?' LIMIT 1", $userID);		
		}
	}

	
	// security check: Ian Turgeon 2008-05-08 - PASS
	


	/**
	 * Resolves a class const using a class Reference and the string name of the constant, used to get around limitations in PHP 5.2 that do not allow parent classes
	 * to see child class constants that should override parent constants. 
	 * @param	classRef	Object Reference such as $this
	 * @param	constant	String of the constant you what the value of, such as 'COL_AUTH_UID'
	 * @return 	value of constant
	 */
	protected function resolveConst($classRef, $constant)
	{
		return constant(get_class($classRef).'::'.$constant);
	}

	// security check: Ian Turgeon 2008-05-08 - PASS
	public function recordExistsForID($userID=0, $authModDBC)
	{
		if(!$this->validateUID($userID)) return false;
		$this->defaultDBM();
		$q = $this->DBM->querySafe("SELECT * FROM ". constant($authModDBC . '::TABLE')." WHERE ". cfg_core_User::ID ."='?'", $userID);
		return $this->DBM->fetch_num($q) > 0;
	}
	
	// security check: Ian Turgeon 2008-05-08 - PASS
	protected function validateUID($userID)
	{
		return core_util_Validator::isPosInt($userID);
	}

	public function validateUsername($username)
	{
		return true;
		//return nm_los_Validator::isString($username);
	}

	// security check: Ian Turgeon 2008-05-08 - FAIL (needs to do something)		
	protected function validateFirstName($name)
	{
		return true;
		//return nm_los_Validator::isString($username);
	}
	
	// security check: Ian Turgeon 2008-05-08 - FAIL (needs to do something)		
	protected function validateLastName($name)
	{
		return true;
		//return nm_los_Validator::isString($username);
	}

	// security check: Ian Turgeon 2008-05-08 - PASS
	protected function validateMiddleName($name)
	{
		return true;
	}
	
	// security check: Ian Turgeon 2008-05-08 - FAIL (needs to do something)
	protected function validateEmail($email)
	{
		return true;
	}	
	
	protected function validateResetURL($URL)
	{
		return true;
	}
	
	// security check: Ian Turgeon 	2008-05-08 - PASS 
	public function removeRecord($userID)
	{
		if($this->validateUID($userID))
		{
			// invalidate the memcache for this user
			
			if(AppCfg::CACHE_MEMCACHE)
			{
				
				core_util_Cache::getInstance()->delete('core_auth_AuthModule:fetchUserByID:'.$userID);
			}
			
			$this->defaultDBM();
			if($q = $this->DBM->querySafe("DELETE FROM ".cfg_core_User::TABLE." WHERE ".cfg_core_User::ID."='?' LIMIT 1", $userID))
			{
				// clean up permissions for the deleted user
				//TODO: this isn't portable across installs
				$PM = nm_los_PermissionsManager::getInstance();
				$PM->removeAllPermsForUser($userID);
				return true;
			}
		}
		return false;
	}
	
	public function getUserName($userID)
	{
		//use fetchUserBYID if memcahe is on
		
		if($user = core_util_Cache::getInstance()->getUserByID($userID))
		{
			return $user->login;
		}
		
		$thisClassName = get_class($this);
		$configClass = constant($thisClassName . '::CONFIG');
		$colUserName = constant($configClass . '::USER_NAME');
		$table = constant($configClass . '::TABLE');
		$q = $this->DBM->querySafe("SELECT $colUserName FROM $table WHERE ".cfg_core_User::ID." = '?' LIMIT 1", $userID);
		if($r = $this->DBM->fetch_obj($q))
		{
			return $r->{$colUserName};
		}
		return false;
	}	

	protected function makeResetKey()
	{
		return sha1(microtime(true));
	}
	
	// security check: Ian Turgeon 2008-05-06 - PASS	
	protected function makePassword()
	{
		$startNumber = rand(0, 21);
		$password = substr(md5(time()), $startNumber, $startNumber + 10); // make one if one wasn't sent
		return array('password' => $password, 'MD5Pass' => md5($password));
	}
	
}
?>