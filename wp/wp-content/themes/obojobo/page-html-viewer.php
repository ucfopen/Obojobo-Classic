<?php
/*
Template Name: HTML Viewe
*/

get_header('html-viewer'); ?>
<div id="lo-preview">
	<h1><span id="title">title</span> <span id="version">version</span></h1>
	Learn Time: <span id="learn-time">learn time</span> minutes.
	<h2>Objective:</h2>
	<span id="objective"></span>

	<h2>Keywords</h2>
	<span id="key-words">key words</span></p>
	
	<h2>Pages:</h2>
	<p>Content Pages: <span id="content-size">content-size</span></p>
	<p>Practice Questions: <span id="practice-size">practice-size</span></p>
	<p>Assessment Questions: <span id="assessment-size">assessment-size</span></p>
</div>

<h2>Content</h2>
<div id="content-pages"></div>

<h2>Practice</h2>
<div id="practice-pages"></div>

<h2>Assessment</h2>
<div id="assessment-pages"></div>

<?php get_footer('html-viewer'); ?>