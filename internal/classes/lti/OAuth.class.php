<?php
namespace lti;

class Exception extends \Exception {}

class OAuth
{
	public static $ltiData;
	public static $key;
	public static $secret;
	public static $timeout;


	public static function validateLtiPassback($key, $secret, $timeout)
	{
		$hmcsha1   = new \Eher\OAuth\HmacSha1();
		$consumer  = new \Eher\OAuth\Consumer($key, $secret);
		$request   = \Eher\OAuth\Request::from_request();
		$signature = $request->build_signature($hmcsha1, $consumer, false);
		$params    = $request->get_parameters();

		// check the oauth signature
		if($signature !== $params['oauth_signature']) throw new Exception("Authorization signatures don't match.");

		// check the body hash
		$body_hash = base64_encode(sha1(file_get_contents('php://input'), TRUE));
		if($body_hash !== $params['oauth_body_hash']) throw new Exception("Authorization hashes don't match.");

		return true;
	}

	public static function validateLtiMessage($ltiData, $key, $secret, $timeout)
	{
		if(empty($_REQUEST['oauth_nonce'])) throw new Exception("Authorization fingerprint is missing.");
		// IS THE OAUTH TIMESTAMP LESS THEN THE OLDEST VALID TIMESTAMP (NOW - MAX AGE DELTA)
		if(((int) $_REQUEST['oauth_timestamp']) < (time() - \lti\OAuth::$timeout)) throw new Exception("Authorization signature is too old.");
		// if($_REQUEST['oauth_timestamp'] >= (time() - \lti\OAuth::$timeout)) throw new Exception("Authorization signature is too old.");
		if($_REQUEST['oauth_consumer_key'] !== $key) throw new Exception("Authorization signature failure.");

		// OK, so OAUTH IS FUN
		// nginx has a redirect from /view/222 to /viewer.php?instID=222
		// OAUTH uses all get and post params for a signature
		// SO, inbound messages from canvas do not contain an instID variable
		// BUT when we get them here, it looks like we have an instID variable
		// by default - oauth includes this, and builds a signature, and we don't match what canvas sends.
		// SO, let's fix that just in case
		$filtered_params = $_REQUEST;
		// if instID= is in the request, it's an old lti/assignment.php?instID= url
		// new REQUEST_URI's will be /view/333
		if(strpos($_SERVER['REQUEST_URI'], 'instID=') == false)
		{
			unset($filtered_params['instID']);
		}

		$hmcsha1   = new \Eher\OAuth\HmacSha1();
		$consumer  = new \Eher\OAuth\Consumer($key, $secret);
		$request   = \Eher\OAuth\Request::from_request(null, null, $filtered_params);
		$signature = $request->build_signature($hmcsha1, $consumer, false);

		if($signature !== $_REQUEST['oauth_signature']) throw new Exception("Authorization signatures don't match.");

		return true;
	}

	public static function buildPostArgs($user, $endpoint, $params, $key, $secret, $outcomeUrl = false)
	{
		$oauthParams = [
			"oauth_consumer_key"                     => $key,
			"oauth_nonce"                            => uniqid(),
			"oauth_timestamp"                        => time(),

			"lti_version"                            => 'LTI-1p0',
			"lti_message_type"                       => 'basic-lti-launch-request',

			"tool_consumer_instance_guid"            => '2e812611fbd3706f68086989f9d5977a.obojobo.ucf.edu',
			"tool_consumer_info_product_family_code" => 'obojobo',
			"tool_consumer_instance_contact_email"   => 'newmedia@ucf.edu',
			"tool_consumer_info_version"             => '2.0',

			"user_id"                                => $user->userID,

			"lis_person_sourcedid"                   => $user->login,
			"lis_person_contact_email_primary"       => $user->email,
			"lis_person_name_given"                  => $user->first,
			"lis_person_name_family"                 => $user->last,

			"launch_presentation_document_target"    => 'iframe',
			"launch_presentation_return_url"         => \AppCfg::URL_WEB.\AppCfg::LTI_LAUNCH_PRESENTATION_RETURN_URL
		];

		if($outcomeUrl)
		{
			$oauthParams['lis_outcome_service_url'] = $outcomeUrl;
		}

		$params   = array_merge($params, $oauthParams);
		$hmcsha1  = new \Eher\OAuth\HmacSha1();
		$consumer = new \Eher\OAuth\Consumer('', $secret);
		$request  = \Eher\OAuth\Request::from_consumer_and_token($consumer, '', 'POST', $endpoint);
		foreach($params as $key => $val)
		{
			$request->set_parameter($key, $val, false);
		}
		$request->sign_request($hmcsha1, $consumer, '');

		return $request->get_parameters();
	}

