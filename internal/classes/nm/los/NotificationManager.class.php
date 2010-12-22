<?php
class nm_los_NotificationManager extends core_db_dbEnabled
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
	
	public function sendScoreNotice($instData, $studentID, $extraAttempts, $scores, $score)
	{
		include_once(AppCfg::DIR_BASE . AppCfg::DIR_SCRIPTS . 'smarty/Smarty.class.php');
		
		// get student info
		$AM = core_auth_AuthManager::getInstance();
		$user = $AM->fetchUserByID($studentID);
		$boundry = '-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_';
		
		
		// load up email template
		$smarty = new Smarty();
		
		$smarty->compile_dir = AppCfg::DIR_BASE . AppCfg::DIR_TEMPLATES . 'compiled/';
		
		$smarty->assign('multiPartBoundry', $boundry);
		$smarty->assign('loLink', AppCfg::URL_WEB . AppCfg::URL_VIEWER . $instData->instID);
		$smarty->assign('loTitle', $instData->name);
		$smarty->assign('loCourse', $instData->courseID);
		$smarty->assign('loInstructor', $instData->userName);
		$smarty->assign('loEnd', $instData->endTime);
		$smarty->assign('loScoreMethod', $instData->scoreMethod);
		$smarty->assign('attemptsRemaining', $instData->attemptCount + $extraAttempts - count($scores));
		
		$smarty->assign('imgDir', AppCfg::URL_WEB . AppCfg::DIR_ASSETS . 'images/score-confirmation/');
		$smarty->assign('finalScore', $score);
		$smarty->assign('attempts', array_reverse($scores));

		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "From: Obojobo <no-reply@obojobo.ucf.edu>\r\n";
		$headers .= "Content-Type: multipart/alternative;boundary=" . $boundry . "\r\n";
		
		$body = "--$boundry" . "\r\n";
		$body .= "Content-Type: text/plain; charset=UTF-8" . "\r\n";
		$body .= "Content-Disposition: inline" . "\r\n";
		$body .= "Content-Transfer-Encoding: 7bit" . "\r\n\r\n";
		$body .= $smarty->fetch(AppCfg::DIR_BASE . AppCfg::DIR_TEMPLATES . 'email-student-attempt-plain.tpl');
		$body .= "\r\n\r\n--$boundry" . "\r\n";
		$body .= "Content-Type: text/html; charset=UTF-8" . "\r\n";
		$body .= "Content-Disposition: inline" . "\r\n";
		$body .= "Content-Transfer-Encoding: 7bit" . "\r\n\r\n";
		$body .= $smarty->fetch(AppCfg::DIR_BASE . AppCfg::DIR_TEMPLATES . 'email-student-attempt-html.tpl');
		$body .= "\r\n\r\n--$boundry" . "--\r\n";
		
		$subject = $smarty->fetch('eval:Results for {$loTitle} {$loCourse|ternary:"($loCourse)":"no course"}');
		
//		trace($body);
		$sent = mail($to, $subject, $body, $headers);
		
		core_util_Log::profile('email', "'$studentID','$user->email','$score','" . ($sent ? '1' : '0' ). "'\n");

		return $sent;
	}
	
}

?>