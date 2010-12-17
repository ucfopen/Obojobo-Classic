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
		$authMod = $AM->getAuthModuleForUserID($studentID);
		$user = $authMod->getUser($studentID);
		
		// load up email template
		$smarty = new Smarty();
		
		$smarty->compile_dir = AppCfg::DIR_BASE . AppCfg::DIR_TEMPLATES . 'compiled/';

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

		$subject = $smarty->fetch('eval:Results for {$loTitle} {$loCourse|ternary:"($loCourse)":"no course"}');
		$body = $smarty->fetch(AppCfg::DIR_BASE . AppCfg::DIR_TEMPLATES . 'email-student-attempt-complete.tpl');
		$sent = $this->sendEmail($user->email, $subject, $body);
		
		core_util_Log::profile('email', "'$studentID','$user->email','$score','" . ($sent ? '1' : '0' ). "'\n");
		trace('here is body:');
		trace($body);
		return $sent;
	}
	
	public function sendEmail($to, $subject, $body, $autoDelay=true)
	{
		// TODO: add autodelay to build up a block of emails 
		
		
		$to = 'iturgeon@gmail.com';

		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

		// Additional headers
		$headers .= "To: $to\r\n";
		$headers .= 'From: Obojobo <no-reply@obojobo.ucf.edu>' . "\r\n";
		
		return mail($to, $subject, $body, $headers);
	}
	
}

?>