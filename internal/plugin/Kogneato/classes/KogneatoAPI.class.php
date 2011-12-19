<?php
class plg_Kogneato_KogneatoAPI extends \rocketD\plugin\PluginAPI
{
	// Block all API calls directly referencing this api

	const PUBLIC_FUNCTION_LIST = 'getKogneatoEngineLink'; // dont allow any direct calls
	private static $instance;
	static public function getInstance()
	{
		if(!isset(self::$instance))
		{
			$selfClass = __CLASS__;
			self::$instance = new $selfClass();
		}
		return self::$instance;
	}
	
	// build an SSO enabled url for the engine core from Kogneato that will automatically log us in there.
	// returns a url formatted like:  http://site.com/embeddedCore/344/iturgeon/12345324313/d41d8cd98f00b204e9800998ecf8427e
	//  siteUrl . GIID .'/'. username .'/'. timestamp .'/'. hash
	public function getKogneatoEngineLink($GIID, $preview=false)
	{
		$API = \obo\API::getInstance();
		
		// get current user
		$user = $API->getUser();
		
		// must be logged in to get a user back
		if($user instanceof \rocketD\auth\User)
		{
			$params = array();
			$params[plg_UCFAuth_SsoHash::SSO_USERID] = $user->login;
			$time = $params[plg_UCFAuth_SsoHash::SSO_TIMESTAMP] = time();
			
			$SSO = new plg_UCFAuth_SsoHash(\AppCfg::KOGNEATO_SSO_SECRET);
			$hash = $SSO->getSsoHash($params);

			return array('url' => \AppCfg::KOGNEATO_SSO_URL. ($preview ? 'preview/' : 'play/') .$GIID.'/'.$user->login.'/'. $time.'/'.$hash, 'GIID' => $GIID) ;
		}
		
		
		return $user; // not logged in - return error from $API->getUser
		
	}

	public function getKogneatoWidgetInfo($GIID)
	{
		$url = \AppCFG::KOGNEATO_JSON_URL . 'remote_getWidgetDescription/0/'.$GIID;

		$result = $this->send($url);

		// check for http response code of 200, TRY AGAIN if so
		if($result['responseCode'] != 200)
		{
			// log error
			\rocketD\util\Error::getError(1008, 'HTTP RESPONSE: ' . $result['responseCode'] . ' body: ' . $result['body']);
			trace('HTTP FAILURE ' . $REQUESTURL, true);
			return false;
		}
		
		$response = json_decode($result['body']);
		
		if(isset($response->description) && isset($response->description->GIID))
		{
			$wData = $response->description;
			return array('title' => $wData->GI_gameTitle, 'width' => $wData->GI_width, 'height' => $wData->GI_height, 'type' => $wData->GR_Name, 'flashVersion' => $wData->GR_flashVersion, 'owner' => $wData->name);
		}
		return false;
	}


	/**
	 * Sends an HTTP POST to the desired URL.  Requires PECL_HTTP http://pecl.php.net/package/pecl_http
	 *
	 * @param string $url    	Full URL to request
	 * @param array $postVars	associative array of post variables to send
	 * @return array 'responseCode' is the http response code (ie 200 or 404) 'body' is the body of the response
	 * @author Ian Turgeon
	 */
	protected function send($url, $postVars=false)
	{
		trace('Sending HTTPRequest ' . $url, true);
		try
		{
			$request = new \HttpRequest($url, HTTP_METH_POST);
			if(is_array($postVars))
			{
				$request->addPostFields($postVars);
			}
			$response = $request->send();
		}
		catch(Exception $e)
		{
			return array('responseCode' =>  0, 'body' => $e->getMessage());
		}
		return array('responseCode' =>  $request->getResponseCode(), 'body' => $request->getResponseBody());
	}

}
?>