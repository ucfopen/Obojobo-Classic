<?php
ob_start();
try
{
	require_once(dirname(__FILE__)."/../../internal/app.php");
	
	//setup
	
	$targetURL = 'https://obojobo.ucf.edu/sso/portal/redirect.php';
	$NID = $_REQUEST['nid'];
	$timestamp = $_REQUEST['epoch'];
	$hash = $_REQUEST['hash'];
	$scores = array();
	$minScore = 80;
	$isDevEnvironment = false;

	// Testing for ian's user
	// if($_SERVER['HTTP_REMOTE_USER'] == 'i0396856')
	// {
	// }

	$isDevEnvironment = preg_match('/^https:\/\/(patest|padev)\d*?\.net\.ucf\.edu\/psp\/PA(TEST|DEV)/', $_SERVER['HTTP_REFERER']);
	// show a default set of learning objects in a dev environment
	if($isDevEnvironment)
	{
		$los = array('2329','2329','2330','2330','2328','2328');
	}
	else
	{
		$los = explode(',', \AppCfg::UCF_PORTAL_ORIENTATION_INSTANCES);
	}

	// ************* TESTING CODE ************
	// $targetURL = 'http://obo/sso/portal/redirect.php';
	// $NID = 'rumplefaceman';
	// $timestamp = time();
	// $hash = md5($NID.$timestamp.\AppCfg::UCF_PORTAL_SECRET);
	// $los = explode(',', \AppCfg::UCF_PORTAL_ORIENTATION_INSTANCES);
	
	
	// valid hash
	if(md5($NID.$timestamp.\AppCfg::UCF_PORTAL_SECRET) === $hash && (int)$timestamp >= time() - \AppCfg::UCF_PORTAL_TIMEOUT /*30 minutes ago*/)
	{
	
		// build the url to add to the links below, this info must be copied to the redirect page
		// The user grabbing this page is actually the portal, the session created here is not the user
		$hashAppend = "&nid=$NID&epoch=$timestamp&hash=$hash";
	
		// look for the user
		$AM  = \rocketD\auth\AuthManager::getInstance();
		$user = $AM->fetchUserByUserName($NID);
		// check to see if the user completed any of the learning objects listed
		$DBM = \rocketD\db\DBManager::getConnection(new \rocketD\db\DBConnectData(\AppCfg::DB_HOST, \AppCfg::DB_USER, \AppCfg::DB_PASS, \AppCfg::DB_NAME, \AppCfg::DB_TYPE));
		$qstr = "SELECT I.instID, MAX( A.score ) AS score, I.name
			FROM obo_lo_instances AS I
			LEFT JOIN obo_log_attempts AS A
			ON A.instID = I.instID AND A.endTime > 0 AND A.userID =  '?'
			WHERE I.instID
			IN ( ? ) 
			GROUP BY I.instID";
		
		$q = $DBM->querySafe($qstr, $user->userID, implode(',', $los));
		// loop through max scores of selected instances
		while($r = $DBM->fetch_obj($q))
		{
			// loop through the selected instances
			foreach($los AS $key => $selectedinstID)
			{
				if($selectedinstID == $r->instID)
				{
					// copy the score the the parrallel array for scores
					$scores[$key] = $r;
					break;
				}
			}
		}
	}
	// invalid hash
	else
	{
		$NM = \obo\util\NotificationManager::getInstance();
		$NM->sendCriticalError('Pagelet - invalid hash', ' calculated hash: '. md5($NID.$timestamp.\AppCfg::UCF_PORTAL_SECRET) . ' given hash ' . $hash . ' timed out: ' . ($timestamp >= time()- \AppCfg::UCF_PORTAL_TIMEOUT ? 'nope' : 'yes') );
		echo "Session timed out or invalid, refresh the page to update.";
		exit();
	}
	
	function formatDisplayForInstance($instID, $instName, $score, $minScore, $targetURL, $hash)
	{
		$output = '';
		if($instID && $instName)
		{
			// No Score
			if($score === NULL)
			{
				$output = '<p style="font-size: 12pt; margin-bottom: 0;"><a style="font-weight:bold;" href="'.$targetURL.'?instID='.$instID.$hash.'" target="_blank" proxied="false">'.$instName.'</a></p>';
				$output .= '<p style="margin-top: 0; font-size: 8pt; color: #990000;">Not yet complete</p>';
			}
			// Completed & Score is above min
			else if( (int)$score >= (int)$minScore)
			{
				$output = '<p style="font-size: 12pt; margin-bottom: 0;">'.$instName.'</p>';
				$output .= '<p style="margin-top: 0; font-size: 8pt;"><span style="color: green">Completed</span> with a score of '.$score.'% (<a  href="'.$targetURL.'?instID='.$instID.$hash.'" target="_blank" proxied="false">Take again</a>)</p>';
			}
			// Completed BUT score is below min
			else
			{
				$output = '<p style="font-size: 12pt; margin-bottom: 0;"><a style="font-weight:bold;" href="'.$targetURL.'?instID='.$instID.$hash.'" target="_blank" proxied="false">'.$instName.'</a></p>';
				$output .= '<p style="margin-top: 0; font-size: 8pt;"><span style="color: #990000">Not yet complete.</span> Your score of <strong style="color: #990000">'.$score.'%</strong> is below the minimum of '.$minScore.'%.</p>';
			}
		}
		return $output;
	}
	
	// Display html
	?>
	<html>
		<head><head>
		<body bgcolor="#F8F8F8">
		<?php
		// we found at least one instances
		if(count($scores) > 0)
		{
			?>
			<p>Are you an Incoming student? If so, you're required to complete the Academic Integrity Modules listed below. The links below are different for first time college students, transfer students, and graduate students.</p>
			<p>Click on each of the links that apply to you and a new window will open with the Academic Integrity module in it. You will need to score <?php echo $minScore; ?>% or better on each module to pass.</p>
			<?php
				if($scores[0] || $scores[1])
				{?>
					<h3>Transfer Students</h3>
						<ul>
						<?php
							echo formatDisplayForInstance($los[0], $scores[0]->name, $scores[0]->score, $minScore, $targetURL, $hashAppend);
							echo formatDisplayForInstance($los[1], $scores[1]->name, $scores[1]->score, $minScore, $targetURL, $hashAppend);
						?>
						</ul>
				<?}
				if($scores[2] || $scores[3])
				{?>
					<h3>First-time College Students</h3>
						<ul>
						<?php
							echo formatDisplayForInstance($los[2], $scores[2]->name, $scores[2]->score, $minScore, $targetURL, $hashAppend);
							echo formatDisplayForInstance($los[3], $scores[3]->name, $scores[3]->score, $minScore, $targetURL, $hashAppend);
						?>
						</ul>
				<?}
				if($scores[4] || $scores[5])
				{?>
					<h3>Graduate Students</h3>
						<ul>
						<?php
							echo formatDisplayForInstance($los[4], $scores[4]->name, $scores[4]->score, $minScore, $targetURL, $hashAppend);
							echo formatDisplayForInstance($los[5], $scores[5]->name, $scores[5]->score, $minScore, $targetURL, $hashAppend);
						?>
						</ul>
				<?}
				?>

			<p>You will need to complete these before <strong>January 9, 2012</strong>.  Otherwise you will receive a hold on your account that will prevent you from registering for classes.</p>
			<?php
			if($isDevEnvironment)
			{
				?>
				<hr>
				<h3>Displaying Temporary Test Links</h3>
				<p>The above learning objects are only for testing single sign on in the Dev and Test Portals.</p>
				<p>They were randomly selected from the public Information Literacy Modules.</p>
				<p>Note that the links <strong>DO</strong> go to the production server.</p>
				<?php
			}
		}
		// Oh NOs, we couldn't retrieve the learning object instances
		else
		{
			?>
			<p>The Academic Integrity modules are not available at this time, please try again later.</p>
			<?php
		}
		?>
		</body>
	</html>
	<?php
}
catch (Exception $e)
{
	require_once(dirname(__FILE__)."/../../internal/app.php");
	$NM = \obo\util\NotificationManager::getInstance();
	$NM->sendCriticalError('Pagelet Error', print_r($e, true) . print_r($_REQUEST, true),  true);
	echo "Session timed out or invalid, refresh the page to update.";
	exit();
}
ob_end_flush();
?>