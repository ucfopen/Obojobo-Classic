<!DOCTYPE html>
<!--[if lt IE 7 ]> <html class="ie ie6"> <![endif]-->
<!--[if IE 7 ]>    <html class="ie ie7"> <![endif]-->
<!--[if IE 8 ]>    <html class="ie ie8"> <![endif]-->
<!--[if IE 9 ]>    <html class="ie ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html class=""> <!--<![endif]-->
<head>
	<meta charset="utf-8" />
	<title>Title</title>
	<link rel="stylesheet" type="text/css" href="/assets/css/lti.css">

	  <!-- GOOGLE FONTS -->
	<link href='//fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,700' rel='stylesheet' type='text/css'>

	<script src="/assets/js/jquery.js"></script>
	<script src="/assets/js/lti/jquery-ui-1.10.2.custom.js"></script>
	<script src="/assets/js/ba-debug.js"></script>
	<script src="/assets/js/viewer/obo.util.js"></script>
	<script src="/assets/js/viewer/obo.remote.js"></script>

	<!--[if lt IE 9]>
		<script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
</head>
<body>

	<section id="wizard">
		<header>
			<div class="heading-container">
				<h1>
					<span class="logo-text">Obojobo&reg;</span><span class="remaining-tagline"> provides Learning Objects for you to use</span>
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
				Choose an object built by groups like <strong>Information Literacy, SARC</strong> and <strong>Experiential Learning</strong>.
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
						<a role="button" class="button embed-button" href="#">Create Instance</a>
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
				<li class="instance-copy-note">
					Note: This will create a copy of your instance. Your original instance won't be modified.
				</li>
				<li class="button-container">
					<a href="#" role="button" class="cancel button">Cancel</a>
					<input id="submit" class="button" name="submit" type="submit" value="Create Instance"/>
				</li>
			</ul>
		</form>
	</section>
	<section id="success">
		<header>
			<span class="logo-text">Obojobo&reg;</span>
			<div class="blue-flourish"></div>
		</header>
		<div class="grey-box">
			<h1>Obojobo Instance Connected:</h1>
			<h2 class="selected-instance-title"></h2>
			<span class="note">Scores for this Obojobo assignment will sync with your Canvas gradebook</span>
		</div>
		<a href="#" class="preview-link" target="_blank">Preview this instance in a new window...</a>
	</section>
	<section id="loading"></section>
	<section id="dead"></section>

	<div id="error-window" title="Error">
		<p>
			Sorry, something went wrong. Please try again.
		</p>
	</div>
	<div id="confirm-window" title="Are you sure?">
		<p>
			Scores will no longer sync to Canvas and you will need to connect to another object.
		</p>
	</div>

	<script type="text/javascript">
		window.__ltiToken         = '{$ltiToken}';
		window.__returnUrl        = '{$returnUrl}';
		window.__webUrl           = '{$webUrl}';
	</script>
	<script type="text/javascript" src="/assets/js/lti.js"></script>
</body>
</html>