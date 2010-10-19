<?php
/**
 * This class contains all logic pertaining to QuestionGroups
 * @author Jacob Bates <jbates@mail.ucf.edu>
 * @author Luis Estrada <lestrada@mail.ucf.edu>
 * 
 * map_qgroup child types:
 * 'q' = question
 * 'm' = media
 */

/**
 * This class contains all logic pertaining to QuestionGroups
 * This includes creating, retrieving, and deleting of data.
 * 
 * map_qgroup child types:
 * 'q' = question
 * 'm' = media
 */
class nm_los_QuestionGroupManager extends core_db_dbEnabled
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
	 * Gets an entire QuestionGroup from the database, including all questions in it
	 * @param $gid (number) QuestionGroup ID
	 * @param $includeKids (bool) True to include questions, False to not include them
	 * @return (QuestionGroup) the requested QuestionGroup
	 */
	// TODO: remove this
	public function getGroup($gid, $includeKids = true)
	{
		$qgroup = new nm_los_QuestionGroup();
		$qgroup->getFromDB($this->DBM, $gid, $includeKids);
		return $qgroup;
	}

	/**
	 * Creates a new QuestionGroup entry and returns the id of the newly created entry. 
	 * @param $qgroup (QuestionGroup) New QuestionGroup data
	 * @return (QuestionGroup) The new QuestionGroup including the new ID
	 */
		// TODO: remove this
	public function newGroup($qgroup = '')
	{
		
		if(!($qgroup instanceof nm_los_QuestionGroup))
		{
	        return false;
		}
		// if id isnt 0, no need to make any changes
		if($qgroup->qGroupID != 0)
		{
			return false;
		}

		$qstr = "INSERT INTO ".cfg_obo_QGroup::TABLE." SET ".cfg_core_User::ID."='?', ".cfg_obo_QGroup::RAND."='?', ".cfg_obo_QGroup::ALTS."='?', ".cfg_obo_QGroup::ALT_TYPE."='?'";
		if( !($q = $this->DBM->querySafe($qstr, $_SESSION['userID'], $qgroup->rand, $qgroup->allowAlts, $qgroup->altMethod)) )
		{
			$this->DBM->rollback();
			return false;	
		}
		$qgroup->qGroupID = $this->DBM->insertID;
		//Question alternate mapping:
		//First, convert each negative questionIndex value into a positive one.

		//Since these questionIndexs are created with each new qgroup we simply convert
		//each questionIndex to postive sequental (1, 2, ...)
		/* Question alternate mapping:
		 * incoming object uses 'questionIndex' to group question alternates
		 * if its value is 0, question has no alts
		 * if questionIndex is not 0, all other questions with the matching index are alts
		 */
		
		
		// TODO: move this to the function that converts remote obects into php objects
		// $groupQIndex = 0;// must start at 1, 0 assumes no alternates
		// $lastAlt = 0;
		// foreach($qgroup->kids as $question)
		// {
		// 	// incriment, question has no alternates
		// 	if($question->questionIndex == 0)
		// 	{
		// 		$groupQIndex ++; 
		// 	}
		// 	// question is in an alternate group
		// 	else
		// 	{
		// 		// is this a new alternate group?
		// 		if($question->questionIndex != $lastAlt)
		// 		{
		// 			$groupQIndex ++; //  incriment, question is grouped with new alternates - must happen before
		// 			$lastAlt = $question->questionIndex;
		// 		}
		// 		$question->questionIndex = $groupQIndex;
		// 	}
		// }
		//Fill in the mapping table for the questions in the group, creating new questions as needed
		$questionMan = nm_los_QuestionManager::getInstance();
		foreach($qgroup->kids as $key => $question)
		{
			$questionMan->newQuestion($question); // create the question if the id is 'dirty'
			
			// always map the question to the qgroup
			$qstr = "INSERT INTO ".cfg_obo_QGroup::MAP_TABLE." SET ".cfg_obo_QGroup::ID."='?', ".cfg_obo_QGroup::MAP_CHILD."='?', ".cfg_obo_QGroup::MAP_TYPE."='q', ".cfg_obo_QGroup::MAP_ORDER."='?'";
			if(!($q = $this->DBM->querySafe($qstr, $qgroup->qGroupID, $question->questionID, $key)))
			{
				$this->DBM->rollback();
				return false;
			}
			
			// always store alternate mapping if set
			if($question->questionIndex != 0)
			{
				$qStr = " INSERT INTO ".cfg_obo_QGroup::MAP_ALT_TABLE." SET ".cfg_obo_QGroup::ID." = '?', ".cfg_obo_Question::ID." = '?', ".cfg_obo_QGroup::MAP_ALT_INDEX." = '?'";
				if(!$q = $this->DBM->querySafe($qStr, $qgroup->qGroupID, $question->questionID, $question->questionIndex))
				{
					$this->DBM->rollback();
					return false;
				}
			}
		}

		return true;
	}
	
	/**
	 * Adds a question to the group mapping table
	 * @param $gid (number) group ID
	 * @param $cid (number) child ID
	 * @param $ctype (number) child type (see table at top of source)
	 * @param $corder (number) child order (0,1,2...)
	 * @param @crequire (boolean) True = Force question to appear in a bs/br type assessment quiz.
	 */
	private function mapQuestion($qgid, $cid, $ctype, $corder)
	{
		$qstr = "INSERT INTO ".cfg_obo_QGroup::MAP_TABLE." SET ".cfg_obo_QGroup::ID."='?', ".cfg_obo_QGroup::MAP_CHILD."='?', ".cfg_obo_QGroup::MAP_TYPE."='?', ".cfg_obo_QGroup::MAP_ORDER."='?'";
		
		if(!($q = $this->DBM->querySafe($qstr, $qgid, $cid, $ctype, $corder)))
		{
			$this->DBM->rollback();
			return false;
		}
	}
	
	public function getQuizSize($qGroupID)
	{
		if(!is_numeric($qGroupID) || $qGroupID <= 0)
		{
			trace('failed input validation', true);
			trace($qGroupID, true);
			return false;
		}
		
		
		if( ($qgroup = core_util_Cache::getInstance()->getQGroup($qGroupID) ) && is_array($qgroup))
		{
			return count($qgroup->kids);
		}
		else
		{
			$q = $this->DBM->querySafe("SELECT (t1.nonAlts + t2.uniqueAlts) AS quizSize FROM (SELECT COUNT(*) AS nonAlts FROM ".cfg_obo_QGroup::MAP_TABLE." AS map
										LEFT JOIN ".cfg_obo_QGroup::MAP_ALT_TABLE." AS alt
										ON map.".cfg_obo_QGroup::MAP_CHILD." = alt.".cfg_obo_Question::ID."
										WHERE map.".cfg_obo_QGroup::ID." = '?' AND alt.".cfg_obo_QGroup::ID." IS NULL) AS t1, ( SELECT Count( DISTINCT ".cfg_obo_QGroup::MAP_ALT_INDEX." ) AS uniqueAlts
						FROM ".cfg_obo_QGroup::MAP_ALT_TABLE."
						WHERE ".cfg_obo_QGroup::ID." = '?') AS t2", $qGroupID, $qGroupID);
			if($r = $this->DBM->fetch_obj($q))
			{
				return $r->quizSize;
			}
		}
		return false;
	}
	
	/**
	 * Deletes a QuestionGroup from the database
	 * @param $qgid (number) question group ID
	 * @return (bool) True if successful, False if incorrect parameter
	 * @deprecated no one should be able to just delete a question group, most of the deleting is done through deleting an LO
	 * 
	 * @todo add some kind of permissions checking here
	 */
		// TODO: remove this
	public function delGroup($qgid = 0)
	{
		if(!is_numeric($qgid) || $qgid < 1)
		{
			return false;
		}
		//Gather up a list of questions to delete
		$qstr = "SELECT ".cfg_obo_QGroup::MAP_CHILD.", ".cfg_obo_QGroup::MAP_TYPE." FROM ".cfg_obo_QGroup::MAP_TABLE." WHERE ".cfg_obo_QGroup::ID."='?' AND ".cfg_obo_QGroup::MAP_TYPE."='q' AND ".cfg_obo_QGroup::MAP_CHILD." NOT IN (
					SELECT ".cfg_obo_QGroup::MAP_CHILD." FROM ".cfg_obo_QGroup::MAP_TABLE." WHERE ".cfg_obo_QGroup::ID."!='?')";
		
		$q = $this->DBM->querySafe($qstr, $qgid, $qgid);
	
		$qman = nm_los_QuestionManager::getInstance();
		while($r = $this->DBM->fetch_obj($q))
		{
			$qman->delQuestion($r->{cfg_obo_QGroup::MAP_CHILD});
		}
		//Clean out entries for this group in the mapping table
		$qstr = "DELETE FROM ".cfg_obo_QGroup::MAP_TABLE." WHERE ".cfg_obo_QGroup::ID."='?'";
		if(!($q = $this->DBM->querySafe($qstr, $qgid)))
		{
			$this->DBM->rollback();
			return false;
		}
		
		//Delete the question group
		$qstr = "DELETE FROM ".cfg_obo_QGroup::TABLE." WHERE ".cfg_obo_QGroup::ID."='?' LIMIT 1";
		if(!($q = $this->DBM->querySafe($qstr, $qgid)))
		{
			$this->DBM->rollback();
			return false;
		}
		
		core_util_Cache::getInstance()->clearQGroup($qgid);
		return true;
	}
}
?>
