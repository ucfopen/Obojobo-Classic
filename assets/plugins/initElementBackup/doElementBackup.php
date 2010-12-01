<?php


if(!function_exists('friendly_url'))
{
	function friendly_url($url)
	{
		//replace accent characters, depends your language is needed
		$a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', 'Œ', 'œ', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', 'Š', 'š', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', 'Ÿ', '?', '?', '?', '?', 'Ž', 'ž', '?', 'ƒ', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?'); 
		$b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o'); 
		$var= str_replace($a, $b, $url);

		// everything to lower and no spaces begin or end
		$url = strtolower(trim($url));
 
		// decode html maybe needed if there's html I normally don't use this
		//$url = html_entity_decode($url,ENT_QUOTES,'UTF8');
 
		// adding - for spaces and union characters
		$find = array(' ', '&', '\r\n', '\n', '+',',');
		$url = str_replace ($find, '-', $url);
 
		//delete and replace rest of special chars
		$find = array('/[^a-z0-9\-<>]/', '/[\-]+/', '/<[^>]*>/');
		$repl = array('', '-', '');
		$url = preg_replace ($find, $repl, $url);
 
		//return the friendly url
		return $url; 
	}
}

// allows us to call this script w/o using the event system
if(isset($executeInitElementBackupType))
{
	$event = $executeInitElementBackupType['event'];
	$id = $executeInitElementBackupType['id'];
}
// listen to the built in modx events
else
{
	$e = &$modx->Event;
	$event = $e->name;
	$id = $e->params['id'];
}

$time = time();

// determine what to save
switch($event)
{
	case 'OnChunkFormSave':
	
		$sql = "SELECT * FROM {$modx->getFullTableName('site_htmlsnippets')} WHERE id = '{$id}'";
		$rs = $modx->db->query($sql);
		$chunk = $modx->db->getRow($rs);
	
		$type = 'chunk';
		$name = $chunk['name'];
		$output = "\$ASE_chunk_raw = <<<'NOWDOC'\n" . serialize($chunk) . "'\nNOWDOC;\n";
		break;

	case 'OnTVFormSave':
		// get tv
		$sql = "SELECT * FROM {$modx->getFullTableName('site_tmplvars')} WHERE id = '{$id}'";
		$rs = $modx->db->query($sql);
		$tv = $modx->db->getRow($rs);
		
		// get mapping of tvs to templates
		$sql = "SELECT * FROM {$modx->getFullTableName('site_tmplvar_templates')} WHERE tmplvarid = '{$id}'";
		$rs = $modx->db->query($sql);
		$templateMaping = $modx->db->makeArray($rs);
		
		// get mapping of tvs to document groups
		$sql = "SELECT * FROM {$modx->getFullTableName('site_tmplvar_access')} WHERE tmplvarid = '{$id}'";
		$rs = $modx->db->query($sql);
		$docMaping = $modx->db->makeArray($rs);
	
		$type = 'tv';
		$name = $tv['name'];
		$output = "\$ASE_tv_raw = = <<<'NOWDOC'\n"  . serialize($tv) . "'\nNOWDOC;\n";
		$output .= "\$ASE_tv_map_to_template_raw = <<<'NOWDOC'\n"  . serialize($templateMaping) . "'\nNOWDOC;\n";
		$output .= "\$ASE_tv_map_to_docgroup_raw = <<<'NOWDOC'\n"  . serialize($docMaping) . "'\nNOWDOC;\n";

		break;

	case 'OnSnipFormSave':

		// get snippet
		$sql = "SELECT * FROM {$modx->getFullTableName('site_snippets')} WHERE id = '{$id}'";
		$rs = $modx->db->query($sql);
		$snip = $modx->db->getRow($rs);

		$type = 'snippet';
		$name = $snip['name'];
		$output = "\$ASE_snippet_raw = <<<'NOWDOC'\n"  . serialize($snip) . "'\nNOWDOC;\n";

		break;

	case 'OnPluginFormSave':
	
		// get plugin
		$sql = "SELECT * FROM {$modx->getFullTableName('site_plugins')} WHERE id = '{$id}'";
		$rs = $modx->db->query($sql);
		$plugin = $modx->db->getRow($rs);
		
		// get event mapping
		$sql = "SELECT * FROM {$modx->getFullTableName('site_plugin_events')} WHERE pluginid = '{$id}'";
		$rs = $modx->db->query($sql);
		$eventMapping = $modx->db->makeArray($rs);
	
		$type = 'plugin';
		$name = $plugin['name'];
		$output = "\$ASE_plugin_raw = <<<'NOWDOC'\n"  . serialize($plugin) . "'\nNOWDOC;\n";
		$output .= "\$ASE_plugin_map_to_event_raw = <<<'NOWDOC'\n" . serialize($eventMapping) . "'\nNOWDOC;\n";
		
		break;
	
	case 'OnTempFormSave':
	
		// get template
		$sql = "SELECT * FROM {$modx->getFullTableName('site_templates')} WHERE id = '{$id}'";
		$rs = $modx->db->query($sql);
		$template = $modx->db->getRow($rs);
	
		$type = 'template';
		$name = $template['templatename'];
		$output = "\$ASE_template_raw = <<<'NOWDOC'\n"  . serialize($template) . "'\nNOWDOC;\n";
		
		break;
	
	case 'OnDocFormSave':
	
		// get document
		$sql = "SELECT * FROM {$modx->getFullTableName('site_content')} WHERE id = '{$id}'";
		$rs = $modx->db->query($sql);
		$doc = $modx->db->getRow($rs);
		
		// get template Variable Values
		$sql = "SELECT * FROM {$modx->getFullTableName('site_tmplvar_contentvalues')} WHERE contentid  = '{$id}'";
		$rs = $modx->db->query($sql);
		$tvValues = $modx->db->makeArray($rs);
	
		// get document groups
		$sql = "SELECT * FROM {$modx->getFullTableName('document_groups')} WHERE document  = '{$id}'";
		$rs = $modx->db->query($sql);
		$docGroups = $modx->db->makeArray($rs);
	
	
		$type = 'doc';
		$name = $doc['pagetitle'];
		$time = $doc['editedon'];
		$output = "\$ASE_doc_raw = <<<'NOWDOC'\n"  . serialize($doc) . "';\n";
		$output .= "\$ASE_doc_map_to_tv_value_raw = <<<'NOWDOC'\n"  . serialize($tvValues) . "'\nNOWDOC;\n";
		$output .= "\$ASE_doc_map_to_group_raw = <<<'NOWDOC'\n"  . serialize($docGroups) . "'\nNOWDOC;\n";

		break;
	default:
		return 1;
		break;
	

}

// write the file
if(!file_exists($modx->config['base_path'].'internal/modxElements/'))
{
	mkdir($modx->config['base_path'].'internal/modxElements/', 0755, true);
}
$file = $modx->config['base_path'].'internal/modxElements/'.$type.'_'. $id . '_' . friendly_url($name) . '_' . $time . '.php';
$fp = fopen($file, 'w');
fwrite($fp, "<?php\n");
fwrite($fp, '$ASE_timestamp = \'' . $time . "';\n");
fwrite($fp, '$ASE_time = \'' . date("F j, Y, g:i a", $time) . "';\n");
fwrite($fp, '$ASE_savedby = \''. $_SERVER['SERVER_NAME'] . ',' . $_ENV['USERNAME'] . ',' . $_ENV['USER'] . ',' . $_SERVER['SERVER_ADDR'] ."';\n");
fwrite($fp, $output);
fwrite($fp, "?>");
fclose($fp);
unset($output);


return 1;

?>