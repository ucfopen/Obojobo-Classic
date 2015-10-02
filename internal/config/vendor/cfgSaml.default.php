<?php

return [
	'sp' => [
		'entityId' => \AppCfg::URL_WEB . 'saml/metadata',
		'assertionConsumerService' => [
			'url' => \AppCfg::URL_WEB . 'saml/acs',
			'binding' => ''
		],
		'NameIDFormat' => '',
		'x509cert' => '',
		'privateKey' => '',
	],
	'idp' => [
		'entityId' => '',
		'singleSignOnService' => [
			'url' => '',
			'binding' => ''
		],
		'x509cert' => ''
	],
];
