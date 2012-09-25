<!DOCTYPE html>
<html>
<head>
<title></title>
<?php
// =========================== DEV AND TEST ENVIRONMENTS =============================
if(\AppCfg::ENVIRONMENT == \AppCfgDefault::ENV_DEV)
{
  ?>

  <!-- DEV OBOJOBO CSS -->
  <link type="text/css" rel="stylesheet" href="/min/b=assets/css/themes&f=default.css" media="screen" />

  <!-- DEV LIBRARY CSS -->
  <link type="text/css" rel="stylesheet" href="/assets/css/tipTip.css" media="screen" />

  <!-- GOOGLE FONTS -->
  <link href='http://fonts.googleapis.com/css?family=Lato:400,700,900' rel='stylesheet' type='text/css'>
  
  <!-- DEV JAVASCRIPT LIBRARIES -->
  <script type="text/javascript" src="/assets/js/jquery.js"></script>
  <script type="text/javascript" src="/assets/js/jquery-ui-1.8.18.custom.min.js"></script>
  <script type="text/javascript" src="/assets/js/modernizr.js"></script>
  <script type="text/javascript" src="/assets/js/date.format.js"></script>
  <script type="text/javascript" src="/assets/js/swfobject.js"></script>
  <script type="text/javascript" src="/assets/js/jquery.tipTip.js"></script>
  <script type="text/javascript" src="/assets/js/ba-debug.js"></script>
  <script type="text/javascript" src="/assets/js/jquery.idletimer.js"></script>
  <script type="text/javascript" src="/assets/js/jquery.idletimeout.js"></script>
  <script type="text/javascript" src="/assets/js/viewer/qa-form.jquery.js"></script>

  <!-- DEV OBOJOBO LIBRARIES -->
  <script type="text/javascript" src="/assets/js/viewer/obo.util.js"></script>
  <script type="text/javascript" src="/assets/js/viewer/obo.view.js"></script>
  <script type="text/javascript" src="/assets/js/viewer/obo.remote.js"></script>
  <script type="text/javascript" src="/assets/js/viewer/obo.model.js"></script>
  <script type="text/javascript" src="/assets/js/viewer/obo.media.js"></script>
  <script type="text/javascript" src="/assets/js/viewer/obo.dialog.js"></script>
  <script type="text/javascript" src="/assets/js/viewer/obo.captivate.js"></script>
  <?php
  if(defined('\AppCfg::GOOGLE_ANALYTICS_ID'))
  {
    echo "<script type='text/javascript'>
    var _gaq = _gaq || [];
    _gaq.push(['_setAccount', '".\AppCfg::GOOGLE_ANALYTICS_ID."']);

    (function() {
      var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
      ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
      var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
    })();</script>";
  }
}
// =========================== PRODUCTION ENVIRONMENT =============================
else
{
  ?>

<link type="text/css" rel="stylesheet" href="/min/b=assets/css&f=themes/default.css,tipTip.css" media="screen" />

<link href='https://fonts.googleapis.com/css?family=Lato:400,700,900' rel='stylesheet' type='text/css'>

<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>

<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js"></script>

<script type="text/javascript" src="/min/b=assets/js&f=
jquery-ui-1.8.18.custom.min.js,modernizr.js,date.format.js,jquery.tipTip.js,ba-debug.js,jquery.idletimer.js,jquery.idletimeout.js,viewer/qa-form.jquery.js,viewer/obo.util.js,viewer/obo.view.js,viewer/obo.remote.js,viewer/obo.model.js,viewer/obo.media.js,viewer/obo.dialog.js,viewer/obo.captivate.js"></script>

  <?php
  if(defined('\AppCfg::GOOGLE_ANALYTICS_ID'))
  {
    echo "<script type='text/javascript'>
    var _gaq = _gaq || [];
    _gaq.push(['_setAccount', '".\AppCfg::GOOGLE_ANALYTICS_ID."']);

    (function() {
      var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
      ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
      var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
    })();</script>";
  }
}
?>

<!-- BEGIN IE CONDITIONALS: -->
<!--[if lte IE 7]>
<script type="text/javascript">
  oldBrowser = true;
</script>
<![endif]-->
<!--[if lte IE 8]>
<link id="ie-8-stylesheet" rel="stylesheet" type="text/css" href="/assets/css/ie.css" media="screen" />
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
  debug.setLevel(<?php echo \AppCfg::ENVIRONMENT == \AppCfgDefault::ENV_DEV ? 5 : 0 ; ?>);

  // global variables from the config
  _materiaLtiUrl = '<?php echo \AppCfg::MATERIA_LTI_URL; ?>';
  
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


  function correctTime()
  {
    // calculate client/server time difference
    var now = new Date(); 
    var clientUTCDate = new Date(now.getUTCFullYear(), now.getUTCMonth(), now.getUTCDate(),  now.getUTCHours(), now.getUTCMinutes(), now.getUTCSeconds());
    var serverUTCDate = new Date('<?php $time = date_create('now', timezone_open('UTC')); echo $time->format("D, d M Y G:i:s"); ?>');
    
    var clientUTCTimestamp = clientUTCDate.getTime();
    var serverUTCTimestamp = serverUTCDate.getTime();

    Date.prototype.correctedMS = serverUTCTimestamp - clientUTCTimestamp;
    Date.prototype.getCorrectedTime = function() {
      return new Date(this.getTime() + this.correctedMS).getTime();
    }

    debug.log('server utc       ', serverUTCDate);
    debug.log('client utc       ', clientUTCDate);
    debug.log('client           ', now);
    debug.log('client corrected ', new Date((new Date()).getCorrectedTime()));
  }

  function init()
  {
    $('html').removeClass('older-browser-background');

    correctTime();

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
      if(typeof event !== 'undefined' && typeof event.ctrlKey !== 'undefined' && event.ctrlKey && typeof event.keyCode !== 'undefined')
      {
        if(event.keyCode === 44)
        {
          obo.model.gotoPrevPage();
        }
        else if(event.keyCode === 46)
        {
          obo.model.gotoNextPage();
        }
      }
    }
  }
</script>
</head>
<body>
</body>
</html>