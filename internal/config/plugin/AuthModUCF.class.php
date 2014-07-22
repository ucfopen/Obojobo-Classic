<?php
class cfg_plugin_AuthModUCF
{
	const UCF_TEST_MODE = true;
	
	// External Employee Table
	const TABLE_EMPLOYEE = 'NM_EMPLOYEE';
	const NID = 'NETWORK_ID';
	const USERNAME = 'urn:oid:1.3.6.1.4.1.17021.3';
	const FIRST = 'urn:oid:2.5.4.42';
	const LAST = 'urn:oid:2.5.4.4';
	const ROLES = 'urn:oid:1.3.6.1.4.1.17021.8';
	const MIDDLE = 'MIDDLE_NAME';

	// if the upstream database isn't availible, build fake email addresses?
	const FAKE_UPSTREAM_EMAIL = true;
	
	// External Student Table
	const TABLE_STUDENT = 'NM_STUDENT';
	
	// EXTERNAL NID CHANGES TABLE
	const TABLE_NID = 'NM_NID_CHANGE';
	const NID_CHANGE_DATE = 'EFFDT';
	const OLD_NID = 'OLD_NID';
	const NEW_NID = 'NEW_NID';
	
	const COL_EXTERNAL_SYNC_NAME= 'AuthMod_PeopleSoft_LastNIDUpdate';
	const MAX_USERNAME_LENGTH = '255';
	const MIN_USERNAME_LENGTH = '2';


	static $SAML_CONFIG = array(
		'sp' => array (
			'entityId' => 'https://kogneatotest.cdl.ucf.edu/saml/metadata',
			'assertionConsumerService' => array (
				'url' => 'https://kogneatotest.cdl.ucf.edu/saml/acs',
				'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST'
			),
			'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
			'x509cert' => 'PUBLIC_KEY_HERE',
			'privateKey' => 'PRIVATE_KEY_HERE',
		),
		'idp' => array (
			'entityId' => 'https://idp.cc.ucf.edu/idp/shibboleth',
			'singleSignOnService' => array (
				'url' => 'https://idp.cc.ucf.edu/idp/profile/SAML2/Redirect/SSO',
				'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect'
			),
			'x509cert' => 'PUBLIC_KEY_HERE'
		),
	);
}

