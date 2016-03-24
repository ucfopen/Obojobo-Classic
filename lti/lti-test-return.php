<?php
/*
This file can handle outbound lti score messages from the lti test interface.
 */
require_once("../internal/app.php");

# process headers if apache isn't around
if ( ! function_exists('apache_request_headers'))
{
	$headers = '';
	foreach ($_SERVER as $name => $value)
	{
		if (substr($name, 0, 5) == 'HTTP_')
		{
			$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
		}
	}
}
else
{
	# just use apache's request headers
	$headers = apache_request_headers();
}

// grab the oauth information:
$authHeaders = $headers['Authorization'];
$authHeaders = str_replace('OAuth ', '', $authHeaders);
$authHeaders = explode(',', $authHeaders);
$oauthData   = array();

foreach($authHeaders as $authHeader)
{
	$authHeader = explode('=', $authHeader);
	$oauthData[$authHeader[0]] = str_replace('"', '', urldecode($authHeader[1]));
}

// shove the oauth header info into the post, since the OAuth classes rely on that
foreach($oauthData as $oauthKey => $oauthValue)
{
	$_POST[$oauthKey] = $oauthValue;
}

$ltiData = new \lti\Data($_POST);

$valid = false;
// validate the oauth signature
if(\lti\OAuth::validateLtiMessage($ltiData, \AppCfg::LTI_OAUTH_KEY, \AppCfg::LTI_OAUTH_SECRET, \AppCfg::LTI_OAUTH_TIMEOUT) === true)
{
	// process the incoming data
	$body     = @file_get_contents('php://input');
	$xml      = simplexml_load_string($body);
	$inMsgId  = (string) $xml->imsx_POXHeader->imsx_POXRequestHeaderInfo->imsx_messageIdentifier;
	$sourceid = (string) $xml->imsx_POXBody->replaceResultRequest->resultRecord->sourcedGUID->sourcedId;
	$score    = (string) $xml->imsx_POXBody->replaceResultRequest->resultRecord->result->resultScore->textString;
	$outMsgId = uniqid();

	//$sm = \obo\ScoreManager::getInstance();
	//list($valid, $description) = $sm->submitLTIQuestion($sourceid, 'materia', $score);
	//
	$valid = true;
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

profile('lti-score', "'".time()."',self(test)','$sourceid','$score','$description','$success'");
