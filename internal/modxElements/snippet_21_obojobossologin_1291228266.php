<?php
$ASE_timestamp = '1291228266';
$ASE_time = 'December 1, 2010, 1:31 pm';
$ASE_savedby = 'obo,,iturgeon,127.0.0.1';
$ASE_snippet_raw = <<<'NOWDOC'
a:10:{s:2:"id";s:2:"21";s:4:"name";s:15:"ObojoboSSOLogin";s:11:"description";s:0:"";s:11:"editor_type";s:1:"0";s:8:"category";s:2:"10";s:10:"cache_type";s:1:"0";s:7:"snippet";s:579:"
require_once($modx->config['base_path'].'internal/config/config.php');
$externalAPI = \obo\API::getInstance();
if(\obo\util\Validator::isPosInt($_REQUEST['view']))
{
	// if they aren't already logged in try to log them in using sso
	if(! $externalAPI->getSessionValid());
	{
		$externalAPI->doLogin("", ""); // this will trigger the sso script inside ModUCFAuth
	}
	$url = $modx->makeUrl(33); // get a reference to the viewer page
	$modx->sendRedirect($url.'?view='. $_REQUEST['view']); // redirect to the requested learning object
	
}
return "Invalid arguments";
";s:6:"locked";s:1:"0";s:10:"properties";s:0:"";s:10:"moduleguid";s:2:" ";}'
NOWDOC;
?>