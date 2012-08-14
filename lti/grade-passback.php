<?php
require_once("../internal/app.php");
include_once(\AppCfg::DIR_BASE . \AppCfg::DIR_SCRIPTS . 'Oauth.class.php');
include_once(\AppCfg::DIR_BASE . \AppCfg::DIR_SCRIPTS . 'smarty/Smarty.class.php');

$valid = false;
$description = "Invalid Oauth Signature";

// validate the oauth signature
if(\LTI\Oauth::validatePost() === true)
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
$smarty = new \Smarty();
$smarty->compile_dir = \AppCfg::DIR_BASE . \AppCfg::DIR_TEMPLATES . 'compiled/';
$smarty->assign('messsageIdOut', $outMsgId);
$smarty->assign('messsageIdIn', $inMsgId);
$smarty->assign('description', $description);
$smarty->assign('success', $success);
$response = $smarty->fetch(\AppCfg::DIR_BASE . \AppCfg::DIR_TEMPLATES . 'lti-replaceResult-response.tpl');

header('Content-Type: application/xml');
echo $response;
\rocketD\util\Log::profile('lti-score', "'".time()."',materia','$sourceid','$score','$description','$success'\n");