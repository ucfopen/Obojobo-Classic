<?
/*
	Single Signon Hash Utility
	
	This class is a implementation class to provide CDWS single signon authentication. 
	Currently, it supports message digest for MD5 (default) and SHA-1. Critical error
	will throws exception. The non critical ones are available through getWarningMessage().
 	
	This utility handles the generating and validating the single sign-on hash. The
	hash is generated from concatenating and message digesting a set of parameters,
	which need to be validated for no alternation, and a shared secret. During validation,
	it checks the hash value from the url parameter and the hash value created locally.
	
	The format of the secret is: 1234<PARAM1>098<PARAM2>44444...
	
	where <PARAM> is a hash parameter can be set in the secret string. Table below shows 
	available predefined parameters.  You can add your own parameter in the secret as long as 
	the URL parameter name in the request match the hash parameter name in the secret. 
	
	Parameter Name		Description			URL parameter	Hash creation parameter
	-----------------------------------------------------------------------------
	userid				User ID					x*					x*
	timestamp			Time stamp				x*					x*	
	name				User Full Name			x					x	
	ip					User's IP addresses		x					x	
	roles				User's roles			x					x	
	hash				SSO authentication hash	x*		
 	
 	* Required fields

	Here are the code snippet on using SsoHash class.
	
	For sender application generating the parameter set with SSO credential:
	
	$sso = new SsoHash("somesecret");
	$id = "ab11111";
	$tstamp = time();
	$params = array();
	$params[SsoHash::SSO_USERID] = $id;
	$params[SsoHash::SSO_TIMESTAMP] = $tstamp;
	$url_str = $sso->getSsoOutParametersAsString($params);
	
	For receiver application validating the SSO credential from a request:
	
	$sso = new SsoHash("somesecret");
	$sso_req = $sso->getSsoInParametersFromRequest();
	$valid = $sso->validateSSOHash($sso_req);
	
	if($valid) {
		$userid = $sso_req[SsoHash::SSO_USERID];
		...
	} else {
		...
	}
*/

class plg_UCFAuth_SsoHash {
	private $secret;			// shared secret
	private $schema = "MD5";	// message digest schema
	private $sso_timeout = 60;	// seconds
	private $warning_msg;		// Warning message during processing
	
	// SSO parameters names constants
	const SSO_USERID = "userid";
	const SSO_TIMESTAMP = "timestamp";
	const SSO_NAME = "name";
	const SSO_ROLES = "roles";
	const SSO_IP = "ip";
	const SSO_HASH = "hash";
	const SSO_RKEY = "rkey";
	
	public function __construct($secret) {
		if( !preg_match('/<'.plg_UCFAuth_SsoHash::SSO_USERID.'>/', $secret) &&	!preg_match('/<'.plg_UCFAuth_SsoHash::SSO_TIMESTAMP.'>/',$secret) ) 
		{
			throw new Exception("Secret is not in the correct format");
		}
		$this->secret = $secret;
	}
	
    /*
     * Get SSO hash.
     * 
     * Param	$params		Array contains SSO data
     * Returns 	hex value of the SSO hash.
     */	
	public function getSsoHash($params) {
		return $this->generateHash($params);	
	}
	
	/*
	 * Get SSO data as array (including hash).
	 * 
     * Param	$params		Array contains SSO data
     * Returns 	Array contains SSO data (including hash). 
	 */	
	public function getSsoOutParametersAsArray($params) {
		$params[plg_UCFAuth_SsoHash::SSO_HASH] = $this->getSsoHash($params);
		return $params;		
	}
	
	/*
	 * Get URL encoded SSO data as string (including hash).
	 * 
     * Param	$params		Array contains SSO data
     * Returns 	URL encoded SSO string (including hash). 
	 */	
	public  function getSsoOutParametersAsString($params) {
		$params[plg_UCFAuth_SsoHash::SSO_HASH] = $this->getSsoHash($params);
		return $this->convertMap2String($params);	
	}
	
	/*
	 * Convert array to url encoded string
	 * 
	 * Param	$params		Array
	 * Returns	URL encoded string
	 */
	public function convertMap2String($arr) {
		$params_str = "";
		$keys = array_keys($arr);
		for($i =0; $i < count($keys); $i++) {
			$params_str .= urlencode($keys[$i]) .'='. urlencode($arr[$keys[$i]]).'&';
		}
		return rtrim($params_str,'&');
	}
	
	/*
	 * Get SSO related parameters from HTTP request. Exception will be thrown
	 * if the $_REQUEST data are invalid.
	 * 
	 * Returns	SSO related parameters as array
	 */
	public function getSsoInParametersFromRequest() {
		return $this->getSsoParametersFromReq();
	}
	
	/*
	 * Validate SSO request by $_REQUEST. Exception will be thrown if the $_REQUEST 
	 * data are invalid.
	 * 
	 * Returns true if valid.
	 */
	public function validateSSOHashFromRequest() {
		$sso_req = $this->getSsoInParametersFromRequest();
		
		// Check IP if provided.
		if(!is_null($sso_req[self::SSO_IP]) && 
				$_SERVER['REMOTE_ADDR'] != $sso_req[self::SSO_IP]) {
			return false;
		}
		return $this->validateSSOHash($sso_req);
	}
	
