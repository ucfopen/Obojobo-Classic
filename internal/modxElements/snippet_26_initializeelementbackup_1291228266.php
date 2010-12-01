<?php
$ASE_timestamp = '1291228266';
$ASE_time = 'December 1, 2010, 1:31 pm';
$ASE_savedby = 'obo,,iturgeon,127.0.0.1';
$ASE_snippet_raw = <<<'NOWDOC'
a:10:{s:2:"id";s:2:"26";s:4:"name";s:23:"InitializeElementBackup";s:11:"description";s:0:"";s:11:"editor_type";s:1:"0";s:8:"category";s:2:"10";s:10:"cache_type";s:1:"0";s:7:"snippet";s:417:"
$docObjs = array();
$docs =  $modx->getChildIds(0,999);
trace( count($docs));
$docObj = array_merge( $modx->getDocuments( array_values($docs), 1, 0, 'pagetitle'), $modx->getDocuments( array_values($docs), 0, 0, 'pagetitle'), $modx->getDocuments( array_values($docs), 0, 1, 'pagetitle'), $modx->getDocuments( array_values($docs), 1, 1, 'pagetitle')  );
trace( count($docObj));

trace($docs);
trace($docObj);
";s:6:"locked";s:1:"0";s:10:"properties";s:0:"";s:10:"moduleguid";s:2:" ";}'
NOWDOC;
?>