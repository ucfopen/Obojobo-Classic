<?php
namespace obo;

class AttemptsManager extends \rocketD\db\DBEnabled
{
	use \rocketD\Singleton;

	public function __construct()
	{
		$this->defaultDBM();
	}

	public function getUnfinishedAttempt($qGroupID = 0){
		if(!\obo\util\Validator::isPosInt($qGroupID))
		{
			return \rocketD\util\Error::getError(2);
		}

		//exit if they havent been assigned a visit id meaning they arent viewing an instance
		if($GLOBALS['CURRENT_INSTANCE_DATA']['visitID'] < 1)
			return false;

		// search for unfinished attempts
		$qstr = "SELECT	A.".\cfg_obo_Attempt::ID."
				FROM ".\cfg_obo_Visit::TABLE." AS V
				LEFT JOIN ".\cfg_obo_Attempt::TABLE." AS A ON A.".\cfg_obo_Visit::ID." = V.".\cfg_obo_Visit::ID."
				WHERE A.".\cfg_obo_QGroup::ID."='?' AND A.".\cfg_obo_Attempt::END_TIME."='0'
				AND V.".\cfg_obo_Instance::ID." = '?'
				AND V.".\cfg_core_User::ID." = '?'	ORDER BY V.".\cfg_obo_Visit::TIME." DESC";

		if(!($q = $this->DBM->querySafe($qstr, $qGroupID, $GLOBALS['CURRENT_INSTANCE_DATA']['instID'], $_SESSION['userID'])))
		{
			trace(mysql_error(), true);
			return false;
		}

		// previous attempt found
		if($r = $this->DBM->fetch_obj($q)){
			return $r->{\cfg_obo_Attempt::ID};
		}
		return false;
	}

	public function startAttempt($qGroupID = 0, $equivalentAttempt = 0)
	{
		if(!\obo\util\Validator::isPosInt($qGroupID))
		{
			return \rocketD\util\Error::getError(2);
		}
		if(is_object($equivalentAttempt))
		{
			if(!\obo\util\Validator::isPosInt($equivalentAttempt->attemptID) || !\obo\util\Validator::isPosInt($equivalentAttempt->score) || !\obo\util\Validator::isPosInt($equivalentAttempt->loID))
			{
				trace($equivalentAttempt, true);
				return \rocketD\util\Error::getError(2);
			}
		}
		//exit if they havent been assigned a visit id meaning they arent viewing an instance
		if($GLOBALS['CURRENT_INSTANCE_DATA']['visitID'] < 1 )
		{
			return \rocketD\util\Error::getError(2002); // error: No visit id assigned.
		}

		// check to see if this is assessment
		$IM =  \obo\lo\InstanceManager::getInstance();
		$instanceData = $IM->getInstanceData($GLOBALS['CURRENT_INSTANCE_DATA']['instID']);

		$lo = new \obo\lo\LO();
		$lo->dbGetInstance($this->DBM, $instanceData->loID);

		$isAssessment = $lo->aGroup->qGroupID == $qGroupID;

		// =============== We're in Practice

		if(!$isAssessment)
		{
			if(!$this->createAttempt($lo->loID, $qGroupID))
			{
				return \rocketD\util\Error::getError(2001);
			}

			return $this->filterQuestionsForNewAttempt($qGroupID, $GLOBALS['CURRENT_INSTANCE_DATA']['attemptID'], $GLOBALS['CURRENT_INSTANCE_DATA']['instID']);
		}

		// ================ We're in the Assessment

		// CLOSED??
		if(!$instanceData->externalLink && ($instanceData->endTime <= time() || $instanceData->startTime >= time()))
		{
			return \rocketD\util\Error::getError(2010); // error: assessment closed
		}

		// ================= RESUME? ===================

		// we return the filtered questions / answers
		// include all the answers they saved
		if($unfinishedAttempt = $this->getUnfinishedAttempt($qGroupID))
		{
			// fail if a linked attempt is found, they cant use equivelant scores after starting an attempt
			if(is_object($equivalentAttempt))
			{
				return \rocketD\util\Error::getError(2007); // error: no assessments attempts available
			}
			// store the open attempts in the session (required to sort out what open instance is making this call)
			$regAttempt = $this->registerCurrentAttempt($unfinishedAttempt);

			if($regAttempt instanceof \rocketD\util\Error)
			{
				return $regAttempt; // return the error if one is made here
			}

			// track resume attempt
			\obo\log\LogManager::getInstance()->trackResumeAttempt();

			$quizState = \obo\ScoreManager::getInstance()->getQuizState($qGroupID);

			$group = \obo\lo\QuestionGroupManager::getInstance()->getGroup($qGroupID);

			if($group->rand || $group->allowAlts)
			{
				$kids = $this->filterQuestionsByAttempt($group->kids, $GLOBALS['CURRENT_INSTANCE_DATA']['attemptID']);
			}
			else
			{
				$kids = $group->kids;
			}

			// insert saved answers
			foreach($kids AS $kid)
			{
				foreach($quizState['questions'] AS $question)
				{
					if($kid->questionID == $question['questionID'])
					{
						$kid->savedAnswer = [
							'answerID'    => $question['answerID'],
							'user_answer' => $question['user_answer'],
							'real_answer' => $question['real_answer']
						];
					}
				}
			}
			return $this->cleanAnswersForAttempt($kids);
		}

		// ==============  NEW ASSESSMENT =================

		// get remaining attempts
		$numRemainingAttempts = $this->getNumRemainingAttempts($GLOBALS['CURRENT_INSTANCE_DATA']['instID']);
		if($numRemainingAttempts == 0)
		{
			return \rocketD\util\Error::getError(2004); // error: no assessments attempts available
		}

		// already used import score?
		if($this->isEquivalentAttemptUsed($_SESSION['userID'], $GLOBALS['CURRENT_INSTANCE_DATA']['instID']))
		{
			return \rocketD\util\Error::getError(2008); // error: no assessments attempts available
		}

		// create the attempt, pass the equivalent attempt if its set
		if(!$this->createAttempt($lo->loID, $qGroupID, $equivalentAttempt))
		{
			return \rocketD\util\Error::getError(2001); // error: should never happen
		}

		// We are importing previous score!
		if(is_object($equivalentAttempt))
		{
			return true;
		}

		$kids = $this->filterQuestionsForNewAttempt($qGroupID, $GLOBALS['CURRENT_INSTANCE_DATA']['attemptID'], $GLOBALS['CURRENT_INSTANCE_DATA']['instID']);

		return $this->cleanAnswersForAttempt($kids); // return new assessment attempt data
	}

