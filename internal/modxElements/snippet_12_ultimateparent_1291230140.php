<?php
$ASE_timestamp = '1291230140';
$ASE_time = 'December 1, 2010, 2:02 pm';
$ASE_savedby = 'obojobo.ucf.edu,,,132.170.240.85';
$ASE_snippet_raw = <<<'NOWDOC'
a:10:{s:2:"id";s:2:"12";s:4:"name";s:14:"UltimateParent";s:11:"description";s:118:"<strong>2.0</strong> Travels up the document tree from a specified document and returns its "ultimate" non-root parent";s:11:"editor_type";s:1:"0";s:8:"category";s:1:"0";s:10:"cache_type";s:1:"0";s:7:"snippet";s:1755:"/*
 * @name UltimateParent
 * @version 2.0 beta (requires MODx 0.9.5+)
 * @author Jason Coward <modx@opengeek.com>
 * 
 * @param &id The id of the document whose parent you want to find.
 * @param &top The top node for the search.
 * @param &topLevel The top level node for the search (root = level 1)
 * 
 * @license Public Domain, use as you like.
 * 
 * @example [[UltimateParent? &id=`45` &top=`6`]] 
 * Will find the ultimate parent of document 45 if it is a child of document 6;
 * otherwise it will return 45.
 * 
 * @example [[UltimateParent? &topLevel=`2`]]
 * Will find the ultimate parent of the current document at a depth of 2 levels
 * in the document hierarchy, with the root level being level 1.
 * 
 * This snippet travels up the document tree from a specified document and
 * returns the "ultimate" parent.  Version 2.0 was rewritten to use the new
 * getParentIds function features available only in MODx 0.9.5 or later.
 * 
 * Based on the original UltimateParent 1.x snippet by Susan Ottwell
 * <sottwell@sottwell.com>.  The topLevel parameter was introduced by staed and
 * adopted here.
 */
$top= isset ($top) && intval($top) ? $top : 0;
$id= isset ($id) && intval($id) ? intval($id) : $modx->documentIdentifier;
$topLevel= isset ($topLevel) && intval($topLevel) ? intval($topLevel) : 0;
if ($id && $id != $top) {
    $pid= $id;
    if (!$topLevel || count($modx->getParentIds($id)) >= $topLevel) {
        while ($parentIds= $modx->getParentIds($id, 1)) {
            $pid= array_pop($parentIds);
            if ($pid == $top) {
                break;
            }
            $id= $pid;
            if ($topLevel && count($modx->getParentIds($id)) < $topLevel) {
                break;
            }
        }
    }
}
return $id;";s:6:"locked";s:1:"0";s:10:"properties";s:0:"";s:10:"moduleguid";s:0:"";}'
NOWDOC;
?>