<?php

namespace lti;

class API
{
	const LTI_SESSION_TOKEN_ID_PREFIX = 'ltiToken';
	static protected $DBM_reference;

	protected static function DBM()
	{
		if(!isset(static::$DBM_reference))
		{
			$DBE = new \rocketD\db\DBEnabled();
			static::$DBM_reference = $DBE->DBM;
		}

		return static::$DBM_reference;
	}

	public static function handleLtiLaunch()
	{

		$ltiData = new \lti\Data($_REQUEST);

		profile('lti',"'lti-launch', '$_SERVER[REQUEST_URI]', '$ltiData->username', '$ltiData->email', '$ltiData->consumer', '$ltiData->resourceId', '".implode(',', $ltiData->roles)."', '".time()."'");

		// ================ VALIDATE REQUIREMENTS ================

		if(empty($_REQUEST['instID']) || ! is_numeric($_REQUEST['instID']))
		{
			\lti\Views::renderUnknownAssignmentError($ltiData, $ltiData->isInstructor());
		}
		$originalInstID = $_REQUEST['instID'];

		// show error if any values are invalid
		\lti\Views::validateLtiAndRenderAnyErrors($ltiData);

		// ================ CHECK & CREATE LTI ASSOCIATION ================

		// link the lti resource id with an instance and duplicate the instance if required
		$resp = static::createLtiAssociationIfNeeded($originalInstID, $ltiData);
		if(!$resp || !is_numeric($resp))
		{
			$msg = ($resp instanceof \obo\util\Error) ? $resp->message : 'Unexpected error creating LTI association.';
			\lti\Views::renderUnexpectedError($ltiData, $msg);
		}

		// looks good, resp should be an instID
		$instID = $resp;

		// ================ RENDER OR REDIRECT DEPENDING ON LTI LAUNCH ROLE ================

		// does the lti role indicate this is an instructor?
		// this overrides their state in the local obojobo database!
		if($ltiData->isInstructor() || $ltiData->isTestUser())
		{
			$instanceData = static::getInstanceDataOrRenderError($instID, $ltiData);

			// We want to store in some additional permissions info in
			// the session so this gives the instructor a way to be
			// able to view the instance in Obojobo
			if($ltiData->isInstructor() && !empty($ltiData->username))
			{
				$user = \rocketD\auth\AuthManager::getInstance()->fetchUserByUserName($ltiData->username);
				if($user instanceof \rocketD\auth\User)
				{
					\obo\perms\PermManager::getInstance()->setSessionPermsForUserToItem($user->userID, \cfg_core_Perm::TYPE_INSTANCE, $instID, array(20));
				}
			}

			profile('lti',"'lti-launch-".($ltiData->isTestUser() ? 'testuser' : 'instructor')."', '".time()."'");
			// static::initAssessmentSession($instID, $ltiData);
			\lti\Views::renderTestUserConfirmPage($instanceData);
			exit();
		}

		// Everyone else
		static::initAssessmentSession($instID, $ltiData);

		// redirect to student view
		$viewURL = \AppCfg::URL_WEB . 'view/' . $instID;
		profile('lti',"'lti-launch-redirect-student', '$viewURL', '".time()."'");

		if($instID != $originalInstID)
		{
			// we need the url to match the new instance - redirect now
			header('Location: ' . $viewURL);
			exit();
		}

		return $instID;
	}

	public static function getInstanceDataOrRenderError($instID, $ltiData)
	{
		$instanceData = \obo\API::getInstance()->getInstanceData($instID);
		if(!$instanceData || !isset($instanceData->instID))
		{
			\lti\Views::renderUnknownAssignmentError($ltiData, true);
		}

		return $instanceData;
	}

	public static function hasLtiLaunchData(Array $data)
	{
		$required = ['lti_message_type','lti_version','oauth_consumer_key','resource_link_id'];
		foreach ($required as $value)
		{
			if(!array_key_exists($value, $data))
			{
				return false;
			}
		}
		return true;
	}

