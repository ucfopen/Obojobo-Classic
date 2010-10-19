<?php
/**
 * This class defines the QuestionGroup data type.
 * @author Jacob Bates <jbates@mail.ucf.edu>
 */

/**
 * This class defines the QuestionGroup data type, 
 * which contains a set of Questions and represents a Quiz.
 * It is used simply for representing data in memory, and has no methods.
 */
class nm_los_QuestionGroup
{
	public $qGroupID;				//Number:
	public $userID;			//Number:
	public $rand;			//Boolean: 
	public $allowAlts;			//Boolean:
	public $altMethod;		//Enum: 'r' or 'k'
	public $kids;			//Array: Questions
	public $quizSize;		//Number: actual size of quiz taking alts into account

	function __construct($qGroupID=0, $userID=0, $name='', $rand=0, $allowAlts=0, $altMethod='r', $kids=Array())
	{
		$this->qGroupID = $qGroupID;
		$this->userID = $userID;
		$this->rand = $rand;
		$this->allowAlts = $allowAlts;
		$this->altMethod = $altMethod;
		$this->kids = $kids;
	}
	
	private function calculateQuizSize()
	{
		if($this->quizSize > 0)
		{
			return $this->quizSize;
		}
		
		$size = 0;
		if(is_array($this->kids))
		{
			$lastAlt = 0;

			//If question alternates are enabled...
			if($this->allowAlts)
			{
				foreach($this->kids AS &$value)
				{
					if($value->questionIndex == 0)
					{
						$size++;
					}
					else
					{
						if($lastAlt != $value->questionIndex ) // this questionIndex wasn't seen before
						{
							$lastAlt = $value->questionIndex;
							$size ++;
						}
					}
				}
			}
			else
			{
				$size = count($this->kids);
			}
		}
		return $size;
	}

	
	public function getFromDB($DBM, $qGroupID, $includeKids = true)
	{

		// whitelist input
		if(!$DBM)
		{
			return false;
		}
		if(!is_numeric($qGroupID) || $qGroupID <= 0)
		{
			return false;
		}
		
		if( ($qgroup = core_util_Cache::getInstance()->getQGroup($qGroupID) ) && is_array($qgroup))
		{
			// copy data to this object
			foreach($qgroup AS $key => $value)
			{
				$this->$key = $value;
			}
			return true;
		}		
		
		//Get Question Group data
		$q = $DBM->querySafe("SELECT * FROM ".cfg_obo_QGroup::TABLE." WHERE ".cfg_obo_QGroup::ID."='?' LIMIT 1", $qGroupID);
		$r = $DBM->fetch_obj($q);
		$this->__construct($r->{cfg_obo_QGroup::ID}, $r->{cfg_core_User::ID}, $r->{cfg_obo_QGroup::TITLE}, $r->{cfg_obo_QGroup::RAND}, $r->{cfg_obo_QGroup::ALTS}, $r->{cfg_obo_QGroup::ALT_TYPE}, Array());
		
		if($includeKids)
		{
			//Gather questions/groups into an Array from mapping table
			$q = $DBM->querySafe("SELECT ".cfg_obo_QGroup::MAP_CHILD.", ".cfg_obo_QGroup::MAP_TYPE." FROM ".cfg_obo_QGroup::MAP_TABLE." WHERE ".cfg_obo_QGroup::ID."='?' ORDER BY ".cfg_obo_QGroup::MAP_ORDER." ASC", $qGroupID);
			$qman = nm_los_QuestionManager::getInstance();

			while($r = $DBM->fetch_obj($q))
			{
				$question = $qman->getQuestion($r->{cfg_obo_QGroup::MAP_CHILD});

				//Gather question alternate grouping links for this question
				$qStr = " SELECT ".cfg_obo_QGroup::MAP_ALT_INDEX." FROM ".cfg_obo_QGroup::MAP_ALT_TABLE." WHERE ".cfg_obo_QGroup::ID." = '?' AND ".cfg_obo_Question::ID." = '?'";
				$q2 = $DBM->querySafe($qStr, $qGroupID, $question->questionID);

				$question->questionIndex = 0;
				if($DBM->fetch_num($q2) == 1)
				{
					if(!$r2 = $DBM->fetch_assoc($q2))
					{
						return false;
					}
					$question->questionIndex = $r2['questionIndex'];
				}
				
				//Push to group:
				$this->kids[] = $question;

			}
			$this->quizSize = $this->calculateQuizSize();
			
			core_util_Cache::getInstance()->setQGroup($qGroupID, $this);
		}
		

		
		return true;
	}
	
}
?>
