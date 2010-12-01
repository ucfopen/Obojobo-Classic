<?php
$ASE_timestamp = '1291228266';
$ASE_time = 'December 1, 2010, 1:31 pm';
$ASE_savedby = 'obo,,iturgeon,127.0.0.1';
$ASE_plugin_raw = <<<'NOWDOC'
a:11:{s:2:"id";s:2:"21";s:4:"name";s:23:"Inherit Parent Template";s:11:"description";s:104:"<strong>1.1</strong> Newly created Resources use the same template as their Parent or Sibling Containers";s:11:"editor_type";s:1:"0";s:8:"category";s:1:"5";s:10:"cache_type";s:1:"0";s:10:"plugincode";s:1277:"/*
 * Inherit Parent Template
 *
 * Written By Raymond Irving - 12 Oct 2006
 *
 * Simply results in new documents inheriting the template 
 * of their parent folder upon creating a new document
 *
 * Configuration:
 * check the OnDocFormPrerender event
 *
 * Version 1.1
 *
 */

global $content;
$e = &$modx->Event;

switch($e->name) {
    case 'OnDocFormPrerender':        
        if ($inheritTemplate == 'From First Sibling') {
            if ($_REQUEST['pid'] > 0 && $id == 0) {
                if ($sibl = $modx->getDocumentChildren($_REQUEST['pid'], 1, 0, 'template', '', 'menuindex', 'ASC', 1)) {
                    $content['template'] = $sibl[0]['template'];
                } else if ($sibl = $modx->getDocumentChildren($_REQUEST['pid'], 0, 0, 'template', '', 'menuindex', 'ASC', 1)) {
                    $content['template'] = $sibl[0]['template'];
                } else if ($parent = $modx->getPageInfo($_REQUEST['pid'], 0, 'template')) {
                    $content['template'] = $parent['template'];
                }
            }
        } else {
             if ($parent = $modx->getPageInfo($_REQUEST['pid'],0,'template')) {
                 $content['template'] = $parent['template'];
             }
        }
        break;
    default:
        break;
}";s:6:"locked";s:1:"0";s:10:"properties";s:82:"&inheritTemplate=Inherit Template;list;From Parent,From First Sibling;From Parent ";s:8:"disabled";s:1:"0";s:10:"moduleguid";s:0:"";}'
NOWDOC;
$ASE_plugin_map_to_event_raw = <<<'NOWDOC'
a:1:{i:0;a:3:{s:8:"pluginid";s:2:"21";s:5:"evtid";s:2:"28";s:8:"priority";s:1:"0";}}'
NOWDOC;
?>