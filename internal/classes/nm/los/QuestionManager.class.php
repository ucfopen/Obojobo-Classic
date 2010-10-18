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
	// TODO: FIX RETURN FOR DB ABSTRACTION
	public function newQuestion($quest)
	{
		$userID = $_SESSION['userID'];
        $this->defaultDBM();

        $qstr = "INSERT INTO ".cfg_obo_Question::TABLE." SET 
			".cfg_core_User::ID."='{$userID}', 
			`".cfg_obo_Question::TYPE."`='?', 
			".cfg_obo_Question::DATE."=UNIX_TIMESTAMP()";
   		//Create line in table
		if( !($q = $this->DBM->querySafe($qstr, $quest['itemType'])) )
		{
			$this->DBM->rollback();
			return false;
		}
		$quest['questionID'] = $this->DBM->insertID;
		$q = $this->DBM->querySafe("SELECT ".cfg_obo_Question::DATE." FROM ".cfg_obo_Question::TABLE." WHERE ".cfg_obo_Question::ID."='?' LIMIT 1", $quest['questionID']); // no need for querySafe
		$r = $this->DBM->fetch_obj($q);
		$quest['createTime']  = $r->{cfg_obo_Question::DATE};

		//Add feedback
		$qstr = "INSERT INTO ".cfg_obo_Question::MAP_FEEDBACK_TABLE." SET ".cfg_obo_Question::ID." = '?', ".cfg_obo_Question::MAP_FEEDBACK_INCORRECT." = '?', ".cfg_obo_Question::MAP_FEEDBACK_CORRECT." = '?'";
		if(!$this->DBM->querySafe($qstr, $quest['questionID'], $quest['feedback']['incorrect'], $quest['feedback']['correct']))
		{

			$this->DBM->rollback();
			return false;
		}
		
		//Add permissions to this question
		if($quest['perms'] != 0)
		{
			$quest['perms'] = new nm_los_Permissions();
		}
		$permman = nm_los_PermissionsManager::getInstance();
		$permman->setGlobalPerms($quest['questionID'], 'q', $quest['perms']);
		
		//Add new page items:
		$order = 0;
		foreach($quest['items'] as $pgItem)
		{
			if($pgItem['pageItemID'] == 0)
			{
				//Insert new page item:
				$qStr = "INSERT INTO ".cfg_obo_Page::ITEM_TABLE." SET ".cfg_obo_Page::ITEM_COMPONENT." = '?', ".cfg_obo_Page::ITEM_DATA." = '?'";
				if(!($r2 = $this->DBM->querySafe($qStr, $pgItem['component'], $pgItem['data'])))
				{
					$this->DBM->rollback();
					return false;
				}
				
				$pageItemID = $this->DBM->insertID;
				
				//Map page item to question:
				$qStr = "INSERT
				 		INTO
				 			".cfg_obo_Question::MAP_ITEM_TABLE."
				 		SET 
							".cfg_obo_Question::ID." = '?',
							".cfg_obo_Question::MAP_ITEM_ORDER." = '?',
							".cfg_obo_Page::ITEM_ID." = '?'";
				if(!($r3 = $this->DBM->querySafe($qStr, $quest['questionID'], $order, $pageItemID)))
				{
					$this->DBM->rollback();
					return false;
				}
				//If page item was a MediaView, map media to a page_item:
				if($pgItem["component"] == "MediaView")
				{
					$mediaOrder = 0;
					
					foreach($pgItem["media"] as $media)
					{
						$qStr = "INSERT INTO ".cfg_obo_Media::MAP_TABLE." SET ".cfg_obo_Page::ITEM_ID."='?', ".cfg_obo_Media::MAP_ORDER."='?', ".cfg_obo_Media::ID."='?'";
						if( !($q = $this->DBM->querySafe($qStr, $pageItemID, $mediaOrder, $media["mediaID"])) )
						{
							$this->DBM->rollback();
							return false;
						}
						
						$mediaOrder++;
					}
				}
			}
			else
			{
				$qStr = "INSERT INTO ".cfg_obo_Question::MAP_ITEM_TABLE." SET ".cfg_obo_Question::ID."='?', ".cfg_obo_Question::MAP_ITEM_ORDER."='?', ".cfg_obo_Page::ITEM_ID."='?'";
				if( !($q = $this->DBM->querySafe($qStr, $quest['questionID'], $order, $pgItem['pageItemID'])) )
				{
					$this->DBM->rollback();
	    			return false;
				}
			}
			
			$order++;
		}
		
		//Give this author full perms for this question
		//$permman->setUserPerms();
		
		//Add answers to the question, making new ones if needed.
		$ansman = nm_los_AnswerManager::getInstance();
		if(isset($quest['answers']))
		{
			foreach($quest['answers'] as $key => $ans){
				//Make new answers if needed
				if($ans['answerID']== 0)
				{
					$newans = $ansman->newAnswer($ans['answer']);
					$ans['answerID'] = $newans->answerID;
				}
				//Update lo_map_qa table
				$this->addAnswer($quest['questionID'], $ans['answerID'], $key, $ans['weight'], $ans['feedback']);
			}
		}
		
		return $quest;
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
		$this->defaultDBM();
		//Check permissions
		//$permMan = nm_los_PermissionsManager::getInstance();
		//$perm = $permMan->getPerm($userID, $questionID, 'q');

		//Log the access attempt
		//$this->logEntry($userID, $questionID, 'q', 'acc', $perm['_read'], '');
		//if($perm['_read']){
			//$permman = nm_los_PermissionsManager::getInstance();
			$q = $this->DBM->querySafe("SELECT * FROM ".cfg_obo_Question::TABLE." WHERE ".cfg_obo_Question::ID."='?' LIMIT 1", $questionID);
			
			if( $r = $this->DBM->fetch_obj($q) )
			{
				$quest = new nm_los_Question($r->{cfg_obo_Question::ID}, $r->{cfg_core_User::ID}, $r->{cfg_obo_Question::TYPE});
			}
			else
			{
				$quest = false;
			}
			
			$quest->questionIndex = $r;
			
			//Gather question-level feedback:
			$qStr = "	SELECT *
						FROM ".cfg_obo_Question::MAP_FEEDBACK_TABLE."
						WHERE ".cfg_obo_Question::ID." = '?'";
			
			$q = $this->DBM->querySafe($qStr, $questionID);
			
			if($r = $this->DBM->fetch_obj($q))
			{
				$quest->feedback = array('correct' => $r->{cfg_obo_Question::MAP_FEEDBACK_CORRECT}, 'incorrect' => $r->{cfg_obo_Question::MAP_FEEDBACK_INCORRECT});
			}
			else
			{
				trace('feedback error for questionID = '.$questionID, true);
				$this->DBM->rollback();
				return false;
			}
			
			/*
			//Load layout into LayoutManager
			$layman = nm_los_LayoutManager::getInstance();
			$layman->getLayout($r->layoutID);
			*/
			//Gather up answers
			// TODO: use pageItemManager to gather pageitems
			$q = $this->DBM->querySafe("SELECT ".cfg_obo_Question::MAP_ANS_WEIGHT.", ".cfg_obo_Question::MAP_ANS_FEEDBACK." ,".cfg_obo_Answer::ID." FROM ".cfg_obo_Question::MAP_ANS_TABLE." WHERE ".cfg_obo_Question::ID."='?' ORDER BY ".cfg_obo_Question::MAP_ANS_ORDER." ASC", $questionID);
			$ansMan = nm_los_AnswerManager::getInstance();
			while($r = $this->DBM->fetch_obj($q))
			{
				if($inc_weight)
				{
					$quest->answers[] = $ansMan->getAnswer($r->{cfg_obo_Answer::ID}, $r->{cfg_obo_Question::MAP_ANS_WEIGHT}, $r->{cfg_obo_Question::MAP_ANS_FEEDBACK});
				}
				else		//Don't include the weight of the answer
				{
					$quest->answers[] = $ansMan->getAnswer($r->{cfg_obo_Answer::ID}, 0, '');
				}
			}
			//Gather page items:
			$qStr = "	SELECT I.*
						FROM ".cfg_obo_Page::ITEM_TABLE." AS I, ".cfg_obo_Question::MAP_ITEM_TABLE." AS M
						WHERE I.".cfg_obo_Page::ITEM_ID." = M.".cfg_obo_Page::ITEM_ID."
						AND M.".cfg_obo_Question::ID." = '?'
						ORDER BY M.".cfg_obo_Question::MAP_ITEM_ORDER."";
						
			$q = $this->DBM->querySafe($qStr, $questionID);
			while($r = $this->DBM->fetch_obj($q))
			{
				$quest->items[] = $r;
				if($r->{cfg_obo_Page::ITEM_COMPONENT} == "MediaView")
				{
					//Fetch media into an array:
					$qStr = "	SELECT MA.".cfg_obo_Media::ID."
								FROM ".cfg_obo_Media::TABLE." AS M, ".cfg_obo_Media::MAP_TABLE." AS MA
								WHERE M.".cfg_obo_Media::ID." = MA.".cfg_obo_Media::ID."
								AND MA.".cfg_obo_Page::ITEM_ID." = '?'
								ORDER BY MA.".cfg_obo_Media::MAP_ORDER."";
					
					$q2 = $this->DBM->querySafe($qStr, $r->{cfg_obo_Page::ITEM_ID});
					$mediaMan = nm_los_MediaManager::getInstance();
					while($r2 = $this->DBM->fetch_obj($q2))
					{
						$quest->items[ (count($quest->items) - 1) ]->media[] = $mediaMan->getMedia($r2->{cfg_obo_Media::ID});
					}
				}
			}
			
			return $quest;
		//}

		//return false;
	}
