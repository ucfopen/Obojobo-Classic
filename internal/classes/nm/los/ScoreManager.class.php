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
class nm_los_ScoreManager extends core_db_dbEnabled
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
	
	/**
	 * Submits a question to be graded
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
		if($GLOBALS['CURRENT_INSTANCE_DATA']['visitID'] < 1) //exit if they do not have an open instance
		{
			trace('visitid invalid', true);
			return false;
		}
		if($GLOBALS['CURRENT_INSTANCE_DATA']['attemptID'] < 1)
		{
			$AM = nm_los_AttemptsManager::getInstance();
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
		if(!is_numeric($questionID) || $questionID < 1)
		{
			trace('questionID invalid', true);
			return false; // error: invalid questionID
		}
		//Check to see if question has already been answered
		$qstr = "SELECT ".cfg_obo_Score::ITEM_ID." FROM ".cfg_obo_Score::TABLE." WHERE ".cfg_obo_Score::ITEM_ID."='?' AND `".cfg_obo_Score::TYPE."`='q' AND ".cfg_obo_Attempt::ID." ='?' LIMIT 1";
		if( !($q = $this->DBM->querySafe($qstr, $questionID, $GLOBALS['CURRENT_INSTANCE_DATA']['attemptID'])) ) //Run the query
		{
			$this->DBM->rollback();
			return false;
		}
		//if Question has already been answered
		if($this->DBM->fetch_num($q) > 0)
		{
			//TODO: 
			// if we are in practice, return false
			//return false;
			// if this is an assessment, users can change answers, update it
			$qstr = "DELETE FROM ".cfg_obo_Score::TABLE." WHERE ".cfg_obo_Score::ITEM_ID."='?' AND `".cfg_obo_Score::TYPE."`='q' AND ".cfg_obo_Attempt::ID." ='?'";
			$this->DBM->querySafe($qstr, $questionID, $GLOBALS['CURRENT_INSTANCE_DATA']['attemptID']);
		}

		$qstr = "SELECT * FROM ".cfg_obo_QGroup::MAP_TABLE." WHERE ".cfg_obo_QGroup::ID."='?' AND ".cfg_obo_QGroup::MAP_CHILD."='?' LIMIT 1";
		
		if(!($q = $this->DBM->querySafe($qstr, $qGroupID, $questionID)))
		{
		    $this->DBM->rollback();
			return false;
		}
		
		if($this->DBM->fetch_num($q) == 0)
		{
			trace('error finding db reference', true);
			return false; // error: 
		}
			
		// check answer and save score
		$qman = nm_los_QuestionManager::getInstance();
		$checkArr = $qman->checkAnswer($questionID, $answer);
		if(is_array($checkArr))
		{
			$qstr = "INSERT INTO ".cfg_obo_Score::TABLE." SET ".cfg_obo_Attempt::ID."='?', ".cfg_obo_QGroup::ID."='?', ".cfg_obo_Score::ITEM_ID."='?', `".cfg_obo_Score::TYPE."`='q', ".cfg_obo_Answer::ID."='?', ".cfg_obo_Score::ANSWER."='?',	".cfg_obo_Score::SCORE."='?'";
			if( !($q = $this->DBM->querySafe($qstr, $GLOBALS['CURRENT_INSTANCE_DATA']['attemptID'], $qGroupID, $questionID, $checkArr['answerID'], $answer, $checkArr['weight'])) )
			{
	            $this->DBM->rollback();
	            trace(mysql_error(), true);
				return false;
			}

			$trackingMan = nm_los_TrackingManager::getInstance();
			$trackingMan->trackSubmitQuestion($qGroupID, $questionID, $answer);

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
			$AM = nm_los_AttemptsManager::getInstance();
			if(!$AM->getCurrentAttemptID())
			{
				return false;
			}
		}		
		
	    $qstr = "SELECT `".cfg_obo_LO::ID."` FROM `".cfg_obo_Instance::TABLE."` WHERE `".cfg_obo_Instance::ID."`='{$GLOBALS['CURRENT_INSTANCE_DATA']["instID"]}' LIMIT 1";
        $q = $this->DBM->query($qstr);
        if($r = $this->DBM->fetch_obj($q)) // instance exists
        {
	        $qstr = "SELECT `".cfg_obo_LO::PGROUP."` FROM `".cfg_obo_LO::TABLE."` WHERE ".cfg_obo_LO::ID."='{$r->{cfg_obo_LO::ID}}' LIMIT 1";
	        $q = $this->DBM->query($qstr);
	        if($r = $this->DBM->fetch_obj($q)) // lo exists
	        {
	            if((int)$r->{cfg_obo_LO::PGROUP} == $qGroupID)
				{
	                return true;
				}
	        }
        }
        
        return false;
	}
	
	/**
	 * Submits a media score
	 * @param $qGroupID (number) QuestionGroup ID
	 * @param $mid (number) Media ID
	 * @param $score (number) The score of the media
	 *
	 * @todo fix the return values
	 *
	 * @return (bool) False if question already answered or no open LO instance.
	 * @return (Array) containing the values in the following table
	 *
	 * Values:
	 * 'weight' = weight (from 0 to 100) of answer (final score for question)
	 * 'answerID' = answer ID (if found)
	 * 'feedback' = customized feedback string (ex. Congratulations! You got the wrong answer!)
	 *
	 */
	public function submitMedia($qGroupID, $questionID, $score)
	{
		if( $GLOBALS['CURRENT_INSTANCE_DATA']['visitID'] < 1 ) //exit if they do not have an open instance
		{
			return false;
		}
		if($GLOBALS['CURRENT_INSTANCE_DATA']['attemptID'] < 1)
		{
			$AM = nm_los_AttemptsManager::getInstance();
			if(!$AM->getCurrentAttemptID())
			{
				return false;
			}
		}
		// TODO: do we need to escape these? shouldnt these be intigers
		if(is_string($qGroupID))
		{
			$qGroupID = mysql_real_escape_string($qGroupID);
		}
		if(is_string($questionID))
		{
			$questionID = mysql_real_escape_string($questionID);
		}
		if(is_string($score))
		{
			$score = mysql_real_escape_string($score);
		}
		//Check to make sure this question is a media question
		$QM = nm_los_QuestionManager::getInstance();
		$question = $QM->getQuestion($questionID);
		if($question->itemType != cfg_obo_Question::QTYPE_MEDIA)
		{
			
			
			return core_util_Error::getError(2);
		}
		
		
		//Check to see if media score has already been input
		$qstr = "SELECT ".cfg_obo_Score::ITEM_ID." FROM `".cfg_obo_Score::TABLE."` WHERE ".cfg_obo_Score::ITEM_ID."='?' AND `".cfg_obo_Score::TYPE."` = 'm' AND ".cfg_obo_Attempt::ID." ='?' LIMIT 1";

		if( !($q = $this->DBM->querySafe($qstr, $questionID, $GLOBALS['CURRENT_INSTANCE_DATA']['attemptID'])) )// Run the query
		{ 
			return false;
		}
		// if this is an assessment, users can change answers, update it
		if($this->DBM->fetch_num($q) > 0)
		{
			$qstr = "DELETE FROM ".cfg_obo_Score::TABLE." WHERE ".cfg_obo_Score::ITEM_ID."='?' AND `".cfg_obo_Score::TYPE."`='m' AND ".cfg_obo_Attempt::ID." ='?'";
			$this->DBM->querySafe($qstr, $questionID, $GLOBALS['CURRENT_INSTANCE_DATA']['attemptID']);
		}

		$qstr = "INSERT INTO ".cfg_obo_Score::TABLE." SET ".cfg_obo_Attempt::ID."='?', ".cfg_obo_QGroup::ID."='?', ".cfg_obo_Score::ITEM_ID."='?', `".cfg_obo_Score::TYPE."`='m', ".cfg_obo_Score::SCORE."='?'";

		if( !($q = $this->DBM->querySafe($qstr, $GLOBALS['CURRENT_INSTANCE_DATA']['attemptID'], $qGroupID, $questionID, $score)) ) // Run the query
		{
			return false;
		}

		$trackingMan = nm_los_TrackingManager::getInstance();
		$trackingMan->trackSubmitMedia($qGroupID, $questionID, $score);

		//return array('weight' => $score);
		return true;
	}
	
	/**
	 * Get all user's  scores for one instance, for class grading use
	 *
	 * @param string $instid 
	 * @return void
	 * @author Ian Turgeon
	 */
	public function getScores($instID = 0)
	{
		if(!is_numeric($instID) || $instID < 1)
		{
			return false; // error: invalid input
		}
		//If they do not have permissions to write to this instance, reject the request
		$roleMan = nm_los_RoleManager::getInstance();
		if(!$roleMan->isSuperUser())
		{
			$permman = nm_los_PermissionsManager::getInstance();
			if( !($permman->getMergedPerm($instID, cfg_obo_Perm::TYPE_INSTANCE, cfg_obo_Perm::WRITE, $_SESSION['userID'])) ){

				// check 2nd Perms system to see if they have read or own
				$pMan = nm_los_PermManager::getInstance();
				$perms = $pMan->getPermsForUserToItem($_SESSION['userID'], cfg_core_Perm::TYPE_INSTANCE, $instID);
				if(!is_array($perms) && !in_array(cfg_core_Perm::P_READ, $perms) && !in_array(cfg_core_Perm::P_OWN, $perms) )
				{
					return false;
				}
			}
		}
		// get from memcache
		
		if(false && $scores = core_util_Cache::getInstance()->getInstanceScores($instID))
		{
			return $scores;
		}
		// grab every assessment attempt for each user (including unsubmitted ones!)
		$qstr = "SELECT t1.".cfg_core_User::ID.", ".cfg_obo_Attempt::ID." as attemptID, ".cfg_obo_Attempt::LINKED_ATTEMPT.", ".cfg_obo_Attempt::SCORE.", ".cfg_obo_Attempt::END_TIME.", COALESCE(".cfg_obo_ExtraAttempt::EXTRA_COUNT.", 0) as additional_attempts
					FROM 
					(
						SELECT A.".cfg_obo_Attempt::ID.", V.".cfg_core_User::ID.", A.".cfg_obo_Attempt::SCORE.", V.".cfg_obo_Instance::ID." as instance_id, A.".cfg_obo_Attempt::LINKED_ATTEMPT.", A.".cfg_obo_Attempt::END_TIME."
 						FROM ".cfg_obo_Attempt::TABLE." AS A, ".cfg_obo_Visit::TABLE." AS V, ".cfg_obo_LO::TABLE." AS L, ".cfg_obo_Instance::TABLE." as I
 						WHERE V.".cfg_obo_Instance::ID." = '?'
						AND V.".cfg_obo_Instance::ID." = I.".cfg_obo_Instance::ID."
						AND L.".cfg_obo_LO::ID." = I.".cfg_obo_LO::ID."
						AND L.".cfg_obo_LO::AGROUP." = A.".cfg_obo_QGroup::ID."
 						AND A.".cfg_obo_Visit::ID." = V.".cfg_obo_Visit::ID."
					) AS t1
					LEFT OUTER JOIN ".cfg_obo_ExtraAttempt::TABLE." AS AA
					ON (AA.".cfg_core_User::ID." = t1.".cfg_core_User::ID." AND AA.".cfg_obo_Instance::ID." = t1.instance_id)
					ORDER BY ".cfg_core_User::ID." ASC, ".cfg_obo_Attempt::ID." ASC";

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
		$userMan = core_auth_AuthManager::getInstance();
		while($r = $this->DBM->fetch_obj($q))
		{
			// only create new array items for each user
			if($r->{cfg_core_User::ID} != $lastUID)
			{
				$i++;
				$result[$i] = array(
					'userID' => $r->{cfg_core_User::ID},
					'user' => $userMan->getNameObject($r->{cfg_core_User::ID}),
					'additional' => $r->additional_attempts,
					'attempts' => array()
				);
				$lastUID = $r->{cfg_core_User::ID};
			}
			// add attempts
			$result[$i]['attempts'][] = array('attemptID' => $r->attemptID, 'score' => $r->{cfg_obo_Attempt::SCORE}, 'linkedAttempt' => $r->{cfg_obo_Attempt::LINKED_ATTEMPT}, 'submitted' => $r->{cfg_obo_Attempt::END_TIME} > 0, 'submitDate' => $r->{cfg_obo_Attempt::END_TIME});
		}

		// store in memcache
		core_util_Cache::getInstance()->setInstanceScores($instID, $result);
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
		if(($loID = nm_los_InstanceManager::getLOID($instID)) == false) // if instanceof Error
		{
			return $loID; // error
		}
		if(($aGroup = nm_los_LOManager::getAssessmentID($loID)) == false) // if instanceof Error
		{
			return $aGroup; // error
		}
			
		$qstr = "SELECT 
					A.".cfg_obo_Attempt::ID.", 
					A.".cfg_obo_QGroup::ID.", 
					A.".cfg_obo_Visit::ID.", 
					A.".cfg_obo_Attempt::SCORE.", 
					A.".cfg_obo_Attempt::START_TIME.", 
					A.".cfg_obo_Attempt::END_TIME.",
					A.".cfg_obo_Attempt::LINKED_ATTEMPT."
				FROM 
					".cfg_obo_Attempt::TABLE." AS A,
					".cfg_obo_Visit::TABLE." AS V
				WHERE 
					V.".cfg_obo_Visit::ID." = A.".cfg_obo_Visit::ID." 
				AND 
					V.".cfg_obo_Instance::ID." = '?'
				AND
					V.".cfg_core_User::ID." = '?'
				AND
					A.".cfg_obo_Attempt::END_TIME." != 0";
			
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
	 * @author Zachary Berry
	 * 
	 * @param $instid (number) instance ID
	 * @param $questionID (number) question ID
	 * 
	 * @return (Array) an array of data.  (refer to table below)
	 *
	 * Score array values:
	 * 'userName' = Name of the user who submitted the question.
	 * 'attemptID' = ID of the attempt
	 * 'score' = The score tied to their response
	 * 'attempt_score' = The score of the attempt
	 * 'answer_id' = The ID of the answer
	 */
	
	//@TODO: Note - Function disabled for 1.1 release.
	public function getQuestionResponses($instID = 0, $questionID = 0)
	{

		$qstr =    "SELECT V.".cfg_core_User::ID.", A.".cfg_obo_Attempt::ID." as attemptID, S.".cfg_obo_Score::SCORE.", A.".cfg_obo_Attempt::SCORE." as attempt_score, S.".cfg_obo_Score::ANSWER." as answer_id
					FROM ".cfg_obo_Score::TABLE." AS S, ".cfg_obo_Attempt::TABLE." AS A, ".cfg_obo_Visit::TABLE." AS V, ".cfg_core_User::TABLE." AS U
					WHERE A.".cfg_obo_Attempt::ID." = S.".cfg_obo_Attempt::ID."
					AND V.".cfg_obo_Visit::ID." = A.".cfg_obo_Visit::ID."
					AND U.".cfg_core_User::ID." = V.".cfg_core_User::ID."
					AND V.".cfg_obo_Instance::ID." = '?'
					AND S.".cfg_obo_Score::ITEM_ID." = '?'
					ORDER BY V.".cfg_core_User::ID;
		
		if( !($q = $this->DBM->querySafe($qstr, $instID, $questionID)) )
		{
			trace(mysql_error(), true);
			$this->DBM->rollback();
			return false;
		}
		
		//We want to sort the result array by users.
		//Each user will then have an array of scores based on the number of attempts they took.
		$returnArr = array();
		$userMan = core_auth_AuthManager::getInstance();
		$userIndex = -1;
		$lastUser = 0;
		//$returnArr[0] = array();
		
		while( $r = $this->DBM->fetch_obj($q) )
		{
			if($lastUser == 0 || $lastUser != $r->{cfg_core_User::ID})
			{
				$userIndex++;
				$returnArr[$userIndex] = array(
					'user' => array(
						'userID' => $r->{cfg_core_User::ID},
						'userName' => $userMan->getNameObject($r->{cfg_core_User::ID}),
					),
					'responses' => array()
				);
			}
			
			array_push($returnArr[$userIndex]['responses'], array(
			'attemptID' => (int)$r->attemptID,
			'score' => (int)$r->{cfg_obo_Score::SCORE},
			'attemptScore' => (int)$r->attempt_score,
			'answerID' => (int)$r->answer_id
			));
			
			$lastUser = $r->{cfg_core_User::ID};
		}
		
		return $returnArr;
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
		$qAttempt = $this->DBM->querySafe("SELECT * FROM ".cfg_obo_Attempt::TABLE." AS A, ".cfg_obo_Visit::TABLE." AS V WHERE A.".cfg_obo_Visit::ID." = V.".cfg_obo_Visit::ID." AND V.".cfg_core_User::ID." = '?' AND V.".cfg_obo_Instance::ID." = '?'", $_SESSION['userID'], $instID);
		while($rAttempt = $this->DBM->fetch_obj($qAttempt))
		{
			$answers = array();
			
			// get all answers for this attempt
			if( !($qScore = $this->DBM->querySafe("SELECT * FROM ".cfg_obo_Score::TABLE." WHERE ".cfg_obo_Attempt::ID." = '?'", $rAttempt->{cfg_obo_Attempt::ID} )) )
			{
				return false;
			}
			while($rScore = $this->DBM->fetch_obj($qScore))
			{
				// if question is itemType q, look up the question
				if($rScore->itemType == 'q')
				{
					if( !($qQuestion = $this->DBM->query("SELECT * FROM ".cfg_obo_Question::TABLE." WHERE ".cfg_obo_Question::ID." = {$rScore->{cfg_obo_Score::ID}}") ) )
					{
						return false;
					}
					$question = $this->DBM->fetch_obj($qQuestion);
				}

				if($question->{cfg_obo_Question::AID} > 0) // if answerID is not zero, look up answer
				{
					$qGivenAnswer = $this->DBM->query("SELECT ".cfg_obo_Answer::TEXT." FROM ".cfg_obo_Answer::TABLE." WHERE ".cfg_obo_Answer::ID." = '".$question->{cfg_obo_Question::AID}."'");
					$rGivenAnswer = $this->DBM->fetch_obj($qGivenAnswer);
					$givenAnswer = $rGivenAnswer->{cfg_obo_Answer::TEXT};
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
						A.".cfg_obo_Attempt::ID.", 
						A.".cfg_obo_QGroup::ID.", 
						A.".cfg_obo_Attempt::SCORE.", 
						A.".cfg_obo_Attempt::START_TIME.", 
						A.".cfg_obo_Attempt::END_TIME.",
						V.".cfg_core_User::ID."
						FROM ".cfg_obo_Attempt::TABLE." AS A,
						 ".cfg_obo_Visit::TABLE." AS V
						WHERE 
						V.".cfg_obo_Instance::ID." = '?' 
						AND A.".cfg_obo_Visit::ID." = V.".cfg_obo_Visit::ID."
						ORDER BY A.".cfg_obo_QGroup::ID.", V.".cfg_obo_Visit::ID.", V.".cfg_core_User::ID, $instID);
		while($r = $this->DBM->fetch_obj($q))
		{
			$r->qscores = array();
			$q2 = $this->DBM->query("SELECT * FROM ".cfg_obo_Score::TABLE." WHERE ".cfg_obo_Attempt::ID."='". $r->{cfg_obo_Attempt::ID} ."'");
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
		if($GLOBALS['CURRENT_INSTANCE_DATA']['attemptID'] < 1)
		{
			$AM = nm_los_AnswerManager::getInstance();
			if(!$AM->getCurrentAttemptID())
			{
				return false;
			}
		}
		if(!is_numeric($qGroupID) || $qGroupID == 0)
		{
			return false;
		}
		//Get the scores for each individual question
		trace('getting answers for attempt: ' . $GLOBALS['CURRENT_INSTANCE_DATA']['attemptID'] . ', qGroupID: ' . $qGroupID);
		
		$qstr = "SELECT
					".cfg_obo_Score::ITEM_ID.", 
					`".cfg_obo_Score::TYPE."`, 
					".cfg_obo_Answer::ID.", 
					".cfg_obo_Score::ANSWER.", 
					".cfg_obo_Score::SCORE." 
					FROM 
						".cfg_obo_Score::TABLE."
					WHERE 
						".cfg_obo_Attempt::ID." ='{$GLOBALS['CURRENT_INSTANCE_DATA']['attemptID']}' 
						AND ".cfg_obo_QGroup::ID."='?' ORDER BY ".cfg_obo_Score::ID." ASC";
		
		if( !($q = $this->DBM->querySafe($qstr, $qGroupID)) )
		{
			trace(mysql_error(), true);
			$this->DBM->rollback();
			return false;
		}

		$state = array('questions' => array());
		$ansman = nm_los_AnswerManager::getInstance();
		$qman = nm_los_QuestionManager::getInstance();
		while( $r = $this->DBM->fetch_obj($q) )
		{
			$question = $qman->getQuestion($r->{cfg_obo_Score::ITEM_ID});
			$real_answer = $ansman->getAnswer($r->{cfg_obo_Answer::ID});
			array_push($state['questions'], array('questionID' => $r->{cfg_obo_Score::ITEM_ID}, 'qtext' => $question->qtext, 'answerID' => $r->{cfg_obo_Answer::ID}, 'user_answer' => $r->{cfg_obo_Score::ANSWER}, 'score' => '', 'real_answer' => $real_answer->answer));
		}
		return $state;
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
			$AM = nm_los_AttemptsManager::getInstance();
			if(!$AM->getCurrentAttemptID())
			{
				return false;
			}
		}
		
		//Gets all scores from the database for this instance across all sessions
		$qstr = "SELECT ".cfg_obo_Score::SCORE." FROM ".cfg_obo_Score::TABLE." WHERE ".cfg_obo_Attempt::ID."='{$GLOBALS['CURRENT_INSTANCE_DATA']['attemptID']}' AND ".cfg_obo_QGroup::ID."='?'";
		
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
			$sum += $r->{cfg_obo_Score::SCORE};
		}
		
		// get the qgroup to get it's length (quizSize may be less then the actual number of kids)
		$qgroup = new nm_los_QuestionGroup();
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
		$qgroup = new nm_los_QuestionGroup();
		$qgroup->getFromDB($this->DBM, $qGroupID, true);
		return $qgroup->calculateQuizSize();

	}
}
?>