	private function cleanAnswersForAttempt($questions)
	{
		// TODO: 09/2016 - clean out QA answers (requires some viewer changes)
		foreach ($questions as &$q)
		{
			foreach ($q->answers as &$a)
			{
				$a->cleanForAssessmentAttempt();
			}
		}

		return $questions;
	}

	/**
	 * Zach: Generates a listing of assessment questions.  Depending on the altMethod options will choose the questions to display.
	 *
	 * @param int $qGroupID
	 * @param int $attemptID
	 * @param int $instID
	 * @return array The 'kids' array.
	 */
	private function filterQuestionsForNewAttempt($qGroupID, $attemptID, $instID)
	{
		if(!\obo\util\Validator::isPosInt($qGroupID))
		{
			return \rocketD\util\Error::getError(2);
		}
		if(!\obo\util\Validator::isPosInt($attemptID))
		{
			return \rocketD\util\Error::getError(2);
		}
		if(!\obo\util\Validator::isPosInt($instID))
		{
			return \rocketD\util\Error::getError(2);
		}

		$qgroupMan = \obo\lo\QuestionGroupManager::getInstance();
		$group = $qgroupMan->getGroup($qGroupID);
		$kids = $group->kids;
		$returnArr = array();

		//If question alternates are enabled... (wont ever happen for practice)
		if($group->allowAlts)
		{
			$firstAttempt = $this->getNumTakenAttempts($instID) == 0;

			// keep the same order across attempts and this isnt the first attempt: just return the previously used order
			if($group->altMethod == 'k' && !$firstAttempt)
			{
				//pull the first attempt picks.
				$returnArr = $this->filterQuestionsByAttempt($kids, $this->getFirstAttempt($attemptID));
			}
			// figure out the order of the questions needed
			else
			{
				// build temporary array that has alts grouped in an array
				$tmpArray = array();
				foreach($kids AS &$value)
				{
					if($value->questionIndex == 0)
					{
						$tmpArray[] = $value;
					}
					else
					{
						if( !is_array($tmpArray[$value->questionIndex]) )
						{
							$tmpArray[$value->questionIndex] = array();
						}
						$tmpArray[$value->questionIndex][] = $value;
					}
				}

				// now reduce alts to a randomly selected alt
				foreach($tmpArray AS &$value)
				{
					if(is_array($value))
					{
						$returnArr[] = $value[array_rand($value)];
					}
					else
					{
						$returnArr[] = $value;
					}
				}
			}
		}
		else // no alternates
		{
			$returnArr = $kids;
		}

		// randomize ?
		if($group->rand)
		{
			shuffle($returnArr);
		}

		// store order if it is altered in any way
		if($group->allowAlts || $group->rand )
		{
			$this->saveQuestionOrder($returnArr, $attemptID);
		}

		return $returnArr;
	}

