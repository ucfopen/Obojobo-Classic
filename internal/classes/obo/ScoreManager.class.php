<?php
/**
 * This class contains all logic pertaining to Scoring and score retrieval
 * @author Jacob Bates <jbates@mail.ucf.edu>
 * @author Luis Estrada <lestrada@mail.ucf.edu>
 */

/**
 * This class contains all logic pertaining to Scoring and score retrieval
 * This includes creating, retrieving, and deleting of data.
 */
namespace obo;
class ScoreManager extends \rocketD\db\DBEnabled
{
	private static $instance;
	
	function __construct()
	{
	    $this->defaultDBM();
	}
	
	static public function getInstance()
	{
		if(!isset(self::$instance))
		{
			$selfClass = __CLASS__;
			self::$instance = new $selfClass();
		}
		return self::$instance;
	}
	
	public function submitLTIQuestion($sourceid, $tool, $score)
	{
		if(empty($sourceid) || (empty($score) && $score != 0)) return array(false, "Invalid sourceid");

		// get the paramaters out of the sourceid
		// AS DEFINED BY lis_result_sourcedid => $loID .'-'. $instID .'-'. $pageItemIndex .'-'. $pageOrQuestionID .'-'. $attemptID .'-'. $visitID,
		list($loID, $instID, $pageItemIndex, $questionID, $attemptID, $visitID) = explode('-', $sourceid);

		// grading not possible
		if(empty($loID) || empty($instID) || !\obo\util\Validator::isPosInt($pageItemIndex, true) || empty($questionID) ||  empty($attemptID) || empty($visitID)) return array(false, "Invalid sourceid");

		$am       = \obo\AttemptsManager::getInstance();
		$details  = $am->getAttemptDetails($attemptID);
		$a        = $details['attempt'];
		$qGroupID = $a->qGroupID;

		// ================== VALIDATE PASSED PARAMS AGAINST ATTEMPT ============================
		if( $a->instID != $instID ) return array(false, "Attempt param mismatch");

		// make sure the visit id from attempt details is associated with the visit id from the lti request
		if($a->visitID != $visitID)
		{
			// Note: sometimes the current visitID wont match the visitID from the attempt table, when resuming attempts, that table is not updated
			// Solution: check out the logs table for a StartAttempt for this lo with a matching attemptID
			$query = "
				SELECT ".\cfg_obo_Visit::ID."
				 FROM  ".\cfg_obo_Track::TABLE."
				WHERE  ".\cfg_core_User::ID."     = '?'
				 AND   ".\cfg_obo_Track::TYPE."  = 'ResumeAttempt'
				 AND   ".\cfg_obo_Instance::ID." = '?'
				 AND   ".\cfg_obo_Visit::ID."    = '?'
				 AND   ".\cfg_obo_Track::TO."    = '?'";

			$q = $this->DBM->querySafe($query, $a->userID, $instID, $visitID, $attemptID);
			if($this->DBM->fetch_num($q) != 1) return array(false, "Attempt param mismatch");
		}

		// does this question belong to this attempt?
		if(strlen($a->qOrder) > 1 && strpos($a->qOrder, (string) $questionID) === false ) return array(false, "Question is not in assessment");
		else
		{
			$qgm = \obo\lo\QuestionGroupManager::getInstance();
			$qGroup = $qgm->getGroup($a->qGroupID, true);

			$found = false;
			foreach($qGroup->kids AS $question)
			{
				if($question->questionID == $questionID)
				{
					$found = true;
					break;
				}
			}
			if(!$found) return array(false, "Question is not in assessment");
		}
		// make sure this is a media question
		$qm = \obo\lo\QuestionManager::getInstance();
		$q  = $qm->getQuestion($questionID);

		if( !($q instanceof \obo\lo\Question) || $q->itemType != \cfg_obo_Question::QTYPE_MEDIA || $q->items[0]->media[0]->itemType != 'kogneato') return array(false, "Question is not valid LTI type");

		// ================================ SAVE THE SCORE ============================
		
		$score = round($score*100);
		// Store the answer in the score table, getting the attempt data from the associated attempt table
		// Update if this question is already answered for this attempt - we only keep the score that matters
		$qstr = "
		INSERT
		INTO ".\cfg_obo_Score::TABLE." 
		(
			".\cfg_obo_Visit::ID.",
			".\cfg_obo_LO::ID.",
			".\cfg_obo_Instance::ID.",
			".\cfg_obo_Score::TIME.",
			".\cfg_obo_Attempt::ID.",
			".\cfg_core_User::ID.",
			".\cfg_obo_QGroup::ID.",
			".\cfg_obo_Score::ITEM_ID.",
			".\cfg_obo_Score::TYPE.",
			".\cfg_obo_Answer::ID.",
			".\cfg_obo_Score::ANSWER.",
			".\cfg_obo_Score::SCORE."
		)
		SELECT
			".\cfg_obo_Visit::ID.",
			".\cfg_obo_LO::ID.",
			".\cfg_obo_Instance::ID.",
			'?' AS ".\cfg_obo_Score::TIME.",
			".\cfg_obo_Attempt::ID.",
			".\cfg_core_User::ID.",
			".\cfg_obo_QGroup::ID.",
			'?' AS ".\cfg_obo_Score::ITEM_ID.",
			'?' AS ".\cfg_obo_Score::TYPE.",
			'?' AS ".\cfg_obo_Answer::ID.",
			'?' AS ".\cfg_obo_Score::ANSWER.",
			'?' AS ".\cfg_obo_Score::SCORE."
		FROM ".\cfg_obo_Attempt::TABLE."
		WHERE ".\cfg_obo_Attempt::ID." = '?'
		
		ON DUPLICATE KEY
		UPDATE
			".\cfg_obo_Score::TIME." = '?',
			".\cfg_obo_Answer::ID." = '?',
			".\cfg_obo_Score::ANSWER."='?',
			".\cfg_obo_Score::SCORE." = '?'";

		if( !($q = $this->DBM->querySafe($qstr, time(), $questionID, 'm', 0, $score, $score, $attemptID, time(), 0, $score, $score)) )
		{
			trace(mysql_error(), true);
			return array(false, "Could not save score");
		}

		$tm = \obo\log\LogManager::getInstance();
		$tm->trackSubmitLTIAssessment($instID, $visitID, $a->{\cfg_obo_LO::ID}, $a->{\cfg_core_User::ID}, $qGroupID, $questionID, $score);

		return  array(true, "Score for item $sourceid saved as $score");;
	}

