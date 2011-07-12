<?php
ob_start();
try
{
	require_once(dirname(__FILE__)."/../../internal/app.php");
	
	//setup
	$los = array(1429, 1428, 1427);
	$targetURL = 'https://obojobo.ucf.edu/sso/portal/redirect.php';
	$NID = $_REQUEST['nid'];
	$timestamp = $_REQUEST['epoch'];
	$hash = $_REQUEST['hash'];
	$scores = array();

	// ************* TESTING CODE ************
	// $targetURL = 'http://obo/sso/portal/redirect.php';
	// $NID = 'funnyfaceman';
	// $timestamp = time();
	// $hash = md5($NID.$timestamp.\AppCfg::UCF_PORTAL_SECRET);

	// valid hash
	if(md5($NID.$timestamp.\AppCfg::UCF_PORTAL_SECRET) === $hash && $timestamp >= time()- \AppCfg::UCF_PORTAL_TIMEOUT /*30 minutes ago*/)
	{
	
		// valid store the session cookie
		session_name(\AppCfg::SESSION_NAME);
		session_start();
		$_SESSION['PORTAL_SSO_NID'] = $NID;
		$_SESSION['PORTAL_SSO_EPOCH'] = $timestamp;
	
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
		$NM->sendCriticalError('Pagelet - invalid hash', ' calculated has: '. md5($NID.$timestamp.\AppCfg::UCF_PORTAL_SECRET) . ' given hash ' . $hash . ' timed out: ' . ($timestamp >= time()- \AppCfg::UCF_PORTAL_TIMEOUT ? 'nope' : 'yes') );
		echo "Session timed out or invalid, refresh the page to update.";
		exit();
	}
	
	// Display html
	?>
	<html>
		<head><head>
		<body bgcolor="#F8F8F8">
		<p>Are you a new student going through orientation?  If so, you're required to complete the Academic Integrity modules listed below.  Click on each of these module links which will open in a new window.  You'll need to score a 80% or better on each module to pass.</p>
		<ul>
		<?php
			foreach($los AS $key => $selectedinstID)
			{
				?>
				<li>
					<?php if($scores[$key]->score){?>
						<p style="font-size: 12pt; margin-bottom: 0;"><?php echo $scores[$key]->name; ?></p>
						<p style="margin-top: 0; font-size: 8pt;"><span style="color: green">Completed</span> with a score of <?php echo $scores[$key]->score; ?>% (<a  href="<?php echo $targetURL;?>?instID=<?php echo $scores[$key]->instID; ?>">Take again</a>)</p>
					<?php } else { ?>
						<p style="font-size: 12pt; margin-bottom: 0;"><a style="font-weight:bold;" href="<?php echo $targetURL;?>?instID=<?php echo $scores[$key]->instID; ?>"><?php echo $scores[$key]->name; ?></a></p>
						<p style="margin-top: 0; font-size: 8pt; color: #990000;">Not yet complete</p>
					<?php } ?>
				</li>
				<?php
			
			}
		?>
		</ul>
	
		<p>You will need to complete these before <strong>August 22nd 2011</strong>.  Otherwise you will receive a hold on your account which will prevent you from registering for classes.</p>
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