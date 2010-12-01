<?php
$ASE_timestamp = '1291228266';
$ASE_time = 'December 1, 2010, 1:31 pm';
$ASE_savedby = 'obo,,iturgeon,127.0.0.1';
$ASE_plugin_raw = <<<'NOWDOC'
a:11:{s:2:"id";s:2:"18";s:4:"name";s:10:"TransAlias";s:11:"description";s:97:"<strong>1.0.1</strong> Human readible URL translation supporting multiple languages and overrides";s:11:"editor_type";s:1:"0";s:8:"category";s:1:"5";s:10:"cache_type";s:1:"0";s:10:"plugincode";s:1196:"/*
 * Initialize parameters
 */
if (!isset ($alias)) { return ; }
if (!isset ($plugin_dir) ) { $plugin_dir = 'transalias'; }
if (!isset ($plugin_path) ) { $plugin_path = $modx->config['base_path'].'assets/plugins/'.$plugin_dir; }
if (!isset ($table_name)) { $table_name = 'common'; }
if (!isset ($char_restrict)) { $char_restrict = 'lowercase alphanumeric'; }
if (!isset ($remove_periods)) { $remove_periods = 'No'; }
if (!isset ($word_separator)) { $word_separator = 'dash'; }
if (!isset ($override_tv)) { $override_tv = ''; }

if (!class_exists('TransAlias')) {
    require_once $plugin_path.'/transalias.class.php';
}
$trans = new TransAlias($modx);

/*
 * see if TV overrides the table name
 */
if(!empty($override_tv)) {
    $tvval = $trans->getTVValue($override_tv);
    if(!empty($tvval)) {
        $table_name = $tvval;
    }
}

/*
 * Handle events
 */
$e =& $modx->event;
switch ($e->name ) {
    case 'OnStripAlias':
        if ($trans->loadTable($table_name, $remove_periods)) {
            $output = $trans->stripAlias($alias,$char_restrict,$word_separator);
            $e->output($output);
            $e->stopPropagation();
        }
        break ;
    default:
        return ;
}";s:6:"locked";s:1:"0";s:10:"properties";s:331:"&table_name=Trans table;list;common,russian,utf8,utf8lowercase;utf8lowercase &char_restrict=Restrict alias to;list;lowercase alphanumeric,alphanumeric,legal characters;legal characters &remove_periods=Remove Periods;list;Yes,No;No &word_separator=Word Separator;list;dash,underscore,none;dash &override_tv=Override TV name;string; ";s:8:"disabled";s:1:"0";s:10:"moduleguid";s:0:"";}'
NOWDOC;
$ASE_plugin_map_to_event_raw = <<<'NOWDOC'
a:1:{i:0;a:3:{s:8:"pluginid";s:2:"18";s:5:"evtid";s:3:"100";s:8:"priority";s:1:"0";}}'
NOWDOC;
?>