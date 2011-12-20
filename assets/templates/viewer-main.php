<!DOCTYPE html>
<html>
<head>
<title></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />

<?php
// =========================== DEV AND TEST ENVIRONMENTS =============================
if(\AppCfg::ENVIRONMENT == \AppCfgDefault::ENV_DEV)
{
  ?>
  <!-- DEV JAVASCRIPT LIBRARIES -->
  <script type="text/javascript" src="/assets/js/jquery-1.7.js"></script>
  <script type="text/javascript" src="/assets/js/jquery.simplemodal.1.4.1.js"></script>
  <script type="text/javascript" src="/assets/js/modernizr-2.0.6.js"></script>
  <script type="text/javascript" src="/assets/js/date.format.js"></script>
  <script type="text/javascript" src="/assets/js/swfobject.js"></script>
  <script type="text/javascript" src="/assets/js/tipTipv13/jquery.tipTip.js"></script>
  <script type="text/javascript" src="/assets/js/ba-debug.js"></script>
  <script type="text/javascript" src="/assets/js/jquery.idletimer.js"></script>
  <script type="text/javascript" src="/assets/js/jquery.idletimeout.js"></script>

  <!-- DEV OBOJOBO LIBRARIES -->
  <script type="text/javascript" src="/assets/js/viewer/obo.util.js"></script>
  <script type="text/javascript" src="/assets/js/viewer/obo.view.js"></script>
  <script type="text/javascript" src="/assets/js/viewer/obo.remote.js"></script>
  <script type="text/javascript" src="/assets/js/viewer/obo.model.js"></script>
  <script type="text/javascript" src="/assets/js/viewer/obo.media.js"></script>
  <script type="text/javascript" src="/assets/js/viewer/obo.kogneato.js"></script>
  <script type="text/javascript" src="/assets/js/viewer/obo.dialog.js"></script>
  <script type="text/javascript" src="/assets/js/viewer/obo.captivate.js"></script>

  <!-- DEV OBOJOBO CSS -->
  <link type="text/css" rel="stylesheet" href="/min/b=assets/css/themes&f=default.css" media="screen" />

  <!-- DEV LIBRARY CSS -->
  <link type="text/css" rel="stylesheet" href="/assets/js/tipTipv13/tipTip.css" media="screen" />

  <!-- GOOGLE FONTS -->
  <link href='http://fonts.googleapis.com/css?family=Lato:400,700,900' rel='stylesheet' type='text/css'>

  <?php
}
// =========================== PRODUCTION ENVIRONMENT =============================
else
{
  ?>
<script type="text/javascript" src="/min/b=assets/js&f=
jquery-1.7.js,
jquery.simplemodal.1.4.1.js,
modernizr-2.0.6.js,
date.format.js,
swfobject.js,
tipTipv13/jquery.tipTip.js,
ba-debug.js,
jquery.idletimer.js,
jquery.idletimeout.js,
viewer/obo.util.js,
viewer/obo.view.js,
viewer/obo.remote.js,
viewer/obo.model.js,
viewer/obo.media.js,
viewer/obo.kogneato.js,
viewer/obo.dialog.js,
viewer/obo.captivate.js"></script>

<link type="text/css" rel="stylesheet" href="/min/b=assets&f=css/themes/default.css,js/tipTipv13/tipTip.css" media="screen" />

<link href='http://fonts.googleapis.com/css?family=Lato:400,700,900' rel='stylesheet' type='text/css'>

  <?php
}
?>

<!-- BEGIN IE CONDITIONALS: -->
<!--[if lte IE 7]>
<script type="text/javascript">
  oldBrowser = true;
</script>
<![endif]-->
<!--[if lte IE 8]>
<link rel="stylesheet" type="text/css" href="/assets/css/ie.css" media="screen" />
<![endif]-->
<!--[if lte IE 9]>
<link id="ie-custom-layout-fix-stylesheet" rel="stylesheet" type="text/css" href="/assets/css/ie-custom-layout-fix.css" media="screen" />
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
  
  // disable logs by defualt
  //debug.setLevel(0);
  
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

    var params = {
        loID:'<?php echo(isset($_REQUEST["loID"]) ? $_REQUEST["loID"] : ''); ?>',
        instID:'<?php echo(isset($_REQUEST["instID"]) ? $_REQUEST["instID"] : ''); ?>'
      };

    obo.model.init(obo.view, {});
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