	/**
	 * Submits a question to be graded MEDIA uses submit media
	 * @param $qgroup (number) QuestionGroup ID
	 * @param $questionID (number) Question ID
	 * @param $answer (string) Answer text (to provide support for both QA and MC question types.
	 * @return (bool) False if question already answered or no open LO instance.
	 * @return (Array) containing the values in the following table
	 *
	 * Values:
	 * 'weight' = weight (from 0 to 100) of answer (final score for question)
	 * 'answerID' = answer ID (if found)
	 * 'feedback' = customized feedback string (ex. Congratulations! You got the wrong answer!)
	 *
	 */
	public function submitQuestion($qGroupID, $questionID, $answer)
	{
		// $questionID = 99999;
		if($GLOBALS['CURRENT_INSTANCE_DATA']['visitID'] < 1) //exit if they do not have an open instance
		{
			trace('visitid invalid', true);
			return false;
		}
		if($GLOBALS['CURRENT_INSTANCE_DATA']['attemptID'] < 1)
		{
			$AM = \obo\AttemptsManager::getInstance();
			if(!$AM->getCurrentAttemptID())
			{
				trace('attemptid invalid', true);
				return false;	
			}

		}
		if(!is_numeric($qGroupID) || $qGroupID < 1)
		{
			trace('qGroupID id invalid', true);
			return false; // error: invalid qGroupID
		}
		if(empty($questionID))
		{
			trace('questionID invalid', true);
			return false; // error: invalid questionID
		}
		
		// ---- Make sure the question is in the current attempt ----
		$AM = \obo\AttemptsManager::getInstance();
		$curAttemptID = $AM->getCurrentAttemptID();
		
		$details = $AM->getAttemptDetails($curAttemptID, false);
		
		if($details['attempt']->qGroupID != $qGroupID)
		{
			// error qgroup isnt this attempt's qgroup
			trace('A question score was submitted for a qgroup it doesnt belong to', true);
			trace("qgroupID: $qGroupID, questionID: $questionID, answer: $answer {$_SESSION['userID']}", true);
			return true;
		}
		if(strlen($details['attempt']->qOrder) > 1 && strpos($details['attempt']->qOrder, (string)$questionID) === false )
		{
			// error we are using qalts and it wasnt selected for the current attempt
			trace('A question score was submitted for an attempt with question banks that it doesnt belong to', true);
			trace("attemptID: $curAttemptID, questionOrder: {$details['attempt']->qOrder},  qgroupID: $qGroupID, questionID: $questionID, answer: $answer {$_SESSION['userID']}", true);
			return true;
		}
		else
		{
			// TODO: alternate way to do this?
			$QGM = \obo\lo\QuestionGroupManager::getInstance();
			$qGroup = $QGM->getGroup($qGroupID, true);

			$found = false;
			foreach($qGroup->kids AS $question)
			{
				if($question->questionID == $questionID)
				{
					$found = true;
				}
				
			}
			if(!$found)
			{
				trace('A question score was submitted for an attempt that it doesnt belong to', true);
				trace("attemptID: $curAttemptID, qgroupID: $qGroupID, questionID: $questionID, answer: $answer {$_SESSION['userID']}", true);
				return true;
			}
			
		}
			
		// check answer and save score
		$qman = \obo\lo\QuestionManager::getInstance();
		$checkArr = $qman->checkAnswer($questionID, $answer);
		if(is_array($checkArr))
		{
			// set the db itemType based on what type of question it is
			switch($checkArr['type'])
			{
				case \cfg_obo_Question::QTYPE_MULTI_CHOICE: // fall through
				case \cfg_obo_Question::QTYPE_SHORT_ANSWER:
					$itemType = 'q';
					break;

				case \cfg_obo_Question::QTYPE_MEDIA:
					$itemType = 'm';
					break;
					
				default:
					return false;
					break;
				
			}
			
			
			// Store the answer in the score table, getting the attempt data from the associated attempt table
			// Update if this question is already answered for this attempt - we only keep the score that matters
			$qstr = "INSERT INTO ".\cfg_obo_Score::TABLE." 
			(".\cfg_obo_Visit::ID.",
			".\cfg_obo_LO::ID.",
			".\cfg_obo_Instance::ID.",
			".\cfg_obo_Score::TIME.",
			".\cfg_obo_Attempt::ID.",
			".\cfg_core_User::ID.",
			".\cfg_obo_QGroup::ID.",
			".\cfg_obo_Score::ITEM_ID.",
			".\cfg_obo_Score::TYPE.",
			".\cfg_obo_Answer::ID.",
			".\cfg_obo_Score::ANSWER.",
			".\cfg_obo_Score::SCORE.")
			SELECT
			".\cfg_obo_Visit::ID.",
			".\cfg_obo_LO::ID.",
			".\cfg_obo_Instance::ID.",
			'?' AS ".\cfg_obo_Score::TIME.",
			".\cfg_obo_Attempt::ID.",
			".\cfg_core_User::ID.",
			".\cfg_obo_QGroup::ID.",
			'?' AS ".\cfg_obo_Score::ITEM_ID.",
			'?' AS ".\cfg_obo_Score::TYPE.",
			'?' AS ".\cfg_obo_Answer::ID.",
			'?' AS ".\cfg_obo_Score::ANSWER.",
			'?' AS ".\cfg_obo_Score::SCORE."
			FROM ".\cfg_obo_Attempt::TABLE." WHERE ".\cfg_obo_Attempt::ID." = '?'
			
			ON DUPLICATE KEY UPDATE ".\cfg_obo_Score::TIME." = '?', ".\cfg_obo_Answer::ID." = '?', ".\cfg_obo_Score::ANSWER."='?', ".\cfg_obo_Score::SCORE." = '?' ";
			if( !($q = $this->DBM->querySafe($qstr, time(), $questionID, $itemType, $checkArr['answerID'], $answer, $checkArr['weight'], $GLOBALS['CURRENT_INSTANCE_DATA']['attemptID'], time(), $checkArr['answerID'], $answer, $checkArr['weight'])) )
			{
 				$this->DBM->rollback();
				trace(mysql_error(), true);
				return false;
			}
			
			// store the event in the tracking table
			$trackingMan = \obo\log\LogManager::getInstance();
			switch($checkArr['type'])
			{
				case \cfg_obo_Question::QTYPE_MULTI_CHOICE: // fall through
				case \cfg_obo_Question::QTYPE_SHORT_ANSWER:
					$trackingMan->trackSubmitQuestion($qGroupID, $questionID, $answer);
					break;

				case \cfg_obo_Question::QTYPE_MEDIA:
					$trackingMan->trackSubmitMedia($qGroupID, $questionID, $checkArr['weight']);
					break;
			}
			

			// just return true if this is assessment
			if(!$this->isPractice($qGroupID))
			{
			    return true;
			}
		}
		return $checkArr;
	}

