<?php
namespace rocketD\auth;
class ModLTI extends AuthModule
{
	use \rocketD\Singleton;

	public static $AUTH_MOD_NAME = \AppCfg::LTI_EXTERNAL_AUTHMOD;

	// Make sure fetchUserByLogin uses the relatedAuthMod
	public function fetchUserByLogin($login)
	{
		$authMod = $this->getRelatedAuthMod();
		$result = $authMod->fetchUserByLogin($login);

		// if the related auth module fails to find them and create if missing is true
		// we want to fall back on just getting them out of the database
		// sometimes fechUserByLogin will fail in an auth module because
		// it returns false if a user doesn't exist upstream, we need to be able to bypass this
		if ( ! ($result instanceof \rocketD\auth\User) && \AppCfg::LTI_CREATE_USER_IF_MISSING)
		{
			$result = parent::fetchUserByLogin($login);
		}

		return $result;
	}

	// Make sure createNewUser uses the relatedAuthMod
	public function createNewUser($userName, $fName, $lName, $mName, $email, $optionalVars=[])
	{
		$authMod = $this->getRelatedAuthMod();
		return $authMod->createNewUser($userName, $fName, $lName, $mName, $email, $optionalVars);
	}

	public function authenticate($requestVars)
	{
		$success = false;
		$userNameIsValid = (bool) $this->validateUsername($requestVars['userName']);

		if ($userNameIsValid && ! empty($requestVars['validLti']) && ! empty($requestVars['ltiData']))
		{
			$ltiData = $requestVars['ltiData'];
			// let the external system have a chance to find  this user
			$this->syncExternalUser($ltiData->username);

			// get the user
			$user = $this->fetchUserByLogin($ltiData->username);

			if ( ! $user && \AppCfg::LTI_CREATE_USER_IF_MISSING)
			{
				$result = $this->createNewUser($ltiData->username, $ltiData->first, $ltiData->last, '', $ltiData->email);
				$user = $this->fetchUserByLogin($ltiData->username);
			}

			if ($user instanceof \rocketD\auth\User)
			{
				// we're really logged in!
				$this->storeLogin($user);
				$success = true;

				// allow the lti role to elevate this user's role
				if(\AppCfg::LTI_USE_ROLE && $ltiData->isInstructor())
				{
					\obo\perms\RoleManager::getInstance()->addUsersToRoles_SystemOnly([$user->userID], [\obo\perms\Role::CONTENT_CREATOR]);
				}
			}
			profile('login', "'{$ltiData->username}','lti','0','".($success?'1':'0')."'");
		}
		return $success;
	}

	protected function getRelatedAuthMod()
	{
		return call_user_func(\AppCfg::LTI_EXTERNAL_AUTHMOD . '::getInstance');
	}

	public function syncExternalUser($userName)
	{
		$authMod = $this->getRelatedAuthMod();
		if (method_exists($authMod, 'syncExternalUser'))
		{
			$authMod->syncExternalUser($userName);
		}
	}

	public function validateUsername($username)
	{
		return $this->getRelatedAuthMod()->validateUsername($username);
	}

	public function isPasswordCurrent($userID)
	{
		return true;
	}

	public function verifyPassword($user, $password) { throw new \Exception("Method not implemented"); }
	protected function addRecord($userID, $userName, $password) { throw new \Exception("Method not implemented"); }
	public function updateRecord($userID, $userName, $password) { throw new \Exception("Method not implemented"); }
	public function validatePassword($pass) { throw new \Exception("Method not implemented"); }
	public function removeRecord($userID) { throw new \Exception("Method not implemented"); }
	public function dbSetPassword($userID, $newPassword) { throw new \Exception("Method not implemented"); }
	public function requestPasswordReset($username, $email, $returnURL) { throw new \Exception("Method not implemented"); }
	protected function sendPasswordResetEmail($sendTo, $returnURL, $resetKey) { throw new \Exception("Method not implemented"); }
	public function changePasswordWithKey($username, $key, $newpass) { throw new \Exception("Method not implemented"); }

}
