<?php
$ASE_timestamp = '1291228266';
$ASE_time = 'December 1, 2010, 1:31 pm';
$ASE_savedby = 'obo,,iturgeon,127.0.0.1';
$ASE_plugin_raw = <<<'NOWDOC'
a:11:{s:2:"id";s:2:"15";s:4:"name";s:14:"Quick Manager+";s:11:"description";s:79:"<strong>1.3.4.1</strong> Enables QuickManager front end content editing support";s:11:"editor_type";s:1:"0";s:8:"category";s:1:"5";s:10:"cache_type";s:1:"0";s:10:"plugincode";s:484:"$show = TRUE;

if ($disabled  != '') {
    $arr = explode(",", $disabled );
    if (in_array($modx->documentIdentifier, $arr)) {
        $show = FALSE;
    }
}

if ($show) {
    include_once($modx->config['base_path'].'assets/plugins/qm/qm.inc.php');
    $qm = new Qm($modx, $jqpath, $loadmanagerjq, $loadfrontendjq, $noconflictjq, $loadtb, $tbwidth, $tbheight, $hidefields, $hidetabs, $hidesections, $addbutton, $tpltype, $tplid, $custombutton, $managerbutton, $logout, $autohide);
}";s:6:"locked";s:1:"0";s:10:"properties";s:993:"&jqpath=Path to jQuery;text;assets/js/jquery-1.3.2.min.js &loadmanagerjq=Load jQuery in manager;list;true,false;true &loadfrontendjq=Load jQuery in front-end;list;true,false;true &noconflictjq=jQuery noConflict mode in front-end;list;true,false;true &loadtb=Load modal box in front-end;list;true,false;true &tbwidth=Modal box window width;text;80% &tbheight=Modal box window height;text;90% &hidefields=Hide document fields from front-end editors;text;parent &hidetabs=Hide document tabs from front-end editors;text; &hidesections=Hide document sections from front-end editors;text; &addbutton=Show add document here button;list;true,false;true &tpltype=New document template type;list;parent,id,selected;parent &tplid=New document template id;int;3 &custombutton=Custom buttons;textarea; &managerbutton=Show go to manager button;list;true,false;true &logout=Logout to;list;manager,front-end;manager &disabled=Plugin disabled on documents;text; &autohide=Autohide toolbar;list;true,false;true ";s:8:"disabled";s:1:"0";s:10:"moduleguid";s:0:"";}'
NOWDOC;
$ASE_plugin_map_to_event_raw = <<<'NOWDOC'
a:4:{i:0;a:3:{s:8:"pluginid";s:2:"15";s:5:"evtid";s:2:"31";s:8:"priority";s:1:"0";}i:1;a:3:{s:8:"pluginid";s:2:"15";s:5:"evtid";s:2:"28";s:8:"priority";s:1:"0";}i:2;a:3:{s:8:"pluginid";s:2:"15";s:5:"evtid";s:2:"13";s:8:"priority";s:1:"0";}i:3;a:3:{s:8:"pluginid";s:2:"15";s:5:"evtid";s:1:"3";s:8:"priority";s:1:"0";}}'
NOWDOC;
?>