<?php
require_once('internal/app.php');

// redirect to login form
if ( ! \obo\API::getInstance()->getSessionValid())
{
	header("Location: /repository-login.php");
	exit;
}

?><!DOCTYPE html>
<html dir="ltr" lang="en-US">
	<head>
		<meta charset="UTF-8" />
		<title>Repository | Obojobo&#8482;</title>
		<link
			href="https://fonts.googleapis.com/css?family=Lato:400,400i,700,700i,900,900i|Roboto+Mono:400,400i,700,700i|Noto+Serif:400,400i,700,700i"
			rel="stylesheet"
		/>
		<link crossorigin="anonymous" media="all" rel="stylesheet" href="/assets/dist/repository.css" />
	</head>
	<body>
		<div id="react-app"></div>
		<div id="react-dialog"></div>
		<script src="//unpkg.com/react@16.14.0/umd/react.development.js"></script>
		<script src="//unpkg.com/react-dom@16.14.0/umd/react-dom.development.js"></script>
		<script src="/assets/dist/repository.js"></script>
	</body>
</html>
