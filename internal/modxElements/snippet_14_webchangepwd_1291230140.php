<?php
$ASE_timestamp = '1291230140';
$ASE_time = 'December 1, 2010, 2:02 pm';
$ASE_savedby = 'obojobo.ucf.edu,,,132.170.240.85';
$ASE_snippet_raw = <<<'NOWDOC'
a:10:{s:2:"id";s:2:"14";s:4:"name";s:12:"WebChangePwd";s:11:"description";s:95:"<strong>1.0</strong> Allows Web User to change their password from the front-end of the website";s:11:"editor_type";s:1:"0";s:8:"category";s:1:"0";s:10:"cache_type";s:1:"0";s:7:"snippet";s:860:"# Created By Raymond Irving April, 2005
#::::::::::::::::::::::::::::::::::::::::
# Params:	
#
#	&tpl			- (Optional)
#		Chunk name or document id to use as a template
#				  
#	Note: Templats design:
#			section 1: change pwd template
#			section 2: notification template 
#
# Examples:
#
#	[[WebChangePwd? &tpl=`ChangePwd`]] 

# Set Snippet Paths 
$snipPath  = (($modx->insideManager())? "../":"");
$snipPath .= "assets/snippets/";

# check if inside manager
if ($m = $modx->insideManager()) {
	return ''; # don't go any further when inside manager
}


# Snippet customize settings
$tpl		= isset($tpl)? $tpl:"";

# System settings
$isPostBack		= count($_POST) && isset($_POST['cmdwebchngpwd']);

# Start processing
include_once $snipPath."weblogin/weblogin.common.inc.php";
include_once $snipPath."weblogin/webchangepwd.inc.php";

# Return
return $output;



";s:6:"locked";s:1:"0";s:10:"properties";s:22:"&tpl=Template;string; ";s:10:"moduleguid";s:0:"";}'
NOWDOC;
?>