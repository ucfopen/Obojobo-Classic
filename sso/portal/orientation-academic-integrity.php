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
		$los = array('1990','1991');
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
	// $los = array();
	
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
			JOIN obo_los AS L
			ON L.loID = I.loID
			LEFT JOIN obo_log_attempts AS A
			ON A.instID = I.instID AND L.aGroupID = A.qGroupID AND A.userID =  '?'
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

			<p>Are you an incoming student?  If so, you're required to complete the Academic Integrity modules listed below.  Click on each of these module links which will open in a new window.  You'll need to score a <?php echo $minScore; ?>% or better on each module to pass.</p>
			<ul>
			<?php

				foreach($los AS $key => $instID)
				{
					if(isset($scores[$key]))
					{
					?>
					<li>
						
						<?php if($scores[$key]->score){
							if((int)($scores[$key]->score) >= (int)$minScore){?>
								<p style="font-size: 12pt; margin-bottom: 0;"><?php echo $scores[$key]->name; ?></p>
								<p style="margin-top: 0; font-size: 8pt;"><span style="color: green">Completed</span> with a score of <?php echo $scores[$key]->score; ?>% (<a  href="<?php echo $targetURL;?>?instID=<?php echo $scores[$key]->instID . $hashAppend; ?>" target="_blank" proxied="false">Take again</a>)</p>
							<?php }else{?>
								<p style="font-size: 12pt; margin-bottom: 0;"><a style="font-weight:bold;" href="<?php echo $targetURL;?>?instID=<?php echo $scores[$key]->instID . $hashAppend; ?>" target="_blank" proxied="false"><?php echo $scores[$key]->name; ?></a></p>
								<p style="margin-top: 0; font-size: 8pt;"><span style="color: #990000">Not yet complete.</span> Your score of <strong style="color: #990000"><?php echo $scores[$key]->score; ?>%</strong> is below the minimum of <?php echo $minScore; ?>%.</p>
							<?php }
						} 
						else { ?>
							<p style="font-size: 12pt; margin-bottom: 0;"><a style="font-weight:bold;" href="<?php echo $targetURL;?>?instID=<?php echo $scores[$key]->instID . $hashAppend; ?>" target="_blank" proxied="false"><?php echo $scores[$key]->name; ?></a></p>
							<p style="margin-top: 0; font-size: 8pt; color: #990000;">Not yet complete</p>
						<?php } ?>
					</li>
					<?php
					}
				}
				?>
				<li>
					<p style="font-size: 12pt; margin-bottom: 0;">Consequences of Academic Misconduct (Coming in August)</p>
					<p style="margin-top: 0; font-size: 8pt; color: #990000;">Not currently available.</p>
				</li>

			</ul>
	
			<p>You will need to complete these before <strong>August 22nd 2011</strong>.  Otherwise you will receive a hold on your account that will prevent you from registering for classes.</p>
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