	public function isPractice($qGroupID)
	{
		if($GLOBALS['CURRENT_INSTANCE_DATA']['attemptID'] < 1)
		{
			$AM = \obo\AttemptsManager::getInstance();
			if(!$AM->getCurrentAttemptID())
			{
				return false;
			}
		}		
		
		$qstr = "SELECT `".\cfg_obo_LO::ID."` FROM `".\cfg_obo_Instance::TABLE."` WHERE `".\cfg_obo_Instance::ID."`='{$GLOBALS['CURRENT_INSTANCE_DATA']["instID"]}' LIMIT 1";
		$q = $this->DBM->query($qstr);
		if($r = $this->DBM->fetch_obj($q)) // instance exists
		{
			$qstr = "SELECT `".\cfg_obo_LO::PGROUP."` FROM `".\cfg_obo_LO::TABLE."` WHERE ".\cfg_obo_LO::ID."='{$r->{\cfg_obo_LO::ID}}' LIMIT 1";
			$q = $this->DBM->query($qstr);
			if($r = $this->DBM->fetch_obj($q)) // lo exists
			{
				if((int)$r->{\cfg_obo_LO::PGROUP} == $qGroupID)
				{
					return true;
				}
			}
		}
		
		return false;
	}
	
	/**
	 * Get all user's  scores for one instance, for class grading use
	 *
	 * @param string $instid 	ID of the instance your looking for
	 * @param int $userID	ID of the user your interested if you only want one user
	 * @return void
	 * @author Ian Turgeon
	 */
	
