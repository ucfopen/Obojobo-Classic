<?php
	require_once(dirname(__FILE__)."/../internal/app.php");

	//Include framework
	include \AppCfg::DIR_BASE.\AppCfg::DIR_AMFPHP."core/amf/app/Gateway.php";

	$gateway = new Gateway();
	$gateway->setClassPath(\AppCfg::DIR_BASE.\AppCfg::DIR_AMFPHP."services/");
	$gateway->setCharsetHandler("mbstring","UTF-8","UTF-8");
	$gateway->setErrorHandling(E_ALL ^ E_NOTICE);
	$gateway->enableGzipCompression(25*1024);
	$gateway->service();
	flush();
?>