	//Helper function: Given a question order in $kids,
	private function saveQuestionOrder($kids, $attemptID)
	{

		if(!\obo\util\Validator::isPosInt($attemptID))
		{
			return \rocketD\util\Error::getError(2);
		}

		$kidIDs = array();
		foreach($kids AS $kid)
		{
			$kidIDs[] = $kid->questionID;
		}

		$this->DBM->startTransaction();
		$qstr = "UPDATE ".\cfg_obo_Attempt::TABLE." SET ".\cfg_obo_Attempt::ORDER." = '?' WHERE ".\cfg_obo_Attempt::ID." = '?'";
		if(!($q = $this->DBM->querySafe($qstr, implode(',', $kidIDs), $attemptID)))
		{
			$this->DBM->rollback();
			return false;
		}
		$this->DBM->commit();
		return true;
	}

	//Helper functions: Given an attemptID returns the kids selected in the order they were selected.
	public function filterQuestionsByAttempt($kids, $attemptID)
	{
		if(!\obo\util\Validator::isPosInt($attemptID))
		{
			return \rocketD\util\Error::getError(2);
		}

		$qstr = "SELECT " . \cfg_obo_Attempt::ORDER . " FROM ". \cfg_obo_Attempt::TABLE ." WHERE ".\cfg_obo_Attempt::ID." = '?'";

		if(!($q = $this->DBM->querySafe($qstr, $attemptID)))
		{
			return false;
		}

		$result = $this->DBM->fetch_obj($q);
		if( strlen($result->{\cfg_obo_Attempt::ORDER}) > 0)
		{
			$kidOrder = explode(",", $result->{\cfg_obo_Attempt::ORDER});

			$returnArr = array();
			foreach($kidOrder AS $kidID)
			{
				foreach($kids AS  $question ) // locate the questionid in the kids passed to this function
				{
					if($question->questionID == $kidID) // question found
					{
						$returnArr[] = $question;
						break;
					}
				}
			}

		}

		else // no question order data, default to natural order
		{
			$returnArr = $kids;
		}

		return $returnArr;
	}

	/**
	 * Zach: Given any attemptID this will return the ID of the first attempt.
	 * Useful in pulling out information from lo_map_qorder.
	 *
	 * @param int $attemptID
	 */
	private function getFirstAttempt($attemptID, $userID = 0)
	{
		// if no userID is set, use the current user
		if(!\obo\util\Validator::isPosInt($userID))
		{
			$userID = $_SESSION['userID'];
		}

		$qstr = "	SELECT A.".\cfg_obo_Attempt::ID."
					FROM ".\cfg_obo_Attempt::TABLE." AS A, ".\cfg_obo_Visit::TABLE." AS V
					WHERE A.".\cfg_obo_Visit::ID." = V.".\cfg_obo_Visit::ID."
					AND V.".\cfg_core_User::ID." = '?'
					AND V.".\cfg_obo_Instance::ID." = (
						SELECT V.".\cfg_obo_Instance::ID."
						FROM ".\cfg_obo_Visit::TABLE." AS V,
							".\cfg_obo_Attempt::TABLE." AS A
						WHERE A.".\cfg_obo_Visit::ID." = V.".\cfg_obo_Visit::ID."
						AND A.".\cfg_obo_Attempt::ID." = '?'
					) ORDER BY A.".\cfg_obo_Attempt::ID." LIMIT 1";

		if(!($q = $this->DBM->querySafe($qstr, $userID, $attemptID)))
		{
			$this->DBM->rollback();
			trace(mysql_error(), true);
			return false;
		}
		$r = $this->DBM->fetch_obj($q);

		return $r->{\cfg_obo_Attempt::ID};
	}

	public function getTotalAttempts($instID = 0, $userID = 0)
	{
		if(!\obo\util\Validator::isPosInt($instID))
		{
			return \rocketD\util\Error::getError(2);
		}
		if(!\obo\util\Validator::isPosInt($instID))
		{
			$userID = $_SESSION['userID'];
		}

		$total = $this->getNumAttempts($instID) + $this->getNumExtraAttempts($instID, $userID);

		return $total;
	}

