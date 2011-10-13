<?php
namespace obo\util;
class NotificationManager extends \rocketD\db\DBEnabled
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
	
	public function sendCriticalError($subject, $message)
	{
		$this->mail('newmedia@mail.ucf.edu', '[OBO ERROR]: ' . $subject, $message);
		// echo($subject . ' ' . $message);
	}
	
	public function sendScoreFailureNotice($instructor, $student, $instData)
	{
		include_once(\AppCfg::DIR_BASE . \AppCfg::DIR_SCRIPTS . 'smarty/Smarty.class.php');
		
		// get student info
		$AM = \rocketD\auth\AuthManager::getInstance();
		$studentName = $AM->getName($student);
		trace('ya');
		// load up template
		$smarty = new \Smarty();
		$smarty->compile_dir = \AppCfg::DIR_BASE . \AppCfg::DIR_TEMPLATES . 'compiled/';
		$smarty->assign('studentName', $studentName);
		$smarty->assign('courseName', $instData->courseID);
		$smarty->assign('repositoryURL', \AppCfg::URL_WEB . \AppCfg::URL_REPOSITORY);
		$smarty->assign('instanceName', $instData->name);
		$smarty->assign('instanceURL', \AppCfg::URL_WEB . \AppCfg::URL_VIEWER . $instData->instID);
		$body = $smarty->fetch(\AppCfg::DIR_BASE . \AppCfg::DIR_TEMPLATES . 'email-instructor-score-sync-failure-plain.tpl');
		$subject = $smarty->fetch('eval:Obojobo Score Sync Notice - {$studentName} - {$courseName}');

		$headers = "MIME-Version: 1.0\n";
		$headers .= "From: Obojobo <no-reply@obojobo.ucf.edu>\n";
		
		$sent = $this->mail($instructor->email, $subject, $body, $headers);
		$this->sendCriticalError('Score Sync Failure - ' . $instData->courseID, 'instructor: '.print_r($instructor, true) . ' Student: ' . print_r($student, true) . ' InstData: ' . print_r($instData, true));
		
		return $sent;
	}
	
	protected function mail($to, $subject, $body, $headers = '')
	{
		return mail($to, $subject, $body, $headers);
		// trace('subject: ' .$subject);
		// trace('to: ' .$to);
		// trace('headers: ' .$headers);
		// trace('body: ' .$body);
		// return mail('iturgeon@gmail.com', $subject . " ($to)", $body, $headers);
	}
	
	public function sendScoreNotice($instData, $studentID, $extraAttempts, $scores, $score)
	{
		include_once(\AppCfg::DIR_BASE . \AppCfg::DIR_SCRIPTS . 'smarty/Smarty.class.php');
		
		// get student info
		$AM = \rocketD\auth\AuthManager::getInstance();
		$student = $AM->fetchUserByID($studentID);
		$boundry = '-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_';
		
		// load up email template
		$smarty = new \Smarty();

		$smarty->compile_dir = \AppCfg::DIR_BASE . \AppCfg::DIR_TEMPLATES . 'compiled/';
		
		$smarty->assign('multiPartBoundry', $boundry);
		$smarty->assign('loLink',\AppCfg::URL_WEB . \AppCfg::URL_VIEWER . $instData->instID);
		$smarty->assign('loTitle', $instData->name);
		$smarty->assign('loCourse', $instData->courseID);
		$smarty->assign('loInstructor', $instData->userName);
		$smarty->assign('loEnd', $instData->endTime);
		$smarty->assign('loScoreMethod', $instData->scoreMethod);
		$smarty->assign('attemptsRemaining', $instData->attemptCount + $extraAttempts - count($scores));
		
		$smarty->assign('imgDir', \AppCfg::URL_WEB . \AppCfg::DIR_ASSETS . 'images/score-confirmation/');
		$smarty->assign('finalScore', $score);
		$smarty->assign('attempts', array_reverse($scores));

		$headers = "MIME-Version: 1.0\n";
		$headers .= "From: Obojobo <no-reply@obojobo.ucf.edu>\n";
		$headers .= "Content-Type: multipart/alternative;boundary=\"" . $boundry . "\"\n";
		
		$body = "--$boundry" . "\n";
		$body .= "Content-Type: text/plain; charset=UTF-8" . "\n";
		$body .= "Content-Disposition: inline" . "\n";
		$body .= "Content-Transfer-Encoding: 7bit" . "\n\n";
		$body .= $smarty->fetch(\AppCfg::DIR_BASE . \AppCfg::DIR_TEMPLATES . 'email-student-attempt-plain.tpl');
		$body .= "\r\n\r\n--$boundry" . "\n";
		$body .= "Content-Type: text/html; charset=UTF-8" . "\n";
		$body .= "Content-Disposition: inline" . "\n";
		$body .= "Content-Transfer-Encoding: 7bit" . "\n\n";
		$body .= $smarty->fetch(\AppCfg::DIR_BASE . \AppCfg::DIR_TEMPLATES . 'email-student-attempt-html.tpl');
		$body .= "\n\n--$boundry" . "--\n";
		
		$subject = $smarty->fetch('eval:Results for {$loTitle} {$loCourse|ternary:"($loCourse)":"no course"}');

		
		$sent = $this->mail($student->email, $subject, $body, $headers);
		\rocketD\util\Log::profile('email', "'$studentID','$student->email','$score','" . ($sent ? '1' : '0' ). "'\n");

		return $sent;
	}
	
}

?>