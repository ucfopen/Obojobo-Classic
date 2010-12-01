<?php
$ASE_timestamp = '1291228266';
$ASE_time = 'December 1, 2010, 1:31 pm';
$ASE_savedby = 'obo,,iturgeon,127.0.0.1';
$ASE_snippet_raw = <<<'NOWDOC'
a:10:{s:2:"id";s:2:"16";s:4:"name";s:9:"WebSignup";s:11:"description";s:66:"<strong>1.1</strong> Basic Web User account creation/signup system";s:11:"editor_type";s:1:"0";s:8:"category";s:1:"0";s:10:"cache_type";s:1:"0";s:7:"snippet";s:1805:"# Created By Raymond Irving April, 2005
#::::::::::::::::::::::::::::::::::::::::
# Usage:     
#    Allows a web user to signup for a new web account from the website
#    This snippet provides a basic set of form fields for the signup form
#    You can customize this snippet to create your own signup form
#
# Params:    
#
#    &tpl        - (Optional) Chunk name or document id to use as a template
#    &groups     - Web users groups to be assigned to users
#    &useCaptcha - (Optional) Determine to use (1) or not to use (0) captcha
#                  on signup form - if not defined, will default to system
#                  setting. GD is required for this feature. If GD is not 
#                  available, useCaptcha will automatically be set to false;
#                  
#    Note: Templats design:
#        section 1: signup template
#        section 2: notification template 
#
# Examples:
#
#    [[WebSignup? &tpl=`SignupForm` &groups=`NewsReaders,WebUsers`]] 

# Set Snippet Paths 
$snipPath = $modx->config['base_path'] . "assets/snippets/";

# check if inside manager
if ($m = $modx->insideManager()) {
    return ''; # don't go any further when inside manager
}


# Snippet customize settings
$tpl = isset($tpl)? $tpl:"";
$useCaptcha = isset($useCaptcha)? $useCaptcha : $modx->config['use_captcha'] ;
// Override captcha if no GD
if ($useCaptcha && !gd_info()) $useCaptcha = 0;

# setup web groups
$groups = isset($groups) ? explode(',',$groups):array();
for($i=0;$i<count($groups);$i++) $groups[$i] = trim($groups[$i]);

# System settings
$isPostBack        = count($_POST) && isset($_POST['cmdwebsignup']);

$output = '';

# Start processing
include_once $snipPath."weblogin/weblogin.common.inc.php";
include_once $snipPath."weblogin/websignup.inc.php";

# Return
return $output;";s:6:"locked";s:1:"0";s:10:"properties";s:22:"&tpl=Template;string; ";s:10:"moduleguid";s:0:"";}'
NOWDOC;
?>