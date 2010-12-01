<?php

$docIDs = $modx->getChildIds(0, 9999);
echo '<pre>';

//******************** DOCUMENTS **********************/
echo "-------------- DOCUMENTS ----------------\n";
$sql = "SELECT * FROM {$modx->getFullTableName('site_content')}";
$rs = $modx->db->query($sql);
$docs = $modx->db->makeArray($rs);

foreach($docs AS $doc)
{	
	echo "{$doc['id']} {$doc['pagetitle']} saved\n";
	$executeInitElementBackupType =  array('event' => 'OnDocFormSave', 'id' => $doc['id']);
	$catch = include('doElementBackup.php');
}

//******************** SITE TEMPLATES **********************/
echo "-------------- TEMPLATES ----------------\n";
$sql = "SELECT * FROM {$modx->getFullTableName('site_templates')}";
$rs = $modx->db->query($sql);
$docs = $modx->db->makeArray($rs);

foreach($docs AS $doc)
{	
	echo "{$doc['id']} {$doc['templatename']} saved\n";
	$executeInitElementBackupType =  array('event' => 'OnTempFormSave', 'id' => $doc['id']);
	$catch = include('doElementBackup.php');
}

//******************** PLUGINS **********************/
echo "-------------- PLUGINS ----------------\n";
$sql = "SELECT * FROM {$modx->getFullTableName('site_plugins')}";
$rs = $modx->db->query($sql);
$docs = $modx->db->makeArray($rs);

foreach($docs AS $doc)
{	
	echo "{$doc['id']} {$doc['name']} saved\n";
	$executeInitElementBackupType =  array('event' => 'OnPluginFormSave', 'id' => $doc['id']);
	$catch = include('doElementBackup.php');
}

//******************** SNIPPETS **********************/
echo "-------------- SNIPPETS ----------------\n";
$sql = "SELECT * FROM {$modx->getFullTableName('site_snippets')}";
$rs = $modx->db->query($sql);
$docs = $modx->db->makeArray($rs);

foreach($docs AS $doc)
{	
	echo "{$doc['id']} {$doc['name']} saved\n";
	$executeInitElementBackupType =  array('event' => 'OnSnipFormSave', 'id' => $doc['id']);
	$catch = include('doElementBackup.php');
}

//******************** TEMPLATE VARIABLES **********************/
echo "-------------- TEMPLATE VARS ----------------\n";
$sql = "SELECT * FROM {$modx->getFullTableName('site_tmplvars')}";
$rs = $modx->db->query($sql);
$docs = $modx->db->makeArray($rs);

foreach($docs AS $doc)
{	
	echo "{$doc['id']} {$doc['name']} saved\n";
	$executeInitElementBackupType =  array('event' => 'OnTVFormSave', 'id' => $doc['id']);
	$catch = include('doElementBackup.php');
}


//******************** CHUNKS **********************/
echo "-------------- CHUNKS ----------------\n";
$sql = "SELECT * FROM {$modx->getFullTableName('site_htmlsnippets')}";
$rs = $modx->db->query($sql);
$docs = $modx->db->makeArray($rs);

foreach($docs AS $doc)
{	
	echo "{$doc['id']} {$doc['name']} saved\n";
	$executeInitElementBackupType =  array('event' => 'OnChunkFormSave', 'id' => $doc['id']);
	$catch = include('doElementBackup.php');
}


//include_once 'doElementBackup.php';

?>