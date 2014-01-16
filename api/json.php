<?php
	
	/**
	 * JSON gateway
	 */
	require_once(dirname(__FILE__)."/../internal/app.php");
	
	$servicesPath = \AppCfg::DIR_BASE.\AppCfg::DIR_AMFPHP."services/";
	$voPath = \AppCfg::DIR_BASE.\AppCfg::DIR_AMFPHP."services/vo/";
	define("PRODUCTION_SERVER", !\AppCfg::DEBUG_MODE); // if debug == true, production server = false
	//Include framework
	include \AppCfg::DIR_BASE.\AppCfg::DIR_AMFPHP."core/json/app/Gateway.php";
	$gateway = new Gateway();
	$gateway->setBaseClassPath($servicesPath);

	$GLOBALS['amfphp']['errorLevel'] = \AppCfg::DEBUG_MODE ? E_ALL ^ E_NOTICE : E_ERROR;

	//Service now
	$gateway->service();
?>