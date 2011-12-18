<!DOCTYPE html>
<html>
<head>
<title></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />

<!-- Minify using Minify -->
<script type="text/javascript" src="/min/b=assets/js&f=
jquery-1.7.js,
jquery.simplemodal.1.4.1.js,
modernizr-2.0.6.js,
date.format.js,
swfobject.js,
tipTipv13/jquery.tipTip.js,
ba-debug.js"></script>
<!--
<script type="text/javascript" src="/min/b=assets/js&f=
viewer/obo.util.js,
viewer/obo.view.js,
viewer/obo.remote.js,
viewer/obo.model.js,
viewer/obo.media.js,
viewer/obo.loader.js,
viewer/obo.kogneato.js,
viewer/obo.dialog.js,
viewer/obo.captivate.js"></script>
-->
<script type="text/javascript" src="/assets/js/viewer/obo.util.js"></script>
<script type="text/javascript" src="/assets/js/viewer/obo.view.js"></script>
<script type="text/javascript" src="/assets/js/viewer/obo.remote.js"></script>
<script type="text/javascript" src="/assets/js/viewer/obo.model.js"></script>
<script type="text/javascript" src="/assets/js/viewer/obo.media.js"></script>
<script type="text/javascript" src="/assets/js/viewer/obo.loader.js"></script>
<script type="text/javascript" src="/assets/js/viewer/obo.kogneato.js"></script>
<script type="text/javascript" src="/assets/js/viewer/obo.dialog.js"></script>
<script type="text/javascript" src="/assets/js/viewer/obo.captivate.js"></script>

<!-- Minify using Minify -->
<link type="text/css" rel="stylesheet" href="/min/b=assets/css/themes&f=default.css" media="screen" />

<link type="text/css" rel="stylesheet" href="/assets/js/tipTipv13/tipTip.css" media="screen" />

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
    
    var params = {
        loID:'<?php echo $_REQUEST['loID']; ?>',
        instID:'<?php echo $_REQUEST['instID']; ?>'
      };

    obo.model.init(obo.view, {});
    debug.log(obo.model);
    obo.model.load(params, function() {
      obo.view.init($('body'));
    });
    
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