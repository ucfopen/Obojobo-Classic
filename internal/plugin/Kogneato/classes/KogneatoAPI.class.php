<?php
class plg_Kogneato_KogneatoAPI extends core_plugin_PluginAPI
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
		$API = nm_los_API::getInstance();
		
		// get current user
		$user = $API->getUser();
		
		// must be logged in to get a user back
		if($user instanceof core_auth_User)
		{
			$params = array();
			$params[plg_UCFAuth_SsoHash::SSO_USERID] = $user->login;
			$time = $params[plg_UCFAuth_SsoHash::SSO_TIMESTAMP] = time();
			
			$SSO = new plg_UCFAuth_SsoHash(AppCfg::KOGNEATO_SSO_SECRET);
			$hash = $SSO->getSsoHash($params);

			return array('url' => AppCfg::KOGNEATO_SSO_URL. ($preview ? 'preview/' : 'play/') .$GIID.'/'.$user->login.'/'. $time.'/'.$hash, 'GIID' => $GIID) ;
		}
		
		
		return $user; // not logged in - return error from $API->getUser
		
	}
}
?>