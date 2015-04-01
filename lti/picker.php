<?php
require_once(dirname(__FILE__).'/../internal/app.php');

// Route the requests according to arguments sent
if (isset($_POST['selectedLoId']))
{
	createNewExternallyLinkedInstance();
}
else
{
	beginPickerSession();
}

function beginPickerSession()
{
	$ltiApi  = \lti\API::getInstance();
	$ltiData = new \lti\Data($_POST);

	\lti\Views::validateLtiAndRenderAnyErrors($ltiData);

	if (!$ltiData->isInstructor())
	{
		\lti\Views::renderIncorrectRoleError($ltiData);
	}

	$ltiInstanceToken = $ltiApi->storeLtiData($ltiData);
	if (!$ltiInstanceToken)
	{
		\lti\Views::renderUnexpectedError($ltiData, "Couldn't store LTI token in session");
	}

	\lti\Views::renderPicker($ltiInstanceToken, $ltiData->returnUrl);
}

function clearAssociatedInstance()
{
	$ltiApi           = \lti\API::getInstance();
	$selectedInstId   = $_POST['selectedInstId'];
	$ltiInstanceToken = $_POST['ltiInstanceToken'];
	$ltiData          = $ltiApi->restoreLtiData($ltiInstanceToken);

	if ( ! $ltiData)
	{
		echo false;
		return;
	}

	// @TODO: do we need to set the start/end times here?
	$instman = \obo\lo\InstanceManager::getInstance();
	$success = $instman->updateInstanceExternalLink($selectedInstId, '');

	if ($success instanceof \rocketD\util\Error || !$success)
	{
		echo json_encode(createResponse(false));
	}
	else
	{
		echo json_encode(createResponse(true, getInstanceData($selectedInstId)));
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

	$ltiApi           = \lti\API::getInstance();
	$ltiData          = $ltiApi->restoreLtiData($ltiInstanceToken);

	$courseName       = $ltiData->contextTitle;

	// create special instance
	$API = \obo\API::getInstance();
	//$name, $loID, $course, $startTime, $endTime, $attemptCount, $scoreMethod = 'h', $allowScoreImport = true
	$selectedInstId = $API->createInstance($instanceName, $selectedLoId, $courseName, 0, 0, $attempts, $scoreMethod, $allowScoreImport);
	if ($selectedInstId instanceof \rocketD\util\Error || !is_int($selectedInstId))
	{
		echo false;
		return;
	}

	$success = $ltiApi->updateExternalLinkForInstance($selectedInstId, $ltiData);
	if($success instanceof \rocketD\util\Error || !$success)
	{
		echo json_encode(createResponse(false));
	}
	else
	{
		//@TODO - what happens here if instanceData is false?
		echo json_encode(createResponse(true, getInstanceData($selectedInstId)));
	}

	//echo json_encode(createResponse(true, getInstanceData($selectedInstId)));
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
