<?php
function formatDisplayForInstance($user, $instID, $minScore, $targetURL, $hash)
{
	
	$im = \obo\lo\InstanceManager::getInstance();
	$instData = $im->getInstanceData($instID);
	
	// if we found the instance 
	if($instData instanceof \obo\lo\InstanceData)
	{
		
		return '<li><p style="font-size: 12pt; margin-bottom: 0;"><a style="font-weight:bold;" href="'.$targetURL.'?instID='.$instID.$hash.'" target="_blank" 
proxied="false">'.$instData->name.'</a></p></li>';
	}
	else
	{
		return false;
	}
	
}


ob_start();
try
{
	require_once(dirname(__FILE__)."/../../internal/app.php");
	
	//setup
	
	$targetURL = 'https://obojobo.ucf.edu/sso/portal/redirect.php';
	$nid = $_REQUEST['nid'];
	$timestamp = $_REQUEST['epoch'];
	$hash = $_REQUEST['hash'];
	$scores = array();
	$minScore = 80;
	$isDevEnvironment = false;

	// figure out if this is being accessed via the test or dev portals
	$isDevEnvironment = preg_match('/^https:\/\/(patest|padev)\d*?\.net\.ucf\.edu\/psp\/PA(TEST|DEV)/', $_SERVER['HTTP_REFERER']);
	if($isDevEnvironment)
	{
		$los = array('2329','2329','2330','2330');
	}
	else
	{
		$los = explode(',', \AppCfg::UCF_PORTAL_ORIENTATION_INSTANCES);
	}

	// ************* TESTING CODE ************
	if(\AppCfg::ENVIRONMENT == \AppCfgDefault::ENV_DEV)
	{
		$targetURL = 'http://obo/sso/portal/redirect.php';
		$nid = 'iturgeon';
		$timestamp = time();
		$hash = md5($nid.$timestamp.\AppCfg::UCF_PORTAL_SECRET);
		$los = explode(',', \AppCfg::UCF_PORTAL_ORIENTATION_INSTANCES);
	}
	
	// valid hash
	if(md5($nid.$timestamp.\AppCfg::UCF_PORTAL_SECRET) === $hash && (int)$timestamp >= time() - \AppCfg::UCF_PORTAL_TIMEOUT /*30 minutes ago*/)
	{
	
		// build the url to add to the links below, this info must be copied to the redirect page
		// The user grabbing this page is actually the portal, the session created here is not the user
		$hashAppend = "&nid=$nid&epoch=$timestamp&hash=$hash";
	
		// look for the user
		$AM  = \rocketD\auth\AuthManager::getInstance();
		$user = $AM->fetchUserByUserName($nid);
		
	}
	// invalid hash
	else
	{
		if(\AppCfg::ENVIRONMENT == \AppCfgDefault::ENV_PROD)
		{
			$nm = \obo\util\NotificationManager::getInstance();
			$nm->sendCriticalError('Pagelet - invalid hash', ' calculated hash: '. md5($nid.$timestamp.\AppCfg::UCF_PORTAL_SECRET) . ' given hash ' . $hash . ' timed out: ' . ($timestamp >= time()- \AppCfg::UCF_PORTAL_TIMEOUT ? 'nope' : 'yes') );
		}
		echo "Session timed out or invalid, refresh the page to update.";
		exit();
	}
	
	$output = array();
	$validCount = 0;
	foreach($los AS $instID)
	{
		$output[] = $tmpOutput = formatDisplayForInstance($user, $instID, $minScore, $targetURL, $hashAppend);
		if($tmpOutput != false) $validCount++;
	}
		
	// Display html
	?>
	<html>
		<head></head>
		<body bgcolor="#F8F8F8">
		<?php
		// we found at least one instances
		if($validCount > 0)
		{
			?>

			<h2>Are you an incoming student?</h2>
                        <p>If you are a New Undergraduate Student or a New Master's Program Student <strong>admitted in Summer 2013</strong> or later, you are required to complete all the Academic Integrity Modules listed below in your group.</p>
                        <p>You need to score <strong><?php echo $minScore; ?>% or higher on all required modules before August 30, 2013</strong>. Otherwise you will receive a hold that prevents you from registering for classes.</p>

			<h3>New Undergraduate Students</h3>
				<ul>
				<?php
					echo $output[0];
					echo $output[1];
					echo $output[2];
				?>
				</ul>
			<h3>New Master's Program Students</h3>
				<ul>
				<?php
					echo $output[3];
				?>
				</ul>
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
	$nm = \obo\util\NotificationManager::getInstance();
	$nm->sendCriticalError('Pagelet Error', print_r($e, true) . print_r($_REQUEST, true),  true);
	echo "Session timed out or invalid, refresh the page to update.";
	exit();
}
ob_end_flush();


?>
