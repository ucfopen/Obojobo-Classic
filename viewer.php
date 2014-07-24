<?php
require_once("internal/app.php");
$API = \obo\API::getInstance();

// ================ LOGIN OR CHECK EXISTING LOGIN ===========================
if(( isset($_REQUEST['username']) && isset($_REQUEST['password'])) || isset($_REQUEST['SAMLResponse']))
{
  $url = $_SERVER['REQUEST_URI'];
  if ($url != "/saml/acs")
    setcookie("redir", $url, 0, "/");
  else
    $url = $_COOKIE['redir'];
  if (!isset($_REQUEST['username']))
    $_REQUEST['username'] = "";
  if (!isset($_REQUEST['password']))
    $_REQUEST['password'] = "";

  $loggedIn = $API->doLogin($_REQUEST['username'],  $_REQUEST['password']);
  if($loggedIn !== true)
  {
    $notice = 'Invalid Login';
  } else {
    header("Location: $url");
  }
}
else
{
  $loggedIn = $API->getSessionValid();  
}

// ================= CHECK FOR REQUIRED ROLE =======================

if($loggedIn === true && isset($_REQUEST['loID']))
{
  $hasRole = $API->getSessionRoleValid(array(\cfg_obo_Role::CONTENT_CREATOR, \cfg_obo_Role::LIBRARY_USER));
  if(!in_array(\cfg_obo_Role::LIBRARY_USER, $hasRole['hasRoles']) && !in_array(\cfg_obo_Role::CONTENT_CREATOR, $hasRole['hasRoles']))
  {
    $loggedIn = false;
    $notice = 'You do not have permission to preview this learning object. For more information view our <a href="/help/faq/">FAQ</a>.';
  }
}

// ================ DISPLAY OUTPUT =================================
// logged in, show the viewer
if($loggedIn === true) 
{
  header('X-UA-Compatible: IE=edge');
  include("assets/templates/viewer-main.php");
}

// not logged in, show login screen
else 
{

  // ================ PREPARE VARS FOR THE TEMPLATE ================

  // Instance requested - student mode
  if(isset($_REQUEST['instID']))
  {
    if($instData = $API->getInstanceData($_REQUEST['instID']))
    {
      // Reject access if this is attempted direct access to an LTI instance:
      if(!empty($instData->externalLink))
      {
        $ltiApi = \lti\API::getInstance();
        if(!$ltiApi->getAssessmentSessionData($_REQUEST['instID']))
        {
          // No session data for LTI - Either they got logged out or they accessed the instance directly.
          header('Location: ' . \AppCfg::URL_WEB . 'error/no-access');
          exit();
        }
      }

      $title = $instData->name;
      $course = $instData->courseID;
      $instructor = $instData->userName;
      $startTime = $instData->startTime;
      $endTime = $instData->endTime;
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
      $startTime = 0;
      $endTime = 0;
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
