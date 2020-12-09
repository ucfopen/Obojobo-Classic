<?php
require_once(dirname(__FILE__).'/../internal/app.php');

// Route the requests according to arguments sent
if (isset($_POST['selectedLoId']))
{
	if($_POST['ltiInstanceToken'] === 'repository'){
		createNewPlainInstance();
	}
	else
	{
		createNewExternallyLinkedInstance();
	}

}
elseif(isset($_GET['repository']))
{
	renderPickerForRepository();
}
else
{
	beginPickerSession();
}

function renderPickerForRepository()
{
	$smarty = \rocketD\util\Template::getInstance();
	$smarty->assign('ltiToken', 'repository');
	$smarty->assign('returnUrl', '/');
	$smarty->assign('webUrl', \AppCfg::URL_WEB);
	$response = $smarty->fetch(\AppCfg::DIR_BASE . \AppCfg::DIR_TEMPLATES . 'lti-picker.tpl');
	echo $response;
}

function beginPickerSession()
{
	$ltiData = new \lti\Data($_POST);

	\lti\Views::validateLtiAndRenderAnyErrors($ltiData);

	if (!$ltiData->isInstructor())
	{
		\lti\Views::renderIncorrectRoleError($ltiData);
	}

	$ltiInstanceToken = \lti\API::storeLtiData($ltiData);
	if (!$ltiInstanceToken)
	{
		\lti\Views::renderUnexpectedError($ltiData, "Couldn't store LTI token in session");
	}

	\lti\Views::renderPicker($ltiInstanceToken, $ltiData->returnUrl);
}

function clearAssociatedInstance()
{
	$selectedInstId   = $_POST['selectedInstId'];
	$ltiInstanceToken = $_POST['ltiInstanceToken'];
	$ltiData          = \lti\API::restoreLtiData($ltiInstanceToken);

	if ( ! $ltiData)
	{
		echo(false);
		return;
	}

	// @TODO: do we need to set the start/end times here?
	$instman = \obo\lo\InstanceManager::getInstance();
	$success = $instman->updateInstanceExternalLink($selectedInstId, '');

	if ($success instanceof \rocketD\util\Error || !$success)
	{
		echo(json_encode(createResponse(false)));
	}
	else
	{
		echo(json_encode(createResponse(true, getInstanceData($selectedInstId))));
	}
}

function createNewExternallyLinkedInstance()
{
	$selectedLoId     = $_POST['selectedLoId'];
	$ltiInstanceToken = $_POST['ltiInstanceToken'];
	$instanceName     = $_POST['instanceName'];
	$attempts         = $_POST['attempts'];
	$scoreMethod      = $_POST['scoreMethod'];
	$allowScoreImport = $_POST['allowScoreImport'] === 'true';

	$ltiData          = \lti\API::restoreLtiData($ltiInstanceToken);

	$courseName       = $ltiData->contextTitle;

	// create special instance
	$API = \obo\API::getInstance();
	//$name, $loID, $course, $startTime, $endTime, $attemptCount, $scoreMethod = 'h', $allowScoreImport = true
	$selectedInstId = $API->createInstance($instanceName, $selectedLoId, $courseName, 0, 0, $attempts, $scoreMethod, $allowScoreImport);
	if ($selectedInstId instanceof \rocketD\util\Error || !is_int($selectedInstId))
	{
		echo(false);
		return;
	}

	$success = \lti\API::updateExternalLinkForInstance($selectedInstId, $ltiData);
	if($success instanceof \rocketD\util\Error || !$success)
	{
		echo(json_encode(createResponse(false)));
	}
	else
	{
		//@TODO - what happens here if instanceData is false?
		echo(json_encode(createResponse(true, getInstanceData($selectedInstId))));
	}
}

function createNewPlainInstance()
{
	$selectedLoId     = $_POST['selectedLoId'];
	$instanceName     = $_POST['instanceName'];
	$attempts         = $_POST['attempts'];
	$scoreMethod      = $_POST['scoreMethod'];
	$allowScoreImport = $_POST['allowScoreImport'] === 'true';

	// create special instance
	$API = \obo\API::getInstance();
	$startTime = time();
	$endTime = new DateTime("+1 year");
	$endTime = $endTime->getTimestamp();

	//$name, $loID, $course, $startTime, $endTime, $attemptCount, $scoreMethod = 'h', $allowScoreImport = true
	$selectedInstId = $API->createInstance($instanceName, $selectedLoId, '', $startTime, $endTime, $attempts, $scoreMethod, $allowScoreImport);
	if ($selectedInstId instanceof \rocketD\util\Error || !is_int($selectedInstId))
	{
		echo(false);
		return;
	}

	echo(json_encode(createResponse(true, getInstanceData($selectedInstId))));
}

function getInstanceData($instID)
{
	$API = \obo\API::getInstance();
	return $API->getInstanceData($instID);
}

function createResponse($success, $instData = false)
{
	$response = array(
		'body'    => false,
		'success' => $success,
	);

	if ($success)
	{
		$response['body'] = $instData;
	}

	return $response;
}
