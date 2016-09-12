<!DOCTYPE html>
<html>
<head>
<title></title>

<?php if(\AppCfg::ENVIRONMENT == \AppCfgDefault::ENV_DEV) : ?>

	<!-- DEV OBOJOBO CSS -->
	<link type="text/css" rel="stylesheet" href="/assets/css/themes/default.css" />

	<!-- DEV LIBRARY CSS -->
	<link type="text/css" rel="stylesheet" href="/assets/css/themes/tipTip.css" />

	<!-- GOOGLE FONTS -->
	<link href='//fonts.googleapis.com/css?family=Lato:400,700,900' rel='stylesheet' type='text/css'>

	<!-- DEV JAVASCRIPT LIBRARIES -->
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
	<script src="/assets/js/jquery-ui-1.8.18.custom.min.js"></script>
	<script src="/assets/js/date.format.js"></script>
	<script src="//ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js"></script>
	<script src="/assets/js/jquery.tipTip.js"></script>
	<script src="/assets/js/ba-debug.js"></script>
	<script src="/assets/js/jquery.idletimer.js"></script>
	<script src="/assets/js/jquery.idletimeout.js"></script>
	<script src="/assets/js/viewer/qa-form.jquery.js"></script>

	<!-- DEV OBOJOBO LIBRARIES -->
	<script src="/assets/js/viewer/obo.util.js"></script>
	<script src="/assets/js/viewer/obo.view.js"></script>
	<script src="/assets/js/viewer/obo.remote.js"></script>
	<script src="/assets/js/viewer/obo.model.js"></script>
	<script src="/assets/js/viewer/obo.media.js"></script>
	<script src="/assets/js/viewer/obo.dialog.js"></script>
	<script src="/assets/js/viewer/obo.captivate.js"></script>

<?php else: ?>

	<link type="text/css" rel="stylesheet" href="/min/b=assets/css&f=themes/default.css,tipTip.css" media="screen" />
	<link href='//fonts.googleapis.com/css?family=Lato:400,700,900' rel='stylesheet' type='text/css'>

	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/jquery.tiptip/1.3/jquery.tipTip.minified.js"></script>
	<script src="//ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js"></script>
	<script src="/min/b=assets/js&f=jquery-ui-1.8.18.custom.min.js,date.format.js,jquery.tipTip.js,ba-debug.js,jquery.idletimer.js,jquery.idletimeout.js,viewer/qa-form.jquery.js,viewer/obo.util.js,viewer/obo.view.js,viewer/obo.remote.js,viewer/obo.model.js,viewer/obo.media.js,viewer/obo.dialog.js,viewer/obo.captivate.js"></script>

<?php endif; ?>

