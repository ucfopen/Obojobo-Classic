<?php
require_once('internal/app.php');
require(\AppCfg::DIR_BASE.'internal/vendor/ucfcdl/php-saml/_toolkit_loader.php');

$samlConfig = include(\AppCfg::DIR_BASE.'internal/config/vendor/cfgSaml.php');
$settings = new OneLogin_Saml2_Settings($samlConfig);

header('Content-Type: text/xml');
echo($settings->getSPMetadata());

