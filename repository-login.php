<?php
require_once('internal/app.php');
require('internal/includes/login.php');

// redirect to repository
if ($loggedIn)
{
	header("Location: /repository.php");
	exit;
}

// render login form
$title = 'Obojobo Repository';
include("assets/templates/login.php");
