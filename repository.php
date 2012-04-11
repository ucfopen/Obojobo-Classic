<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
<meta charset="UTF-8" />
<title>Repository | Obojobo&#8482;</title>
<link rel="profile" href="http://gmpg.org/xfn/11" />

<script type='text/javascript' src='/wp/wp-includes/js/jquery/jquery.js?ver=1.7.1'></script>

<script type='text/javascript' src='/wp/wp-includes/js/swfobject.js?ver=2.2'></script>

<style type="text/css" media="screen">
  html, body{
    overflow: hidden;
    margin:0 !important;
    padding:0;
    height:100%;
  }
</style>
<script type="text/javascript">
  var SWF_URL = "Repository_2_0_4.swf";

  // START PREVENT CLOSE
  window.onbeforeunload = confirmExit;

   function thisMovie(movieName) {
       if (navigator.appName.indexOf("Microsoft") != -1) {
           return window[movieName];
       } else {
           return document[movieName];
       }
   }

  function confirmExit(){
    $isCloseable = thisMovie(SWF_URL).isCloseable();
    if($isCloseable !== true){
      return $isCloseable;
    }
  }
  // END PREVENT CLOSE
</script> 
<script type="text/javascript" defer="defer"> 
  // SWFOBJECT
  var flashvars = new Object();
  flashvars.view = ""; 
  flashvars.preview = ""; 

  var params = new Object();
  params.menu = "false";
  params.allowScriptAccess = "sameDomain";
  params.allowFullScreen = "true";
  params.base = "/assets/flash/";
  params.bgcolor = "#869ca7";

  var attributes = new Object(); 
  attributes.id = SWF_URL;
  attributes.name = SWF_URL;

  swfobject.embedSWF( "/assets/flash/" + SWF_URL, "flexApp", "100%", "100%", "10",  "/assets/flash/expressInstall.swf", flashvars, params, attributes);
</script>
</head>
<body >
  <div id="flexApp">
    <div style="margin: 0 auto; margin-top: 4em; border: thin solid gray; padding: 20px; width: 500px; color: #222222; font-family: Verdana,sans-serif; font-size: 73%; line-height: 130%;">
      <a style="padding-right: 20px; width: 158px; float: left; border: 0px;" href="http://www.adobe.com/go/getflashplayer"><img src="/assets/images/get_adobe_flash_player.png" alt="Download Flash Player" /></a>
      <p style="padding: 0; margin: 0; float: left; width: 320px;">
        Obojobo requires that you have the Flash Player plug-in (version 10 or greater) installed.<br /><a href="http://www.adobe.com/go/getflashplayer">Click here to download the latest version.</a>
      </p>
      <div style="clear:both;"></div>
    </div>
  </div>
  <script type='text/javascript' src='/wp/wp-includes/js/admin-bar.js?ver=20111130'></script>
</body>
</html>