	public function getScoresForAllUsers($instID, $userID=0)
	{
		if(!is_numeric($instID) || $instID < 1)
		{
			return false; // error: invalid input
		}
		//********************  GET ALL SCORES - REQUIRES INSTANCE PERMISSIONS  ****************************//
			
		//If they do not have permissions to write to this instance, reject the request
		$IM = \obo\lo\InstanceManager::getInstance();
		if(!$IM->userCanEditInstance($_SESSION['userID'], $instID))
		{
			return \rocketD\util\Error::getError(4);
		}

		// get from memcache
		if($scores = \rocketD\util\Cache::getInstance()->getScoresForAllUsers($instID))
		{
			return $scores;
		}
		// grab every assessment attempt for each user (including unsubmitted ones!)
		$qstr = "SELECT
					t1.".\cfg_core_User::ID.",
					".\cfg_obo_Attempt::ID." as attemptID,
					".\cfg_obo_Attempt::LINKED_ATTEMPT.",
					".\cfg_obo_Attempt::SCORE.",
					".\cfg_obo_Attempt::END_TIME.",
					COALESCE(".\cfg_obo_ExtraAttempt::EXTRA_COUNT.", 0) as additional_attempts
					FROM 
					(
						SELECT
							A.".\cfg_obo_Attempt::ID.",
							V.".\cfg_core_User::ID.",
							A.".\cfg_obo_Attempt::SCORE.",
							V.".\cfg_obo_Instance::ID." as instance_id,
							A.".\cfg_obo_Attempt::LINKED_ATTEMPT.",
							A.".\cfg_obo_Attempt::END_TIME."
 						FROM 
							".\cfg_obo_Attempt::TABLE." AS A,
							".\cfg_obo_Visit::TABLE." AS V,
							".\cfg_obo_LO::TABLE." AS L,
							".\cfg_obo_Instance::TABLE." as I
 						WHERE
							V.".\cfg_obo_Instance::ID." = '?'
							AND V.".\cfg_obo_Instance::ID." = I.".\cfg_obo_Instance::ID."
							AND L.".\cfg_obo_LO::ID." = I.".\cfg_obo_LO::ID."
							AND L.".\cfg_obo_LO::AGROUP." = A.".\cfg_obo_QGroup::ID."
 							AND A.".\cfg_obo_Visit::ID." = V.".\cfg_obo_Visit::ID."
					) AS t1
					LEFT OUTER JOIN ".\cfg_obo_ExtraAttempt::TABLE." AS AA
						ON (AA.".\cfg_core_User::ID." = t1.".\cfg_core_User::ID."
						AND AA.".\cfg_obo_Instance::ID." = t1.instance_id)
					ORDER BY
						".\cfg_core_User::ID." ASC,
						".\cfg_obo_Attempt::ID." ASC";


		if(!($q = $this->DBM->querySafe($qstr, $instID)))
		{
			trace(mysql_error(), true);
			$this->DBM->rollback();
			return false;
		}

		$result = array();
		$i = -1;
		$lastUID = -1;
		$attempts;

		$userMan = \rocketD\auth\AuthManager::getInstance();
		while($r = $this->DBM->fetch_obj($q))
		{
			// only create new array items for each user
			if($r->{\cfg_core_User::ID} != $lastUID)
			{
				$i++;
				$result[$i] = array(
					'userID' => $r->{\cfg_core_User::ID},
					'user' => $userMan->getNameObject($r->{\cfg_core_User::ID}),
					'additional' => $r->additional_attempts,
					'attempts' => array(),
					// TODO: when this uses SYSTEM EVENTS, we will probably need to be more geralized
					'synced' => false ,
					'syncedScore' => 0
				);
				$lastUID = $r->{\cfg_core_User::ID};
			}
			// add attempts
			$result[$i]['attempts'][] = array('attemptID' => $r->attemptID, 'score' => $r->{\cfg_obo_Attempt::SCORE}, 'linkedAttempt' => $r->{\cfg_obo_Attempt::LINKED_ATTEMPT}, 'submitted' => $r->{\cfg_obo_Attempt::END_TIME} > 0, 'submitDate' => $r->{\cfg_obo_Attempt::END_TIME});
		}
		
		// store in memcache
		\rocketD\util\Cache::getInstance()->setScoresForAllUsers($instID, $result);
		return $result;
	}

