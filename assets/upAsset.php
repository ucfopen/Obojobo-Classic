<?php
/**
 * This script recieves a file upload and saves it to the media library
 * 
 * GET parameters:
 * 't' = Title of media file
 * 'd' = Description of media file
 * 'c' = Copyright info of file
 * 'l' = Length (in seconds) of audio file
 * 'PHPSESSID' = PHP Session ID
 */
require_once(dirname(__FILE__)."/../internal/app.php");

session_id($_GET['s']);
session_name(AppCfg::SESSION_NAME);
session_start();
header("Expires: mon, 06 jan 1990 00:00:01 gmt");
header("Pragma: no-cache");
header("Cache-Control: no-store, no-cache, must-revalidate");

$mediaMan = nm_los_MediaManager::getInstance();
$return =  $mediaMan->handleMediaUpload($_FILES['Filedata'], $_GET['t'], $_GET['d'], $_GET['c'], $_GET['l']);


if($return == true) echo 0; // return success
else if($return == false) echo 1000; // return other error
else echo $return; // return the filedata error


?>