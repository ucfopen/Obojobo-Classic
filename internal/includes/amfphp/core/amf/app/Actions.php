<?php
/**
 * Actions modify the AMF message PER BODY
 * This allows batching of calls
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package flashservices
 * @subpackage filters
 * @version $Id: Filters.php,v 1.6 2005/04/02   18:37:51 pmineault Exp $
 */

/**
 * Catches any special request types and classifies as required
 */
function adapterAction (&$amfbody) {
	$baseClassPath = $GLOBALS['amfphp']['classPath'];

	$uriclasspath = "";
	$classname = "";
	$classpath = "";
	$methodname = "";
	$isWebServiceURI = false;

	$target = $amfbody->targetURI;
	
	if (strpos($target, "http://") === false && strpos($target, "https://") === false) { // check for a http link which means web service
		//Check to see if this is in fact a RemotingMessage
		$body = $amfbody->getValue();
		$handled = false;
		
		$messageType = $body[0]->_explicitType;
		if($messageType == 'flex.messaging.messages.RemotingMessage')
		{
			$handled = true;
			
			//Fix for AMF0 mixed array bug in Flex 2
			if(isset($body[0]->body['length']))
			{
				unset($body[0]->body['length']);
			}
			
			$amfbody->setValue($body[0]->body);
			$amfbody->setSpecialHandling("RemotingMessage");
			$amfbody->setMetadata("clientId", $body[0]->clientId);
			$amfbody->setMetadata("messageId", $body[0]->messageId);
			
			$GLOBALS['amfphp']['lastMessageId'] = $body[0]->messageId;
			
			$methodname = $body[0]->operation;
			$classAndPackage = $body[0]->source;
			$classname = $classAndPackage;
			$uriclasspath = str_replace('.','/',$classAndPackage) . '.php';
			$classpath = $baseClassPath . $uriclasspath;
		}
		elseif($messageType == "flex.messaging.messages.CommandMessage")
		{
			if($body[0]->operation == 5)
			{
				$handled = true;
				$amfbody->setSpecialHandling("Ping");
				$amfbody->setMetadata("clientId", $body[0]->clientId);
				$amfbody->setMetadata("messageId", $body[0]->messageId);
				$amfbody->noExec = true;
			}
		}
		
		if(!$handled)
		{
			$uriclasspath = "amfphp/Amf3Broker.php";
			$classpath = $baseClassPath . "amfphp/Amf3Broker.php";
			$classname = "Amf3Broker";
			$methodname = "handleMessage";
		}

	} else { // This is a web service and is unsupported
		trigger_error("Web services are not supported in this release", E_USER_ERROR);
	} 

	$amfbody->classPath = $classpath;
	$amfbody->uriClassPath = $uriclasspath;
	$amfbody->className = $classname;
	$amfbody->methodName = $methodname;

	return true;
} 

/**
 * ExecutionAction executes the required methods
 */
function executionAction (&$amfbody) 
{
	$specialHandling = $amfbody->getSpecialHandling();

	if (!$amfbody->isSpecialHandling() || $amfbody->isSpecialHandling(array('pageFetch', 'RemotingMessage')))
	{
		$construct = &$amfbody->getClassConstruct();
		$method = $amfbody->methodName;
		$args = $amfbody->getValue();

		if(\AppCfg::PROFILE_MODE)
		{
			$t = microtime(1);
			$mem1 = memory_get_usage(true);
			$results = Executive::doMethodCall($amfbody, $construct, $method, $args); // do the magic
			\rocketD\util\Log::profile('amfphp_Methods', "'{$_SESSION['userID']}','$method','".round((microtime(1) - $t),5)."','".time().",'$mem1','".memory_get_usage(true)."','".memory_get_peak_usage(true)."'");
		}
		else
		{
			$results = Executive::doMethodCall($amfbody, $construct, $method, $args); // do the magic
		}
		
		global $amfphp;

		if($results !== '__amfphp_error')
		{
			if($specialHandling == 'RemotingMessage')
			{
				
				$wrapper = new AcknowledgeMessage($amfbody->getMetadata("messageId"), $amfbody->getMetadata("clientId"));
				$wrapper->body = $results;
				$amfbody->setResults($wrapper);
			}
			else
			{
				$amfbody->setResults($results);
			}
			
			$amfbody->responseURI = $amfbody->responseIndex . "/onResult";  
		}
		return false;
	}
	elseif($specialHandling == 'Ping')
	{
		$wrapper = new AcknowledgeMessage($amfbody->getMetadata("messageId"), $amfbody->getMetadata("clientId"));
		$amfbody->setResults($wrapper);
		$amfbody->responseURI = $amfbody->responseIndex . "/onResult";
	}
	return true;
}
?>