	public static function updateAndAuthenticateUser($ltiData)
	{

		// If this is the test user we don't want to create a new
		// user, skip authentication
		if($ltiData->isTestUser())
		{
			return true;
		}

		$AM = \rocketD\auth\AuthManager::getInstance();

		$success = $AM->authenticate(array('userName' => $ltiData->username, 'validLti' => true, 'ltiData' => $ltiData));

		if ( ! $success) \lti\Views::logError($ltiData);

		return $success;
	}

	/**
	 * Saves the lti data into the session and creates a Hash to identify it
	 * @return string LTI Instance Token Hash (a random sha1 string)
	 */
	public static function storeLtiData($ltiData)
	{
		if(!static::startSession())
		{
			return false;
		}

		$tokenId = static::generateLtiToken();

		// We serialize the ltiData class instance so other session_start calls
		// don't have to do load ltiData
		$_SESSION[self::LTI_SESSION_TOKEN_ID_PREFIX . $tokenId] = serialize($ltiData);

		return $tokenId;
	}

	public static function restoreLtiData($tokenId)
	{
		if(!static::startSession())
		{
			return false;
		}

		$fullToken = self::LTI_SESSION_TOKEN_ID_PREFIX . $tokenId;

		if(!isset($_SESSION[$fullToken]))
		{
			return false;
		}
		return unserialize($_SESSION[$fullToken]);
	}

	public static function createLtiAssociationIfNeeded($originalInstID, $ltiData)
	{
		$assocations = static::getAssociationsForOriginalItemId($originalInstID);
		$duplicateCreated = false;

		$anyAssociationsFoundWithCurrentResourceLink = isset($assocations[$ltiData->resourceId]);
		$targetInstId = $anyAssociationsFoundWithCurrentResourceLink ? $assocations[$ltiData->resourceId]->item_id : $originalInstID;
		$anyAssociationsFoundWithOriginalInst = count($assocations) > 0;

		$instanceData = \obo\API::getInstance()->getInstanceData($targetInstId);
		$instanceSupportsExternalLink = isset($instanceData->externalLink) && $instanceData->externalLink == $ltiData->consumer;

		// Edge case: We need to check to see if the targetInstance doesnt exist.
		if(!$instanceData || $instanceData instanceof \obo\util\Error)
		{
			// If the target instance is the original instance...
			if($targetInstId == $originalInstID)
			{
				// ...nothing can be done, so return an error.
				return \obo\util\Error::getError(8005);
			}
			// Otherwise, we know that the target instance doesn't exist but
			// we are not sure if the original instance exists.
			// Since the target instance doesn't exist then $instanceSupportsExternalLink
			// must be false, therefore the instance will be duplicated. This duplication
			// will return an error if the duplication fails, so we don't have to check
			// to see if the original instance exists or not.
		}

		// Do we need to duplicate the instance?
		if(
			// If we don't have any lti assocation with this assignment (resource link)
			// BUT we found an association that already exists for the original instance
			(!$anyAssociationsFoundWithCurrentResourceLink && $anyAssociationsFoundWithOriginalInst)
			// OR our associated instance doesn't support this systems external link
			|| !$instanceSupportsExternalLink
			// OR we don't have any lti associations with this assignment (resource link)
			// BUT we found an association that already exists for the target instance
			|| (!$anyAssociationsFoundWithCurrentResourceLink && static::isInstIDInAssociationTable($targetInstId))
		)
		{
			$targetInstId = static::duplicateInstance($originalInstID, $ltiData);
			if(!$targetInstId || !is_numeric($targetInstId) || $targetInstId instanceof \rocketD\util\Error)
			{
				return \obo\util\Error::getError(8003);
			}

			profile('lti', "'duplicate-instance','$originalInstID','$targetInstId','$ltiData->resourceId','$ltiData->contextTitle','$anyAssociationsFoundWithCurrentResourceLink','$anyAssociationsFoundWithOriginalInst','$instanceSupportsExternalLink','".time()."'");

			$duplicateCreated = true;
		}

		// Associate instance with this resource link
		if(!$anyAssociationsFoundWithOriginalInst || $duplicateCreated)
		{
			profile('lti',"'insert-association','$originalInstID','$targetInstId','$ltiData->resourceId','$ltiData->contextTitle','$anyAssociationsFoundWithCurrentResourceLink','$anyAssociationsFoundWithOriginalInst','$instanceSupportsExternalLink','$duplicateCreated' '".time()."'");

			if(static::insertAssociation($originalInstID, $ltiData, $targetInstId) !== 1)
			{
				return \obo\util\Error::getError(8001);
			}
		}

		return $targetInstId;
	}

