<?php
$ASE_timestamp = '1291228266';
$ASE_time = 'December 1, 2010, 1:31 pm';
$ASE_savedby = 'obo,,iturgeon,127.0.0.1';
$ASE_plugin_raw = <<<'NOWDOC'
a:11:{s:2:"id";s:2:"14";s:4:"name";s:14:"ManagerManager";s:11:"description";s:97:"<strong>0.3.8</strong> Customize the MODx Manager to offer bespoke admin functions for end users.";s:11:"editor_type";s:1:"0";s:8:"category";s:1:"5";s:10:"cache_type";s:1:"0";s:10:"plugincode";s:1506:"// You can put your ManagerManager rules EITHER in a chunk OR in an external file - whichever suits your development style the best

// To use an external file, put your rules in /assets/plugins/managermanager/mm_rules.inc.php 
// (you can rename default.mm_rules.inc.php and use it as an example)
// The chunk SHOULD have php opening tags at the beginning and end

// If you want to put your rules in a chunk (so you can edit them through the Manager),
// create the chunk, and enter its name in the configuration tab.
// The chunk should NOT have php tags at the beginning or end

// ManagerManager requires jQuery 1.3+
// The URL to the jQuery library. Choose from the configuration tab whether you want to use 
// a local copy (which defaults to the jQuery library distributed with ModX 1.0.1)
// a remote copy (which defaults to the Google Code hosted version)
// or specify a URL to a custom location.
// Here we set some default values, because this is a convenient place to change them if we need to,
// but you should configure your preference via the Configuration tab.
$js_default_url_local = $modx->config['site_url']. '/assets/js/jquery-1.3.2.min.js';
$js_default_url_remote = 'http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js';

// You don't need to change anything else from here onwards
//-------------------------------------------------------

// Run the main code
$asset_path = $modx->config['base_path'] . 'assets/plugins/managermanager/mm.inc.php';
include($asset_path);";s:6:"locked";s:1:"0";s:10:"properties";s:286:"&config_chunk=Configuration Chunk;text;mm_demo_rules; &remove_deprecated_tv_types_pref=Remove deprecated TV types;list;yes,no;yes &which_jquery=jQuery source;list;local (assets/js),remote (google code),manual url (specify below);local (assets/js) &js_src_type=jQuery URL override;text; ";s:8:"disabled";s:1:"0";s:10:"moduleguid";s:0:"";}'
NOWDOC;
$ASE_plugin_map_to_event_raw = <<<'NOWDOC'
a:4:{i:0;a:3:{s:8:"pluginid";s:2:"14";s:5:"evtid";s:2:"53";s:8:"priority";s:1:"0";}i:1;a:3:{s:8:"pluginid";s:2:"14";s:5:"evtid";s:2:"35";s:8:"priority";s:1:"0";}i:2;a:3:{s:8:"pluginid";s:2:"14";s:5:"evtid";s:2:"29";s:8:"priority";s:1:"0";}i:3;a:3:{s:8:"pluginid";s:2:"14";s:5:"evtid";s:2:"28";s:8:"priority";s:1:"0";}}'
NOWDOC;
?>