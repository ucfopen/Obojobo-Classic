<?php
namespace obo\util;
class NotificationManager extends \rocketD\db\DBEnabled
{
	use \rocketD\Singleton;

	public function sendCriticalError($subject, $message)
	{
		$this->mail('i@ucf.edu,z@ucf.edu', '[OBO ERROR]: ' . $subject, $message);
	}

	protected function mail($to, $subject, $body, $headers = '')
	{
		return mail($to, $subject, $body, $headers);
	}

	public function sendScoreNotice($instData, $studentID, $extraAttempts, $scores, $score)
	{
		// get student info
		$AM = \rocketD\auth\AuthManager::getInstance();
		$student = $AM->fetchUserByID($studentID);
		$boundry = '-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_';

		// load up email template
		if ($smarty = \rocketD\util\Template::getInstance())
		{
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

			$emailPlainFetchedContent = $smarty->fetch(\AppCfg::DIR_BASE . \AppCfg::DIR_TEMPLATES . 'email-student-attempt-plain.tpl');
			$emailHtmlFetchedContent = $smarty->fetch(\AppCfg::DIR_BASE . \AppCfg::DIR_TEMPLATES . 'email-student-attempt-html.tpl');
			if($emailPlainFetchedContent === '' || $emailHtmlFetchedContent === '')
			{
				\rocketD\util\Error::getError(0, "SMARTY email templates not fetchable!");
			}
			else
			{
				$body = "--$boundry" . "\n";
				$body .= "Content-Type: text/plain; charset=UTF-8" . "\n";
				$body .= "Content-Disposition: inline" . "\n";
				$body .= "Content-Transfer-Encoding: 7bit" . "\n\n";
				$body .= $emailPlainFetchedContent;
				$body .= "\r\n\r\n--$boundry" . "\n";
				$body .= "Content-Type: text/html; charset=UTF-8" . "\n";
				$body .= "Content-Disposition: inline" . "\n";
				$body .= "Content-Transfer-Encoding: 7bit" . "\n\n";
				$body .= $emailHtmlFetchedContent;
				$body .= "\n\n--$boundry" . "--\n";

				$subject = $smarty->fetch('eval:Results for {$loTitle} {$loCourse|ternary:"($loCourse)":"no course"}');

				$sent = $this->mail($student->email, $subject, $body, $headers);
			}
		}

		profile('email', "'$studentID','$student->email','$score','" . ($sent ? '1' : '0' ). "'");

		return $sent;
	}
}