	public function getScoresForUser($instID, $userID)
	{
		if(!is_numeric($instID) || $instID < 1)
		{
			return false; // error: invalid input
		}
		
		if(!is_numeric($userID) || $userID < 1)
		{
			return false; // error: invalid input
		}

		//********************  GET ONLY ONE USERS SCORES  ************************//
		// get from memcache
		if($scores = \rocketD\util\Cache::getInstance()->getScoresForUser($instID, $userID))
		{
			return $scores;
		}
		
		// TODO: fish around in memcache for this info if it exists
		
		// grab every assessment attempt for me (including unsubmitted ones!)
		$qstr = "SELECT
					t1.".\cfg_core_User::ID.",
					".\cfg_obo_Attempt::ID." as attemptID,
					".\cfg_obo_Attempt::LINKED_ATTEMPT.",
					".\cfg_obo_Attempt::SCORE.",
					".\cfg_obo_Attempt::END_TIME.",
					COALESCE(".\cfg_obo_ExtraAttempt::EXTRA_COUNT.", 0) as additional_attempts
					FROM 
					(
						SELECT
							A.".\cfg_obo_Attempt::ID.",
							V.".\cfg_core_User::ID.",
							A.".\cfg_obo_Attempt::SCORE.",
							V.".\cfg_obo_Instance::ID." as instance_id,
							A.".\cfg_obo_Attempt::LINKED_ATTEMPT.",
							A.".\cfg_obo_Attempt::END_TIME."
 						FROM
							".\cfg_obo_Attempt::TABLE." AS A,
							".\cfg_obo_Visit::TABLE." AS V,
							".\cfg_obo_LO::TABLE." AS L,
							".\cfg_obo_Instance::TABLE." as I
 						WHERE
							V.".\cfg_obo_Instance::ID." = '?'
							AND V.".\cfg_obo_Instance::ID." = I.".\cfg_obo_Instance::ID."
							AND L.".\cfg_obo_LO::ID." = I.".\cfg_obo_LO::ID."
							AND L.".\cfg_obo_LO::AGROUP." = A.".\cfg_obo_QGroup::ID."
	 						AND A.".\cfg_obo_Visit::ID." = V.".\cfg_obo_Visit::ID."
							AND A.".\cfg_core_User::ID." = '?'
					) AS t1
					LEFT OUTER JOIN ".\cfg_obo_ExtraAttempt::TABLE." AS AA
					ON
						(AA.".\cfg_core_User::ID." = t1.".\cfg_core_User::ID."
						AND AA.".\cfg_obo_Instance::ID." = t1.instance_id)
					ORDER BY
						".\cfg_core_User::ID." ASC,
						".\cfg_obo_Attempt::ID." ASC";

		if(!($q = $this->DBM->querySafe($qstr, $instID, $userID)))
		{
			trace(mysql_error(), true);
			$this->DBM->rollback();
			return false;
		}

		$userMan = \rocketD\auth\AuthManager::getInstance();

		while($r = $this->DBM->fetch_obj($q))
		{
			// only create new array items for each user
			if(!isset($result))
			{
				$result = array(
					'userID' => $userID,
					'user' => $userMan->getNameObject($userID),
					'additional' => $r->additional_attempts,
					'attempts' => array(),
					// TODO: when this uses SYSTEM EVENTS, we will probably need to be more geralized
					'synced' => false,
					'syncedScore' => 0
				);
			}
			// add attempts
			$result['attempts'][] = array('attemptID' => $r->attemptID, 'score' => $r->{\cfg_obo_Attempt::SCORE}, 'linkedAttempt' => $r->{\cfg_obo_Attempt::LINKED_ATTEMPT}, 'submitted' => $r->{\cfg_obo_Attempt::END_TIME} > 0, 'submitDate' => $r->{\cfg_obo_Attempt::END_TIME});
		}
		
		\rocketD\util\Cache::getInstance()->setScoresForUser($instID, $userID, $result);
		
		return $result;
	}

	public function getResponsesForAllUsers($instID, $offset, $amount)
	{
		if(!is_numeric($instID) || $instID < 1 || !is_numeric($offset) || !is_numeric($amount))
		{
			return false; // error: invalid input
		}

		$instm = \obo\lo\InstanceManager::getInstance();
		if(($loID = $instm->getLOID($instID)) == false) // if instanceof Error
		{
			return $loID; // error
		}
		$lom = \obo\lo\LOManager::getInstance();
 		if(($aGroupID = $lom->getAssessmentID($loID)) == false) // if instanceof Error
		{
			return $aGroupID; // error
		}

		//********************  GET RESPONSES FOR ALL USERS - REQUIRES INSTANCE PERMISSIONS  ****************************//

		//If they do not have permissions to write to this instance, reject the request
		$IM = \obo\lo\InstanceManager::getInstance();
		if(!$IM->userCanEditInstance($_SESSION['userID'], $instID))
		{
			return \rocketD\util\Error::getError(4);
		}

		// grab every assessment attempt for each user (including unsubmitted ones!)
		$qstr = "SELECT
					".\cfg_obo_Score::TIME.", ".\cfg_core_User::ID.", ".\cfg_obo_Score::ITEM_ID.", ".\cfg_obo_Answer::ID.", ".\cfg_obo_Score::ANSWER.", ".\cfg_obo_Score::SCORE."
					FROM ".\cfg_obo_Score::TABLE."
					WHERE ".\cfg_obo_Instance::ID." = '?'
					AND ".\cfg_obo_QGroup::ID." = '?'
					LIMIT ?, ?";

		if(!($q = $this->DBM->querySafe($qstr, $instID, $aGroupID, $offset, $amount)))
		{
			trace(mysql_error(), true);
			$this->DBM->rollback();
			return false;
		}

		$result = array();
		while($r = $this->DBM->fetch_obj($q))
		{
			$result[] = $r;
		}

		return $result;
	}

