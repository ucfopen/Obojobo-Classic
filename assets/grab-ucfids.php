<?php

ini_set('display_errors', 1);



require_once(dirname(__FILE__)."/../internal/app.php");
require_once (dirname( __FILE__ )."/../internal/classes/rocketD/db/DBEnabled.class.php");
require_once (dirname( __FILE__ )."/../internal/classes/rocketD/db/DBConnectData.class.php");

session_name(\AppCfg::SESSION_NAME);
session_start();

$cerebroConn = new \rocketD\db\DBConnectData(\AppCfg::UCF_DB_HOST, \AppCfg::UCF_DB_USER, \AppCfg::UCF_DB_PASS, \AppCfg::UCF_DB_NAME, \AppCfg::UCF_DB_TYPE);
$cerebroDB = \rocketD\db\DBManager::getConnection($cerebroConn);


$api = \obo\API::getInstance();

$am = \rocketD\auth\AuthManager::getInstance();
$roleMan = \obo\perms\RoleManager::getInstance();

if(!$roleMan->isSuperUser())
{
	header("HTTP/1.0 404 Not Found");
	die();
}


function getUcfID($username)
{
	global $cerebroDB;

	$userTable = \cfg_plugin_AuthModUCF::TABLE_PEOPLE;
	$userId    = \cfg_plugin_AuthModUCF::NID;
	$ucfID     = \cfg_plugin_AuthModUCF::PPS_NUMBER;

	$q = $cerebroDB->querySafe("SELECT * FROM {$userTable} WHERE {$userId} = '?' ", $username);

	if ( !($result = $cerebroDB->fetch_array($q)))
	{
		echo "Unable to find UCF ID for '{$username}'\n";
		return false;
	}

	return $result[$ucfID];
}

function update($user, $ucfID)
{
	global $am;
	global $apply;

	$authMod = $am->getAuthModuleForUserID($user->userID);
	if(!$authMod)
	{
		echo "Unable to determine auth mod for '{$user->login}'\n";
		return false;
	}

	if(!($authMod instanceof plg_UCFAuth_UCFAuthModule))
	{
		echo "User has unexpected auth mod: '{$authMod}'\n";
		return false;
	}

	if(!$apply)
	{
		return true;
	}

	//$user->updateUser($userID, $userName, $fName, $lName, $mName, $email, $optionalVars=0)
	$result = $authMod->updateUser($user->userID, $user->login, $user->first, $user->last, $user->mi, $user->email, ['ucfID' => $ucfID]);
	if(!$result || !isset($result['success']) || !$result['success'])
	{
		echo "Unable to update '{$user->login}'!\n";
		return false;
	}

	return true;
}







$users = $am->getAllUsers();

echo '<pre>';
//print_r($users);

$apply = false;
if(isset($_GET['apply']) && $_GET['apply'] === '1')
{
	$apply = true;
}

$totalCount = 0;
$foundCount = 0;
$missedCount = 0;
$updatedCount = 0;
$failedCount = 0;

foreach($users as $user)
{
	$login = $user->login;
	$ucfID = getUcfID($login);
	if($ucfID)
	{
		$foundCount++;
		$result = update($user, $ucfID);
		if($result)
		{
			$updatedCount++;
		}
		else
		{
			$failedCount++;
		}
	}
	else
	{
		$missedCount++;
	}

	$totalCount++;
}

echo "\n\n";
echo "Total: {$totalCount}\n";
echo "Found: {$foundCount}/{$totalCount}\n";
echo "Missed: {$missedCount}/{$totalCount}\n";
echo "Updated: {$updatedCount}/{$foundCount}\n";
echo "Failed: {$failedCount}/{$foundCount}\n";

if(!$apply)
{
	echo "\n\nNOTHING DONE - RUN WITH ?apply=1 TO UPDATE USERS!";
}

?>