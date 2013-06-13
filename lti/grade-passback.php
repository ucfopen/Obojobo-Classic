<?php
require_once("../internal/app.php");

// Fallback code to handle empty POST:
if(empty($_POST) && function_exists('apache_request_headers'))
{
	// grab the oauth information:
	$headers     = apache_request_headers();
	$authHeaders = $headers['Authorization'];
	$authHeaders = str_replace('OAuth ', '', $authHeaders);
	$authHeaders = explode(',', $authHeaders);
	$oauthData   = array();
	foreach($authHeaders as $authHeader)
	{
		$authHeader = explode('=', $authHeader);
		$oauthData[$authHeader[0]] = str_replace('"', '', urldecode($authHeader[1]));
	}
	$body = file_get_contents("php://input");
	// shove the oauth header info into the post, since the OAuth classes rely on that
	foreach($oauthData as $oauthKey => $oauthValue)
	{
		$_POST[$oauthKey] = $oauthValue;
	}
}

$valid = false;
$description = "Invalid Oauth Signature";

$ltiData = new \Lti\Data($_POST);

$outMsgId = 0;

// validate the oauth signature
if(\Lti\OAuth::validateLtiMessage($ltiData, \AppCfg::MATERIA_LTI_KEY, \AppCfg::MATERIA_LTI_SECRET, \AppCfg::MATERIA_LTI_TIMELIMIT) === true)
{
	// process the incoming data
	$body       = @file_get_contents('php://input');
	$xml        = simplexml_load_string($body);
	$inMsgId    = (string) $xml->imsx_POXHeader->imsx_POXRequestHeaderInfo->imsx_messageIdentifier;
	$sourceid   = (string) $xml->imsx_POXBody->replaceResultRequest->resultRecord->sourcedGUID->sourcedId;
	$score      = (string) $xml->imsx_POXBody->replaceResultRequest->resultRecord->result->resultScore->textString;
	$outMsgId   = uniqid();

	$sm = \obo\ScoreManager::getInstance();
	list($valid, $description) = $sm->submitLTIQuestion($sourceid, 'materia', $score);
}

// build response
$success = $valid ? "success" : "failure";
$smarty = \rocketD\util\Template::getInstance();
$smarty->assign('messsageIdOut', $outMsgId);
$smarty->assign('messsageIdIn', $inMsgId);
$smarty->assign('description', $description);
$smarty->assign('success', $success);
$response = $smarty->fetch(\AppCfg::DIR_BASE . \AppCfg::DIR_TEMPLATES . 'lti-replaceResult-response.tpl');

header('Content-Type: application/xml');
echo $response;
\rocketD\util\Log::profile('lti-score', "'".time()."',materia','$sourceid','$score','$description','$success'");