<!DOCTYPE html>
<html class="">
<head>
	<meta charset="utf-8" />
	<title>Title</title>
	<link rel="stylesheet" type="text/css" href="/assets/css/jquery.timepicker.css">
	<!-- <link type="text/css" rel="stylesheet" href="/assets/css/tipTip.css" media="screen" /> -->
	<link rel="stylesheet" type="text/css" href="/assets/css/lti.css">

	  <!-- GOOGLE FONTS -->
	<link href='http://fonts.googleapis.com/css?family=Lato:400,700,900' rel='stylesheet' type='text/css'>

	<script src="/assets/js/jquery.js"></script>
	<script src="/assets/js/lti/jquery-ui-1.10.0.custom"></script>
	<script src="/assets/js/jquery.timepicker.min.js"></script>
	<script src="/assets/js/ba-debug.js"></script>
	<!--<script type="text/javascript" src="/assets/js/jquery.tipTip.js"></script>-->
	<script src="/assets/js/viewer/obo.util.js"></script>
	<script src="/assets/js/viewer/obo.remote.js"></script>
	<!--[if lt IE 9]>
		<script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
</head>
<body>
	<!--
	<header>
		<div id="logo"></div>
	</header>-->
	<!--
	<section id="wizard">
		<h1>Find a learning object</h1>
		<p class="about-obo">
			Obojobo is a Learning Object system designed, crafted, and maintained by The University of Central Florida. Visit <a target="_blank" href="/">obojobo.ucf.edu</a> for more information.
		</p>
		<a id="community-library-button" class="community-library-button wizard-button" href="#">
			<h2>Browse the Public Library</h2>
			<p>Use one of the many objects built by other departments such as Information Literacy, SARC and Experiental Learning.</p>
		</a>
		<h3>Or, use something from your library</h3>
		<ul id="your-library-buttons" class="your-library-buttons">
			<li class="my-objects-button-container"><a class="my-objects-button wizard-button" href="#">Browse My Objects</a></li>
			<li class="my-instances-button-container"><a class="my-instances-button wizard-button" href="#">Browse My Instances</a></li>
			<li class="create-new-button-container"><a class="create-new-button wizard-button" href="/repository" target="_blank">Create a new object<br />at Obojobo</a></li>
		</ul>
		<p class="note">
			If you want to use one of your draft objects or use an instance that you haven’t published yet select Create a new object to open the Obojobo repository, publish it, then return to this page.
		</p>
	</section>-->

	<section id="wizard">
		<header>
			<div class="heading-container">
				<h1>
					<span class="logo-text">Obojobo&reg;</span> provides Learning Objects for you to use
				</h1>
				<a target="_blank" href="/" class="learn-more">Learn more...</a>
			</div>
			<div class="blue-flourish"></div>
		</header>

		<p>Connect to a Learning Object from one of these libraries:</p>

		<a role="button" class="wizard-button-container community-library-button-container">
			<div href="#" class="community-library-button wizard-button">
				Community<br />Library
			</div>
			<p>
				Choose an object built by groups like <strong>Information Literacy, SARC</strong> and <strong>Experiental Learning</strong>.
			</p>
		</div>
		<a role="button" class="wizard-button-container personal-library-button-container">
			<div href="#" class="personal-library-button wizard-button">
				Personal<br />Library
			</div>
			<p>
				Choose an object you created in the <strong>My Objects</strong> or <strong>My Instances</strong> section at Obojobo.
			</p>
		</a>
	</section>

	<section id="select-object">
		<div id="controls">
			<nav id="tabs">
				<ul>
					<li class="tab community-library-tab"><a href="#">Community Library</a></li>
					<li class="tab my-objects-tab"><a href="#">My Objects</a></li>
					<li class="tab my-instances-tab"><a href="#">My Instances</a></li>
				</ul>
			</nav>
			<a href="#" role="button" class="back-button">Back to library selection</a>
		</div>
		<div id="list-overlay">
			<h1>Select an object:</h1>
			<a id="refresh" href="#">Refresh listing</a>
			<input type="text" id="search"></div>
		</div>
		<div id="list-container">
			<ul class="template">
				<li class="template obo-item">
					<div class="description">
						<h2><span class="title"></span> <span class="version"></span></h2>
						<div class="availabilty">
							Available
							<span class="start-date"></span>
							to
							<span class="end-date"></span>
						</div>
						<div class="learning-objective"></div>
					</div>
					<div class="buttons">
						<a class="preview external" target="_blank" href="#">Preview</a>
						<a role="button" class="button embed-button" href="#">Select</a>
					</div>
				</li>
			</ul>
			<div class="section community-library">
				<ul></ul>
			</div>
			<div class="section my-instances">
				<ul></ul>
				<div class="no-items no-instances">
					<p>You do not have any instances. Click the button below to create an instance at Obojobo, then return to this page and select your newly created instance.</p>
					<a class="button" role="button" target="_blank" href="/repository">Create a new instance at Obojobo</a>
				</div>
			</div>
			<div class="section my-objects">
				<ul></ul>
				<div class="no-items no-objects">
					<p>You do not have any objects. Click the button below to create an object at Obojobo, then return to this page and select your newly created instance.</p>
					<a class="button" role="button" target="_blank" href="/repository">Create a new object at Obojobo</a>
				</div>
			</div>
		</div>
	</section>
	<section id="create-instance">
		<form>
			<h1>Create Instance</h1>
			<div class="error-notice">
				<p>Please correct the following errors:</p>
				<ul>
				</ul>
			</div>
			<ul>
				<li>
					<span class="label">Instance Name:</span>
					<input id="instance-name" name="instance-name" />
				</li>
				<li>
					<span class="label">Course:</span>
					<input id="course" name="course" />
				</li>
				<!--<li>
					<input name="specify-dates" id="specify-dates" type="checkbox" checked="checked">
					<label for="specify-dates">Specify start and end dates...</label>
				</li>-->
				<li>
					<span class="label">Start Date:</span>
					<input id="start-date" class="date" name="start-date" />
					<span class="at">at</span>
					<input id="start-time" class="time" name="start-time" value="7:00am" />
					<span class="est">EST</span>
				</li>
				<li>
					<span class="label">End Date:</span>
					<input id="end-date" class="date" name="end-date" />
					<span class="at">at</span>
					<input id="end-time" class="time" name="end-time" />
					<span class="est">EST</span>
				</li>
				<li>
					<p class="instance-window-note">
						Students will be able to see your object at any time but the assessment section will only be avaliable during this time frame.
					</p>
				</li>
				<li>
					<span class="label">Attempts:</span>
					<input id="attempts" name="attempts" value="1" />
				</li>
				<li class="score-method-container">
					<span class="label">Score Method:</span>
					<select id="score-method" name="score-method">
						<option value="h" selected="selected">Record highest attempt score</option>
						<option value="r">Record last attempt score</option>
						<option value="m">Record average of all attempts</option>
					</select>
				<li>
					<input name="import-scores" id="import-scores" type="checkbox" checked="checked">
					<label for="import-scores">Allow past scores to be imported</label>
				</li>
				<li class="button-container">
					<a href="#" role="button" class="cancel button">Cancel</a>
					<input id="submit" class="button" name="submit" type="submit" value="Create Instance"/>
				</li>
			</ul>
		</form>
	</section>

	<script type="text/javascript" src="/assets/js/lti.js"></script>
	<?php if(defined('\AppCfg::GOOGLE_ANALYTICS_ID')): ?>
		<script type='text/javascript'>
		var _gaq = _gaq || [];
		_gaq.push(['_setAccount', '<?php echo \AppCfg::GOOGLE_ANALYTICS_ID; ?>']);
		_gaq.push(['_trackPageview']);

		(function() {
			var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		})();
		</script>
	<?php endif ?>
</body>
</html>