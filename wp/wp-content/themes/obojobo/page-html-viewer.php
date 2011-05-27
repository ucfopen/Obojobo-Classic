<?php
/*
Template Name: HTML Viewe
*/

get_header('html-viewer'); ?>
<div id="section-overview" class="section section-active">
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
	<a href="#Content">Load Content</a>
</div>

<div id="section-content" class="section"></div>

<div id="section-practice" class="section"></div>

<div id="section-assessment" class="section"></div>

<div id="section-feedback" class="section"></div>

<?php get_footer('html-viewer'); ?>