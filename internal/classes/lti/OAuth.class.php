<?php
namespace Lti;

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
			return $provider->timestamp >= time() - \Lti\OAuth::$timeout ? OAUTH_OK : OAUTH_TOKEN_EXPIRED;
		};

		$consumerHandler = function($provider)
		{
			$provider->consumer_secret = \Lti\OAuth::$secret;

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
			"launch_presentation_return_url"         => \AppCfg::LTI_LAUNCH_PRESENTATION_RETURN_URL
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

		$streamHeaders = $request->to_header() . "\r\n" . 'Content-Type: application/xml' . "\r\n"; // add content type header

		// ================= SEND REQUEST =================================
		// try stream first
		$params = array('http' => array('method' => 'POST', 'content' => $body, 'header' => $streamHeaders)); 
		$streamContext = stream_context_create($params);
		$fp = @fopen($endpoint, 'rb', false, $streamContext);

		if($fp)
		{
			$response = @stream_get_contents($fp);
		}
		// fall back to pecl_http
		elseif(defined('HTTP_METH_POST'))
		{
			// create an keyed array 'name' => 'value'
			$headers = explode("\r\n", $streamHeaders);
			$peclHeaders = array();
			foreach($headers as $h)
			{
				if(!empty($h))
				{
					$name = substr($h, 0, strpos($h, ':'));
					$peclHeaders[$name] = substr($h, strpos($h, ':')+2);
				}
			}
			try
			{
				$request = new \HttpRequest($endpoint, HTTP_METH_POST);
				$request->setHeaders($peclHeaders);
				$request->setBody($body);

				$request->send();
				$response = $request->getResponseBody();
			}
			catch(Exception $e)
			{
				$response = false;
			}
		}

		if(!$response)
		{
			return false;
		}

		// supress errors so we can attempt to parse response without halting exec
		libxml_use_internal_errors(true);
		$xml = simplexml_load_string($response);
		if(!$xml)
		{
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
		}

		return $result;
	}
}
