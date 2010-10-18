<?php
/**
 * Filters modify the AMF message has a whole, actions modify the AMF message PER BODY
 * This allows batching of calls
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package flashservices
 * @subpackage filters
 * @version $Id: Filters.php,v 1.6 2005/04/02   18:37:51 pmineault Exp $
 */

/**
 * required files
 */ 
require_once(AMFPHP_BASE . 'amf/util/TraceHeader.php');

/**
 * DeserializationFilter has the job of taking the raw input stream and converting in into valid php objects.
 * 
 * The DeserializationFilter is just part of a set of Filter chains used to manipulate the raw data.  Here we
 * get the input stream and convert it to php objects using the helper class AMFInputStream.
 */
function deserializationFilter(&$amf) {

	include_once(AMFPHP_BASE . "amf/io/AMFDeserializer.php");
	$deserializer = new AMFDeserializer($amf->rawData); // deserialize the data
	
	$deserializer->deserialize($amf); // run the deserializer
	
	//Add some headers
	$headers = $amf->_headerTable;
	if(isset($headers) && is_array($headers))
	{
		foreach($headers as $value)
		{
			Headers::setHeader($value->name, $value->value);
		}
	}

}

/**
 * Executes each of the bodys
 */
function batchProcessFilter(&$amf)
{
	$c = $amf->numBody();
	
	while($c--)
	{
		$bodyObj = &$amf->getBodyAt($c);
		foreach($GLOBALS['amfphp']['actions'] as &$action)
		{
			if($action($bodyObj) === false)
			{
				break;
			}
		}
	}
}

/**
 * Serializes the object
 */
function serializationFilter (&$amf) {
	include_once(AMFPHP_BASE . "amf/io/AMFSerializer.php");
	$serializer = new AMFSerializer(); // Create a serailizer around the output stream
	$result = $serializer->serialize($amf); // serialize the data
	$amf->outputStream = $result;
}
?>