/*
	public function saveQuestion($quest){
		$this->defaultDBM();
		
		if($quest->questionID == 0){	//If question doesn't exist, create it
			return $this->newQuestion($quest);
		}else{					//If it does exist, update it
			//Check permissions
			//$permMan = nm_los_PermissionsManager::getInstance();
			//$perm = $permMan->getPerm($userID, $ans->answerID, 'a');
			
			//$access = $perm['_write'];

			//Log the access attempt
			//$this->logEntry($userID, $ans->answerID, 'a', 'mod', $access, '');
			//Save Answer data
			
			if( !($q = $this->DBM->query("UPDATE ".self::table." SET
				type='{$quest->type}', 
				question_text='{$quest->qtext}', 
			WHERE id={$quest->questionID} LIMIT 1")) ){
				$this->DBM->rollback();
				//die();
				return false;	
			}
		}

		return $quest;
	}
*/

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
			//die();
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

		//Gather up a list of answers to delete
		
		$qstr = "SELECT ".cfg_obo_Answer::ID." FROM ".cfg_obo_Question::MAP_ANS_TABLE." WHERE ".cfg_obo_Question::ID."='?' AND ".cfg_obo_Answer::ID." NOT IN (
					SELECT ".cfg_obo_Answer::ID." FROM ".cfg_obo_Question::MAP_ANS_TABLE." WHERE ".cfg_obo_Question::ID."!='?')";
		
		$q = $this->DBM->querySafe($qstr, $questionID, $questionID);

		$aman = nm_los_AnswerManager::getInstance();
		while($r = $this->DBM->fetch_obj($q))
		{
			$aman->delAnswer($r->{cfg_obo_Answer::ID});
		}
		//Clean out entries for this question in the mapping table
		if( !($q = $this->DBM->querySafe("DELETE FROM ".cfg_obo_Question::MAP_ANS_TABLE." WHERE ".cfg_obo_Question::ID."='?'", $questionID)) )
		{
			$this->DBM->rollback();
			//die();
			return false;	
		}
		
		//Delete media references
		if( !($q = $this->DBM->querySafe("DELETE FROM ".cfg_obo_Media::MAP_TABLE." WHERE ".cfg_obo_Page::ITEM_ID."='?' ", $questionID)) )
		{
			$this->DBM->rollback();
			//die();
			return false;
		}
		
		//Delete the question
		if( !($q = $this->DBM->querySafe("DELETE FROM ".cfg_obo_Question::TABLE." WHERE ".cfg_obo_Question::ID."='?' LIMIT 1", $questionID)) )
		{
			$this->DBM->rollback();
			//die();
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
	public function checkAnswer($questionID, $answer)
	{
		if($questionID == 0)
		{
			return false;
		}
		// if the answer is numeric and the question is a Multiple choice, check using the id
		$qstr = "SELECT `".cfg_obo_Question::TYPE."` FROM ".cfg_obo_Question::TABLE." WHERE ".cfg_obo_Question::ID."='?'";
		$r = $this->DBM->fetch_obj($this->DBM->querySafe($qstr, $questionID));
		$qType = $r->{cfg_obo_Question::TYPE};
		switch($qType)
		{
			case 'MC':
				$qstr = "SELECT
					".cfg_obo_Question::MAP_ANS_WEIGHT.", 
					".cfg_obo_Answer::ID.", 
					".cfg_obo_Question::MAP_ANS_FEEDBACK." 
					FROM 
					".cfg_obo_Question::MAP_ANS_TABLE."
					 WHERE ".cfg_obo_Question::ID."='?'
					 AND ".cfg_obo_Answer::ID." = '?'";
				if( !($q = $this->DBM->querySafe($qstr, $questionID, $answer)) )
				{
					return false;
				}
				break;
			case 'QA':
				// trim whitespace from the submitted answer
				$answer = trim($answer);
				// don't bother getting feedback here, not needed if its correct
				$qstr = "SELECT ".cfg_obo_Question::MAP_ANS_WEIGHT.", ".cfg_obo_Answer::ID.", ".cfg_obo_Question::MAP_ANS_FEEDBACK." FROM ".cfg_obo_Question::MAP_ANS_TABLE." 
							WHERE ".cfg_obo_Question::ID."='?'
							AND ".cfg_obo_Answer::ID." IN 
							(
								SELECT ".cfg_obo_Answer::ID." FROM  ".cfg_obo_Answer::TABLE."
								WHERE ".cfg_obo_Answer::TEXT."='?'
							)
							LIMIT 1";
				if( !($q = $this->DBM->querySafe($qstr, $questionID, $answer)) )
				{
					return false;
				}
				break;
			case 'Media':
				$qstr = "SELECT ".cfg_obo_Question::MAP_ANS_WEIGHT." FROM ".cfg_obo_Question::MAP_ANS_TABLE." WHERE ".cfg_obo_Question::ID."='?'";
				//echo $qstr;
				if( !($q = $this->DBM->querySafe($qstr, $questionID)) )
				{
					return false;
				}
				break;
			default:
				break;
		}



		// if match found, return values from match
		if( $r = $this->DBM->fetch_obj($q) )
		{
			return array('weight' => $r->{cfg_obo_Question::MAP_ANS_WEIGHT}, 'answerID' => $r->{cfg_obo_Answer::ID}, 'feedback' => $r->{cfg_obo_Question::MAP_ANS_FEEDBACK});
		}
		// no match at all, completely wrong
		else
		{
			switch($qType)
			{
				case 'MC':
					
					
					return core_util_Error::getError(2);
					break;
				case 'QA':
					// have to get feedback for QA's as their feedback is based on a wrong answer
					
					//Add feedback
					$qstr = "SELECT ".cfg_obo_Question::MAP_FEEDBACK_INCORRECT." FROM ".cfg_obo_Question::MAP_FEEDBACK_TABLE." WHERE ".cfg_obo_Question::ID." = '?'";
					if(!($q = $this->DBM->querySafe($qstr, $questionID)))
					{
						return false;
					}
					
					$r = $this->DBM->fetch_obj($q);
					return array('weight' => 0, 'answerID' => 0, 'feedback' => $r->{cfg_obo_Question::MAP_FEEDBACK_INCORRECT});
					break;
				case 'Media':
					return array('weight' => 100, 'answerID' =>0, 'feedback' => '');
				default:
					break;
			}
			
		}
	}
}
?>
