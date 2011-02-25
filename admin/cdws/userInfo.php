<?php
require_once(dirname(__FILE__)."/../../internal/app.php");

$API = \obo\API::getInstance();
$result = $API->getSessionRoleValid(array('SuperUser'));
if(! in_array('SuperUser', $result['hasRoles']) )
{
	exit();
}

$DBM = \rocketD\db\DBManager::getConnection(new \rocketD\db\dbConnectData\AppCfg::DB_HOST,\AppCfg::DB_USER,\AppCfg::DB_PASS,\AppCfg::DB_NAME,\AppCfg::DB_TYPE));
//if(!$DBM->db_select('los_backup')) exit('unable to connect to backup database');

$nid = $_GET['nid'];
$uid = 0;
$email = '';
$result = $DBM->query("SELECT userID FROM `obo_user_auth_ucf` WHERE login = '$nid'");
$r = $DBM->fetch_assoc($result);
$uid = $r['userID'];

echo '<form action="" method="get" accept-charset="utf-8">
	<label for="nid">NID</label><input type="text" name="nid" value="" id="nid">
	<p><input type="submit" value="Continue &rarr;"></p>
</form>';

if(is_numeric($uid) && $uid > 0)
{
	echo '<h1>Data for user '.$uid.':</h1>';
	
	$UM = \rocketD\auth\AuthManager::getInstance();
	$result = $UM->fetchUserByID($uid);
	$lastLogin = $result->lastLogin;
	
	echo '<h2>User Info:</h2><hr>';
	echo '<h3>'.$result->first.' '.$result->last.' - '.$nid.'</h3>';
	$email = $result->email;
	
	writeResult('User is in los database', is_numeric($result->userID));
	writeData($result);
	writeHelp('If the user is in the database that means they attempted to login and put in a valid NID. It doesn\'t mean they logged in successfully.');
	
	writeResult('User has email', isRealStr($email));
	writeHelp('User must have email.');
	
	if(isRealStr($email))
	{
		$q = 'SELECT * FROM obo_users WHERE email = "'.$email.'"';
		$result = $DBM->query($q);
		$results = array();
		while($r = $DBM->fetch_assoc($result))
		{
			$results[] = $r;
		}
		writeResult('Another Obojobo user doesn\'t share the same email address', sizeof($results) == 1);
		if(sizeof($results) != 1)
		{
			echo '<br>Here are the users with that email:';
			writeData($results);
		}
		writeHelp('No user should have the same email.  If they do, this is a problem that needs to be resolved by New Media.');
	}
	
	writeResult('User has lastLogin', is_numeric($lastLogin) && $lastLogin > 0);
	if(is_numeric($lastLogin) && $lastLogin > 0)
	{
		writeData(date('m/d/y @ h:i:s A', $lastLogin));
	}
	else
	{
		writeData(0);
	}
	writeHelp('If this is 0 it means they have never logged in.');
	echo '<br><b> - Roles:</b>';
	$roles = $API->getUserRoles($uid);
	writeData($roles);
	writeHelp('If the user is Faculty/Staff and they are attempting to use the Repository they should have the "LibraryUser" and/or "ContentCreator" roles listed above.  "LibraryUser" provides Basic access to the Repository. "ContentCreator" provides Pro access to the Repository.  Students most likely won\'t have any roles');
	
	$DBM = \rocketD\db\DBManager::getConnection(new \rocketD\db\dbConnectData\AppCfg::DB_MODX_HOST,\AppCfg::DB_MODX_USER,\AppCfg::DB_MODX_PASS,\AppCfg::DB_MODX_NAME,\AppCfg::DB_MODX_TYPE));
	
	//if(!$DBM->db_select('los_modx'))
	//{
	//	exit('Cannot connect to modx database!');
