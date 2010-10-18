<?php
/**
 * This class handles all database calls and logic pertaining to Answers.
 * @author Jacob Bates <jbates@mail.ucf.edu>
 * @author Luis Estrada <lestrada@mail.ucf.edu>
 */

/**
 * This class handles all database calls and logic pertaining to Answers.
 * This includes creating, retrieving, and deleting of data.
 */
class nm_los_AnswerManager extends core_db_dbEnabled
{
	private static $instance;
	
	public function __construct()
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
	 * Creates an answer entry and returns the new object
	 * @param $atext (string) text for new answer
	 */
	public function newAnswer($answer='')
	{
		if(!is_string($answer)/* || $answer == '' */)
			return false; // invalid input
		
		$userID = $_SESSION['userID'];

        $qstr = "INSERT INTO ".cfg_obo_Answer::TABLE." SET ".cfg_core_User::ID." = '?', ".cfg_obo_Answer::TEXT." = '?'";
        
        if(!($q = $this->DBM->querySafe($qstr, $userID, $answer)))
        {
        	$this->DBM->rollback();
        	trace(mysql_error(), true);
        	//die();
			return false;
        }
        
		return new nm_los_Answer($this->DBM->insertID, $userID, $answer);
	}
	
	/**
	 * Retrieves an answer from the database
	 * @param $answerID (number) Answer id, this is used to select the answer text from the database
	 * @param $weight (number) How much the answer is worth, between 0 and 100
	 * @param $feedback (string) Special feedback for this answer
	 * @return (Answer) Full answer object with weight and feedback appended to it
	 */
	public function getAnswer($answerID=0, $weight=0, $feedback='')
	{
		$qstr = "SELECT * FROM ".cfg_obo_Answer::TABLE." WHERE ".cfg_obo_Answer::ID." = '?' LIMIT 1";

		if(!($q = $this->DBM->querySafe($qstr,  $answerID)))
		{
			$this->DBM->rollback();
			trace(mysql_error(), true);
			//die();
			return false;
		}
		if( $r = $this->DBM->fetch_obj($q) )
		{
			return new nm_los_Answer($r->{cfg_obo_Answer::ID}, $r->{cfg_core_User::ID}, $r->{cfg_obo_Answer::TEXT}, $weight, $feedback);
		}	
		return false;
	}
		
	/**
	 * Deletes an answer from the database
	 * @param $answerID (number) ID of answer to delete
	 * @return (bool) True if successful, False if not
	 */
	public function delAnswer($answerID = 0)
	{
		if(!is_numeric($answerID) || $answerID < 1)
			return false;
		
		$qstr = "DELETE FROM ".cfg_obo_Answer::TABLE." WHERE ".cfg_obo_Answer::ID."='?' LIMIT 1";
		
		if(!($q = $this->DBM->querySafe($qstr, $answerID)))
		{
			$this->DBM->rollback();
			trace(mysql_error(), true);
			return false;
		}
		
		return true;
	}
}
?>
