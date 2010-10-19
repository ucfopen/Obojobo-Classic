<?php
require_once(dirname(__FILE__) . '/../../../internal/app.php');

$DBM = core_db_DBManager::getConnection(new core_db_dbConnectData(AppCfg::DB_MODX_HOST, AppCfg::DB_MODX_USER, AppCfg::DB_MODX_PASS, AppCfg::DB_MODX_NAME, AppCfg::DB_MODX_TYPE));
?>