<!-- BEGIN IE CONDITIONALS: -->
<!--[if lte IE 7]>
<script type="text/javascript">
	__oldBrowser = true;
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
			__oldBrowser = true;
		}
	}


	// global variables
	<?php foreach ($globalJSVars as $key => $value) : ?>
		<?= $key ?> = <?= (is_numeric($value) ? $value : "'$value'") ?>;
	<?php endforeach ?>

	debug.setLevel(<?= (\AppCfg::ENVIRONMENT == \AppCfgDefault::ENV_DEV ? 5 : 0) ?>);

	$(function() {
		if(typeof __oldBrowser !== 'undefined' && __oldBrowser === true)
		{
			$('html').addClass('older-browser-background');
			$('body').append($('#template-older-browser-dialog').html());
			$('#ignore-older-browser-warning').click(function(event) {
				event.preventDefault();
				$('#older-browser-dialog').remove();
				init();
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
			loID:'<?= (isset($loID) ? $loID : ''); ?>',
			instID:'<?= (isset($instID) ? $instID : ''); ?>'
		};

		obo.model.init(obo.view, {});
		obo.model.load(params, function()
		{
			if(obo.util.isInIFrame())
			{
				$('html').addClass('embedded');
				$('body').append($('#template-embedded-dialog').html());
				var lo = obo.model.getLO();
				var $embeddedDialog = $('#embedded-dialog');
				$embeddedDialog.find('h1').html(lo.title);
				$embeddedDialog.find('.time-estimate-min').html(lo.learnTime);
				if(obo.model.getMode() === 'preview')
				{
					$embeddedDialog.find('.attempts').css('visibility', 'hidden');
				}
				else
				{
					$embeddedDialog.find('.num-attempts').html(lo.instanceData.attemptCount);
				}
				$embeddedDialog.find('.button')
					.attr('href', window.location)
					.on('click', function() {
						obo.model.killPage('Launched in a new window. Refresh this page to relaunch.', 'Notice');
					});

				setTimeout(function() {
					obo.model.killPage('The session has expired - please refresh the page to open this object.', 'Notice');
				}, 60 * 2.5 * 1000); // Mimic timeout interval for Canvas
			}
			else
			{
				obo.view.init($('body'));
			}
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
<?php include(\AppCfg::DIR_BASE . 'assets/templates/google_analytics.php'); ?>
</head>
<body>


<script type="text/template" id="template-preview-mode-notification">
	<div id="preview-mode-notification">
		<div id="preview-mode-bar">
			<div id="preview-label-container">
				<p>Previewing</p>
			</div>
			<div id="enable-teach-view">
				<label>X-Ray:</label>
				<div id="teach-view-switch">
					<span class="switch-on">ON</span>
					<span class="switch-off">OFF</span>
					<div id="teach-view-switch-dial"></div>
				</div>
			</div>
		</div>
		<div id="preview-mode-help-container">
			<div id="preview-mode-help-subcontainer">
				<div id="preview-mode-help">
					<h1>About Preview Mode:</h1>
					<p>In preview mode you can explore this learning object freely.  Your interactions and scores will not be recorded and there is no attempt limit.  Students do not have access to this mode.</p>
					<h2>About X-Ray:</h2>
					<p>X-Ray will reveal question answers, question alternates, and allow you to see all questions before they are randomized.</p>
				</div>
				<a href="#">More Info</a>
			</div>
		</div>
	</div>
</script>

<script type="text/template" id="template-main">
	<div id="wrapper">
		<header id='header'>
			<h1 id="lo-title"></h1>
			<nav id="navigation">
				<ul id="nav-list">
					<li><a class="nav-item" id="nav-overview" href="#" role="button" title="Learning Object Overview">Overview</a></li><li><a class="nav-item" id="nav-content" href="#" role="button" title="Content Pages">Content</a></li><li><a class="nav-item" id="nav-practice" href="#" role="button" title="Practice Questions">Practice</a></li><li><a class="nav-item" id="nav-assessment" href="#" role="button" title="Assessment">Assessment</a></li>
				</ul>
				<a id="settings-button" href="#" role="button" title="Settings Menu">Settings</a>
				<a id="log-out-button" href="#" role="button" title="Logout of Obojobo">Logout</a>
			</nav>
		</header>
		<section id="content"></section><nav id="page-navigation-bottom" class="page-navigation"><ul><li><a href="#" role="button" class="prev-page-button">Prev</a></li><li><a class="next-page-button" href="#" role="button">Next</a></li></ul></nav>
		<div class="push"></div>
	</div>
	<footer id="footer">
		<div class="footer-container">
			<div id='logo'>powered by Obojobo</div>
			<h5>&copy; <span class="copyright-year">2011</span> University of Central Florida</h5>
		</div>
	</footer>
</script>

<script type="text/template" id="template-final-content-page-complete">
	<div class="page-centered">
		<h2>Content Section Complete</h2>
		<p>You can return to any page if you wish to review its contents.</p>
		<a class="next-section-button" href="#" role="button" title="Next Sextion" >Next Section <span class="triange-right"></span></a>
	</div>
</script>

<script type="text/template" id="template-final-content-page-incomplete">
	<div class="page-centered" >
		<h2>You Skipped a Page or Two</h2>
		<ul class="missed-pages-list"></ul>
		<p>You can visit those pages now or continue to the Practice Section.</p>
		<a class="next-section-button" href="#" role="button">Next Section <span class="triange-right"></span></a>
	</div>
</script>

<script type="text/template" id="template-overview-page">
	<div id="overview-page">
		<h2>Overview</h2>
		<p>Welcome to the '<span id="overview-blurb-title">Title</span>' learning object.  This object has four sections: this overview page, a content section to teach you about this topic, a self-paced practice quiz to test yourself on your knowledge of the topic, and finally a recorded assessment quiz to prove your mastery of the subject.</p>
		<div class="overview-details">

			<section id="overview-learning-objective">
				<h3>Learning Objective - What you will learn:</h3>
				<div id="objective"></div>
			</section>

			<section id="overview-time-estimate">
				<h3>Time Estimate:</h3>
				<span id="learn-time">10 Minutes</span>
				<p>The author(s) estimate it will take this amount of time to complete this learning object.</p>
			</section>

			<span id="content-size" class="content-size-display">content-size</span>
			<span id="practice-size" class="content-size-display">practice-size</span>
			<span id="assessment-size" class="content-size-display">assessment-size</span>
		</div>
		<!--
		<section id="overview-instructions">
			<p>Click '<em>Get Started</em>' below to move on to the '<em>Content</em>' section and start learning more about this topic.</p>
		</section>-->
		<a class="button" id="get-started-button" href="#" role="button">Get Started <span class="triange-right"></span></a>
	</div>
</script>

<script type="text/template" id="template-swf-alt-text">
	<div id="swf-alt-text">
		<p>This component requires a version of the Adobe Flash Plugin that you don't have installed.<br><a class="button" href="http://get.adobe.com/flashplayer/" target="_blank">Download Flash</a></p>
	</div>
</script>

<script type="text/template" id="template-flv-alt-text">
	<div id="flv-alt-text">
		<p>This video requires a version of the Adobe Flash Plugin that you don't have installed.<br><a class="button" href="http://get.adobe.com/flashplayer/" target="_blank">Download Flash</a></p>
	</div>
</script>

<script type="text/template" id="template-practice-overview">
	<div id="practice-overview">
		<h2>Practice What You Learned</h2>
		<hr>
		<ul class="overview-feature-list">
			<li><figure><span class="icon-dynamic-background"></span><figcaption> questions</figcaption></figure></li>
			<li><figure><img src="/assets/images/viewer/icon-unlimited-attempts.png"><figcaption>Unlimited attempts</figcaption></figure></li>
			<li><figure><img src="/assets/images/viewer/icon-scores-not-recorded.png"><figcaption>Scores not recorded</figcaption></figure></li>
		</ul>
		 <h3>How Practice Works</h3>
		<p>This section is optional but recommended to help you prepare for the questions in the Assessment Section. Unlike the Assessment, you are free to review the content at any time.  You will also receive feedback on your answers when available.</p>
			<hr>
		<a class="button" id="start-practice-button" href="#" role="button">Begin Practice <span class="triange-right"></span></a>
	</div>
</script>

<script type="text/template" id="template-final-practice-page-complete">
	<div class="page-centered" >
		<h2>Practice Section Complete</h2>
		<p>You can return to any question if you wish to review its contents.</p>
		<a class="next-section-button" href="#" role="button" title="Next Sextion" >Next Section <span class="triange-right"></span></a>
	</div>
</script>

<script type="text/template" id="template-final-practice-page-incomplete">
	<div class="page-centered">
		<h2>You Skipped a Question or Two</h2>
		<ul class="missed-pages-list"></ul>
		<p>You can visit those questions now or continue to the Assessment Section.</p>
		<a class="next-section-button" href="#" role="button">Next Section <span class="triange-right"></span></a>
	</div>
</script>

<script type="text/template" id="template-assessment-overview">
	<div id="assessment-overview">
		<section>
			<h2>Time to Test Your Knowledge</h2>
			<hr>
			<ul class="overview-feature-list">
				<li><figure><span class="icon-dynamic-background"></span><figcaption> Questions</figcaption></figure></li>
				<li><figure><span class="icon-dynamic-background assessment-attempt-count"></span><figcaption class="assessment-attempt-count"> Attempts remaining</figcaption></figure></li>
				<li><figure><img src="/assets/images/viewer/icon-scores-are-recorded.png"><figcaption class="final-score-method">Your highest attempt score counts</figcaption></figure></li>
				<li><figure><img src="/assets/images/viewer/icon-cant-review-content.png"><figcaption>Content section closed</figcaption></figure></li>
			</ul>

			<section class="assessment-missed-section">
				<h4>You Skipped a Page or Two</h4>
				<p>You can proceed to the Assessment at any time, however reviewing any content you skipped may help you achieve a higher score.  <!--<a href="#" role="button">You can review those pages here</a>.--></p>
				<ul>
					<li><figure><span class="icon-missed-count"></span><figcaption>Content Pages</figcaption></figure></li>
					<li><figure><span class="icon-missed-count"></span><figcaption>Practice Questions</figcaption></figure></li>
				</ul>
			</section>

			<!-- @TODO -->
			<section class="assessment-import-score-section">
				<h3>Would you like to import your previous high score?</h3>
				<p>
					You have previously completed this Learning Object for another course with a high score of <strong><span class="previous-score"></span>%</strong>. Would you like to use that score for this course or ignore it and begin the Assessment?
				</p>
				<div class="assessment-import-score-options">
					<div>
						<a id="dont-import-previous-score-button" class="button" href="#" role="button">Do Not Import</a>
						<div>
							Do not import the previous score. Instead begin the assessment below.
						</div>
					</div>
					<span>or</span>
					<div>
						<a id="do-import-previous-score-button" class="button" href="#" role="button">Import Score: <span class="previous-score"></span>%</a>
						<div>
							Import previous score. Your score for this learning object will be <span class="previous-score"></span>%.
						</div>
					</div>
				</div>
			</section>

			<div id="assessment-info">
				<h3>How Assessment Works</h3>
				<p>
					You have <span class="assessment-attempt-count"></span> attempts for this assessment.  Once you begin, you cannot leave the assessment.  If your browser closes while taking the assessment you can continue where you left off.  You will receive a score at the end of each attempt, as well as an e-mail confirmation.
				</p>
			</div>

			<div id="assessment-info-no-attempts">
				<h3>No Attempts Remaining</h3>
				<p>
					You do not have any remaining attempts for this object. Click on '<strong>View Your Scores</strong>' below to review how you did.
				</p>
			</div>

			<div id="assessment-info-closed">
				<h3>Assessment Closed</h3>
				<p>
					The assessment for this object closed on <span class="assessment-close-date"></span>.
				</p>
			</div>

			<div class="flash-notice" id="no-flash-notice">
				<p>
					This section utilizes Flash that your browser doesn't have installed. Please install the latest version of the Flash player to gain access to this section:
				</p>
				<a target="_blank" href="http://get.adobe.com/flashplayer/">Install Flash Player</a>
			</div>

			<div class="flash-notice" id="ios-flash-notice">
				<p>
					This section utilizes Flash that your device doesn't support. To access this section you will need to view this page on a computer that has Flash installed.
				</p>
			</div>

			<div class="flash-notice" id="old-version-flash-notice">
				<p>
					Your flash player is out of date and will not be able to correctly view content in this section. Please upgrade your Flash player to gain access to this section:
				</p>
				<a target="_blank" href="http://get.adobe.com/flashplayer/">Upgrade your Flash Player</a>
			</div>
		</section>
		<hr>
		<a id="view-scores-button" class="button" href="#" role="button">View Your Scores</a>
		<a id="start-assessment-button" class="button" href="#" role="button">Start Assessment <span class="triange-right"></span></a>

	</div>
</script>

<script type="text/template" id="template-final-assessment-page-complete">
	<div class="page-centered" >
		<h2>Review</h2>
		<p>Click this button to submit your assessment:</p>
		<a id="submit-assessment-button" class="button" href="#" role="button" title="Submit assessment" >Submit assessment</a>
	</div>
</script>

<script type="text/template" id="template-final-assessment-page-incomplete">
	<div class="page-centered" >
		<h2>You Skipped a Question or Two</h2>
		<ul class="missed-pages-list"></ul>
		<p>Answer the questions you skipped, then return to this page to submit your assessment. You can submit your assessment now, but you won't get credit for any unanswered questions.</p>
		<a id="submit-assessment-button" class="button" href="#" role="button" title="Submit incomplete assessment" >Submit incomplete assessment</a>
	</div>
</script>

<script type="text/template" id="template-score-results">
	<div class="page-centered" id="score-results">
		<div id="recent-attempt">
			<div id="attempt-score-result">
				<h2>Attempt 1 Score:</h2>
				<span id="attempt-score">0%</span>
			</div>
			<div id="recorded-score-container">
				<span>Recorded Score: <span class="recorded-score">0%</span></span>
				<span id="recorded-score-note">(This is your highest attempt score)</span>
			</div>
			<h3>Details:</h3>
			<p class="assessment-open">
				You have <span class="attempts-remaining">1 attempt</span> remaining.  If you'd like to try for a higher score than <span class="recorded-score">0%</span> then return to the assessment overview page to begin another attempt.  Otherwise you may close this window or review the other sections.
			</p>
			<p class="out-of-attempts">
				You have completed this learning object as you have no attempts remaining. You may now close this window or review the other sections.
			</p>
			<p class="assessment-closed">
				You have a <span class="recorded-score">0%</span> for this object. This assessment closed on <span class="assessment-close-date"></span>.
			</p>
			<div id="assessment-close-notice">The assessment will close on <span class="assessment-close-date"></span>.</div>
			<a class="button" id="return-to-overview-button" href="#" role="button" title="Return to the Assessment Overview page" >Return to the Assessment Overview page</a>
			<div id="badge-info">
				<h3>Badges Earned:</h3>
				<iframe name="badges" id="badges" frameborder="0"></iframe>
				<p class="badge-not-awarded">
					This learning object awards badges - Score a <span class="badge-min-score"></span> to get a badge!
				</p>
				<p class="badges-expired">
					This listing has expired. Refresh the page to see the badges you've earned.
				</p>
			</div>
		</div>
		<div id="all-attempts">
			<h3>All Attempt Scores:</h3>
			<div class="attempts-remaining-container">(<span class="attempts-remaining">1 Attempt</span> Available)</div>
			<div id="attempt-history-container">
				<table id="attempt-history">
					<tbody>
						<tr>
							<th>#</th><th>Score</th><th>Time</th>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</script>

<script type="text/template" id="template-older-browser-dialog">
	<div id="older-browser-dialog">
		<h1>Your browser is out of date and may not work well with Obojobo.</h1>
		<p>Please upgrade or update your browser with one of the following:</p>
		<ul>
			<a href="https://www.google.com/chrome"><li class="google-chrome">Google Chrome</li></a>
			<a href="http://www.mozilla.org/en-US/firefox/new/"><li class="firefox">Firefox</li></a>
			<a href="http://www.opera.com/"><li class="opera">Opera</li></a>
			<a href="http://windows.microsoft.com/en-US/internet-explorer/products/ie/home"><li class="ie">Internet Explorer</li></a>
			<a href="http://www.apple.com/safari/"><li class="safari">Safari</li></a>
		</ul>
		<p class="note">You can continue to Obojobo but important features may not function or display correctly: <a id="ignore-older-browser-warning" href="#">Proceed anyway</a></p>
	</div>
</script>

<script type="text/template" id="template-embedded-dialog">
	<div id="embedded-dialog">
		<span class="logo">Obojobo</span>
		<h1></h1>
		<ul class="details-summary">
			<li class="time-estimate">Time Estimate: <span class="time-estimate-min"></span> Min.</li>
			<li class="attempts"><span class="num-attempts"></span> Assessment Attempts</li>
		</ul>
		<a target="_blank" href="#" class="button" role="button">Open in new tab</a>
	</div>
</script>

</body>
</html>