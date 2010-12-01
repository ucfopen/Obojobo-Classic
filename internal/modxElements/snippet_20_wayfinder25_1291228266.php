<?php
$ASE_timestamp = '1291228266';
$ASE_time = 'December 1, 2010, 1:31 pm';
$ASE_savedby = 'obo,,iturgeon,127.0.0.1';
$ASE_snippet_raw = <<<'NOWDOC'
a:10:{s:2:"id";s:2:"20";s:4:"name";s:12:"WayFinder2.5";s:11:"description";s:51:"<strong>2.5beta2</strong> Beta version of WayFinder";s:11:"editor_type";s:1:"0";s:8:"category";s:1:"0";s:10:"cache_type";s:1:"0";s:7:"snippet";s:5822:"
/**
 * Wayfinder Snippet to build site navigation menus
 *
 * Totally refactored from original DropMenu nav builder to make it easier to
 * create custom navigation by using chunks as output templates. By using
 * templates, many of the paramaters are no longer needed for flexible output
 * including tables, unordered- or ordered-lists (ULs or OLs), definition lists
 * (DLs) or in any other format you desire.
 *
 * @version 2.5 beta 2
 * @author Kyle Jaebker (muddydogpaws.com)
 * @author Ryan Thrash (collabpad.com)
 *
 * @example [[Wayfinder? &startId=`0`]]
 *
 */
$wf_version = '2.5beta2';
$wayfinder_base = $modx->config['base_path']."assets/snippets/wayfinder2.5/";

$phx = isset($phx) ? $phx : 1;
$debug = isset($debug) ? intval($debug) : 0;

$files = array(
	'wayfinder' => $wayfinder_base.'Wayfinder.class.php',
    //'firephp' => $wayfinder_base.'includes/FirePHPCore/FirePHP.class.php'
);

if ($phx) {
    $files['phx'] = $wayfinder_base.'includes/phx/phx.pre.class.inc.php';
}

if ($debug) {
	$files['debug'] = $wayfinder_base . 'includes/debug/debug.class.inc.php';
	$files['debug_templates'] = $wayfinder_base . 'includes/debug/debug.templates.php';
}

/* Include a custom config file if specified */
if (isset($config)) {
    $files['config'] = $wayfinder_base . 'configs/' . $config . '.config.php';
} else {
    $files['config'] = $wayfinder_base . 'configs/default.config.php';
}

/* Load classes */
foreach ($files as $filetype => $filename) {
	if (file_exists($filename)) {
		if (strpos($filename, '.class.')) {
            include_once($filename);
        } else {
            include($filename);
        }
	} else {
        $modx->logEvent(1, 3, 'Wayfinder: The following file was not found: ' . $filename);
        return 'Wayfinder: The following file was not found: ' . $filename;
	}
}

if (class_exists('Wayfinder')) {
    $wf = new Wayfinder($modx);
} else {
    return 'error: Wayfinder class not found';
}

/* validate against class file */
$versionCheck = $wf->versionCheck($wf_version);
if (!empty($versionCheck)) {
    return $versionCheck;
}

/* load config array */
$wf->config = array(
    'base_path' => $wayfinder_base,
    'id' => isset($startId) ? $startId : $modx->documentIdentifier,
    'level' => isset($level) ? $level : 0,
    'includeDocs' => isset($includeDocs) ? $includeDocs : 0,
    'excludeDocs' => isset($excludeDocs) ? $excludeDocs : 0,
    'ph' => isset($ph) ? $ph : FALSE,
    'debug' => $debug ? TRUE : FALSE,
    'ignoreHidden' => isset($ignoreHidden) ? $ignoreHidden : FALSE,
    'hideSubMenus' => isset($hideSubMenus) ? $hideSubMenus : FALSE,
    'useWeblinkUrl' => isset($useWeblinkUrl) ? $useWeblinkUrl : TRUE,
    'fullLink' => isset($fullLink) ? $fullLink : FALSE,
    'nl' => isset($removeNewLines) ? '' : "\n",
    'sortOrder' => isset($sortOrder) ? strtoupper($sortOrder) : 'ASC',
    'sortBy' => isset($sortBy) ? $sortBy : 'menuindex',
    'limit' => isset($limit) ? $limit : 0,
    'cssTpl' => isset($cssTpl) ? $cssTpl : FALSE,
    'jsTpl' => isset($jsTpl) ? $jsTpl : FALSE,
    'rowIdPrefix' => isset($rowIdPrefix) ? $rowIdPrefix : FALSE,
    'textOfLinks' => isset($textOfLinks) ? $textOfLinks : 'menutitle',
    'titleOfLinks' => isset($titleOfLinks) ? $titleOfLinks : 'pagetitle',
    'displayStart' => isset($displayStart) ? $displayStart : FALSE,
    'phx' => $phx ? TRUE : FALSE,
    'ignoreSecurity' => isset($ignoreSecurity) ? $ignoreSecurity : FALSE
);

//get user class definitions
$wf->css = array(
    'first' => isset($firstClass) ? $firstClass : '',
    'last' => isset($lastClass) ? $lastClass : 'last',
    'here' => isset($hereClass) ? $hereClass : 'active',
    'parent' => isset($parentClass) ? $parentClass : '',
    'row' => isset($rowClass) ? $rowClass : '',
    'outer' => isset($outerClass) ? $outerClass : '',
    'inner' => isset($innerClass) ? $innerClass : '',
    'level' => isset($levelClass) ? $levelClass: '',
    'self' => isset($selfClass) ? $selfClass : '',
    'weblink' => isset($webLinkClass) ? $webLinkClass : '',
    'odd' => isset($oddClass) ? $oddClass : '',
    'even' => isset($evenClass) ? $evenClass : '',
    'secure' => isset($secureClass) ? $secureClass : ''
);

//get user templates
$wf->templates = array(
    'outerTpl' => isset($outerTpl) ? $outerTpl : '',
    'rowTpl' => isset($rowTpl) ? $rowTpl : '',
    'parentRowTpl' => isset($parentRowTpl) ? $parentRowTpl : '',
    'parentRowHereTpl' => isset($parentRowHereTpl) ? $parentRowHereTpl : '',
    'hereTpl' => isset($hereTpl) ? $hereTpl : '',
    'innerTpl' => isset($innerTpl) ? $innerTpl : '',
    'innerRowTpl' => isset($innerRowTpl) ? $innerRowTpl : '',
    'innerHereTpl' => isset($innerHereTpl) ? $innerHereTpl : '',
    'activeParentRowTpl' => isset($activeParentRowTpl) ? $activeParentRowTpl : '',
    'categoryFoldersTpl' => isset($categoryFoldersTpl) ? $categoryFoldersTpl : '',
    'startItemTpl' => isset($startItemTpl) ? $startItemTpl : '',
    'lastRowTpl' => isset($lastRowTpl) ? $lastRowTpl : '',
    'firstRowTpl' => isset($firstRowTpl) ? $firstRowTpl : ''
);

//Process Wayfinder
$output = $wf->buildMenu();

if ($wf->config['debug']) {
    //$output .= $wf->renderDebugOutput();
}

// Handle debugging
if ($wf->config['debug'])
{
	$debug = new WFDebug($dbg_templates);
	if (isset($_GET['dbg_dump']))
	{
		$debug_html = $debug->render_popup($wf, $wf_version);
		if ($_GET['dbg_dump'] == 'save')
			$debug->saveDebugConsole($debug_html, $wf_version);
		else
			exit($debug_html);
	}
	else
		$output = $debug->render_link($wf) . $output;
}

//Ouput Results
if ($wf->config['ph']) {
    $modx->setPlaceholder($wf->config['ph'],$output);
} else {
    return $output;
}
";s:6:"locked";s:1:"0";s:10:"properties";s:0:"";s:10:"moduleguid";s:2:" ";}'
NOWDOC;
?>