	public function getNumAttempts($instID)
	{
		if(!\obo\util\Validator::isPosInt($instID))
		{
			return \rocketD\util\Error::getError(2);
		}
		$qstr = "	SELECT ".\cfg_obo_Instance::ATTEMPT_COUNT."
					FROM ".\cfg_obo_Instance::TABLE."
					WHERE ".\cfg_obo_Instance::ID." = '?'";

		if(!($q = $this->DBM->querySafe($qstr, $instID)))
		{
			return 0;
		}

		$r = $this->DBM->fetch_obj($q);
		return (int) $r->{\cfg_obo_Instance::ATTEMPT_COUNT};
	}

	public function getNumExtraAttempts($instID = 0, $userID = 0)
	{
		if(!\obo\util\Validator::isPosInt($instID))
		{
			return \rocketD\util\Error::getError(2);
		}
		// if no userID is set, use the current user
		if(!\obo\util\Validator::isPosInt($userID))
		{
			$userID = $_SESSION['userID'];
		}

		$qstr = "	SELECT ".\cfg_obo_ExtraAttempt::EXTRA_COUNT."
					FROM ".\cfg_obo_ExtraAttempt::TABLE."
					WHERE ".\cfg_obo_Instance::ID." = '?'
					AND ".\cfg_core_User::ID." = '?'";

		if(!($q = $this->DBM->querySafe($qstr, $instID, $userID)))
		{
			return 0;
		}

		$r = $this->DBM->fetch_obj($q);

		if(empty($r->{\cfg_obo_ExtraAttempt::EXTRA_COUNT})) return 0;

		return (int) $r->{\cfg_obo_ExtraAttempt::EXTRA_COUNT};
	}

	public function getNumTakenAttempts($instID = 0, $userID = 0)
	{
		if(!\obo\util\Validator::isPosInt($instID))
		{
			return \rocketD\util\Error::getError(2);
		}
		// if no userID is set, use the current user
		if(!\obo\util\Validator::isPosInt($userID))
		{
			$userID = $_SESSION['userID'];
		}

		$qstr = "	SELECT * FROM ".\cfg_obo_Attempt::TABLE." AS A, ".\cfg_obo_Visit::TABLE." AS V
					WHERE V.".\cfg_core_User::ID." = '?'
					AND V.".\cfg_obo_Instance::ID." = '?'
					AND A.".\cfg_obo_Visit::ID." = V.".\cfg_obo_Visit::ID."
					AND A.".\cfg_obo_Attempt::END_TIME." > 0";

		if(!($q = $this->DBM->querySafe($qstr, $userID, $instID)))
		{
			$this->DBM->rollback();
			return false;
		}

		return $this->DBM->fetch_num($q);

	}

	public function getNumRemainingAttempts($instID = 0, $userID = 0)
	{
		if(!\obo\util\Validator::isPosInt($instID))
		{
			return \rocketD\util\Error::getError(2);
		}
		// if no userID is set, use the current user
		if(!\obo\util\Validator::isPosInt($userID))
		{
			$userID = $_SESSION['userID'];
		}

		return $this->getTotalAttempts($instID, $userID) - $this->getNumTakenAttempts($instID, $userID);
	}

	public function setAdditionalAttempts($userID = 0, $instID = 0, $count = 0)
	{
		if(!\obo\util\Validator::isPosInt($instID))
		{
			return \rocketD\util\Error::getError(2);
		}
		if(!\obo\util\Validator::isPosInt($userID))
		{
			return \rocketD\util\Error::getError(2);
		}
		if(!\obo\util\Validator::isPosInt($count, true))
		{
			return \rocketD\util\Error::getError(2);
		}

		//If they do not have permissions to write to this instance, reject the request
		$IM = \obo\lo\InstanceManager::getInstance();
		if(!$IM->userCanEditInstance($_SESSION['userID'], $instID))
		{
			return \rocketD\util\Error::getError(4);
		}

		if($count == 0)
		{
			return $this->removeAdditionalAttempts($userID, $instID);
		}
		else
		{
			$curCount = $this->getNumExtraAttempts($instID, $userID);

			if($curCount == 0)
			{
				$qstr = "	INSERT INTO ".\cfg_obo_ExtraAttempt::TABLE." (".\cfg_core_User::ID.", ".\cfg_obo_Instance::ID.", ".\cfg_obo_ExtraAttempt::EXTRA_COUNT.")
							VALUES('?', '?', '?')";

				if(!($q = $this->DBM->querySafe($qstr, $userID, $instID, $count)))
				{
					$this->DBM->rollback();
					trace(mysql_error(), true);
					//die();
					return false;
				}
			}
			else
			{
				$qstr = "	UPDATE ".\cfg_obo_ExtraAttempt::TABLE."
							SET ".\cfg_obo_ExtraAttempt::EXTRA_COUNT."='?'
							WHERE ".\cfg_core_User::ID." = '?'
							AND ".\cfg_obo_Instance::ID." = '?'
							LIMIT 1";

				if(!($q = $this->DBM->querySafe($qstr, $count, $userID, $instID)))
				{
					$this->DBM->rollback();
					trace(mysql_error(), true);
					return false;
				}
			}

			\rocketD\util\Cache::getInstance()->clearScoresForAllUsers($instID);

			return true;
		}
	}

