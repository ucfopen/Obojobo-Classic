<?php
  $loggedIn = $API->getSessionValid();  

  // ================ LOGIN OR CHECK EXISTING LOGIN ===========================
  if(isset($_REQUEST['cmdweblogin']) || isset($_REQUEST['SAMLResponse']))
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

