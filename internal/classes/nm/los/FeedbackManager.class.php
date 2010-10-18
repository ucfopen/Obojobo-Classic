<?php
/**
 * This class contains all logic pertaining to user submitted feedback
 * @author Zachary Berry <zberry@mail.ucf.edu>
 */

/**
 * This class contains all logic pertaining to user submitted feedback
 */
class nm_los_FeedbackManager extends core_db_dbEnabled
{
	const table = "lo_feedback";
	
	public function __construct()
	{
		$this->defaultDBM();
	}
	
	public function submitFeedback($comment_type, $comment)
	{
		$uid = $_SESSION['UID'];
		
		$qstr = "INSERT INTO `".self::table."` (`user_id`, `time`, `comment_type`, `comment`) 
								VALUES ('?', '?', '?', '?')";
									
		if(!$this->DBM->querySafe(	$qstr,
									$uid,
									time(),
									$comment_type,
									$comment))
		{
			core_util_Log::trace(mysql_error(), true);
			$this->DBM->rollback();
			//die();
			return false;
		}
		
		return true;	
	}
}
?>