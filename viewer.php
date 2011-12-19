<?php
require_once("internal/app.php");
$API = \obo\API::getInstance();


// ================ LOGIN OR CHECK LOGIN ===========================
if( isset($_REQUEST['username']) && isset($_REQUEST['password']) )
{
  $loggedIn = $API->doLogin($_REQUEST['username'],  $_REQUEST['password']);
}
else
{
  $loggedIn = $API->getSessionValid();  
}

// ================ DISPLAY OUTPUT =================================
if($loggedIn === true) // logged in, show the viewer
{
  include("assets/templates/viewer-main.php");
}
else // not logged in
{

  // ================ PREPARE VARS FOR THE TEMPLATE ================


  // Instance requested - student mode
  if(isset($_REQUEST['instID']))
  {
    if($instData = $API->getInstanceData($_REQUEST['instID']))
    {
      $title = $instData->name;
      $course = $instData->courseID;
      $instructor = $instData->userName;
      $startDate = date('m/j/y', $instData->startTime);
      $startTime = date('g:s a', $instData->startTime);
      $endDate = date('m/j/y', $instData->endTime);
      $endTime = date('g:s a', $instData->endTime);
    }
    else
    {
      header("HTTP/1.0 404 Not Found");
      exit();
    }
  }

  // lo requested - preview mode
  elseif(isset($_REQUEST['loID']))
  {
    if($loMeta = $API->getLOMeta($_REQUEST['loID']))
    {
      $title = $loMeta->title . ' ' . $loMeta->version . '.' . $loMeta->subVersion;
      $course = 'PREVIEW ONLY';
      $instructor = 'only visible to authors';
      $startDate = 'Date Here';
      $startTime = 'Time Here';
      $endDate = 'Date Here';
      $endTime = 'Time Here';    
    }
    else
    {
      header("HTTP/1.0 404 Not Found");
      exit();
    }
  }
 
  // =============== RENDER LOGIN TEMPLATE ========================
  include("assets/templates/login.php");
}

?>
