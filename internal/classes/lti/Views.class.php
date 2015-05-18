<?
namespace lti;

class Views
{

	// @TODO For a class named View, this method is doing too much
	static public function validateLtiAndRenderAnyErrors($ltiData)
	{
		$ltiApi = \lti\API::getInstance();

		$valid = \lti\OAuth::validateLtiMessage($ltiData, \AppCfg::LTI_OAUTH_KEY, \AppCfg::LTI_OAUTH_SECRET, \AppCfg::LTI_OAUTH_TIMEOUT);
		if($valid instanceof \OAuthException)
		{
			if($valid->getCode() === OAUTH_TOKEN_EXPIRED)
			{
				static::renderExpiredError($ltiData);
			}
			else
			{
				static::renderUnexpectedError($ltiData, $valid->getMessage());
			}
		}
		else if(!$valid)
		{
			static::renderInvalidLTI($ltiData);
		}

		if(!$ltiData->hasValidUserData())
		{
			static::renderUnknownUserError($ltiData, 'invalid-user-data');
		}

		if(!$ltiApi->updateAndAuthenticateUser($ltiData))
		{
			static::renderUnknownUserError($ltiData, 'unable-to-update-and-authenticate');
		}

		if(!$ltiData->hasValidRole())
		{
			static::renderUnknownRoleError($ltiData);
		}
	}

	static public function renderInvalidLTI($ltiData)
	{
		\lti\Views::logError($ltiData);
		profile('lti',"'invalid-lti', '$ltiData->username', '$ltiData->email', '$ltiData->consumer', '$ltiData->resourceId', '".time()."'");
		$template = static::createErrorTemplate($ltiData);
		static::renderTemplate($template, 'lti-error-unknown-error');
	}

	static public function renderUnexpectedError($ltiData, $errorMessage)
	{
		\lti\Views::logError($ltiData);
		profile('lti',"'unexpected-error', '$ltiData->username', '$ltiData->email', '$ltiData->consumer', '$ltiData->resourceId', '$errorMessage', '".time()."'");
		$template = static::createErrorTemplate($ltiData);
		$template->assign('errorMessage', $errorMessage);
		static::renderTemplate($template, 'lti-error-unexpected-error');
	}

	static public function renderUnknownAssignmentError($ltiData, $isInstructor = false)
	{
		\lti\Views::logError($ltiData);
		profile('lti',"'unknown-assignment', '$ltiData->username', '$ltiData->email', '$ltiData->consumer', '$ltiData->resourceId', '".time()."'");
		$template = self::createErrorTemplate($ltiData);
		if($isInstructor)
		{
			self::renderTemplate($template, 'lti-error-unknown-assignment-instructor');
		}
		else
		{
			self::renderTemplate($template, 'lti-error-unknown-assignment');
		}
	}

	static public function renderUnknownRoleError($ltiData)
	{
		\lti\Views::logError($ltiData);
		profile('lti',"'unknown-role', '$ltiData->username', '$ltiData->email', '$ltiData->consumer', '$ltiData->resourceId', '".time()."'");
		$template = static::createErrorTemplate($ltiData);
		static::renderTemplate($template, 'lti-error-unknown-role');
	}

	static public function renderIncorrectRoleError($ltiData)
	{
		\lti\Views::logError($ltiData);
		profile('lti',"'incorrect-role', '$ltiData->username', '$ltiData->email', '$ltiData->consumer', '$ltiData->resourceId', '".implode(',', $ltiData->roles)."', '".time()."'");
		$template = static::createErrorTemplate($ltiData);
		static::renderTemplate($template, 'lti-error-incorrect-role');
	}

	static public function renderUnknownUserError($ltiData, $errorDetail='')
	{
		\lti\Views::logError($ltiData);
		profile('lti',"'unknown-user', '$errorDetail', '$ltiData->username', '$ltiData->email', '$ltiData->consumer', '$ltiData->resourceId', '".time()."'");
		$template = static::createErrorTemplate($ltiData);
		static::renderTemplate($template, 'lti-error-unknown-user');
	}

	static public function renderExpiredError($ltiData)
	{
		\lti\Views::logError($ltiData);
		profile('lti',"'expired', '$ltiData->username', '$ltiData->email', '$ltiData->consumer', '$ltiData->resourceId', '".time()."'");
		$template = static::createErrorTemplate($ltiData);
		static::renderTemplate($template, 'lti-error-expired');
	}

	static public function renderPicker($ltiInstanceToken, $returnUrl)
	{
		// render page:
		$smarty = \rocketD\util\Template::getInstance();
		$smarty->assign('ltiToken', $ltiInstanceToken);
		$smarty->assign('returnUrl', $returnUrl);
		$smarty->assign('webUrl', \AppCfg::URL_WEB);
		$response = $smarty->fetch(\AppCfg::DIR_BASE . \AppCfg::DIR_TEMPLATES . 'lti-picker.tpl');
		echo $response;
	}

	static public function renderTestUserConfirmPage($instanceData)
	{
		// render page:
		$smarty = \rocketD\util\Template::getInstance();
		$smarty->assign('instanceTitle', $instanceData->name);
		$smarty->assign('instanceCourseName', $instanceData->courseID);
		$smarty->assign('previewLink', \AppCfg::URL_WEB.'preview/'.$instanceData->loID);
		$response = $smarty->fetch(\AppCfg::DIR_BASE . \AppCfg::DIR_TEMPLATES . 'lti-test-confirmation.tpl');
		echo $response;
	}

	static protected function createErrorTemplate($ltiData)
	{
		$smarty = \rocketD\util\Template::getInstance();
		$smarty->assign('systemName', $ltiData->consumer);
		$smarty->assign('errorTemplatePath', \AppCfg::DIR_BASE . \AppCfg::DIR_TEMPLATES . 'lti-error.tpl');
		return $smarty;
	}

	static protected function renderTemplate($smarty, $templateFileName)
	{
		$response = $smarty->fetch(\AppCfg::DIR_BASE . \AppCfg::DIR_TEMPLATES . $templateFileName . '.tpl');
		echo $response;
		exit();
	}

	// @TODO MOVE THIS
	static public function logError($ltiData = false)
	{
		$session = isset($_SESSION) ? print_r($_SESSION, true) : '';
		profile('lti-dump', "[".date('r')." (".time().")"."] ltiData:\n".print_r($ltiData, true)."\nPOST:".print_r($_POST, true)."\nSESSION:".$session);
	}
}
