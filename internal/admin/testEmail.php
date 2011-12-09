<?php
	require_once(dirname(__FILE__)."/../../internal/app.php");

	$API = \obo\API::getInstance();
	include_once(\AppCfg::DIR_BASE . \AppCfg::DIR_SCRIPTS . 'smarty/Smarty.class.php');
	
	// get student info
	$AM = \rocketD\auth\AuthManager::getInstance();
	$student = $AM->fetchUserByID(4337);
	$boundry = '-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_';
	
	// load up email template
	$smarty = new \Smarty();

	$smarty->compile_dir = \AppCfg::DIR_BASE . \AppCfg::DIR_TEMPLATES . 'compiled/';
	
	$smarty->assign('multiPartBoundry', $boundry);
	$smarty->assign('loLink',\AppCfg::URL_WEB . \AppCfg::URL_VIEWER . 555);
	$smarty->assign('loTitle', 'Learning Object Title');
	$smarty->assign('loCourse', 'Course');
	$smarty->assign('loInstructor', 'Senior Instructor');
	$smarty->assign('loEnd', time());
	$smarty->assign('loScoreMethod', 'm');
	$smarty->assign('attemptsRemaining', 5);
	
	$smarty->assign('imgDir', \AppCfg::URL_WEB . \AppCfg::DIR_ASSETS . 'images/score-confirmation/');
	$smarty->assign('finalScore', 100);
	$smarty->assign('attempts', array());

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
	\rocketD\util\Log::profile('email', "'$studentID','$user->email','$score','" . ($sent ? '1' : '0' ). "'\n");

?>