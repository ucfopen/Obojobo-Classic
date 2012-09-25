<?php
require_once(dirname(__FILE__)."/../../../internal/app.php");

header('Content-Type: application/xml',true);
echo '<?xml version="1.0" encoding="UTF-8" ?>';
?>
<config>
	<remoting_gateway><?php echo \AppCfg::URL_WEB.\AppCfg::AMF_GATEWAY; ?></remoting_gateway>
	<json_gateway><?php echo \AppCfg::URL_WEB.\AppCfg::JSON_GATEWAY; ?></json_gateway>

	<debug><?php echo \AppCfg::DEBUG_MODE; ?></debug>
	
	<show_debug_ui><?php echo false; ?></show_debug_ui>

	<section_help>
		<![CDATA[xml/SectionHelp.xml]]>
	</section_help>

	<instance_help>
		<![CDATA[xml/InstanceHelp.xml]]>
	</instance_help>

	<wordlist_url>
		<![CDATA[assets/creator/wordlists/en_us.gspl]]>
	</wordlist_url>

	<creator_guidelines_url>
		<![CDATA[modules/creator/guidelines.xml]]>
	</creator_guidelines_url>

	<creator_verbs_url>
		<![CDATA[modules/creator/task-verbs.xml]]>
	</creator_verbs_url>

	<asset_script_url>
		<![CDATA[<?php echo \AppCfg::URL_WEB.\AppCfg::DIR_ASSETS; ?>getAsset.php?id=]]>
	</asset_script_url>

	<upload_script_url>
		<![CDATA[<?php echo \AppCfg::URL_WEB.\AppCfg::DIR_ASSETS; ?>upAsset.php]]>
	</upload_script_url>

	<csv_url>
		<![CDATA[<?php echo \AppCfg::URL_WEB.\AppCfg::DIR_ASSETS; ?>csv.php]]>
	</csv_url>

	<repository>
		<![CDATA[<?php echo \AppCfg::URL_WEB.\AppCfg::URL_REPOSITORY; ?>]]>
	</repository>

	<creator>
		<![CDATA[<?php echo \AppCfg::URL_WEB.\AppCfg::URL_CREATOR; ?>]]>
	</creator>

	<viewer>
		<![CDATA[<?php echo \AppCfg::URL_WEB.\AppCfg::URL_VIEWER; ?>]]>
	</viewer>
	
	<preview>
		<![CDATA[<?php echo \AppCfg::URL_WEB.\AppCfg::URL_PREVIEW; ?>]]>
	</preview>

	<status_url>
		<![CDATA[<?php echo \AppCfg::URL_STATUS; ?>]]>
	</status_url>

	<materia_lti_url>
		<![CDATA[<?php echo \AppCfg::URL_WEB.'assets/materia.php'; ?>]]>
	</materia_lti_url>

	<updates_url>
		<![CDATA[<?php echo \AppCfg::URL_WEB.\AppCfg::URL_UPDATES; ?>]]>
	</updates_url>

	<knownIssues_url>
		<![CDATA[<?php echo \AppCfg::URL_WEB.\AppCfg::URL_KNOWN_ISSUES; ?>]]>
	</knownIssues_url>

	<wiki_url>
		<![CDATA[<?php echo \AppCfg::URL_WEB.\AppCfg::URL_WIKI; ?>]]>
	</wiki_url>

	<about_url>
		<![CDATA[<?php echo \AppCfg::URL_WEB.\AppCfg::URL_ABOUT; ?>]]>
	</about_url>

	<home_url>
		<![CDATA[<?php echo \AppCfg::URL_WEB; ?>]]>
	</home_url>

	<twitter_proxy_url>
		<![CDATA[<?php echo \AppCfg::URL_WEB.\AppCfg::URL_TWITTER_PROXY; ?>]]>
	</twitter_proxy_url>
	
	<pro_account_form_url>
		<![CDATA[<?php echo \AppCfg::URL_ACCOUNT_FORM; ?>]]>
	</pro_account_form_url>

	<pro_account_formID>
		<![CDATA[<?php echo \AppCfg::ACCOUNT_FORM_ID; ?>]]>
	</pro_account_formID>
	
	<student_guide_url>
		<![CDATA[<?php echo \AppCfg::URL_STUDENT_QSTART; ?>]]>
	</student_guide_url>

	<help_url>
		<![CDATA[/help/]]>
	</help_url>
		
	<shared_object_name>
		<![CDATA[mindshare]]>
	</shared_object_name>
	
	<next_minimum_flash_version>
		<![CDATA[<?php echo \AppCfg::FLASH_VER_WARN; ?>]]>
	</next_minimum_flash_version>

	<creator_shared_object_name>
		<![CDATA[mindshare_creator]]>
	</creator_shared_object_name>

	<request_timeout_seconds>120</request_timeout_seconds>

	<idle_warning_seconds>900</idle_warning_seconds>

	<inactive_warning_seconds>300</inactive_warning_seconds>
	
	<verify_session_interval_seconds>30</verify_session_interval_seconds>

	<disable_incomplete_features>false</disable_incomplete_features>

	<max_file_size><?php echo \AppCfg::MAX_FILE_SIZE; ?></max_file_size>

	<jpeg_quality>30</jpeg_quality>

	<lock_interval_seconds>30</lock_interval_seconds>
</config>
