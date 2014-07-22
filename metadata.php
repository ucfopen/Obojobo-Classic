<?php
require_once('internal/app.php');
require('internal/plugin/UCFAuth/packages/php-saml/_toolkit_loader.php');

$settings = new OneLogin_Saml2_Settings(\cfg_plugin_AuthModUCF::$SAML_CONFIG);
$metadata = $settings->getSPMetadata();
$errors = $settings->validateMetadata($metadata);

if (empty($errors))
{
  header('Content-Type: text/xml');
  echo $metadata;
} else {
  throw new OneLogin_Saml2_Error(
    'Invalid SP metadata: '.implode(', ', $errors),
    OneLogin_Saml2_Error::METADATA_SP_INVALID
  );
}

