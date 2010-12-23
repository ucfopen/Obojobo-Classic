<?php
/**
 * This class contains all logic pertaining to user submitted feedback
 * @author Zachary Berry <zberry@mail.ucf.edu>
 */

/**
 * This class contains all logic pertaining to user submitted feedback
 */

// TODO: delete this?
namespace obo\lo;
class FeedbackManager extends \rocketD\db\DBEnabled
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
			\rocketD\util\Log::trace(mysql_error(), true);
			$this->DBM->rollback();
			//die();
			return false;
		}
		
		return true;	
	}
}
?>