<?
namespace lti;

class API extends \rocketD\db\DBEnabled
{
	const LTI_SESSION_TOKEN_ID_PREFIX = 'ltiToken';

	static private $instance;

	static public function getInstance()
	{
		if(!isset(self::$instance))
		{
			$selfClass = __CLASS__;
			self::$instance = new $selfClass();
		}
		return self::$instance;
	}

	public function updateAndAuthenticateUser($ltiData)
	{
		$createIfMissing = \AppCfg::LTI_CREATE_USER_IF_MISSING;

		// If this is the test user we don't want to create a new
		// user, skip authentication
		if($ltiData->isTestUser())
		{
			return true;
		}

		$AM = \rocketD\auth\AuthManager::getInstance();

		$success = $AM->authenticate(array('userName' => $ltiData->username, 'validLti' => true, 'createIfMissing' => $createIfMissing));

		// We need to potentially elevate this users roles if we are creating them on the fly
		// and the external system claims them to be an instructor:
		if($success && $createIfMissing && $ltiData->isInstructor())
		{
			$user = $AM->fetchUserByUserName($ltiData->username);

			$RM = \obo\perms\RoleManager::getInstance();
			$success = $RM->addUsersToRole_SystemOnly(array($user->userID), "ContentCreator");
		}

		return $success;
	}

	/**
	 * Saves the lti data into the session and creates a Hash to identify it
	 * @return string LTI Instance Token Hash (a random sha1 string)
	 */
	public function storeLtiData($ltiData)
	{
		if(!$this->startSession())
		{
			return false;
		}

		$tokenId = $this->generateLtiToken();

		// We serialize the ltiData class instance so other session_start calls
		// don't have to do load ltiData
		$_SESSION[self::LTI_SESSION_TOKEN_ID_PREFIX . $tokenId] = serialize($ltiData);

		return $tokenId;
	}

	public function restoreLtiData($tokenId)
	{
		if(!$this->startSession())
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

	public function createLtiAssociationIfNeeded($originalInstID, $ltiData)
	{
		$API = \obo\API::getInstance();
		$assocations = $this->getAssociationsForOriginalItemId($originalInstID);
		$duplicateCreated = false;

		$anyAssociationsFoundWithCurrentResourceLink = isset($assocations[$ltiData->resourceId]);
		$targetInstId = $anyAssociationsFoundWithCurrentResourceLink ? $assocations[$ltiData->resourceId]->item_id : $originalInstID;
		$anyAssociationsFoundWithOriginalInst = count($assocations) > 0;

		$instanceData = $API->getInstanceData($targetInstId);
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
			|| (!$anyAssociationsFoundWithCurrentResourceLink && $this->isInstIDInAssociationTable($targetInstId))
		) {
			$targetInstId = $this->duplicateInstance($originalInstID, $ltiData);
			if(!$targetInstId || !is_numeric($targetInstId) || $targetInstId instanceof \rocketD\util\Error)
			{
				return \obo\util\Error::getError(8003);
			}

			\rocketD\util\Log::profile('lti',"'duplicate-instance','$originalInstID','$targetInstId','$ltiData->resourceId','$ltiData->contextTitle','$anyAssociationsFoundWithCurrentResourceLink','$anyAssociationsFoundWithOriginalInst','$instanceSupportsExternalLink','".time()."'");

			$duplicateCreated = true;
		}

		// Associate instance with this resource link
		if(!$anyAssociationsFoundWithOriginalInst || $duplicateCreated)
		{
			\rocketD\util\Log::profile('lti',"'insert-association','$originalInstID','$targetInstId','$ltiData->resourceId','$ltiData->contextTitle','$anyAssociationsFoundWithCurrentResourceLink','$anyAssociationsFoundWithOriginalInst','$instanceSupportsExternalLink','$duplicateCreated' '".time()."'");

			if($this->insertAssociation($originalInstID, $ltiData, $targetInstId) !== 1)
			{
				return \obo\util\Error::getError(8001);
			}
		}

		return $targetInstId;
	}

	public function duplicateInstance($originalInstId, $ltiData)
	{
		// First, duplicate the instance
		$IM = \obo\lo\InstanceManager::getInstance();

		// We override the courseID to the this course
		$newInstId = $IM->duplicateInstance_systemOnly($originalInstId, array('courseID' => $ltiData->contextTitle));
		if(!$newInstId)
		{
			return false;
		}

		// Set the 'external link' property of the new instance
		$success = $this->updateExternalLinkForInstance($newInstId, $ltiData);
		if(!$success)
		{
			return \obo\util\Error::getError(8001); // unable to set/update assoc
		}

		// Set a new assocation for this instance
		/*$success = $this->setOrUpdateLtiInstanceAssociation($newInstId, $originalInstID, $ltiData);
		if(!$success || $success instanceof \obo\util\Error)
		{
			return false;
		}*/

		return $newInstId;
	}

	public function updateExternalLinkForInstance($instID, $ltiData)
	{
		$IM = \obo\lo\InstanceManager::getInstance();
		return $IM->updateInstanceExternalLink($instID, $ltiData->consumer);
	}

