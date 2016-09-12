<?php
require_once('internal/app.php');

// ================= CHECK FOR LTI LAUNCH DATA =======================

if(!isset($_REQUEST['loID']) && \lti\API::hasLtiLaunchData($_REQUEST))
{
	// Change behavior to LTI launch
	$instID = \lti\API::handleLtiLaunch();
	$loggedIn = \obo\API::getInstance()->getSessionValid();
}
else
{
	// Not an LTI, behave like a normal view/preview
	require('internal/includes/login.php');
}

$API = \obo\API::getInstance();

// ================= CHECK FOR REQUIRED ROLE TO SEE PREVIEW =======================

if($loggedIn === true && isset($_REQUEST['loID']))
{
	$hasRole = $API->getSessionRoleValid(array(\cfg_obo_Role::CONTENT_CREATOR, \cfg_obo_Role::LIBRARY_USER));
	if(!in_array(\cfg_obo_Role::LIBRARY_USER, $hasRole['hasRoles']) && !in_array(\cfg_obo_Role::CONTENT_CREATOR, $hasRole['hasRoles']))
	{
		$loggedIn = false;
		$notice = 'You do not have permission to preview this learning object. For more information view our <a href="/help/faq/">FAQ</a>.';
	}
}

// ================ DISPLAY OUTPUT =================================

if($loggedIn === true)
{
	// logged in, show the viewer

	$instID = isset($instID) ? $instID : filter_input(INPUT_GET, 'instID', FILTER_VALIDATE_INT);
	$loID = filter_input(INPUT_GET, 'loID', FILTER_VALIDATE_INT);
	$globalJSVars = [
		'_materiaLtiUrl'  => \AppCfg::MATERIA_LTI_URL,
		'_webUrl'         => \AppCfg::URL_WEB,
		'_credhubUrl'     => \AppCfg::CREDHUB_URL,
		'_credhubTimeout' => (int) \AppCfg::CREDHUB_TIMEOUT,
	];

	header('X-UA-Compatible: IE=edge');
	include('assets/templates/viewer.php');
}
else
{
	// not logged in, show login screen

	$title = 'Obojobo';
	// Instance requested - student mode
	if(isset($_REQUEST['instID']))
	{
		if($instData = $API->getInstanceData($_REQUEST['instID']))
		{
			// Reject access if this is attempted direct access to an LTI instance:
			if(!empty($instData->externalLink))
			{
				if(!\lti\API::getAssessmentSessionData($_REQUEST['instID']))
				{
					// No session data for LTI - Either they got logged out or they accessed the instance directly.
					header('Location: ' . \AppCfg::URL_WEB . 'error/no-access.html');
					exit();
				}
			}

			$title = $instData->name;
			$course = $instData->courseID;
			$instructor = $instData->userName;
			$startTime = $instData->startTime;
			$endTime = $instData->endTime;
		}
		else
		{
			header("HTTP/1.0 404 Not Found");
			exit();
		}
	}

	// lo requested - preview mode
	elseif(isset($_REQUEST['loID']))
	{
		if($loMeta = $API->getLOMeta($_REQUEST['loID']))
		{
			$title = $loMeta->title . ' ' . $loMeta->version . '.' . $loMeta->subVersion;
			$course = 'PREVIEW ONLY';
			$instructor = 'only visible to authors';
			$startTime = 0;
			$endTime = 0;
		}
		else
		{
			header("HTTP/1.0 404 Not Found");
			exit();
		}
	}

	// =============== RENDER LOGIN TEMPLATE ========================
	include('assets/templates/login.php');
}
