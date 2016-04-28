<?php
namespace rocketD\auth;
class ModLTI extends AuthModule
{
	use \rocketD\Singleton;

	public function authenticate($requestVars)
	{
		$success = false;
		$userNameIsValid = (bool) $this->validateUsername($requestVars['userName']);

		if ($userNameIsValid && ! empty($requestVars['validLti']) && ! empty($requestVars['ltiData']))
		{
			$ltiData = $requestVars['ltiData'];
			$authMod = $this->getRelatedAuthMod();
			// let the external system have a chance to find  this user
			$this->syncExternalUser($requestVars['userName']);

			// get the user
			$user = $authMod->fetchUserByLogin($requestVars['userName']);

			if ( ! $user && \AppCfg::LTI_CREATE_USER_IF_MISSING)
			{
				$result = $authMod->createNewUser($ltiData->username, $ltiData->first, $ltiData->last, '', $ltiData->email);
				$user = parent::fetchUserByLogin($ltiData->username);
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
			profile('login', "'{$requestVars['userName']}','lti','0','".($success?'1':'0')."'");
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

	//
	public function verifyPassword($user, $password) {}
	protected function addRecord($userID, $userName, $password) {}
	public function updateRecord($userID, $userName, $password) {}
	public function validatePassword($pass) {}
	public function removeRecord($userID) {}
	public function dbSetPassword($userID, $newPassword) {}
	public function requestPasswordReset($username, $email, $returnURL) {}
	protected function sendPasswordResetEmail($sendTo, $returnURL, $resetKey) {}
	public function changePasswordWithKey($username, $key, $newpass) {}

}
