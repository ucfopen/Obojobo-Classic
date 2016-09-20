<?php
// This api endpoint is the newer endpoint
// urls look like /api/viewer.php?m=createInstanceVisit&p1=1&p2=cats
// /api/viewer.php?m=<method>&p1=<param1>&p2=<param2>
// The service is assumed by defining defaultServiceName below
// The newer format is in /api/viewer.php
// ObojoboAmfphpGet assumes the service is loRepostitory

require_once(dirname(__FILE__)."/../internal/app.php");

$config = new \Amfphp_Core_Config();
$config->checkArgumentCount = false;
$config->serviceFolders = [\AppCfg::DIR_BASE . 'internal/includes/amfphpServices/'];
$config->pluginsFolders[] = \AppCfg::DIR_BASE . 'internal/includes/amfphpPlugins';
$config->disabledPlugins = ['AmfphpLogger', 'AmfphpErrorHandler','AmfphpMonitor', 'AmfphpDummy', 'AmfphpGet', 'LegacyAmfphpGet', 'AmfphpDiscovery', 'AmfphpAuthentication', 'AmfphpVoConverter'];

$config->pluginsConfig = [
	'ObojoboAmfphpVoConverter' => [
		'voFolders' => [\AppCfg::DIR_BASE . \AppCfg::DIR_CLASSES],
		'enforceConversion' => false
	]
];

$gateway = \Amfphp_Core_HttpRequestGatewayFactory::createGateway($config);
$gateway->service();
$gateway->output();
