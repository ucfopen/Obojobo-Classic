<?php
require_once(dirname(__FILE__)."/../app.php");
$DBM = \rocketD\db\DBManager::getConnection(new \rocketD\db\dbConnectData(\AppCfg::DB_HOST, \AppCfg::DB_USER, \AppCfg::DB_PASS, \AppCfg::DB_NAME, \AppCfg::DB_TYPE));


$DBM->querySafe("UPDATE obo_logs SET itemType = '?' WHERE itemType = '?' ", '\\obo\\logs\\CleanOrphans', 'nm_los_tracking_CleanOrphans');
$DBM->querySafe("UPDATE obo_logs SET itemType = '?' WHERE itemType = '?' ", '\\obo\\logs\\DeleteInstance', 'nm_los_tracking_DeleteInstance');
$DBM->querySafe("UPDATE obo_logs SET itemType = '?' WHERE itemType = '?' ", '\\obo\\logs\\DeleteLO', 'nm_los_tracking_DeleteLO');
$DBM->querySafe("UPDATE obo_logs SET itemType = '?' WHERE itemType = '?' ", '\\obo\\logs\\EndAttempt', 'nm_los_tracking_EndAttempt');
$DBM->querySafe("UPDATE obo_logs SET itemType = '?' WHERE itemType = '?' ", '\\obo\\logs\\ImportScore', 'nm_los_tracking_ImportScore');
$DBM->querySafe("UPDATE obo_logs SET itemType = '?' WHERE itemType = '?' ", '\\obo\\logs\\PageChanged', 'nm_los_tracking_PageChanged');
$DBM->querySafe("UPDATE obo_logs SET itemType = '?' WHERE itemType = '?' ", '\\obo\\logs\\ResumeAttempt', 'nm_los_tracking_ResumeAttempt');
$DBM->querySafe("UPDATE obo_logs SET itemType = '?' WHERE itemType = '?' ", '\\obo\\logs\\SectionChanged', 'nm_los_tracking_SectionChanged');
$DBM->querySafe("UPDATE obo_logs SET itemType = '?' WHERE itemType = '?' ", '\\obo\\logs\\StartAttempt', 'nm_los_tracking_StartAttempt');
$DBM->querySafe("UPDATE obo_logs SET itemType = '?' WHERE itemType = '?' ", '\\obo\\logs\\SubmitMedia', 'nm_los_tracking_SubmitMedia');
$DBM->querySafe("UPDATE obo_logs SET itemType = '?' WHERE itemType = '?' ", '\\obo\\logs\\SubmitQuestion', 'nm_los_tracking_SubmitQuestion');
$DBM->querySafe("UPDATE obo_logs SET itemType = '?' WHERE itemType = '?' ", '\\obo\\logs\\Visited', 'nm_los_tracking_Visited');
$DBM->querySafe("UPDATE obo_logs SET itemType = '?' WHERE itemType = '?' ", '\\obo\\logs\\LoggedIn', 'nm_los_tracking_LoggedIn');
$DBM->querySafe("UPDATE obo_logs SET itemType = '?' WHERE itemType = '?' ", '\\obo\\logs\\LoggedOut', 'nm_los_tracking_LoggedOut');
$DBM->querySafe("UPDATE obo_logs SET itemType = '?' WHERE itemType = '?' ", '\\obo\\logs\\NextPreviousUsed', 'nm_los_tracking_NextPreviousUsed');

exit('done');


?>