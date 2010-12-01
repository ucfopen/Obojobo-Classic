<?php
$ASE_timestamp = '1291228266';
$ASE_time = 'December 1, 2010, 1:31 pm';
$ASE_savedby = 'obo,,iturgeon,127.0.0.1';
$ASE_snippet_raw = <<<'NOWDOC'
a:10:{s:2:"id";s:1:"5";s:4:"name";s:18:"FirstChildRedirect";s:11:"description";s:87:"<strong>1.0</strong> Automatically redirects to the first child of a Container Resource";s:11:"editor_type";s:1:"0";s:8:"category";s:1:"0";s:10:"cache_type";s:1:"0";s:7:"snippet";s:1103:"/*
 * @name FirstChildRedirect
 * @author Jason Coward <jason@opengeek.com>
 * @modified-by Ryan Thrash <ryan@vertexworks.com>
 * @license Public Domain
 * @version 1.0
 * 
 * This snippet redirects to the first child document of a folder in which this
 * snippet is included within the content (e.g. [!FirstChildRedirect!]).  This
 * allows MODx folders to emulate the behavior of real folders since MODx
 * usually treats folders as actual documents with their own content.
 * 
 * Modified to make Doc ID a required parameter... now defaults to the current 
 * Page/Folder you call the snippet from.
 * 
 * &docid=`12` 
 * Use the docid parameter to have this snippet redirect to the
 * first child document of the specified document.
 */

$docid = (isset($docid))? $docid: $modx->documentIdentifier;

$children= $modx->getActiveChildren($docid, 'menuindex', 'ASC');
if (!$children === false) {
    $firstChild= $children[0];
    $firstChildUrl= $modx->makeUrl($firstChild['id']);
} else {
    $firstChildUrl= $modx->makeUrl($modx->config['site_start']);
}
return $modx->sendRedirect($firstChildUrl);
";s:6:"locked";s:1:"0";s:10:"properties";s:0:"";s:10:"moduleguid";s:0:"";}'
NOWDOC;
?>