//	}
	
	$q = 'SELECT * FROM modx_web_users WHERE username = "'.$result->login.'"';
	$result = $DBM->query($q);
	$found = false;
	$modxAttrib;
	if($r = $DBM->fetch_assoc($result))
	{
		$found = true;
		$modxID = $r['id'];
		
		$q = 'SELECT * FROM modx_web_user_attributes WHERE internalKey = '.$modxID;
		$result = $DBM->query($q);
		$modxAttrib = $DBM->fetch_assoc($result);
	}
	writeResult('User is in modx database', $found);
	//Mask out pw
	if($r['password'])
	{
		$r['password'] = '(View in DB)';
	}
	writeData($r);
	writeData($modxAttrib);
	writeHelp('User most likely should be in the ModX database.  If not, then either ModX had a problem and couldn\'t create them or ModX was not involved for some reason.');
	
	writeResult('User has e-mail in modx', isRealStr($modxAttrib['email']));
	writeHelp('This is not required.');
	
	writeResult('E-mail in Obojobo and ModX match', $modxAttrib['email'] == $email);
	writeHelp('These should match although it is not required.');
	
	if(isRealStr($modxAttrib['email']))
	{
		$results = array();
		$q = 'SELECT * FROM modx_web_user_attributes WHERE email = "'.$modxAttrib['email'].'"';
		$result = $DBM->query($q);
		while($r = $DBM->fetch_assoc($result))
		{
			$results[] = $r;
		}
		
		writeResult('Another ModX user doesn\'t share the same email address', sizeof($results) == 1);
		if(sizeof($results) != 1)
		{
			echo '<br>Here are all the users with this email address:';
			writeData($results);
		}
		writeHelp('No other user in ModX should be sharing an email address with this user.  If so, there is a problem that must be resolved by New Media.');
	}
	
	
	writeResult('User is not blocked in modx', $modxAttrib['blocked'] == 0);
	writeHelp('If the user is blocked that means they incorrectly logged in a significant number of times.  They won\'t be able to use the system until they are unblocked by New Media.');
	
	echo '<br> - <b>ModX Failed login count:</b> '.$modxAttrib['failedlogincount'];
	writeHelp('This number represents the number of times a user failed logging into the website login (not the Repository or Viewer login, which is separate).  If this number is high it may mean that there is a problem with the website login.  You may consider asking the user to login directly in the Repository or Viewer.');
	
	echo '<h2>Logins:</h2><hr>';
	printLogs($nid);
	writeHelp('Log data is collected from the profile logs available on the server.  The first block of data shows which log files are available (which gives you a range of days if you look at the file name). Next, the list shows a NO for each time LDAP failed to bind and a YES for each successful LDAP bind.  A YES <i>should</i> mean a login success, however it is possible that a bug in the system could deny login even after a successful LDAP bind!');
	
	echo '<h2>Other resources:</h2><hr>';
	echo '<ul>';
	echo '<li><a href="'.'tools/showInteractionLog.php?uid='.$uid.'">View users interaction log</a></li>';
	echo '</ul>';
	writeHelp('This displays all logs for the user.  This can be useful to determine if the user has used the system at all.  If you see logs you can infer that the user has logged in once at one time.');
}

function isRealStr($s)
{
	return is_string($s) && strlen($s) > 0;
}

function writeResult($msg, $pass)
{
	if($pass === true)
	{
		echo '<br><b> - '.$msg.': <font color="green">PASS</font></b><br>';
		//echo 'This means that the user entered their NID into Obojobo and Obojobo created a record for them.  It doesnt imply they logged in succesfully.';
	}
	else
	{
		echo '<br><b> - '.$msg.': <font color="red">FAIL</font></b><br>';
	}
}

function writeData($data)
{
	echo '<br><pre>';
	if($data)
	{
		print_r($data);
	}
	else
	{
		echo '[No data]';
	}
	echo '</pre>';
}

function writeHelp($s)
{
	echo '<p style="width: 500px; font-size: small; background-color:#dddddd; padding: 10px">'.$s.'</p>';
}

function printLogs($nid)
{
	$dir = '../../internal/logs';
	if ($handle = opendir($dir)) {
	    echo "Files:";
	
		$logs = array();

	    /* This is the correct way to loop over the directory. */
	    while (false !== ($file = readdir($handle))) {
			if(strpos($file, 'profile_login') !== false)
			{
	        	//echo "$file\n";
				$logs[] = $file;
			}
	    }

	    closedir($handle);
		
		sort($logs);
		
		writeData($logs);
	}
	
	$numSuccess = 0;
	$total = 0;
	echo '<div style="border: thin solid black; padding: 10px; width: 450px; height: 250px; overflow: auto; font-family:courier new; font-size:10pt">';
	foreach($logs as $log)
	{
		$file = fopen($dir.'/'.$log, 'r') or exit("Can't read log");
		while(!feof($file))
		{
			//echo fgets($file)."\n";
			$a = explode(',', str_replace("'", "", fgets($file)));
			//print_r($a);
			if($a[0] == $nid)
			{
				$total++;
				if($a[4] == 1)
				{
					$numSuccess++;
				}
			
				echo '<br>';
				echo ($a[4] == 1 ? '<b><font color="green">YES</font></b>' : '<b><font color="red">NO </font></b>').': '.date('m/d/y @ h:i:s A', $a[3]).' (Exec time: '.$a[2].')';
			}
		}
	}
	echo '</div>';
	echo '<br>';
	echo '<br><b>Total:</b> '.$total;
	echo '<br><b>Failed:</b> '.($total - $numSuccess);
	echo '<br><b>Success:</b> '.$numSuccess;
	fclose($file);
}

?>