	public function removeAdditionalAttempts($userID = 0, $instID = 0)
	{
		if(!\obo\util\Validator::isPosInt($instID))
		{
			return \rocketD\util\Error::getError(2);
		}
		if(!\obo\util\Validator::isPosInt($userID))
		{
			return \rocketD\util\Error::getError(2);
		}


		//If they do not have permissions to write to this instance, reject the request
		$IM = \obo\lo\InstanceManager::getInstance();
		if(!$IM->userCanEditInstance($_SESSION['userID'], $instID))
		{
			return \rocketD\util\Error::getError(4);
		}

		$qstr = "	DELETE FROM ".\cfg_obo_ExtraAttempt::TABLE."
					WHERE ".\cfg_core_User::ID." = '?'
					AND ".\cfg_obo_Instance::ID." = '?'
					LIMIT 1";

		if(!($q = $this->DBM->querySafe($qstr, $userID, $instID)))
		{
			$this->DBM->rollback();
			trace(mysql_error(), true);
			//die();
			return false;
		}

		$result = $this->DBM->affected_rows($q);

		if($result == 0)
		{
			return false;
		}
		else
		{
			\rocketD\util\Cache::getInstance()->clearScoresForAllUsers($instID);
			return true;
		}
	}

	/**
	 * End a practice or assessment attempt. !!!! NEVER let the client send $equivalentAttempt directly !!!!!
	 *
	 * @param int $qGroupID ID of the question group of the attempt to end
	 * @param object $equivalentAttempt object built by getEquivalentAttempt(), !!!! NEVER let the client send $equivalentAttempt directly !!!!!
	 * @return void
	 * @author Ian Turgeon
	 */
	public function endAttempt($qGroupID = 0, $equivalentAttempt = 0)
	{
		if(!\obo\util\Validator::isPosInt($qGroupID))
		{
			return \rocketD\util\Error::getError(2);
		}

		if($GLOBALS['CURRENT_INSTANCE_DATA']['visitID'] < 1) //exit if they do not have an open instance
		{
			trace('no visit id', true);
			return false; // error: no valid visit id
		}

		if($GLOBALS['CURRENT_INSTANCE_DATA']['attemptID'] < 1)
		{
			trace('no attempt id', true);
			return false; // error: no attempt running
		}
		if(!is_numeric($qGroupID) || $qGroupID < 1)
		{
			trace('invalid group', true);
			return false; // error: invalid input
		}
		if(is_object($equivalentAttempt))
		{
			if(!\obo\util\Validator::isPosInt($equivalentAttempt->attemptID) || !\obo\util\Validator::isPosInt($equivalentAttempt->score) || !\obo\util\Validator::isPosInt($equivalentAttempt->loID))
			{
				return \rocketD\util\Error::getError(2);
			}
		}

		// if not importing a score, use scoremanager to calcualte the score
		if(!is_object($equivalentAttempt))
		{
			$scoreMan = \obo\ScoreManager::getInstance();
			$score = $scoreMan->calculateFinal($qGroupID);
		}
		// if importing, just accept the sent score
		else
		{
			$score = $equivalentAttempt->score;
		}

		$qstr = "UPDATE ".\cfg_obo_Attempt::TABLE." SET ".\cfg_obo_Attempt::END_TIME."=UNIX_TIMESTAMP(), ".\cfg_obo_Attempt::SCORE."='?'	WHERE ".\cfg_obo_Attempt::ID."='?'";
		if(!$q = $this->DBM->querySafe($qstr, $score, $GLOBALS['CURRENT_INSTANCE_DATA']['attemptID']))
		{
			return false;
		}

		if(!is_object($equivalentAttempt))
		{
			$trackingMan = \obo\log\LogManager::getInstance();
			$trackingMan->trackEndAttempt();
		}
		$this->unRegisterCurrentAttempt();

		// check to see if we need to update the score externally
		$IM = \obo\lo\InstanceManager::getInstance();
		$instData = $IM->getInstanceData($GLOBALS['CURRENT_INSTANCE_DATA']['instID']);

		// Must clear all score related cache for this user to take this score into account
		\rocketD\util\Cache::getInstance()->clearScoresForAllUsers($instData->instID);
		// clear score
		\rocketD\util\Cache::getInstance()->clearScoresForUser($instData->instID, $_SESSION['userID']);
		// clear equivalent cache
		\rocketD\util\Cache::getInstance()->clearEquivalentAttempt($_SESSION['userID'], $instData->loID);

		$scoreman = \obo\ScoreManager::getInstance();
		$scores = $scoreman->getScoresForUser($instData->instID, $_SESSION['userID']);

		$submittableScores = $scoreman->calculateUserOverallScoreForInstance($scores);
		switch($instData->scoreMethod)
		{
			case 'r': // recent (latest)
				$submittableScore = $submittableScores['latest'];
				break;
			case 'm': // mean (average)
				$submittableScore = $submittableScores['average'];
				break;
			case 'h': // highest (max)
				$submittableScore = $submittableScores['max'];
				break;
		}

		if($instData->externalLink)
		{
			// Send the score via LTI
			//@TODO: Check to make sure session variables are set
			$assessmentData = \lti\API::getAssessmentSessionData($instData->instID);
			if(!$assessmentData)
			{
				profile('lti',"'cant-find-assessment-session-data', {$_SESSION["userID"]}, '$submittableScore', '".time()."'");
				\lti\Views::logError();
			}
			else
			{
				$secret  = \AppCfg::LTI_OAUTH_SECRET;
				$success = \lti\API::sendScore($submittableScore, $instData->instID, $assessmentData['sourceId'], $assessmentData['outcomeUrl'], $secret);

				if(!$success)
				{
					profile('lti', "'send-score-failed', {$_SESSION["userID"]}, '$submittableScore'");
					\lti\Views::logError();
				}
			}
		}

		// Send email responce to student
		if(\AppCfg::NOTIFY_SCORE == true)
		{
			$attempts = $scores['attempts'];

			// filter out incomplete attempts
			if(count($attempts) > 0)
			{
				foreach($attempts AS &$attempt)
				{
					if($attempt['submitted'] != true)
					{
						unset($attempt);
					}
				}
			}
			else
			{
				$attempts = array();
			}
			$NM = \obo\util\NotificationManager::getInstance();
			$NM->sendScoreNotice($instData, $_SESSION['userID'], $scores['additional'], $attempts, $submittableScore);
		}

		$BM = \obo\lo\BadgeManager::getInstance();

		return array(
			'score'     => $score,
			'badgeInfo' => $BM->getBadgeInfo($instData->loID, $instData->instID),
		);
	}

