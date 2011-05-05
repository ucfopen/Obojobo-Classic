<?php
require_once(dirname(__FILE__) . '/../../../internal/app.php');

$DBM = \rocketD\db\DBManager::getConnection(new \rocketD\db\dbConnectData(\AppCfg::DB_MODX_HOST, \AppCfg::DB_MODX_USER, \AppCfg::DB_MODX_PASS, \AppCfg::DB_MODX_NAME, \AppCfg::DB_MODX_TYPE));
?>