	/**
	 * Get Assessment Scores
	 * WARNING - DO NOT EXPOSE DIRECTLY TO API - NO PERMISSIONS CHECKING HERE
	 *
	 * @param string $instID 
	 * @param string $userID 
	 * @return void
	 * @author Ian Turgeon
	 */
	public function getAssessmentScores($instID, $userID)
	{
		$instm = \obo\lo\InstanceManager::getInstance();
		if(($loID = $instm->getLOID($instID)) == false) // if instanceof Error
		{
			return $loID; // error
		}
		$lom = \obo\lo\LOManager::getInstance();
 		if(($aGroup = $lom->getAssessmentID($loID)) == false) // if instanceof Error
		{
			return $aGroup; // error
		}
			
		$qstr = "SELECT 
					A.".\cfg_obo_Attempt::ID.", 
					A.".\cfg_obo_QGroup::ID.", 
					A.".\cfg_obo_Visit::ID.", 
					A.".\cfg_obo_Attempt::SCORE.", 
					A.".\cfg_obo_Attempt::START_TIME.", 
					A.".\cfg_obo_Attempt::END_TIME.",
					A.".\cfg_obo_Attempt::LINKED_ATTEMPT."
				FROM 
					".\cfg_obo_Attempt::TABLE." AS A,
					".\cfg_obo_Visit::TABLE." AS V
				WHERE 
					V.".\cfg_obo_Visit::ID." = A.".\cfg_obo_Visit::ID." 
				AND 
					V.".\cfg_obo_Instance::ID." = '?'
				AND
					V.".\cfg_core_User::ID." = '?'
				AND
					A.".\cfg_obo_Attempt::END_TIME." != 0";
			
		//echo $qstr;
		
		if(!($q = $this->DBM->querySafe($qstr, $instID, $userID)))
		{
			$this->DBM->rollback();
		//	echo "ERROR: getAssessmentScores";
			error_log("ERROR: getAssessmentScores".mysql_error());
			//exit;
			return false;
		}
		
		$list = array();
		while($r = $this->DBM->fetch_obj($q))
		{
			$list[] = $r;
		}
		
		return $list;
	}
	
	/**
	 * @author Zachary Berry
	 * 
	 * Returns statistical information on a given question.
	 *
	 * @param unknown_type $questionID
	 */
	public function getQuestionStatistics($questionID, $includeAllAttempts)
	{
		
	}

	/**
	 * get student's attempt data for their own review
     * @param $instID	(number)	Instance id to retrieve score data from
	 * @return (array)	
	 */	
	public function getStudentInstanceAnswers($instID=0)
	{
		
		// returns object that looks like:
		/*
			Array {
				[0] -> Array{
					[0] -> 'student answer for question 1, on attempt 1',
					[1] -> 'student answer for question 2 on attempt 2'
				},
				[1] -> Array{
					[0] -> 'student answer for question 2 on attempt 1',
					[1] -> 'student answer for question 2 on attempt 2'
				}
			}
		
		
		
		*/
		// look up instance id to find any attempts by current user
		
		
		// current user is owner of the attempt

		// get question text 
		// get submitted answer text
		
		$attempts = array();
		
		// make sure the attempt belongs to the current user
		$qAttempt = $this->DBM->querySafe("SELECT * FROM ".\cfg_obo_Attempt::TABLE." AS A, ".\cfg_obo_Visit::TABLE." AS V WHERE A.".\cfg_obo_Visit::ID." = V.".\cfg_obo_Visit::ID." AND V.".\cfg_core_User::ID." = '?' AND V.".\cfg_obo_Instance::ID." = '?'", $_SESSION['userID'], $instID);
		while($rAttempt = $this->DBM->fetch_obj($qAttempt))
		{
			$answers = array();
			
			// get all answers for this attempt
			if( !($qScore = $this->DBM->querySafe("SELECT * FROM ".\cfg_obo_Score::TABLE." WHERE ".\cfg_obo_Attempt::ID." = '?'", $rAttempt->{\cfg_obo_Attempt::ID} )) )
			{
				return false;
			}
			while($rScore = $this->DBM->fetch_obj($qScore))
			{
				// if question is itemType q, look up the question
				if($rScore->itemType == 'q')
				{
					if( !($qQuestion = $this->DBM->query("SELECT * FROM ".\cfg_obo_Question::TABLE." WHERE ".\cfg_obo_Question::ID." = {$rScore->{\cfg_obo_Score::ID}}") ) )
					{
						return false;
					}
					$question = $this->DBM->fetch_obj($qQuestion);
				}

				if($question->{\cfg_obo_Question::AID} > 0) // if answerID is not zero, look up answer
				{
					$qGivenAnswer = $this->DBM->query("SELECT ".\cfg_obo_Answer::TEXT." FROM ".\cfg_obo_Answer::TABLE." WHERE ".\cfg_obo_Answer::ID." = '".$question->{\cfg_obo_Question::AID}."'");
					$rGivenAnswer = $this->DBM->fetch_obj($qGivenAnswer);
					$givenAnswer = $rGivenAnswer->{\cfg_obo_Answer::TEXT};
				}
				// else if($question->answer != '') // else if answer is not blank, return that
				// {
				// 	$givenAnswer = $question->answer;
				// }
				else // else no answer given.
				{
					$givenAnswer = 'no answer given';
				}
			}
			
		}
		
		

	}
	
