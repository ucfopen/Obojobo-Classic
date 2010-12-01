<?php
$ASE_timestamp = '1291228266';
$ASE_time = 'December 1, 2010, 1:31 pm';
$ASE_savedby = 'obo,,iturgeon,127.0.0.1';
$ASE_snippet_raw = <<<'NOWDOC'
a:10:{s:2:"id";s:2:"18";s:4:"name";s:14:"doDisplayIndex";s:11:"description";s:0:"";s:11:"editor_type";s:1:"0";s:8:"category";s:2:"10";s:10:"cache_type";s:1:"0";s:7:"snippet";s:683:"
$content = "";
$tv = $modx->getTemplateVar('displayIndex', "", $modx->documentIdentifier);
if(is_array($tv)){
	if($tv['value']){
		if(strlen($modx->documentObject['menutitle']) > 0){
			$title = $modx->documentObject['menutitle'];
		}
		else{
			$title = $modx->documentObject['pagetitle'];
		}
		$content = '<h1>Index of ' . $title . '</h1>';
		//$content .= $modx->runSnippet('Wayfinder', array('startId' => $modx->documentIdentifier, 'level' => 99)); 
                $content .= $modx->runSnippet('Wayfinder2.5', array('startId' => $modx->documentIdentifier, 'level' => 99, 'ignoreSecurity' => true, 'secureClass' => 'protectedLink')); 
	}
}

return $content;
";s:6:"locked";s:1:"0";s:10:"properties";s:0:"";s:10:"moduleguid";s:2:" ";}'
NOWDOC;
?>