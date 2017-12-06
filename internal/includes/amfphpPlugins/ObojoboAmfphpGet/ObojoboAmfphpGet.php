<?php

class ObojoboAmfphpGet implements Amfphp_Core_Common_IDeserializer, Amfphp_Core_Common_IDeserializedRequestHandler, Amfphp_Core_Common_IExceptionHandler, Amfphp_Core_Common_ISerializer {

	/**
	* the content-type string indicating a cross domain ajax call
	*/
	const CONTENT_TYPE = 'text/amfphpget';

	/**
	* Flag used to determine if we're using the legacy query strings used in Obojobo
	*/
	protected $use_legacy_query_string = false;

	/**
	 * return error details.
	 * @see Amfphp_Core_Config::CONFIG_RETURN_ERROR_DETAILS
	 * @var boolean
	 */
	protected $returnErrorDetails = false;

	/**
	 * constructor. Add filters on the HookManager.
	 * @param array $config optional key/value pairs in an associative array. Used to override default configuration values.
	 */
	public function  __construct(array $config = null) {
		$filterManager = Amfphp_Core_FilterManager::getInstance();
		$filterManager->addFilter(Amfphp_Core_Gateway::FILTER_DESERIALIZER, $this, 'filterHandler');
		$filterManager->addFilter(Amfphp_Core_Gateway::FILTER_DESERIALIZED_REQUEST_HANDLER, $this, 'filterHandler');
		$filterManager->addFilter(Amfphp_Core_Gateway::FILTER_EXCEPTION_HANDLER, $this, 'filterHandler');
		$filterManager->addFilter(Amfphp_Core_Gateway::FILTER_SERIALIZER, $this, 'filterHandler');
		$filterManager->addFilter(Amfphp_Core_Gateway::FILTER_HEADERS, $this, 'filterHeaders');
		$this->returnErrorDetails = (isset ($config[Amfphp_Core_Config::CONFIG_RETURN_ERROR_DETAILS]) && $config[Amfphp_Core_Config::CONFIG_RETURN_ERROR_DETAILS]);

	}

	/**
	 * If the content type contains the 'json' string, returns this plugin
	 * @param mixed null at call in gateway.
	 * @param String $contentType
	 * @return this or null
	 */
	public function filterHandler($handler, $contentType){
		// only use this handler if the request uri contains the old style of api request: http://server.com/api/script.php/loRepository.API_METHOD/arg1/arg2
		if(strpos($_SERVER['REQUEST_URI'], 'loRepository.') !== false){
			$this->use_legacy_query_string = true;
			return $this;
		}

		// Only enable this filter if this is a get request using newer format: http://server.com/api/script.php?m=API_METHOD&p1=arg1....
		if ($_SERVER['REQUEST_METHOD'] == 'GET' && strpos($_SERVER['QUERY_STRING'], 'm=') !== false ){
			trace('Using ObojoboAmfphpGet');
			return $this;
		}

	}

	/**
	 * deserialize
	 * @see Amfphp_Core_Common_IDeserializer
	 * @param array $getData
	 * @param array $postData
	 * @param string $rawPostData
	 * @return string
	 */
	public function deserialize(array $getData, array $postData, $rawPostData){
		return $getData;
	}

	/**
	 * Retrieve the serviceName, methodName and parameters from the PHP object
	 * representing the JSON string
	 * call service
	 * @see Amfphp_Core_Common_IDeserializedRequestHandler
	 * @param array $deserializedRequest
	 * @param Amfphp_Core_Common_ServiceRouter $serviceRouter
	 * @return the service call response
	 */
	public function handleDeserializedRequest($deserializedRequest, Amfphp_Core_Common_ServiceRouter $serviceRouter){
		if($this->use_legacy_query_string){
			$query = str_replace("{$_SERVER['SCRIPT_NAME']}/", '', $_SERVER['REQUEST_URI']); // loRepository.createInstanceVisit/1
			$splits = explode('/', $query);
			$service_and_method = array_shift($splits);
			$s_and_m_splits = explode('.', $service_and_method);
			$serviceName = $s_and_m_splits[0];
			$methodName = $s_and_m_splits[1];
			$parameters = $splits;
			// check for arrays that need to be parsed
			// ie: .com/api/json.api/class.method/[1,2,3]/a
			// this makes sure parameters[0] = array(1,2,3)
			if(count($parameters))
			{
				foreach ($parameters as $key => $value)
				{
					if(substr($value, 0, 1) == '[' && substr($value, -1) == ']')
					{
						$parameters[$key] = explode(',', substr($value, 1, -1));
					}
				}
			}
			return $serviceRouter->executeServiceCall($serviceName, $methodName, $parameters);
		}
		else{
			$serviceName = 'loRepository'; // force this for Obojobo

			trace('======================================');
			trace($deserializedRequest);

			if(isset ($deserializedRequest['m'])){
				$methodName = $deserializedRequest['m'];
			}else{
				throw new Exception('MethodName field missing in call parameters \n' . print_r($deserializedRequest, true));
			}
			$parameters = array();
			$paramCounter = 1;
			while(isset ($deserializedRequest["p$paramCounter"])){
				$parameters[] = $deserializedRequest["p$paramCounter"];
				$paramCounter++;
			}
			return $serviceRouter->executeServiceCall($serviceName, $methodName, $parameters);
		}


	}

	/**
	 * handle exception
	 * @see Amfphp_Core_Common_IExceptionHandler
	 * @param Exception $exception
	 * @return stdClass
	 */
	public function handleException(Exception $exception){
		$error = new stdClass();
		$error->message = $exception->getMessage();
		$error->code = $exception->getCode();
		if($this->returnErrorDetails){
			$error->file = $exception->getFile();
			$error->line = $exception->getLine();
			$error->stack = $exception->getTraceAsString();
		}
		return (object)array('error' => $error);

	}

	/**
	 * Encode the PHP object returned from the service call into a JSON string
	 * @see Amfphp_Core_Common_ISerializer
	 * @param mixed $data
	 * @return string the encoded JSON string sent to JavaScript
	 */
	public function serialize($data){
		$encoded = json_encode($data);
		if(isset ($_GET['callback'])){
			return $_GET['callback'] . '(' . $encoded . ');';
		}else{
			return $encoded;
		}
	}


	/**
	 * sets return content type to json
	 * @param array $headers
	 * @param string $contentType
	 * @return array
	 */
	public function filterHeaders($headers, $contentType){
		$headers['Content-Type'] =  'application/json';
		return $headers;
	}


}