	protected function isInstIDInAssociationTable($itemId)
	{
		$qstr = "	SELECT * FROM obo_lti
					WHERE item_id = '?'
					ORDER BY created_at DESC
					LIMIT 1";

		if(!($q = $this->DBM->querySafe($qstr, $itemId)))
		{
			return \obo\util\Error::getError(2);
		}

		return $this->DBM->affected_rows() > 0;
	}


	// Builds an array of data from the lti association table BY RESOURCE_LINK
	// In the case where there are multiple LTI associations for a resource_link
	// the newest one is returned.
	// Multiple associations for the same link can occur when a user changes
	// the instance linked to the resource
	protected function getAssociationsForOriginalItemId($originalItemId)
	{
		$qstr = "	SELECT * FROM obo_lti
					WHERE original_item_id = '?'
					ORDER BY created_at ASC";

		if(!($q = $this->DBM->querySafe($qstr, $originalItemId)))
		{
			return \obo\util\Error::getError(2);
		}

		$links = array();
		while($r = $this->DBM->fetch_obj($q))
		{
			$links[$r->resource_link] = $r;
		}

		return $links;
	}

	protected function getLtiInstanceAssociation($originalInstID, $ltiData)
	{
		// This query will only get the most recent record:
		$qstr = "	SELECT * FROM obo_lti
					WHERE original_item_id = '?'
					AND context_id = '?'
					ORDER BY created_at DESC
					LIMIT 1";

		//if(!($q = $this->DBM->querySafe($qstr, $ltiData->contextId, $ltiData->consumerId)))
		if(!($q = $this->DBM->querySafe($qstr, $originalInstID, $ltiData->contextId)))
		{
			return \obo\util\Error::getError(2);
		}

		if(!($r = $this->DBM->fetch_obj($q)))
		{
			return false; // no association found
		}

		return $r;
	}

	public function initAssessmentSession($instID, $ltiData)
	{
		return $this->setAssessmentSessionData($instID, $ltiData);
	}

	public function getAssessmentSessionData($instID)
	{
		if(	isset($_SESSION["lti.$instID.consumer"]) &&
			isset($_SESSION["lti.$instID.outcomeUrl"]) &&
			isset($_SESSION["lti.$instID.resourceLinkId"]) &&
			isset($_SESSION["lti.$instID.sourceId"])
		)
		{
			return array(
				"consumer"       => $_SESSION["lti.$instID.consumer"],
				"outcomeUrl"     => $_SESSION["lti.$instID.outcomeUrl"],
				"resourceLinkId" => $_SESSION["lti.$instID.resourceLinkId"],
				"sourceId"       => $_SESSION["lti.$instID.sourceId"],
			);
		}

		return false;
	}

	public function getInstanceDataForLti($ltiData)
	{
		// find details about the association
		$associationData = $this->getLtiInstanceAssociation($ltiData);
		if($associationData instanceof \obo\util\Error || !$associationData)
		{
			return false;
		}

		// verify that this is a valid instance
		$API = \obo\API::getInstance();
		$instanceData = $API->getInstanceData($associationData->item_id);
		if(!$instanceData || !isset($instanceData->instID))
		{
			\rocketD\util\Log::profile('lti',"'missing-instance','$associationData->item_id', '$ltiData->contextTitle', '$ltiData->resourceId', '".time()."'");
			return false;
		}

		if ($instanceData->externalLink == '')
		{
			\rocketD\util\Log::profile('lti',"'no-longer-associated','$associationData->item_id', '$ltiData->contextTitle', '$ltiData->resourceId', '".time()."'");
			return false;
		}

		return $instanceData;
	}

	public function sendScore($score, $instID, $sourceID, $serviceUrl, $secret)
	{
		if(!($score >= 0) || empty($instID) || empty($sourceID) || empty($serviceUrl) || empty($secret))
		{
			\rocketD\util\Log::profile('lti', "'outcome-no-passback', '$inst_id', '{$_SESSION['userID']}', '$service_url', '$score', '$source_id', '".time()."'");
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

		\rocketD\util\Log::profile('lti', "'outcome-".($success ? 'success':'failure')."', '$instID', '{$_SESSION['userID']}', '$serviceUrl', '$score', '$sourceID', '$error', '".time()."'");

		return $success;
	}

	// Uses the Obojobo way to start the session by utilizing getSessionValid.
	protected function startSession()
	{
		// This will start/restore the session we want
		$API = \obo\API::getInstance();
		$getSessionValidResult = $API->getSessionValid();
		if(!$getSessionValidResult || $getSessionValidResult instanceof \RocketD\util\Error)
		{
			return false;
		}

		return true;
	}

	protected function insertAssociation($originalInstId, $ltiData, $newInstId = false)
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

		if(!($q = $this->DBM->querySafe(
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

		return $this->DBM->affected_rows();
	}

	protected function setAssessmentSessionData($instID, $ltiData)
	{
		$_SESSION["lti.$instID.consumer"]       = $ltiData->consumer;
		$_SESSION["lti.$instID.outcomeUrl"]     = $ltiData->serviceUrl;
		$_SESSION["lti.$instID.resourceLinkId"] = $ltiData->resourceId;
		$_SESSION["lti.$instID.sourceId"]       = $ltiData->sourceId;
	}

	protected function generateLtiToken()
	{
		return str_replace('.', '', uniqid('', true));
	}
}