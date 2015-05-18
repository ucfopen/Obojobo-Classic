<?php
namespace lti;

class OAuth
{
	public static $ltiData;
	public static $key;
	public static $secret;
	public static $timeout;

	public static function validateLtiMessage($ltiData, $key, $secret, $timeout)
	{
		if(!$ltiData->oauthNonce)
		{
			return false;
		}

		self::$ltiData = $ltiData;
		self::$key     = $key;
		self::$secret  = $secret;
		self::$timeout = $timeout;

		$timestampChecker = function($provider)
		{
			//@TODO - Check to see if nonce is already used
			return $provider->timestamp >= time() - \lti\OAuth::$timeout ? OAUTH_OK : OAUTH_TOKEN_EXPIRED;
		};

		$consumerHandler = function($provider)
		{
			$provider->consumer_secret = \lti\OAuth::$secret;

			return OAUTH_OK;
		};

		// ===============  VALIDATE THE OAUTH SIG ===============
		try
		{
			$op = new \OAuthProvider();
			$op->consumerHandler($consumerHandler);
			$op->timestampNonceHandler($timestampChecker);
			$op->is2LeggedEndpoint(true);
			//return true;
			$op->checkOAuthRequest();
			return true;
		}
		catch (\OAuthException $e)
		{
			return $e;
		}

		\lti\Views::logError($ltiData);
		return false;
	}

	public static function buildPostArgs($user, $endpoint, $params, $key, $secret, $outcomeUrl = false)
	{
		$roleMan = \obo\perms\RoleManager::getInstance();

		$oauthParams = array(
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
		);

		if($outcomeUrl)
		{
			$oauthParams['lis_outcome_service_url'] = $outcomeUrl;
		}

		$params = array_merge($params, $oauthParams);

		include_once(\AppCfg::DIR_BASE . \AppCfg::DIR_SCRIPTS . 'oauth.php');

		$consumer = new \OAuthConsumer('', $secret); // create the consumer
		$request = \OAuthRequest::from_consumer_and_token($consumer, '', 'POST', $endpoint );
		foreach($params as $key => $val)
		{
			$request->set_parameter($key, $val, false);
		}
		$request->sign_request(new \OAuthSignatureMethod_HMAC_SHA1(), $consumer, '');

		return $request->get_parameters();
	}

	// Returns a result array containing a success and error property.
	// If it worked success = true and error = false.
	// If it failed success = false and error holds a string of why it failed
	public static function sendBodyHashedPOST($endpoint, $body, $secret)
	{
		// ================ BUILD OAUTH REQUEST =========================
		include_once(\AppCfg::DIR_BASE . \AppCfg::DIR_SCRIPTS . 'oauth.php');

		$bodyHash = base64_encode(sha1($body, TRUE)); // build body hash
		$consumer = new \OAuthConsumer('', $secret); // create the consumer

		$request = \OAuthRequest::from_consumer_and_token($consumer, '', 'POST', $endpoint, array('oauth_body_hash' => $bodyHash) );
		$request->sign_request(new \OAuthSignatureMethod_HMAC_SHA1(), $consumer, '');

		$response = false;
		$timeouts = array(10, 25, 35);
		foreach ($timeouts as $timeoutIndex => $timeout)
		{
			$attemptCount = $timeoutIndex + 1;

			// ================= SEND REQUEST =================================
			$streamHeaders = $request->to_header() . "\r\n" . 'Content-Type: application/xml' . "\r\n"; // add content type header
			$params = array('http' => array('timeout' => $timeout, 'method' => 'POST', 'content' => $body, 'header' => $streamHeaders));
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

		$result = array(
			'success' => !empty($codeMajorValue) && $codeMajorValue[0] == 'success',
			'error'   => false
		);

		if(!$result['success'])
		{
			$result['error'] = $xml->imsx_POXHeader->imsx_POXResponseHeaderInfo->imsx_statusInfo->imsx_description;
			profile('lti-dump', "[".date('r')." (".time().")"."] Result Error:\n".print_r($response, true)."\nBody:".print_r($body, true));
		}

		return $result;
	}
}
