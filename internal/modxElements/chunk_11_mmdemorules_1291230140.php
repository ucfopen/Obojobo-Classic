<?php
$ASE_timestamp = '1291230140';
$ASE_time = 'December 1, 2010, 2:02 pm';
$ASE_savedby = 'obojobo.ucf.edu,,,132.170.240.85';
$ASE_chunk_raw = <<<'NOWDOC'
a:8:{s:2:"id";s:2:"11";s:4:"name";s:13:"mm_demo_rules";s:11:"description";s:81:"ManagerManager rules for the demo content. Should be modified for your own sites.";s:11:"editor_type";s:1:"0";s:8:"category";s:1:"4";s:10:"cache_type";s:1:"0";s:7:"snippet";s:1844:"// PHP *is* allowed
// $news_role and $news_tpl will not apply to demo content but are left as a demonstration of what can be done

// For everyone
mm_default('pub_date');
mm_renameField('introtext','Summary');
mm_changeFieldHelp('alias', 'The URL that will be used to reach this resource. Only numbers, letters and hyphens can be used');
mm_widget_tags('documentTags',' '); // Give blog tag editing capabilities to the 'documentTags (3)' TV
mm_widget_showimagetvs(); // Always give a preview of Image TVs
// mm_widget_colors('color', '#666666'); // make a color selector widget for the 'colour' TV

// For everyone except administrators
mm_hideFields('link_attributes', '!1');
mm_hideFields('loginName ', '!1');
// mm_renameField('alias','URL alias','!1');

// News editors role -- creating a variable makes it easier to manage if this changes in the future
$news_role = '3';
mm_hideFields('pagetitle,menutitle,link_attributes,template,menuindex,description,show_in_menu,which_editor,is_folder,is_richtext,log,searchable,cacheable,clear_cache', $news_role);
mm_renameTab('settings', 'Publication settings', $news_role);	
mm_synch_fields('pagetitle,menutitle,longtitle', $news_role);
mm_renameField('longtitle','Headline', $news_role, '', 'This will be displayed at the top of each page');

// News story template
$news_tpl = '8';
// mm_createTab('Categories','HrCats', '', $news_tpl, '', '600');
// mm_moveFieldsToTab('updateImage1', 'general', '', $news_tpl);
// mm_hideFields('menuindex,show_in_menu', '', $news_tpl);
mm_changeFieldHelp('longtitle', 'The story\'s headline', '', $news_tpl);
mm_changeFieldHelp('introtext', 'A short summary of the story', '', $news_tpl);
mm_changeFieldHelp('parent', 'To move this story to a different folder: Click this icon to activate, then choose a new folder in the tree on the left.', '', $news_tpl);

";s:6:"locked";s:1:"0";}'
NOWDOC;
?>