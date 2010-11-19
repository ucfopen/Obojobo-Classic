<?php
/**
 * This class contains all logic pertaining to Questions
 * @author Jacob Bates <jbates@mail.ucf.edu>
 * @author Luis Estrada <lestrada@mail.ucf.edu>
 */

/**
 * This class contains all logic pertaining to Questions
 * This includes creating, retrieving, and deleting of data.
 */
class nm_los_QuestionManager extends core_db_dbEnabled
{
	private static $instance;
	
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
	 * Creates a new question object in the database
	 * @param $quest (Question) The new question object
	 * @return (Question) question object with new ID
	 */
	public function newQuestion($question)
	{
		if($question->questionID == 0)
		{
			$question->userID = $_SESSION['userID'];
			$this->defaultDBM();
			
			//*************  assign each answer a unique id *******************//
			foreach($question->answers AS $answer)
			{
				$answer->answerID = core_util_UID::createUID(); // assign the id
			}
		
			$qstr = "INSERT INTO ".cfg_obo_Question::TABLE." SET ".cfg_obo_Question::DATA."='?'";
			if( !($q = $this->DBM->querySafe($qstr, $this->db_serialize($question)) ) )
			{
				$this->DBM->rollback();
				return false;
			}
			$question->questionID = $this->DBM->insertID;
			$question->createTime = time();
			return true;
		}
		return false;
	}
	
	/**
	 * Retrieves a question object from the database
	 * @param $questionID (number) question ID
	 * @param $inc_weight (bool) True means that the weights for answers is included, False means they aren't
	 *
	 * @todo Add permission checking again
	 */
	// TODO: FIX RETURN FOR DB ABSTRACTION
	public function getQuestion($questionID=0, $inc_weight=true)
	{
		return $this->getQuestionNew($questionID, $inc_weight);
	}


	protected function getQuestionNew($questionID=0, $inc_weight=true)
	{
		$this->defaultDBM();
		
			$q = $this->DBM->querySafe("SELECT * FROM ".cfg_obo_Question::TABLE." WHERE ".cfg_obo_Question::ID."='?' LIMIT 1", $questionID);
			
			if( $r = $this->DBM->fetch_obj($q) )
			{
				$quest = unserialize(base64_decode($r->questionData));
				if($quest instanceof nm_los_Question)
				{
					$quest->questionID = $questionID; // the question id isn't set in the serialized data when its inserted into the database
				}
			}
			else
			{
				$quest = false;
			}
			
			return $quest;
	}

	/**
	 * Adds an existing Answer to a question
	 * @param $questionID (number) Question ID
	 * @param $answerID (number) Answer ID
	 * @param $order (number) The order of the answer in the question (0,1,2...)
	 * @param $weight (number) The weight of the answer (score someone gets if they select this answer)
	 * @param $feedback (string) The feedback string that will be shown to the user if they select this answer
	 */
	private function addAnswer($questionID, $answerID, $order, $weight, $feedback){
		$this->defaultDBM();
		
	//	$feedback_safe = mysql_escape_string($feedback);

		$qstr = "INSERT INTO ".cfg_obo_Question::MAP_ANS_TABLE." SET
			".cfg_obo_Question::ID."='?', ".cfg_obo_Question::MAP_ANS_ORDER."='?', ".cfg_obo_Question::MAP_ANS_WEIGHT."='?',
			".cfg_obo_Answer::ID."='?',
			".cfg_obo_Question::MAP_ANS_FEEDBACK."='?'";
		
		if( !($q = $this->DBM->querySafe($qstr, $questionID, $order, $weight, $answerID, $feedback)) )
		{
			$this->DBM->rollback();
			return false;
		}
	}
	
	/**
	 * Deletes a question from the database
	 * @param $questionID (number) Question ID
	 * @return (bool) True if successful, False if incorrect parameter
	 */
	public function delQuestion($questionID = 0)
	{
		if(!is_numeric($questionID) || $questionID == 0)
		{
			return false;
		}
		$this->defaultDBM();
		
		//Delete media references
		if( !($q = $this->DBM->querySafe("DELETE FROM ".cfg_obo_Media::MAP_TABLE." WHERE ".cfg_obo_Page::ITEM_ID."='?' ", $questionID)) )
		{
			$this->DBM->rollback();
			return false;
		}
		
		//Delete the question
		if( !($q = $this->DBM->querySafe("DELETE FROM ".cfg_obo_Question::TABLE." WHERE ".cfg_obo_Question::ID."='?' LIMIT 1", $questionID)) )
		{
			$this->DBM->rollback();
			return false;
		}
		
		return true;
	}

	/**
	 * Returns the weight of an answer to a question
	 * @param $questionID (number) Question ID
	 * @param $answer (string) answer text (to provide support for both QA and MC question types)
	 * @return (Array) Containing the values in the following table
	 * 
	 * Values: 
	 * 'weight' = weight (from 0 to 100) of answer (final score for question)
	 * 'answerID' = answer ID (if found)
	 * 'feedback' = customized feedback string (ex. Congratulations! You got the wrong answer!)
	 * 
	 * @todo Apply partial-credit algorithm for QA question types
	 * @todo TEST THIS
	 */
	public function checkAnswer($questionID, $userAnswer)
	{
		if($questionID == 0)
		{
			return false;
		}
		
		$question = $this->getQuestion($questionID);
		
		trace($question->itemType);
		trace($userAnswer);
		// if the answer is numeric and the question is a Multiple choice, check using the id
		switch($question->itemType)
		{
			/***************** MULTIPLE CHOICE *****************/
			case cfg_obo_Question::QTYPE_MULTI_CHOICE:
				// search for the answer id and return the weight for that answer
				foreach($question->answers AS $answer)
				{
					if($answer->answerID == $userAnswer)
					{
						// answer found - return the values
						return array(
							'weight' => $answer->weight,
							'answerID' => $answer->answerID,
							'feedback' => $answer->feedback,
							'type' => $question->itemType
						);
					}
				}
				return false; //  answer not found in question, this shouldnt happen
				break;
				
			/***************** FILL IN THE BLANK QUESTION/ANSWER *****************/
			case cfg_obo_Question::QTYPE_SHORT_ANSWER:
				$userAnswer = strtolower(trim($userAnswer));// trim whitespace from the submitted answer
				foreach($question->answers AS $answer)
				{
					if(strtolower($answer->answer) == $userAnswer)
					{
						// answer found
						return array(
							'weight' => $answer->weight,
							'answerID' => 0,
							'feedback' => $question->feedback['correct'],
							'type' => $question->itemType
						);
					}
				}
				
				// user's answer is not one of the correct answers
				return array(
					'weight' => 0,
					'answerID' => 0,
					'feedback' => $question->feedback['incorrect'],
					'type' => $question->itemType
				);
				break;
				
			/***************** MEDIA QUESTION *****************/
			case cfg_obo_Question::QTYPE_MEDIA:
				if(!nm_los_Validator::isScore($userAnswer))
				{
					trace('submitted media question is value ' . $userAnswer, true);
					return false; // invalid input
				}
				return array('weight' => $userAnswer, 'answerID' => 0, 'feedback' => '', 'type' => $question->itemType);
				break;
			default:
				return false;
				break;
		}
		
	}
}
?>
