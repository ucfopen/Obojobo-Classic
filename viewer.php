<!DOCTYPE html>
<html
<head>
<title></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<!-- <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script> -->
<script type="text/javascript" src="/assets/js/jquery/1.7/jquery.min.js"></script>
<!--<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js"></script>-->
<script type="text/javascript" src="/assets/js/ba-debug.min.js"></script>
<!-- <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js"></script> -->
<!--<script type="text/javascript" src="/assets/js/fancybox/jquery.fancybox-1.3.4.pack.js"></script>-->
<!--<script type="text/javascript" src="/assets/js/poshytip/src/jquery.poshytip.min.js"></script>-->
<script type="text/javascript" src="/assets/js/tipTipv13/jquery.tipTip.js"></script>
<!--<script type="text/javascript" src="/assets/js/jquery.innerShiv.js"></script>-->
<!--<script type="text/javascript" src="/assets/js/jquery.activity-indicator-1.0.0.min.js"></script>-->
<script type="text/javascript" src="/assets/js/jquery.simplemodal.1.4.1.min.js"></script>
<!--<script type="text/javascript" src="/assets/flowplayer/flowplayer-3.2.6.min.js"></script>-->
<script type="text/javascript" src="/assets/js/swfobject.js"></script>
<!--<script type="text/javascript" src="/assets/jwplayer/jwplayer.js"></script>-->
<script type="text/javascript" src="/assets/js/date.format.js"></script>

<script type="text/javascript" src="/assets/js/viewer/obo.remote.js"></script>
<script type="text/javascript" src="/assets/js/viewer/obo.util.js"></script>
<script type="text/javascript" src="/assets/js/viewer/obo.model.js"></script>
<script type="text/javascript" src="/assets/js/viewer/obo.view.js"></script>
<script type="text/javascript" src="/assets/js/viewer/obo.dialog.js"></script>
<script type="text/javascript" src="/assets/js/viewer/obo.loader.js"></script>
<script type="text/javascript" src="/assets/js/viewer/obo.media.js"></script>
<script type="text/javascript" src="/assets/js/viewer/obo.captivate.js"></script>
<script type="text/javascript" src="/assets/js/viewer/obo.kogneato.js"></script>

<!--
<script type="text/javascript" src="/assets/js/obo.min.js"></script>
-->

<!-- <script type="text/javascript" src="/assets/js/jquery.history.js"></script> -->

<!--<link rel="stylesheet" type="text/css" href="/assets/js/poshytip/src/tip-twitter/tip-twitter.css">-->
<link rel="stylesheet" type="text/css" href="/assets/js/tipTipv13/tipTip.css">
<!--<link rel="stylesheet" type="text/css" href="/assets/flowplayer/style.css">-->

<link rel="stylesheet" type="text/css" href="/assets/css/themes/classic.css" media="screen">
<!--<link rel="stylesheet" type="text/css" href="/assets/css/ui-lightness/jquery-ui-1.8.16.custom.css" media="screen">-->
<!-- <link rel="stylesheet" type="text/css" href="/assets/css/tablet.css" media="screen and (max-width: 957px)" /> -->
<!-- <link rel="stylesheet" type="text/css" href="/assets/css/viewer-phone.css" media="screen and (max-device-width: 500px) and (orientation: portrait)" /> -->


<link id="theme-blue" rel="stylesheet" type="text/css" href="/assets/css/themes/blue.css" media="screen" />
<!--<link rel="stylesheet" href="/assets/js/fancybox/jquery.fancybox-1.3.4.css" type="text/css" media="screen" />-->

<!-- BEGIN IE CONDITIONALS: -->
<!--[if lte IE 7]>
<script type="text/javascript">
  oldBrowser = true;
</script>
<![endif]-->
<!--[if lte IE 8]>
<link rel="stylesheet" type="text/css" href="/assets/css/ie.css" media="screen" />
<![endif]-->
<!--[if IE 9]>
<link rel="stylesheet" type="text/css" href="/assets/css/ie9.css" media="screen" />
<![endif]-->
<!-- END IE CONDITIONALS -->

<link href='http://fonts.googleapis.com/css?family=Lato:400,700,900' rel='stylesheet' type='text/css'>

<script type="text/javascript" src="/assets/js/modernizr-2.0.6.js"></script>








<script type="text/javascript">
  // Guess if they have an old browser
  // We check for IE <= 7 up in the IE conditionals.
  // We assume Chrome is up to date
  if(typeof navigator.userAgent !== 'undefined')
  {
    var ua = navigator.userAgent;
    // find firefox version 3.5 and below:
    var oldFirefox = /firefox\/(3\.[0-5]|[0-2]\.)/gi;
    // find opera version 10 and below:
    var oldOpera = /opera (10|[0-9])\.|opera.*?version\/(10|[0-9])\./gi;
    // find safari version 3 and below:
    var oldSafari = /safari\/[0-3]\./gi;

    if(oldFirefox.test(ua) || oldOpera.test(ua) || oldSafari.test(ua))
    {
      oldBrowser = true;
    }
  }
  
  // Polyfills:
  Modernizr.load({
    test: Modernizr.multiplebgs,
    nope: '/assets/css/multiplebgfix.css'
  });
  
  
  $(function() {
    if(typeof oldBrowser !== 'undefined' && oldBrowser === true)
    {
      $('html').addClass('older-browser-background');
      $('body').load('/assets/templates/viewer.html #older-browser-dialog', function() {
        $('#ignore-older-browser-warning').click(function(event) {
          event.preventDefault();
          $('#older-browser-dialog').remove();
          init();
        });
      });
      
    }
    else
    {
      init();
    }
  });

  function init()
  {
    $('html').removeClass('older-browser-background');
    debug.log('ready');
    
    
    // @TODO: are these the right params?
    var params = {
        loID:'<?php echo $_REQUEST['loID']; ?>',
        instID:'<?php echo $_REQUEST['instID']; ?>'
      };
    /*
    // @TODO - this syntax is weird
    obo.model = obo.model(obo.view, {});
    obo.model.loadLO(loID, function() {
      obo.view.init($('body'));
    });*/
    obo.model.init(obo.view, {});
    debug.log(obo.model);
    obo.model.load(params, function() {
      obo.view.init($('body'));
    });
    /*obo.model.loadInstance('1937', function() {
      obo.view.init($('body'));
    });*/
    
    document.onkeypress = function(event)
    {
      if(event.ctrlKey && event.keyCode == 44)
      {
        obo.model.gotoPrevPage();
      }
      else if(event.ctrlKey && event.keyCode == 46)
      {
        obo.model.gotoNextPage();
      }
    }
  }
</script>
</head>



<body>


</body>
</html>
