<?php
require_once(dirname(__FILE__)."/../app.php");
ini_set('error_log', \AppCfg::DIR_BASE.\AppCfg::DIR_LOGS.'php_errors'. date('m_d_y', time()) .'_cron.txt');
error_log('NIDUPDATE CRON RUN');
$modPeopleSoft = new nm_los_auth_ModPeopleSoft();
$modPeopleSoft->updateNIDChanges();
?>