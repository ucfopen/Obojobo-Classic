<?php
$ASE_timestamp = '1310421200';
$ASE_time = 'July 11, 2011, 5:53 pm';
$ASE_savedby = 'obo,,iturgeon,127.0.0.1';
$ASE_template_raw = <<<'NOWDOC'
a:9:{s:2:"id";s:1:"5";s:12:"templatename";s:11:"FlexMinimal";s:11:"description";s:0:"";s:11:"editor_type";s:1:"0";s:8:"category";s:1:"0";s:4:"icon";s:0:"";s:13:"template_type";s:1:"0";s:7:"content";s:4952:"<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>[(site_name)] | [*pagetitle*]</title>
	<base href="[(site_url)]" >
	<meta name="generator" content="TextMate http://macromates.com/">
	<meta name="author" content="Ian Turgeon">
	<style type="text/css" media="screen">
		html, body{
                        overflow: hidden;
			margin:0;
			padding:0;
			height:100%;
		}
		#flexApp{
			margin:0;
			padding:0;
			width:100%;
			height:100%;
		}
	</style>

	<script type="text/javascript" src="/assets/js/chromeless_35.js"></script>
		<script type="text/javascript" src="/assets/js/swfobject.js"></script>
	<script type="text/javascript">
			
		// START CHROMELESS POPUP
		//For paramater explanations, see accompanying faq.htm file
		function openIT(u,W,H,X,Y,n,b,x,m,r) {
			var cU  ='close.gif'   //gif for close on normal state.
			var cO  ='close.gif'  //gif for close on mouseover.
			var cL  ='clock.gif'      //gif for loading indicator.
			var mU  ='minimize.gif'     //gif for minimize to taskbar on normal state.
			var mO  ='minimize.gif'    //gif for minimize to taskbar on mouseover.
			var xU  ='max.gif'     //gif for maximize normal state.
			var xO  ='max.gif'    //gif for maximize on mouseover.
			var rU  ='restore.gif'     //gif for minimize on normal state.
			var rO  ='restore.gif'    //gif for minimize on mouseover.
			var tH  ='Chromeless Window'   //title for the title bar in html format.
			var tW  ='Chromeless Window'   //title for the task bar of Windows.
			var wB  ='#D5D5FF'   //Border color.
			var wBs ='#D5D5FF'   //Border color on window drag.
			var wBG ='#D5D5FF'   //Background of the title bar.
			var wBGs='#D5D5FF'   //Background of the title bar on window drag.
			var wNS ='toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1'  //Html parameters for Netscape.
			var fSO ='scrolling=auto noresize'   //Html parameters for main content frame.
			var brd =b;   //Extra border size.
			var max =x;   //Maxzimize option (true|false).
			var min =m;   //Minimize to taskbar option (true|false).
			var res =r;   //Resizable window (true|false).
			var tsz =0;   //Height of title bar.
			return chromeless(u,n,W,H,X,Y,cU,cO,cL,mU,mO,xU,xO,rU,rO,tH,tW,wB,wBs,wBG,wBGs,wNS,fSO,brd,max,min,res,tsz)
		}
		// START CHROMELESS POPUP

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
			$isCloseable = thisMovie("MindShareViewer").isCloseable();
			if($isCloseable !== true){
				return $isCloseable;
			}
		}
		// END PREVENT CLOSE
	</script>	
	<script type="text/javascript" defer="defer">	
		// SWFOBJECT
		var flashvars = new Object();
               [!getFlashvars? &getVars=`[*flashVars*]`&sessionVars=`[*sessionVars*]`!]


		var params = new Object();
		params.menu = "false";
		params.allowScriptAccess = "sameDomain";
		params.allowFullScreen = "true";
		params.base = "/assets/flash/";
		params.bgcolor = "#869ca7";

		var attributes = new Object(); 
		attributes.id = "$[*FlexApplicationName*]";
		attributes.name = "[*FlexApplicationName*]";

		swfobject.embedSWF( "/assets/flash/[*FlexApplicationName*].swf", "flexApp", "100%", "100%", "10",  "/assets/flash/expressInstall.swf", flashvars, params, attributes);
	</script>
</head>
<body><div id="flexApp">
<div style="margin: 0 auto; margin-top: 4em; border: thin solid gray; padding: 20px; width: 500px; color: #222222; font-family: Verdana,sans-serif; font-size: 73%; line-height: 130%;">
            <a style="padding-right: 20px; width: 158px; float: left; border: 0px;" href="http://www.adobe.com/go/getflashplayer"><img src="/assets/images/get_adobe_flash_player.png" alt="Download Flash Player" /></a>
            <p style="padding: 0; margin: 0; float: left; width: 320px;">
                Obojobo requires that you have the Flash Player plug-in (version 10 or greater) installed.<br /><a href="http://www.adobe.com/go/getflashplayer">Click here to download the latest version.</a>
            </p>
            <div style="clear:both;"></div>
        </div>
</div>
<script type="text/javascript">
<!-- Google Analytics -->
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
var pageTracker = _gat._getTracker("UA-3665955-1");
pageTracker._initData();
pageTracker._trackPageview();
<!-- End Google Analytics -->
// -->

</script></body>
</html>";s:6:"locked";s:1:"0";}'
NOWDOC;
?>