	private function createAttempt($loID=0, $qGroupID = 0, $equivalentAttempt=false)
	{
		if(!\obo\util\Validator::isPosInt($qGroupID))
		{
			return \rocketD\util\Error::getError(2);
		}
		if(!\obo\util\Validator::isPosInt($loID))
		{
			return \rocketD\util\Error::getError(2);
		}
		if(is_object($equivalentAttempt))
		{
			if(!\obo\util\Validator::isPosInt($equivalentAttempt->attemptID) || !\obo\util\Validator::isPosInt($equivalentAttempt->score) || !\obo\util\Validator::isPosInt($equivalentAttempt->loID))
			{
				return \rocketD\util\Error::getError(2);
			}
		}
		if($GLOBALS['CURRENT_INSTANCE_DATA']['visitID'] < 1) //exit if they do not have an open instance
		{
			return false;
		}
		//insert the new attempt
		$qstr = "INSERT INTO ".\cfg_obo_Attempt::TABLE."
			SET ".\cfg_core_User::ID."='?',
			".\cfg_obo_Instance::ID."='?',
			".\cfg_obo_LO::ID."='?',
			".\cfg_obo_QGroup::ID."='?',
			".\cfg_obo_Visit::ID."='?',
			".\cfg_obo_Attempt::START_TIME."=UNIX_TIMESTAMP(),
			".\cfg_obo_Attempt::END_TIME."='0',
			".\cfg_obo_Attempt::LINKED_ATTEMPT."='?'";

		if(!($q = $this->DBM->querySafe($qstr, $_SESSION['userID'], $GLOBALS['CURRENT_INSTANCE_DATA']['instID'], $loID, $qGroupID, $GLOBALS['CURRENT_INSTANCE_DATA']['visitID'], $equivalentAttempt->attemptID)))
		{
			$this->DBM->rollback();
			trace(mysql_error(), true);
			return false;
		}

		$regAttempt = $this->registerCurrentAttempt($this->DBM->insertID);


		if($regAttempt instanceof \rocketD\util\Error)
		{
			return $regAttempt;
		}
		if(!is_object($equivalentAttempt))
		{
			$trackingMan = \obo\log\LogManager::getInstance();
			$trackingMan->trackStartAttempt();
		}
		return true;
	}