	public static function duplicateInstance($originalInstId, $ltiData)
	{
		// First, duplicate the instance
		$IM = \obo\lo\InstanceManager::getInstance();

		// We override the courseID to this course
		$newInstId = $IM->duplicateInstance_systemOnly($originalInstId, array('courseID' => $ltiData->contextTitle));
		if(!$newInstId)
		{
			return false;
		}

		// Set the 'external link' property of the new instance
		$success = static::updateExternalLinkForInstance($newInstId, $ltiData);
		if(!$success)
		{
			return \obo\util\Error::getError(8001); // unable to set/update assoc
		}

		if(\AppCfg::LTI_COPY_PERMS_ON_DUPLICATE)
		{
			// Copy all permissions from the old instance to the new one
			// In some cases, this is a bad idea (old instructor passes batton to new instructor)
			// but, if we don't do this - it doesn't behave like ANYONE expects - making a ghost instance
			$PM = \obo\perms\PermManager::getInstance();
			$PM->duplictePermsToNewItem(\cfg_core_Perm::TYPE_INSTANCE, $originalInstId, $newInstId);
		}

		return $newInstId;
	}

	public static function updateExternalLinkForInstance($instID, $ltiData)
	{
		$IM = \obo\lo\InstanceManager::getInstance();
		return $IM->updateInstanceExternalLink($instID, $ltiData->consumer);
	}

	public static function initAssessmentSession($instID, $ltiData)
	{
		$_SESSION["lti.{$instID}.consumer"]       = $ltiData->consumer;
		$_SESSION["lti.{$instID}.outcomeUrl"]     = $ltiData->serviceUrl;
		$_SESSION["lti.{$instID}.resourceLinkId"] = $ltiData->resourceId;
		$_SESSION["lti.{$instID}.sourceId"]       = $ltiData->sourceId;
	}

	public static function getAssessmentSessionData($instID)
	{
		if(	isset($_SESSION["lti.{$instID}.consumer"]) &&
			isset($_SESSION["lti.{$instID}.outcomeUrl"]) &&
			isset($_SESSION["lti.{$instID}.resourceLinkId"]) &&
			isset($_SESSION["lti.{$instID}.sourceId"])
		)
		{
			return array(
				"consumer"       => $_SESSION["lti.{$instID}.consumer"],
				"outcomeUrl"     => $_SESSION["lti.{$instID}.outcomeUrl"],
				"resourceLinkId" => $_SESSION["lti.{$instID}.resourceLinkId"],
				"sourceId"       => $_SESSION["lti.{$instID}.sourceId"],
			);
		}

		return false;
	}

