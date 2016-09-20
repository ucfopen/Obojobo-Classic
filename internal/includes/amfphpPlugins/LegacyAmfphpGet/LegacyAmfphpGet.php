<?php

/**
 * LEGACY OBOJOBO GET ENDPOINT - enables really old obojobo api usage.
 * If this plugin is enabled, it will assume it's being used
 *
 * Requires at least PHP 5.2.
 */
class LegacyAmfphpGet implements Amfphp_Core_Common_IDeserializer, Amfphp_Core_Common_IDeserializedRequestHandler, Amfphp_Core_Common_IExceptionHandler, Amfphp_Core_Common_ISerializer {

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
		return $this;
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

		// str_replace('/api/json.php/', '', '/api/json.php/loRepository.createInstanceVisit/1');
		$query = str_replace("{$_SERVER['SCRIPT_NAME']}/", '', $_SERVER['REQUEST_URI']); // loRepository.createInstanceVisit/1
		$splits = explode('/', $query);
		$service_and_method = array_shift($splits);
		$s_and_m_splits = explode('.', $service_and_method);
		$serviceName = $s_and_m_splits[0];
		$methodName = $s_and_m_splits[1];
		$parameters = $splits;
		return $serviceRouter->executeServiceCall($serviceName, $methodName, $parameters);
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
		$headers['Content-Type'] = 'application/json';
		return $headers;
	}


}
