<?php
require_once("internal/app.php");

$API = \obo\API::getInstance();


// If login crudentials sent attempt to log in
if( isset($_REQUEST['username']) && isset($_REQUEST['password']) )
{
  $loggedIn = $API->doLogin($_REQUEST['username'],  $_REQUEST['password']);
}
else
{
  $loggedIn = $API->getSessionValid();  
}

if($loggedIn === true)
{
  include("assets/templates/viewer-main.php");
}
else
{
  $instData = $API->getInstanceData($_REQUEST['instID']);
  
  $title = $instData->name;
  $course = $instData->courseID;
  $instructor = $instData->userName;
  $startDate = date('m/j/y', $instData->startTime);
  $startTime = date('g:s a', $instData->startTime);
  $endDate = date('m/j/y', $instData->endTime);
  $endTime = date('g:s a', $instData->endTime);

  include("assets/templates/login.php");
}

?>