	/**
	 * build an object to store score data for deleted instances
     * @param $instID	(number)	Instance id to retrieve score data from
	 * @return (array)	Object array of attempts, each containing an array of question answers
	 */
	// TODO: FIX RETURN FOR DB ABSTRACTION
	public function buildInstanceScoresObject($instID=0)
	{
		$attempts = array();
		$q = $this->DBM->querySafe("SELECT 
						A.".\cfg_obo_Attempt::ID.", 
						A.".\cfg_obo_QGroup::ID.", 
						A.".\cfg_obo_Attempt::SCORE.", 
						A.".\cfg_obo_Attempt::START_TIME.", 
						A.".\cfg_obo_Attempt::END_TIME.",
						V.".\cfg_core_User::ID."
						FROM ".\cfg_obo_Attempt::TABLE." AS A,
						 ".\cfg_obo_Visit::TABLE." AS V
						WHERE 
						V.".\cfg_obo_Instance::ID." = '?' 
						AND A.".\cfg_obo_Visit::ID." = V.".\cfg_obo_Visit::ID."
						ORDER BY A.".\cfg_obo_QGroup::ID.", V.".\cfg_obo_Visit::ID.", V.".\cfg_core_User::ID, $instID);
		while($r = $this->DBM->fetch_obj($q))
		{
			$r->qscores = array();
			$q2 = $this->DBM->query("SELECT * FROM ".\cfg_obo_Score::TABLE." WHERE ".\cfg_obo_Attempt::ID."='". $r->{\cfg_obo_Attempt::ID} ."'");
			while($r2 = $this->DBM->fetch_obj($q2))
			{
				$r->qscores[] = $r2;
			}
			$attempts[] = $r;
		}
		return $attempts;

	}

	/**
	 * Get scores for the already submitted questions and the final score if it exists.
	 * Basically, it gets the current state of the quiz (used for instance resuming and Quiz overview screen)
	 * @param $qGroupID (number) Question Group ID
	 * @return (Array) Special return array with the following structure:
	 *
	 * 'final' = final score for quiz (if finished)
	 * 'questions' = array of arrays with the below structure:
	 *
	 * 'questionID' = question id
	 * 'qtext' = question text
	 * 'answerID' = answer id
	 * 'user_answer' = the user-submitted answer
	 * 'score' = the score earned for the question
	 * 'real_answer' = the correct answer
	 *
	 * @todo convert the return value to a class for easier documentation
	 */
	public function getQuizState($qGroupID = 0)
	{
		if($GLOBALS['CURRENT_INSTANCE_DATA']['visitID'] < 1) //exit if they do not have an open instance
		{
			return false;
		}
		if($GLOBALS['CURRENT_INSTANCE_DATA']['attemptID'] < 1) // exit if they dont have an open attempt
		{
			return false;
		}
		if(!is_numeric($qGroupID) || $qGroupID == 0)
		{
			return false;
		}
		//Get the scores for each individual question
		
		$qstr = "SELECT
					".\cfg_obo_Score::ITEM_ID.", 
					`".\cfg_obo_Score::TYPE."`, 
					".\cfg_obo_Answer::ID.", 
					".\cfg_obo_Score::ANSWER.", 
					".\cfg_obo_Score::SCORE." 
					FROM 
						".\cfg_obo_Score::TABLE."
					WHERE 
						".\cfg_obo_Attempt::ID." ='{$GLOBALS['CURRENT_INSTANCE_DATA']['attemptID']}' 
						AND ".\cfg_obo_QGroup::ID."='?' ORDER BY ".\cfg_obo_Score::ID." ASC";
		
		if( !($q = $this->DBM->querySafe($qstr, $qGroupID)) )
		{
			trace(mysql_error(), true);
			$this->DBM->rollback();
			return false;
		}

		$state = array('questions' => array());
		$qman = \obo\lo\QuestionManager::getInstance();
		while( $r = $this->DBM->fetch_obj($q) )
		{
			$question = $qman->getQuestion($r->{\cfg_obo_Score::ITEM_ID});
			// use the answer from the question id submitted
			foreach($question->answers AS $answer)
			{
				if($answer->answerID == $r->{\cfg_obo_Answer::ID})
				{
					$real_answer = $answer;
				}
			}
			array_push($state['questions'], array('questionID' => $r->{\cfg_obo_Score::ITEM_ID}, 'qtext' => $question->qtext, 'answerID' => $r->{\cfg_obo_Answer::ID}, 'user_answer' => $r->{\cfg_obo_Score::ANSWER}, 'score' => '', 'real_answer' => $real_answer->answer));
		}
		return $state;
	}

	public function calculateUserOverallScoreForInstance($scores)
	{
		if( !is_array($scores) || !is_array($scores['attempts']) || count($scores['attempts']) == 0 ) return 0;

		$values = array(
			'min' => INF, // for convience, need to reset to 0 if no submitted scores exist
			'max' => 0,
			'average' => 0,
			'latest' => 0,
			'count' => 0
		);

		// get all submitted scores
		$submittedScores = array();
		foreach($scores['attempts'] as $attempt)
		{
			if($attempt['submitted']) $submittedScores[] = $attempt['score'];
		}

		// caluclate stats
		if($cnt = count($submittedScores))
		{
			$values['min']     = min($submittedScores);
			$values['max']     = max($submittedScores);
			$values['latest']  = $submittedScores[$cnt - 1];
			$values['average'] = round(array_sum($submittedScores) / $cnt);
			$values['count']   = $cnt;
		}
		else
		{
			$values['min'] = 0; // no scores, so reset min to 0
		}

		return $values;
	}

	/********************************************************************/
	
	/**
	 * Calculates the final score for a given QuestionGroup while user is still in the attempt
	 * @param $qgroup (number) QuestionGroup ID
	 * @return (number) final score
	 */
	public function calculateFinal($qGroupID)
	{
		if($GLOBALS['CURRENT_INSTANCE_DATA']['attemptID'] < 1 )
		{
			$AM = \obo\AttemptsManager::getInstance();
			if(!$AM->getCurrentAttemptID())
			{
				return false;
			}
		}
		
		//Gets all scores from the database for this instance across all sessions
		$qstr = "SELECT ".\cfg_obo_Score::SCORE." FROM ".\cfg_obo_Score::TABLE." WHERE ".\cfg_obo_Attempt::ID."='{$GLOBALS['CURRENT_INSTANCE_DATA']['attemptID']}' AND ".\cfg_obo_QGroup::ID."='?'";
		
		if( !($q = $this->DBM->querySafe($qstr, $qGroupID)) )
		{
			trace(mysql_error(), true);
			$this->DBM->rollback();
			//die();
			return false;
		}
		
		//Get data from query and add up scores
		$sum = 0;
		while( $r = $this->DBM->fetch_obj($q) )
		{
			$sum += $r->{\cfg_obo_Score::SCORE};
		}
		
		// get the qgroup to get it's length (quizSize may be less then the actual number of kids)
		$qgroup = new \obo\lo\QuestionGroup();
		$qgroup->getFromDB($this->DBM, $qGroupID, true);
			
		//Calculate score (checking for divide by zero
		if($qgroup->quizSize != 0)
		{
			$score = round($sum / $qgroup->quizSize);
		}
		else
		{
			$score = 0;
		}
		return $score;
	}

	/**
	 * Gets total number of questions in a question group (excluding media)
	 * @param $qGroupID (number) Question Group ID
	 * @return (number) total number of questions
	 *
	 */
	// TODO: remove this
	private function getNumQuestions($qGroupID)
	{
		$qgroup = new \obo\lo\QuestionGroup();
		$qgroup->getFromDB($this->DBM, $qGroupID, true);
		return $qgroup->calculateQuizSize();

	}

	public function getQuestionScoreForAttempt($attemptID, $questionID)
	{
		if(empty($attemptID) || empty($questionID)) return false;

		$query = "SELECT * FROM ".\cfg_obo_Score::TABLE." WHERE ".\cfg_obo_Attempt::ID." = '?' AND ".\cfg_obo_Score::ITEM_ID." = '?'";
		if( !($q = $this->DBM->querySafe($query, $attemptID, $questionID)) ) return false;
		if( $r = $this->DBM->fetch_obj($q) ) return $r->{\cfg_obo_Score::SCORE};
		return false;
	}
}
?>