	/*
	 * Validate SSO request by array. Exception will be thrown if the $params 
	 * are invalid.
	 * 
	 * Param	$params		Array contains SSO data
	 * Returns true if valid.
	 */
	public function validateSSOHash($params) {
		return $this->validate($params);
	}
	
	/*
	 * Set the message digest schema.
	 * 
	 * Possible values: MD5, SHA1
	 * 
	 */
	public function setMessageDigestSchema($schema ) {
		$this->schema = $schema;
	}
	
	/*
	 * Set the SSO timeout value
	 * 
	 * Default: 60 seconds
	 */
	public function setTimeout($sso_timeout ) {
		$this->sso_timeout = $sso_timeout;
	}

	public function getMessageDigestSchema() {
		return $this->schema;
	}
	
	public function getWarningMessage() {
		return $this->warning_msg;
	}
	
	public function getTimeout() {
		return $this->sso_timeout;
	}
	
	function __destruct() {
        $this->secret = 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX';	// erase secret
    }

/* 
 * Private methods
 * 
 */	
	private function checkInput($data, $pattern) {
		if(is_null($data) || is_null($pattern)) {
			return false;
		}
		return preg_match($pattern,$data);
	}
	private function generateHash($params) {
		$data = $this->secret;
		$outstr = "";
		
		// Replace key tokens
		$keys = array_keys($params);
		for($i =0; $i < count($keys); $i++) {
			$data = str_ireplace('<'.$keys[$i].'>',$params[$keys[$i]],$data);
		}
		
		// Set timestamp if it is in secret but is missing in the parameter list.
		if(stripos($data,self::SSO_TIMESTAMP) > 0) {
			$timestamp = time();
			$params[$this->SSO_TIMESTAMP] = $timestamp;
			$data = str_ireplace('<'.$this->SSO_TIMESTAMP.'>',$timestamp,$data);			
		}

		// Do the MD
		if($this->schema == "MD5") { 
			$outstr = md5($data);
		} else if($this->schema == "SHA1") { 
			$outstr = sha1($data);
		} else {
			throw new Exception('Invalid Message Digest');
		}
		$data = 'YYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYY'; //erase data
		return $outstr;
	}

	private function getSsoParametersFromReq() {
		$params = array();
		$keys = array_keys($_REQUEST);
		for($i =0; $i < count($keys); $i++) {
			$params[$keys[$i]] = $_REQUEST[$keys[$i]];
		}

		if(!$this->checkInput($_REQUEST[self::SSO_USERID],"/[[:alnum:]]{2,15}/")) {
			unset($param[self::SSO_USERID]);
			throw new Exception("Invalid user id: " + 
					urlencode($_REQUEST[self::SSO_USERID]));
		}
		
		if(!$this->checkInput($_REQUEST[self::SSO_TIMESTAMP],"/[[:digit:]]{10,11}/")) {
			unset($param[self::SSO_TIMESTAMP]);
			throw new Exception("Invalid timestamp: " + 
					urlencode($_REQUEST[self::SSO_TIMESTAMP]));
		}
		
		if(!$this->checkInput($_REQUEST[self::SSO_NAME],"/[[:graph:]]{2,100}/")) {
			unset($param[self::SSO_NAME]);
			$warning_msg = "Invalid user name info ";
		}

		if(!$this->checkInput($_REQUEST[self::SSO_ROLES],"/[[:graph:]]{2,500}/")) {
			unset($param[self::SSO_ROLES]);
			$warning_msg = "Invalid user roles info";
		}
		
		if(!$this->checkInput($_REQUEST[self::SSO_IP],"/\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b/")) {
			unset($param[self::SSO_IP]);
			$warning_msg = "Invalid IP address";
		}

		if(!$this->checkInput($_REQUEST[self::SSO_HASH],"/[[:alnum:]]{16,64}/")) {
			unset($param[self::SSO_HASH]);
			throw new Exception("Invalid SSO authentication token: " + 
					urlencode($_REQUEST[self::SSO_HASH]));
		}
		return $params;		
	}
	
	private function validate($params) {
		if(!$this->checkInput($params[self::SSO_TIMESTAMP],"/[[:digit:]]{10,11}/")) {
			throw new Exception("Invalid timestamp: " + 
					urlencode($params[self::SSO_TIMESTAMP]));
		}
		
		if(!$this->checkInput($params[self::SSO_HASH],"/[[:alnum:]]{16,64}/")) {
			throw new Exception("Invalid SSO authentication token: " + 
					urlencode($params[self::SSO_HASH]));
		}
		$now = time();
		$tstamp = (int) $params[self::SSO_TIMESTAMP];
		$hash = $params[self::SSO_HASH];
		if (($now - $tstamp) > $this->sso_timeout) {	// if > timeout allowance
			return false;
		}		
		return $hash == $this->generateHash($params);
	}
}

?>