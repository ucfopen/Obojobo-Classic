<?php
require_once('internal/app.php');
require('internal/includes/login.php');

// redirect to repository
if ($loggedIn)
{
	header("Location: /stats.php");
	exit;
}

// render login form
$title = 'Obojobo Stats';
include("assets/templates/login.php");