	// TODO: FIX RETURN FOR DB ABSTRACTION
	public function getAttemptDetails($attemptID = 0, $includeScores = true )
	{
		if(!\obo\util\Validator::isPosInt($attemptID))
		{
			return \rocketD\util\Error::getError(2);
		}

		$qstr = "SELECT * FROM " . \cfg_obo_Attempt::TABLE . " WHERE ".\cfg_obo_Attempt::ID."='?'";
		if(!($q = $this->DBM->querySafe($qstr, $attemptID)))
		{
			trace(mysql_error(), true);
			return false;
		}
		$r = $this->DBM->fetch_obj($q);
		$result = array();
		$result['attempt'] = $r;

		$details = array();
		if($includeScores)
		{
			$qstr = "SELECT `".\cfg_obo_Score::TYPE."`, ".\cfg_obo_Score::ITEM_ID.", ".\cfg_obo_Answer::ID.", ".\cfg_obo_Score::ANSWER.", ".\cfg_obo_Score::SCORE." FROM ".\cfg_obo_Score::TABLE." WHERE ".\cfg_obo_Attempt::ID."='?'";
			if(!($q = $this->DBM->querySafe($qstr, $attemptID)))
			{
				trace(mysql_error(), true);
				return false;
			}

			while( $r = $this->DBM->fetch_obj($q) )
			{
				$details[] = $r;
			}
		}

		$result['scores'] = $details;
		return $result;
	}



	protected function unRegisterCurrentAttempt()
	{

		if(is_array($GLOBALS['CURRENT_INSTANCE_DATA']))
		{
			// locate session with cur attemtp
			if(is_array($_SESSION['OPEN_INSTANCE_DATA']))
			{
				foreach($_SESSION['OPEN_INSTANCE_DATA'] AS $key => $value)
				{
					if($value['instID'] == $GLOBALS['CURRENT_INSTANCE_DATA']['instID'])
					{
						$_SESSION['OPEN_INSTANCE_DATA'][$key]['attemptID'] = -1;
						$GLOBALS['CURRENT_INSTANCE_DATA'] = $_SESSION['OPEN_INSTANCE_DATA'][$key];
					}
				}
			}
		}
	}

	protected function registerCurrentAttempt($attemptID)
	{
		// store the open attempts in the session (required to sort out what open instance is making this call)

		if(!\obo\util\Validator::isPosInt($attemptID))
		{
			return \rocketD\util\Error::getError(2);
		}

		if(!is_array($GLOBALS['CURRENT_INSTANCE_DATA']))
		{
			return \rocketD\util\Error::getError(2006);
		}
		if( !\obo\util\Validator::isPosInt($GLOBALS['CURRENT_INSTANCE_DATA']['instID']) )
		{
			return \rocketD\util\Error::getError(2006);
		}

		$curIsnt = $GLOBALS['CURRENT_INSTANCE_DATA']['instID'];
		$_SESSION['OPEN_INSTANCE_DATA'][$curIsnt]['attemptID'] = $attemptID;
		$GLOBALS['CURRENT_INSTANCE_DATA'] = $_SESSION['OPEN_INSTANCE_DATA'][$curIsnt];

		return true;
	}

	public function isEquivalentAttemptUsed($userID, $instID)
	{
		if(!\obo\util\Validator::isPosInt($userID))
		{
			return \rocketD\util\Error::getError(2);
		}
		if(!\obo\util\Validator::isPosInt($instID))
		{
			return \rocketD\util\Error::getError(2);
		}
		// check permission
		if($_SESSION['userID'] != $userID)
		{
			return \rocketD\util\Error::getError(4);
		}

		$q = $this->DBM->querySafe("SELECT * FROM ".\cfg_obo_Attempt::TABLE." WHERE ".\cfg_core_User::ID." = '?' AND ".\cfg_obo_Instance::ID." = '?' AND ".\cfg_obo_Attempt::LINKED_ATTEMPT." != 0", $userID, $instID);
		return $this->DBM->fetch_num($q) > 0;

	}

