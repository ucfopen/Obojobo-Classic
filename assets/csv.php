<?php
require_once (dirname( __FILE__ )."/../internal/app.php");
switch($_GET['function'])
{
	// Older scores method (designed for Webcourses)
	// Kept here if we want to provide a 'full data set' export
	// See #131 (https://github.com/ucfcdl/Obojobo/issues/131)
	/*
	case 'scores':
		if ($_GET['instID'] > 0 && strlen($_GET['filename']) > 0)
		{
			$api = \obo\API::getInstance();
			$scores = $api->getScoresForInstance($_GET['instID']);
			if (is_array($scores))
			{
				$UM = \rocketD\auth\AuthManager::getInstance();
				session_write_close();
				header("Pragma: public");
				header("Expires: 0"); // set expiration time
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Content-Type: application/force-download");
				header("Content-Type: application/octet-stream");
				header("Content-Type: application/download");
				header("Content-Disposition: attachment; filename=\"{$_GET['filename']}.csv\"");
				echo "User ID,Last Name,First Name,MI,Score,Date Updated\r\n";
				
				usort($scores, "compareFunction");
				
				foreach ($scores as $user)
				{
					$score = getCountedScore($user['attempts'], $_GET['method']);
					if($score != -1) echo $UM->getUserName($user['userID']).','.$user['user']['last'].','.$user['user']['first'].','.$user['user']['mi'].','.$score['score'].','.date('m/d/Y G:i:s',$score['date'])."\r\n";
				}
				
				exit();
			}
		}
		break;
	 */
	case 'scores':
		if ($_GET['instID'] > 0 && strlen($_GET['filename']) > 0)
		{
			$api = \obo\API::getInstance();
			$scores = $api->getScoresForInstance($_GET['instID']);
			$inst_data = $api->getInstanceData($_GET['instID']);
			$column_name = 'Obojobo: '.$inst_data->name;
			if (is_array($scores))
			{
				$UM = \rocketD\auth\AuthManager::getInstance();
				session_write_close();
				header("Pragma: public");
				header("Expires: 0"); // set expiration time
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Content-Type: application/force-download");
				header("Content-Type: application/octet-stream");
				header("Content-Type: application/download");
				header("Content-Disposition: attachment; filename=\"{$_GET['filename']}.csv\"");
				echo "Student,ID,SIS User ID,SIS Login ID,Section,{$column_name},Date Updated\r\n";
				
				usort($scores, "compareFunction");
				
				foreach ($scores as $user)
				{
					$score = getCountedScore($user['attempts'], $_GET['method']);
					$fullName = $user['user']['last'].', '.$user['user']['first'];
					if($score != -1) echo '"'.$fullName.'","","","'.$UM->getUserName($user['userID']).'","","'.$score['score'].'","'.date('m/d/Y G:i:s',$score['date']).'"'."\r\n";
				}
				
				exit();
			}
		}
		break;
	case 'stats':

			$API = \obo\API::getInstance();
			$stats = $API->getLOStats($_GET['los'], $_GET['stat'], $_GET['start'], $_GET['end'], $_GET['resolution'], false);
			if(is_array($stats) && count($stats) > 0)
			{
				session_write_close();
				header("Pragma: public");
				header("Expires: 0"); // set expiration time
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Content-Type: application/force-download");
				header("Content-Type: application/octet-stream");
				header("Content-Type: application/download");
				header("Content-Disposition: attachment; filename=\"Obojobo_Stat{$_GET['stat']}_".date('m-d-y',$_GET['start'])."_to_".date('m-d-y',$_GET['end']).".csv\"");
				echo '"' . implode('","', array_keys((array)$stats[0])) . '"' . "\r\n";
				foreach($stats AS $row)
				{
					echo '"' . implode('","', (array)$row) . '"' . "\r\n";
				}
			}
			else
			{
				trace('Stats - no returned stats', true);
				trace($stats);
			}
			exit();
		break;
	default:
		break;
}

function compareFunction($a, $b)
{
	$n1 = $a['user']['last'].$a['user']['first'].$a['user']['mi'];
	$n2 = $b['user']['last'].$b['user']['first'].$b['user']['mi'];
	
	return strcmp($n1, $n2);
}

function getCountedScore($scores, $method)
{
	//Filter out unsubmitted scores:
	$attempts = array();
	foreach($scores as $scoreData)
	{
		if($scoreData['submitted']) $attempts[] = $scoreData;
	}
	if(count($attempts) == 0) return -1;
	
	switch($method)
	{
		case 'h': //Highest:
			// return highest score and the date that it was achieved
			$highest = -1; // need to use -1 here to capture a max score of 0 properly
			$date = 0;
			foreach ($attempts as $scoreData)
			{
				$curScore = $scoreData['score'];
				if ($curScore > $highest)
				{
					$highest = $curScore;
					$date = $scoreData['submitDate'];
				}
			}
			return array('score' => $highest, 'date' => $date);
		case 'm': //Mean:
			// return the average score and the latest date.. there is some innacuracy here as the latest score may not have changed the overall average
			$total = 0;
			foreach ($attempts as $scoreData)
			{
				$total += $scoreData['score'];
			}
			return array('score' => $total/count($scores), 'date' => $attempts[count($attempts)-1]['submitDate']);
		case 'r': //Recent:
			// return the last score and date
			return array('score' => $attempts[count($attempts)-1]['score'], 'date' => $attempts[count($attempts)-1]['submitDate']);  
	}
	exit();
}

header('HTTP/1.0 404 Not Found');

?>