<?php
require_once('internal/app.php');
require('internal/vendor/ucfcdl/php-saml/_toolkit_loader.php');

$samlConfig = include(DIR_BASE.'includes/config/vendor/cfgSaml.php');
$settings = new OneLogin_Saml2_Settings($samlConfig);

header('Content-Type: text/xml');
echo($settings->getSPMetadata());