	public function useEquivalentAttempt($visitKey)
	{
		// first register the visitKey
		$VM = \obo\VisitManager::getInstance();
		if(!$VM->registerCurrentViewKey($visitKey))
		{
			return \rocketD\util\Error::getError(5);
		}
		// make sure its not already used
		if($this->isEquivalentAttemptUsed($_SESSION['userID'], $GLOBALS['CURRENT_INSTANCE_DATA']['instID']))
		{
			return \rocketD\util\Error::getError(2008);
		}
		// get the qgroupid and the loID
		$IM = \obo\lo\InstanceManager::getInstance();
		$instData = $IM->getInstanceData($GLOBALS['CURRENT_INSTANCE_DATA']['instID']);
		if(!($instData instanceof \obo\lo\InstanceData ))
		{
			return \rocketD\util\Error::getError(2);
		}
		// make sure the instance allows importing
		if($instData->allowScoreImport != 1)
		{
			return \rocketD\util\Error::getError(2009);
		}
		$lo = new \obo\lo\LO();
		if(!$lo->dbGetFull($this->DBM, $instData->loID))
		{
			return \rocketD\util\Error::getError(2);
		}

		// get the equivalent data
		$equivalent = $this->getEquivalentAttempt($_SESSION['userID'], $GLOBALS['CURRENT_INSTANCE_DATA']['instID'], $lo->loID);
		if(!is_object($equivalent))
		{
			return \rocketD\util\Error::getError(2);
		}
		// submit the attempt
		if($this->startAttempt($lo->aGroup->qGroupID, $equivalent) != true)
		{
			return \rocketD\util\Error::getError(2);
		}
		// keep the attemptID
		// $curAttempt = $GLOBALS['CURRENT_INSTANCE_DATA']['attemptID'];

		// end the attempt
		if($this->endAttempt($lo->aGroup->qGroupID, $equivalent) != true)
		{
			return \rocketD\util\Error::getError(2);
		}

		$TM = \obo\log\LogManager::getInstance();
		$TM->trackImportScore(/* $curAttempt, $equivalent->attemptID*/);

		$BM = \obo\lo\BadgeManager::getInstance();

		return array(
			'success'   => true,
			'badgeInfo' => $BM->getBadgeInfo($instData->loID, $instData->instID),
		);
	}

	/**
	 * Get Assessment Attempt scores for equivalent instances.  Returns max score for each instance of the same learning object, excluding the current instance
	 *
	 * @param number $userID
	 * @param number $instID
	 * @param number $loID - optional
	 * @return error on input error, array of attempts otherwise
	 * @author Ian Turgeon
	 */
	public function getEquivalentAttempt($userID, $instID, $loID=0)
	{
		if(!\obo\util\Validator::isPosInt($userID))
		{
			return \rocketD\util\Error::getError(2);
		}
		if(!\obo\util\Validator::isPosInt($instID))
		{
			return \rocketD\util\Error::getError(2);
		}
		// check permission
		if($_SESSION['userID'] != $userID)
		{
			return \rocketD\util\Error::getError(4);
		}

		// if $loID isnt sent, get it
		if(!\obo\util\Validator::isPosInt($loID))
		{
			$IM = \obo\lo\InstanceManager::getInstance();
			$loID = $IM->getLOID($instID);
			if(!\obo\util\Validator::isPosInt($loID))
			{
				return \rocketD\util\Error::getError(2);
			}
		}

		// get unfiltered list from cache or database
		if(!($attempt = \rocketD\util\Cache::getInstance()->getEquivalentAttempt($userID, $loID)))
		{
			$qstr = "SELECT
					".\cfg_obo_Attempt::ID.",
					".\cfg_obo_Instance::ID.",
					".\cfg_obo_LO::ID.",
					".\cfg_obo_Attempt::START_TIME.",
					".\cfg_obo_Attempt::END_TIME.",
					".\cfg_obo_Attempt::SCORE."
				FROM ".\cfg_obo_Attempt::TABLE."
				WHERE
					".\cfg_obo_LO::ID."='?'
					AND ".\cfg_core_User::ID."='?'
					AND ".\cfg_obo_Instance::ID." != '?'
				ORDER BY ".\cfg_obo_Attempt::SCORE." DESC
				LIMIT 1";

			if(!($q = $this->DBM->querySafe($qstr, $loID, $userID, $instID)))
			{
				return false;
			}
			$attempts = array();

			if($r = $this->DBM->fetch_obj($q))
			{
				$attempt = $r;
			}
			// store unfiltered in cache
			\rocketD\util\Cache::getInstance()->setEquivalentAttempt($userID, $loID, $attempt);
		}

		return $attempt;
	}

	public function getCurrentAttemptID()
	{
		if(is_array($GLOBALS['CURRENT_INSTANCE_DATA']))
		{
			if($GLOBALS['CURRENT_INSTANCE_DATA']['attemptID'] > 0)
			{
				return $GLOBALS['CURRENT_INSTANCE_DATA']['attemptID'];
			}
		}

		return false;
	}


}