	// Returns a result array containing a success and error property.
	// If it worked success = true and error = false.
	// If it failed success = false and error holds a string of why it failed
	public static function sendBodyHashedPOST($endpoint, $body, $secret)
	{
		// ================ BUILD OAUTH REQUEST =========================

		$bodyHash = base64_encode(sha1($body, TRUE)); // build body hash
		$hmcsha1  = new \Eher\OAuth\HmacSha1();
		$consumer = new \Eher\OAuth\Consumer('', $secret);
		$request  = \Eher\OAuth\Request::from_consumer_and_token($consumer, '', 'POST', $endpoint, ['oauth_body_hash' => $bodyHash]);
		$request->sign_request($hmcsha1, $consumer, '');

		$response = false;
		$timeouts = [10, 25, 35];
		foreach ($timeouts as $timeoutIndex => $timeout)
		{
			$attemptCount = $timeoutIndex + 1;

			// ================= SEND REQUEST =================================
			$streamHeaders = $request->to_header() . "\r\n" . 'Content-Type: application/xml' . "\r\n"; // add content type header
			$params = ['http' => ['timeout' => $timeout, 'method' => 'POST', 'content' => $body, 'header' => $streamHeaders]];
			$streamContext = stream_context_create($params);
			$fp = @fopen($endpoint, 'rb', false, $streamContext);

			if(!$fp)
			{
				// No way to contact server, so write it in the log!
				profile('lti-dump', "[".date('r')." (".time().")"."] ATTEMPT $attemptCount FAIL: Can't Send Data:\nBody:".print_r($body, true)."\nLast Error:".print_r(error_get_last(), true));
				continue; // Stop this attempt, try again
			}

			if($response = @stream_get_contents($fp)) break; //Success

			// This attempt didn't work - log the error and try again
			profile('lti-dump', "[".date('r')." (".time().")"."] ATTEMPT $attemptCount FAIL: OAUTH no Response:\nBody:".print_r($body, true)."\nLast Error:".print_r(error_get_last(), true));
		}

		if(!$response)
		{
			profile('lti-dump', "[".date('r')." (".time().")"."] OUT OF ATTEMPTS");
			return false;
		}

		// supress errors so we can attempt to parse response without halting exec
		libxml_use_internal_errors(true);
		$xml = simplexml_load_string($response);
		if(!$xml)
		{
			profile('lti-dump', "[".date('r')." (".time().")"."] Unable to read XML:\n".print_r($response, true)."\nBody:".print_r($body, true));
			return false;
		}

		$codeMajorValue = $xml->imsx_POXHeader->imsx_POXResponseHeaderInfo->imsx_statusInfo->imsx_codeMajor;

		$result = [
			'success' => !empty($codeMajorValue) && $codeMajorValue[0] == 'success',
			'error'   => false
		];

		if(!$result['success'])
		{
			$result['error'] = $xml->imsx_POXHeader->imsx_POXResponseHeaderInfo->imsx_statusInfo->imsx_description;
			profile('lti-dump', "[".date('r')." (".time().")"."] Result Error:\n".print_r($response, true)."\nBody:".print_r($body, true));
		}

		return $result;
	}
}
