<?php
// This api endpoint emulates legacy obojobo urls so we don't have to re-compile the repository
// urls look like /api/json.php/loRepository.createInstanceVisit/1
// /api/json.php/<service>.<method>/<arg1>/<arg2
// The newer format is in /api/viewer.php

require_once(dirname(__FILE__)."/../internal/app.php");

$config = new \Amfphp_Core_Config();
$config->checkArgumentCount = false;
$config->serviceFolders = [\AppCfg::DIR_BASE . 'internal/includes/amfphpServices/'];
$config->pluginsFolders[] = \AppCfg::DIR_BASE . 'internal/includes/amfphpPlugins';
$config->disabledPlugins = [
	'AmfphpLogger',
	'AmfphpErrorHandler',
	'AmfphpMonitor',
	'AmfphpDummy',
	'AmfphpGet',
	'AmfphpDiscovery',
	'AmfphpAuthentication',
	'AmfphpVoConverter'
];

$config->pluginsConfig = [
	'ObojoboAmfphpVoConverter' => [
		'voFolders' => [\AppCfg::DIR_BASE . \AppCfg::DIR_CLASSES],
		'enforceConversion' => false
	],
];

$gateway = \Amfphp_Core_HttpRequestGatewayFactory::createGateway($config);
$gateway->service();
$gateway->output();
