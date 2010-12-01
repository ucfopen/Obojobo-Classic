<?php
$ASE_timestamp = '1291228266';
$ASE_time = 'December 1, 2010, 1:31 pm';
$ASE_savedby = 'obo,,iturgeon,127.0.0.1';
$ASE_snippet_raw = <<<'NOWDOC'
a:10:{s:2:"id";s:1:"6";s:4:"name";s:3:"Jot";s:11:"description";s:75:"<strong>1.1.4</strong> User comments with moderation and email subscription";s:11:"editor_type";s:1:"0";s:8:"category";s:1:"0";s:10:"cache_type";s:1:"0";s:7:"snippet";s:1971:"/*####
#
# Author: Armand "bS" Pondman (apondman@zerobarrier.nl)
#
# Latest Version: http://modxcms.com/Jot-998.html
# Jot Demo Site: http://projects.zerobarrier.nl/modx/
# Documentation: http://wiki.modxcms.com/index.php/Jot (wiki)
#
####*/

$jotPath = $modx->config['base_path'] . 'assets/snippets/jot/';
include_once($jotPath.'jot.class.inc.php');

$Jot = new CJot;
$Jot->VersionCheck("1.1.4");
$Jot->Set("path",$jotPath);
$Jot->Set("action", $action);
$Jot->Set("postdelay", $postdelay);
$Jot->Set("docid", $docid);
$Jot->Set("tagid", $tagid);
$Jot->Set("subscribe", $subscribe);
$Jot->Set("moderated", $moderated);
$Jot->Set("captcha", $captcha);
$Jot->Set("badwords", $badwords);
$Jot->Set("bw", $bw);
$Jot->Set("sortby", $sortby);
$Jot->Set("numdir", $numdir);
$Jot->Set("customfields", $customfields);
$Jot->Set("guestname", $guestname);
$Jot->Set("canpost", $canpost);
$Jot->Set("canview", $canview);
$Jot->Set("canedit", $canedit);
$Jot->Set("canmoderate", $canmoderate);
$Jot->Set("trusted", $trusted);
$Jot->Set("pagination", $pagination);
$Jot->Set("placeholders", $placeholders);
$Jot->Set("subjectSubscribe", $subjectSubscribe);
$Jot->Set("subjectModerate", $subjectModerate);
$Jot->Set("subjectAuthor", $subjectAuthor);
$Jot->Set("notify", $notify);
$Jot->Set("notifyAuthor", $notifyAuthor);
$Jot->Set("validate", $validate);
$Jot->Set("title", $title);
$Jot->Set("authorid", $authorid);
$Jot->Set("css", $css);
$Jot->Set("cssFile", $cssFile);
$Jot->Set("cssRowAlt", $cssRowAlt);
$Jot->Set("cssRowMe", $cssRowMe);
$Jot->Set("cssRowAuthor", $cssRowAuthor);
$Jot->Set("tplForm", $tplForm);
$Jot->Set("tplComments", $tplComments);
$Jot->Set("tplModerate", $tplModerate);
$Jot->Set("tplNav", $tplNav);
$Jot->Set("tplNotify", $tplNotify);
$Jot->Set("tplNotifyModerator", $tplNotifyModerator);
$Jot->Set("tplNotifyAuthor", $tplNotifyAuthor);
$Jot->Set("tplSubscribe", $tplSubscribe);
$Jot->Set("debug", $debug);
$Jot->Set("output", $output);
return $Jot->Run();";s:6:"locked";s:1:"0";s:10:"properties";s:0:"";s:10:"moduleguid";s:0:"";}'
NOWDOC;
?>