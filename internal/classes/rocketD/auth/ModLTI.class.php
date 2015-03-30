<?php
namespace rocketD\auth;
class ModLTI extends AuthModule
{
	use \rocketD\Singleton;

	const MAX_USERNAME_LENGTH = '255';
	const MIN_USERNAME_LENGTH = '2';

	public function authenticate($requestVars)
	{
		$success = false;
		$userNameIsValid = (bool) $this->validateUsername($requestVars['userName']);

		if ($userNameIsValid && ! empty($requestVars['validLti']) && ! empty($requestVars['ltiData']))
		{
			$authMod = $this->getRelatedAuthMod();
			// let the external system have a chance to find  this user
			$this->syncExternalUser($requestVars['userName']);

			// get the user
			$user = $authMod->fetchUserByLogin($requestVars['userName']);
			if ( ! $user && \AppCfg::LTI_CREATE_USER_IF_MISSING)
			{
				$lti = $requestVars['ltiData'];
				list($created, $error) = $authMod->createNewUser($lti->username, $lti->first, $lti->last, '', $lti->email);
				$user = $authMod->fetchUserByLogin($lti->username);
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
			\rocketD\util\Log::profile('login', "'{$requestVars['userName']}','lti','0','".($success?'1':'0')."'");
		}
		return $success;
	}

	protected function getRelatedAuthMod()
	{
		return call_user_func(\AppCfg::SSO_EXTERNAL_AUTHMOD . '::getInstance');
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
		// make sure the string length is less then 255, our usernames aren't that long
		if(strlen($username) > self::MAX_USERNAME_LENGTH)
		{
			return "User name maximum length is {self::MAX_USERNAME_LENGTH} characters.";
		}
		// make sure the username is atleast 2 characters
		if(strlen($username) < self::MIN_USERNAME_LENGTH)
		{
			return "User name minimum length is {self::MIN_USERNAME_LENGTH} characters.";
		}
		return true;
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