	public static function sendScore($score, $instID, $sourceID, $serviceUrl, $secret)
	{
		if(!($score >= 0) || empty($instID) || empty($sourceID) || empty($serviceUrl) || empty($secret))
		{
			profile('lti', "'outcome-no-passback', '$instID', '{$_SESSION['userID']}', '$serviceUrl', '$score', '$sourceID', '".time()."'");
			return false;
		}

		// We need to set the score to a 0-1 value:
		$score = (int) $score;
		$score = $score / 100;

		$success = false;
		$error = false;

		// render message body:
		if ($smarty = \rocketD\util\Template::getInstance())
		{
			$smarty->assign('score', $score);
			$smarty->assign('message', uniqid());
			$smarty->assign('sourceId', $sourceID);
			$messageBody = $smarty->fetch(\AppCfg::DIR_BASE . \AppCfg::DIR_TEMPLATES . 'lti-outcomes-xml.tpl');

			$result = \lti\OAuth::sendBodyHashedPOST($serviceUrl, $messageBody, $secret);
			if(isset($result['success']))
			{
				$success = $result['success'];
			}
			if(isset($result['error']))
			{
				$error = $result['error'];
			}
		}

		profile('lti', "'outcome-".($success ? 'success':'failure')."', '$instID', '{$_SESSION['userID']}', '$serviceUrl', '$score', '$sourceID', '$error', '".time()."'");

		return $success;
	}

	protected static function isInstIDInAssociationTable($itemId)
	{
		$qstr = "	SELECT * FROM obo_lti
					WHERE item_id = '?'
					ORDER BY created_at DESC
					LIMIT 1";

		if(!($q = static::DBM()->querySafe($qstr, $itemId)))
		{
			return \obo\util\Error::getError(2);
		}

		return static::DBM()->affected_rows() > 0;
	}


	// Builds an array of data from the lti association table BY RESOURCE_LINK
	// In the case where there are multiple LTI associations for a resource_link
	// the newest one is returned.
	// Multiple associations for the same link can occur when a user changes
	// the instance linked to the resource
	protected static function getAssociationsForOriginalItemId($originalItemId)
	{
		$qstr = "	SELECT * FROM obo_lti
					WHERE original_item_id = '?'
					ORDER BY created_at ASC";

		if(!($q = static::DBM()->querySafe($qstr, $originalItemId)))
		{
			return \obo\util\Error::getError(2);
		}

		$links = array();
		while($r = static::DBM()->fetch_obj($q))
		{
			$links[$r->resource_link] = $r;
		}

		return $links;
	}

	protected static function getLtiInstanceAssociation($originalInstID, $ltiData)
	{
		// This query will only get the most recent record:
		$qstr = "	SELECT * FROM obo_lti
					WHERE original_item_id = '?'
					AND context_id = '?'
					ORDER BY created_at DESC
					LIMIT 1";

		if(!($q = static::DBM()->querySafe($qstr, $originalInstID, $ltiData->contextId)))
		{
			return \obo\util\Error::getError(2);
		}

		if(!($r = static::DBM()->fetch_obj($q)))
		{
			return false; // no association found
		}

		return $r;
	}

	// Uses the Obojobo way to start the session by utilizing getSessionValid.
	protected static function startSession()
	{
		// This will start/restore the session we want
		$getSessionValidResult = \obo\API::getInstance()->getSessionValid();
		if(!$getSessionValidResult || $getSessionValidResult instanceof \RocketD\util\Error)
		{
			return false;
		}

		return true;
	}

	protected static function insertAssociation($originalInstId, $ltiData, $newInstId = false)
	{
		$newInstId = $newInstId ? $newInstId : $originalInstId;

		// create new association
		$qstr = "	INSERT INTO obo_lti
					SET `item_id` = '?',
					`original_item_id` = '?',
					`resource_link` = '?',
					`consumer` = '?',
					`consumer_guid` = '?',
					`user_id` = '?',
					`name` = '?',
					`context_id` = '?',
					`context_title` = '?',
					`created_at` = '?'";

		if(!($q = static::DBM()->querySafe(
			$qstr,
			$newInstId,
			$originalInstId,
			$ltiData->resourceId,
			$ltiData->consumer,
			$ltiData->consumerId,
			$_SESSION['userID'],
			$ltiData->fullName,
			$ltiData->contextId,
			$ltiData->contextTitle,
			time()
		)))
		{
			return \obo\util\Error::getError(2);
		}

		return static::DBM()->affected_rows();
	}

	protected static function generateLtiToken()
	{
		return str_replace('.', '', uniqid('', true));
	}
}
