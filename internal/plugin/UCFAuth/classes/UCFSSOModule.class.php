<?php

class plg_UCFAuth_UCFSSOModule extends \rocketD\auth\AuthModule
{
	use \rocketD\Singleton;

	public function authenticate($requestVars)
	{
		$success = false;
		$userName = '';

		if ($this->checkSSO())
		{
			$userName = trim($_REQUEST['nid']);
			// allow external to sync
			$this->syncExternalUser($userName);

			$authMod = $this->getRelatedAuthMod();
			$user = $authMod->fetchUserByLogin($userName);

			// create if not in external
			if ( ! $user)
			{
				list($created, $error) = $authMod->createNewUser($userName, '', '', '', '', []);
				$user = $authMod->fetchUserByLogin($userName);
			}

			// all's well!
			if ($user instanceof \rocketD\auth\User)
			{
				$this->storeLogin($user);
				$success = true;
			}

			\rocketD\util\Log::profile('login', "'$userName','ucf-sso','0','".time().",'".($success?'1':'0')."'");
		}
		return $success;
	}

	protected function getRelatedAuthMod()
	{
		return call_user_func(\AppCfg::SSO_EXTERNAL_AUTHMOD . '::getInstance');
	}

	protected function checkSSO()
	{
		if (empty($_REQUEST['nid']) || empty($_REQUEST['epoch']) || empty($_REQUEST['hash'])) return false;

		$nid        = trim($_REQUEST['nid']);
		$timestamp  = (int) trim($_REQUEST['epoch']);
		$hash       = trim($_REQUEST['hash']);
		$validHash  = md5("{$nid}{$timestamp}".\AppCfg::SSO_SECRET) === $hash;
		$notExpired = $timestamp >= time() - \AppCfg::SSO_TIMEOUT;

		return $validHash && $notExpired;
	}

	public function syncExternalUser($userName)
	{
		$authMod = $this->getRelatedAuthMod();
		$authMod->syncExternalUser($userName);
	}

	public function isPasswordCurrent($userID)
	{
		return true;
	}

	public function validateUsername($username) {}
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
