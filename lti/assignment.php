<?php
require_once(dirname(__FILE__) . "/../internal/app.php");

$ltiApi = \lti\API::getInstance();
$ltiData = new \lti\Data($_POST);

\rocketD\util\Log::profile('lti',"'assignment-visit', '$_SERVER[REQUEST_URI]', '$ltiData->remoteId', '$ltiData->username', '$ltiData->email', '$ltiData->consumer', '$ltiData->resourceId', '".time()."'");

// make sure required instID parameter is present
if(!isset($_GET['instID']) || !is_numeric($_GET['instID']))
{
	\lti\Views::renderUnknownAssignmentError($ltiData, $ltiData->isInstructor());
}
$originalInstID = $_GET['instID'];

// show error if any values are invalid
\lti\Views::validateLtiAndRenderAnyErrors($ltiData);

// create lti association and duplicate the instance if required
$instID = $ltiApi->createLtiAssociationIfNeeded($originalInstID, $ltiData);
if($instID instanceof \obo\util\Error)
{
	\lti\Views::renderUnexpectedError($ltiData, $instID->message);
}
else if(!$instID || !is_numeric($instID))
{
	\lti\Views::renderUnexpectedError($ltiData, 'Unexpected return value when potentially creating new LTI association.');
}

// Depending on role we want to either show preview mode or the actual instance:
if($ltiData->isInstructor())
{
	$API = \obo\API::getInstance();
	$instanceData = $API->getInstanceData($instID);
	if(!$instanceData || !isset($instanceData->instID))
	{
		\lti\Views::renderUnknownAssignmentError($ltiData, true);
	}

	$loID = $instanceData->loID;

	// We want to store in some additional permissions info in
	// the session so this gives the instructor a way to be
	// able to view the instance in Obojobo
	if(!empty($ltiData->username))
	{
		$AM = \rocketD\auth\AuthManager::getInstance();
		$user = $AM->fetchUserByUserName($ltiData->username);

		$PM = \obo\perms\PermManager::getInstance();
		$PM->setSessionPermsForUserToItem($user->userID, \cfg_core_Perm::TYPE_INSTANCE, $instID, array(20));
	}

	// show the preview:
	$previewURL = \AppCfg::URL_WEB . 'preview/' . $loID;
	\rocketD\util\Log::profile('lti',"'assignment-visit-redirect', '$previewURL', '".time()."'");
	header('Location: ' . $previewURL);
}
else
{
	$ltiApi->initAssessmentSession($instID, $ltiData);

	// show the instance:
	// Passing in consumer here so that the viewer page can better determine that this url came from obo embedded in an external system
	$viewURL = \AppCfg::URL_WEB . 'view/' . $instID . '?consumer='.$ltiData->consumer;
	\rocketD\util\Log::profile('lti',"'assignment-visit-redirect', '$viewURL', '".time()."'");
	header('Location: ' . $viewURL);
}

