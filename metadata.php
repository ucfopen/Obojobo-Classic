<?php
require_once('internal/app.php');
require('internal/plugin/UCFAuth/packages/php-saml/_toolkit_loader.php');

$settings = new OneLogin_Saml2_Settings(\cfg_plugin_AuthModUCF::$SAML_CONFIG);
$metadata = $settings->getSPMetadata();

header('Content-Type: text/xml');
echo $metadata;

