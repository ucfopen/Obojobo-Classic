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
namespace obo\lo;
class QuestionGroupManager extends \rocketD\db\DBEnabled
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
		$qgroup = new \obo\lo\QuestionGroup();
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
		
		if(!($qgroup instanceof \obo\lo\QuestionGroup))
		{
	        return false;
		}
		// if id isnt 0, no need to make any changes
		if($qgroup->qGroupID != 0)
		{
			return false;
		}

		$qstr = "INSERT INTO ".\cfg_obo_QGroup::TABLE." SET ".\cfg_core_User::ID."='?', ".\cfg_obo_QGroup::RAND."='?', ".\cfg_obo_QGroup::ALTS."='?', ".\cfg_obo_QGroup::ALT_TYPE."='?'";
		if( !($q = $this->DBM->querySafe($qstr, $_SESSION['userID'], $qgroup->rand, $qgroup->allowAlts, $qgroup->altMethod)) )
		{
			$this->DBM->rollback();
			return false;	
		}
		$qgroup->qGroupID = $this->DBM->insertID;

		//Fill in the mapping table for the questions in the group, creating new questions as needed
		$questionMan = \obo\lo\QuestionManager::getInstance();
		foreach($qgroup->kids as $key => $question)
		{
			$questionMan->newQuestion($question); // create the question if the id is 'dirty'
			// map the question to this group
			$this->mapQuestionToGroup($qgroup->qGroupID, $question->questionID, $key, $question->questionIndex);
		}

		return true;
	}
	
	public function mapQuestionToGroup($qgroupID, $questionID, $index, $altIndex=0)
	{
		// map the question to the qgroup
		$qstr = "INSERT IGNORE INTO ".\cfg_obo_QGroup::MAP_TABLE." SET ".\cfg_obo_QGroup::ID."='?', ".\cfg_obo_QGroup::MAP_CHILD."='?', ".\cfg_obo_QGroup::MAP_ORDER."='?'";
		if(!($q = $this->DBM->querySafe($qstr, $qgroupID, $questionID, $index)))
		{
			$this->DBM->rollback();
			return false;
		}
		

		// store alternate mapping if set
		if($altIndex > 0)
		{
			$qStr = " INSERT IGNORE INTO ".\cfg_obo_QGroup::MAP_ALT_TABLE." SET ".\cfg_obo_QGroup::ID." = '?', ".\cfg_obo_Question::ID." = '?', ".\cfg_obo_QGroup::MAP_ALT_INDEX." = '?'";
			if(!$q = $this->DBM->querySafe($qStr, $qgroupID, $questionID, $altIndex))
			{
				$this->DBM->rollback();
				return false;
			}
		}
		return true;
	}

	
	// NOT USED - CHECK REPO FOR PREVIOUS IMPLIMENTATION
	// private function mapQuestion($qgid, $cid, $ctype, $corder)

	// NOT USED - CHECK REPO FOR PREVIOUS IMPLIMENTATION
	// public function getQuizSize($qGroupID)
	
	// NOT USED - CHECK REPO FOR PREVIOUS IMPLIMENTATION
	//public function delGroup($qgid = 0){}
}
?>
