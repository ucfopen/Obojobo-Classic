<?php
$ASE_timestamp = '1291230140';
$ASE_time = 'December 1, 2010, 2:02 pm';
$ASE_savedby = 'obojobo.ucf.edu,,,132.170.240.85';
$ASE_plugin_raw = <<<'NOWDOC'
a:11:{s:2:"id";s:2:"17";s:4:"name";s:24:"TinyMCE Rich Text Editor";s:11:"description";s:50:"<strong>3.3.5.1</strong> Javascript WYSIWYG Editor";s:11:"editor_type";s:1:"0";s:8:"category";s:1:"6";s:10:"cache_type";s:1:"0";s:10:"plugincode";s:4018:"// Set the name of the plugin folder
$plugin_dir = "tinymce";

// Set path and base setting variables
if(!isset($mce_path))
{ 
	$mce_path = MODX_BASE_PATH . 'assets/plugins/'.$plugin_dir . '/'; 
	$mce_url  = MODX_BASE_URL  . 'assets/plugins/'.$plugin_dir . '/'; 
}

$params['customparams']    = $customparams;
$params['blockFormats']    = $mce_formats;
$params['entity_encoding'] = $entity_encoding;
$params['entities']        = $entities;
$params['pathoptions']     = $mce_path_options;
$params['resizing']        = $mce_resizing;
$params['disabledButtons'] = $disabledButtons;
$params['link_list']       = $link_list;
$params['theme']           = $webtheme;
$params['custom_plugins']  = $webPlugins;
$params['custom_buttons1'] = $webButtons1;
$params['custom_buttons2'] = $webButtons2;
$params['custom_buttons3'] = $webButtons3;
$params['custom_buttons4'] = $webButtons4;
$params['toolbar_align']   = $webAlign;
$params['width']           = $width;
$params['height']          = $height;

$params['mce_path']        = $mce_path;
$params['mce_url']         = $mce_url;

include_once $mce_path . 'lang/tinymce.lang.php';
include_once $mce_path . 'tinymce.functions.php';

$mce = new TinyMCE($params);

// Handle event
$e = &$modx->Event; 
switch ($e->name)
{
	case "OnRichTextEditorRegister": // register only for backend
		$e->output("TinyMCE");
		break;

	case "OnRichTextEditorInit": 
		if($editor!=="TinyMCE") return;
		
		$params['elements']        = $elements;
		$params['css_selectors']   = $modx->config['tinymce_css_selectors'];
		$params['use_browser']     = $modx->config['use_browser'];
		$params['editor_css_path'] = $modx->config['editor_css_path'];
		
		if($modx->isBackend())
		{
			$params['theme']           = $modx->config['tinymce_editor_theme'];
			$params['language']        = getTinyMCELang($modx->config['manager_language']);
			$params['frontend']        = false;
			$params['custom_plugins']  = $modx->config['tinymce_custom_plugins'];
			$params['custom_buttons1'] = $modx->config['tinymce_custom_buttons1'];
			$params['custom_buttons2'] = $modx->config['tinymce_custom_buttons2'];
			$params['custom_buttons3'] = $modx->config['tinymce_custom_buttons3'];
			$params['custom_buttons4'] = $modx->config['tinymce_custom_buttons4'];
			$params['toolbar_align']   = $modx->config['manager_direction'];
			$params['webuser']         = null;
			
			$html = $mce->get_mce_script($params);
		}
		else
		{
			$frontend_language = isset($modx->config['fe_editor_lang']) ? $modx->config['fe_editor_lang']:'';
			$webuser = (isset($modx->config['rb_webuser']) ? $modx->config['rb_webuser'] : null);
			
			$params['webuser']         = $webuser;
			$params['language']        = getTinyMCELang($frontend_language);
			$params['frontend']        = true;
			
			$html = $mce->get_mce_script($params);
		}
		$e->output($html);
		break;

	case "OnInterfaceSettingsRender":
		global $usersettings,$settings;
		$action = $modx->manager->action;
		switch ($action)
		{
			case 11:
				$mce_settings = '';
				break;
			case 12:
				$mce_settings = $usersettings;
				break;
			case 17:
				$mce_settings = $settings;
				break;
			default:
				$mce_settings = $settings;
				break;
		}
		
		$params['use_editor']       = $modx->config['base_url'].$modx->config['use_editor'];
        $params['editor_css_path']  = $modx->config['editor_css_path'];
		$params['theme']            = $mce_settings['tinymce_editor_theme'];
		$params['css_selectors']    = $mce_settings['tinymce_css_selectors'];
		$params['custom_plugins']   = $mce_settings['tinymce_custom_plugins'];
		$params['custom_buttons1']  = $mce_settings['tinymce_custom_buttons1'];
		$params['custom_buttons2']  = $mce_settings['tinymce_custom_buttons2'];
		$params['custom_buttons3']  = $mce_settings['tinymce_custom_buttons3'];
		$params['custom_buttons4']  = $mce_settings['tinymce_custom_buttons4'];
		
		$html = $mce->get_mce_settings($params);
		$e->output($html);
		break;
		
	default :
		return; // stop here - this is very important. 
		break; 
}
";s:6:"locked";s:1:"0";s:10:"properties";s:1203:"&customparams=Custom Parameters;textarea; &mce_formats=Block Formats;text;p,h1,h2,h3,h4,h5,h6,div,blockquote,code,pre &entity_encoding=Entity Encoding;list;named,numeric,raw;named &entities=Entities;text; &mce_path_options=Path Options;list;rootrelative,docrelative,fullpathurl;docrelative &mce_resizing=Advanced Resizing;list;true,false;true &disabledButtons=Disabled Buttons;text; &link_list=Link List;list;enabled,disabled;enabled &webtheme=Web Theme;list;simple,editor,creative,custom;simple &webPlugins=Web Plugins;text;style,advimage,advlink,searchreplace,contextmenu,paste,fullscreen,nonbreaking,xhtmlxtras,visualchars,media &webButtons1=Web Buttons 1;text;undo,redo,selectall,|,pastetext,pasteword,|,search,replace,|,nonbreaking,hr,charmap,|,image,link,unlink,anchor,media,|,cleanup,removeformat,|,fullscreen,code,help &webButtons2=Web Buttons 2;text;bold,italic,underline,strikethrough,sub,sup,|,|,blockquote,bullist,numlist,outdent,indent,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,|,styleprops &webButtons3=Web Buttons 3;text; &webButtons4=Web Buttons 4;text; &webAlign=Web Toolbar Alignment;list;ltr,rtl;ltr &width=Width;text;100% &height=Height;text;400";s:8:"disabled";s:1:"0";s:10:"moduleguid";s:0:"";}'
NOWDOC;
$ASE_plugin_map_to_event_raw = <<<'NOWDOC'
a:3:{i:0;a:3:{s:8:"pluginid";s:2:"17";s:5:"evtid";s:2:"85";s:8:"priority";s:1:"0";}i:1;a:3:{s:8:"pluginid";s:2:"17";s:5:"evtid";s:2:"87";s:8:"priority";s:1:"0";}i:2;a:3:{s:8:"pluginid";s:2:"17";s:5:"evtid";s:2:"88";s:8:"priority";s:1:"0";}}'
NOWDOC;
?>