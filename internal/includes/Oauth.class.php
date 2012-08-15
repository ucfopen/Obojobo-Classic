<?php
namespace LTI;
class Oauth
{

	public static function validatePost()
	{
		$timestampNonceChecker = function($provider)
		{
			return ($provider->timestamp >= time() - \AppCfg::MATERIA_LTI_TIMELIMIT) ? OAUTH_OK : OAUTH_TOKEN_EXPIRED;
		};

		$consumerHandler = function($provider)
		{
			$provider->consumer_secret = \AppCfg::MATERIA_LTI_SECRET;
			return OAUTH_OK;
		};

		// ===============  VALIDATE THE OAUTH SIG ===============
		try
		{
			$op = new \OAuthProvider();
			$op->consumerHandler($consumerHandler);
			$op->timestampNonceHandler($timestampNonceChecker);
			$op->is2LeggedEndpoint(true);
			$result = $op->checkOAuthRequest();
			return true;
		}
		catch (\OAuthException $e)
		{
			trace('rdLTI OAuth invalid');
			trace(\OAuthProvider::reportProblem($e));
			return false;
		}
	}

	public static function buildPostArgs($endpoint, $params, $key, $secret, $enablePassback)
	{
		$api = \obo\API::getInstance();
		$me = $api->getUser();
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

			"user_id"                                => $me->userID,
			
			"lis_person_sourcedid"                   => $me->login,
			"lis_person_contact_email_primary"       => $me->email,

			"launch_presentation_document_target"    => 'iframe',
			"launch_presentation_return_url"         => \AppCfg::URL_WEB . 'lti/return',
		);

		$params = array_merge($params, $oauthParams);

		if($enablePassback) $params['lis_outcome_service_url'] = \AppCfg::URL_WEB . 'lti/grade-passback.php';

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

	public static function sendBodyHashedPOST($endpoint, $body, $secret)
	{
		// ================ BUILD OAUTH REQUEST =========================
		include_once(\AppCfg::DIR_BASE . \AppCfg::DIR_SCRIPTS . 'oauth.php');

		$bodyHash = base64_encode(sha1($body, TRUE)); // build body hash
		$consumer = new \OAuthConsumer('', $secret); // create the consumer

		$request = \OAuthRequest::from_consumer_and_token($consumer, '', 'POST', $endpoint, array('oauth_body_hash' => $bodyHash) );
		$request->sign_request(new \OAuthSignatureMethod_HMAC_SHA1(), $consumer, '');

		$streamHeaders = $request->to_header() . "\r\nContent-Type: application/xml\r\n"; // add content type header

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
				trace($e);
				$response = false;
			}
		}

		// success ?
		if($response)
		{
			$xml = simplexml_load_string($response);
			$success = $xml->imsx_POXHeader->imsx_POXResponseHeaderInfo->imsx_statusInfo->imsx_codeMajor;
			return !empty($success) && $success[0] == 'success';
		}
		
		return false;
	}

}