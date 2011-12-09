<?php

function duckPack_loadAPI()
{
	global $modx;
	require_once($modx->config['base_path'].'internal/app.php');
}

function duckPack_externalUserIsLocalManager($username)
{
	global $modx;
	// check for duplicate user name
	if($username=="") {
		return false;
	}
	else {
		$sql = "SELECT id FROM ".$modx->getFullTableName("manager_users")." WHERE username='$username'";
		if(!$rs = $modx->db->query($sql)){
			return false;
		} 
		$limit = $modx->db->getRecordCount($rs);
		if($limit>0) {
			return true;
		}
	}
	
	return false;
}

function duckPack_externalUserValidForLocal($username, $email, $password, $isManager)
{
	global $modx;
	// check for duplicate user name
	if($username=="") {
		return false;
	}
	else {
		$sql = "SELECT id FROM ".$modx->getFullTableName( ($isManager ? 'manager_users' : 'web_users'))." WHERE username='$username'";
		if(!$rs = $modx->db->query($sql)){
			return false;
		} 
		$limit = $modx->db->getRecordCount($rs);
		if($limit > 0) {
			return false;
		}		 
	}

	// verify email

	return true;
}

function duckPack_createLocalManager($username, $password, $fullname, $email)
{
	global $modx;
	
	if(!duckPack_externalUserValidForLocal($username, $email, $password, true)) return false;
	
	// create the user account
	$sql = "INSERT INTO ".$modx->getFullTableName("manager_users")." (username, password) 
			VALUES('".$username."', md5('".$password."'));";
	$rs = $modx->db->query($sql);
	if(!$rs){
		return false;
	}		  
	// now get the id
	$key = $modx->db->getInsertId();

	// save user attributes
	$sql = "INSERT INTO ".$modx->getFullTableName("user_attributes")." (internalKey, fullname, role, email, comment) 
			VALUES($key, '$fullname', '1', '$email','generated from external user');";
	$rs = $modx->db->query($sql);
	if(!$rs){
		return false;
	}

	// invoke OnWebSaveUser event
	$modx->invokeEvent("OnWebSaveUser",
						array(
							"mode"		   => "new",
							"userid"	   => $key,
							"username"	   => $username,
							"userpassword" => $password,
							"useremail"	   => $email,
							"userfullname" => $fullname
						));
	return true;
}

function duckPack_updateLocalManager($username, $password, $fullname, $email)
{
	global $modx;
	
	$sql = "SELECT id FROM ". $modx->getFullTableName('manager_users'). " WHERE username='$username'";
	if(!$rs = $modx->db->query($sql))
	{
		return false;
	}
	$row = $modx->fetchRow($ds);
	
	$sql = "UPDATE ".$modx->getFullTableName("user_attributes")." SET fullname='".$modx->db->escape($fullname)."', email='".$modx->db->escape($email)."' WHERE internalKey='".$row['id']."'";
	if($modx->db->query($sql))
	{
		return true;
	}
	
	return false;
}

function duckPack_externalUserIsLocalWebUser($username)
{
	global $modx;
	// check for duplicate user name
	if($username=="") {
		return false;
	}
	else
	{
		$sql = "SELECT id FROM ".$modx->getFullTableName("web_users")." WHERE username='$username'";
		if(!$rs = $modx->db->query($sql))
		{
			return false;
		} 
		$limit = $modx->db->getRecordCount($rs);
		if($limit>0)
		{
			return true;
		}		 
	}
	
	return false;		 
}

function duckPack_createLocalWebUser($username, $password, $fullname, $email)
{
	global $modx;
	if(!duckPack_externalUserValidForLocal($username, $email, $password, false)) return false;
	// create the user account
	$sql = "INSERT INTO ".$modx->getFullTableName("web_users")." (username, password) 
			VALUES('".$username."', md5('".$password."'));";
	$rs = $modx->db->query($sql);
	if(!$rs)
	{
		return false;
	}		  
	// now get the id
	$key = $modx->db->getInsertId();
	duckPack_syncGroups($key);
	// save user attributes
	$sql = "INSERT INTO ".$modx->getFullTableName("web_user_attributes")." (internalKey, fullname, role, email, comment) 
			VALUES($key, '$fullname', '1', '$email', 'generated from external user');";
	$rs = $modx->db->query($sql);
	if(!$rs)
	{
		return false;
	}
	// invoke OnWebSaveUser event
	$modx->invokeEvent("OnWebSaveUser",
						array(
							"mode"		   => "new",
							"userid"	   => $key,
							"username"	   => $username,
							"userpassword" => $password,
							"useremail"	   => $email,
							"userfullname" => $fullname
						));
	return true;
}

function duckPack_syncGroups($modxUserID)
{
	global $modx;
	$oboAPI = \obo\API::getInstance();
	$roles = $oboAPI->getUserRoles();
	$groups = array();
	foreach($roles as $role)
	{
		$groups[] = $role->name;
	}
			
	// get array of the external user's groups that match modx user groups
	if(count($groups)>0)
	{
		// get the local roles that match the external by name
		$ds = $modx->dbQuery("SELECT id FROM ".$modx->getFullTableName("webgroup_names")." WHERE name IN ('".implode("','",$groups)."')");
		$externalMatchingGroups = array();
		if($ds)
		{
			while ($row = $modx->fetchRow($ds))
			{
				$externalMatchingGroups[] = $row['id'];
			}
		}
	}
			
	// get user's current modx groups
	$ds = $modx->dbQuery("SELECT * FROM ".$modx->getFullTableName("web_groups")." WHERE webuser='$modxUserID'");
	$userLocalGroups = array();
	if($ds)
	{
		while ($row = $modx->fetchRow($ds))
		{
			$userLocalGroups[] = $row['webgroup'];
		}
	}		 
	
	// find local groups to remove
	if(count($userLocalGroups) > 0)
	{
		foreach($userLocalGroups as $curGroup)
		{
			if(!in_array($curGroup, $externalMatchingGroups)) // local groups that aren't in the external groups list 
			{
				// remove the record for this group
				$sql = "DELETE FROM ".$modx->getFullTableName('web_groups')." WHERE webgroup='$curGroup' AND webuser='$modxUserID'";
				$rs = $modx->db->query($sql);
			}
		}
	}
	
	// find external groups to add
	if(count($externalMatchingGroups) > 0)
	{
		foreach($externalMatchingGroups as $curGroup)
		{
			if(!in_array($curGroup, $userLocalGroups)) // local groups that aren't in the external groups list 
			{
				// remove the record for this group
				$sql = "INSERT INTO ".$modx->getFullTableName('web_groups')." SET webgroup='".$curGroup."', webuser='".$modxUserID."'";
				$rs = $modx->db->query($sql);
			}
		}
	}		 
}


function duckPack_updateLocalWebUser($username, $password, $fullname, $email)
{
	global $modx;
	// check for valid user name
	$sql = "SELECT id FROM ".$modx->getFullTableName("web_users")." WHERE username='$username'";
	if(!$rs = $modx->db->query($sql))
	{
		return false;
	}
	$row = $modx->fetchRow($rs);
	duckPack_syncGroups($row['id']);
	
	$sql = "UPDATE ".$modx->getFullTableName("web_user_attributes")." SET fullname='".$modx->db->escape($fullname)."', email='".$modx->db->escape($email)."' WHERE internalKey='".$row['id']."'";
	
	if($modx->db->query($sql))
	{
		return true;
	}		
	return false;		 
}

function duckPack_logInExternalUserAsWebUser($username)
{
	global $modx;

	$table_webUsers = $modx->getFullTableName("web_users");
	$table_webUserAttrib = $modx->getFullTableName("web_user_attributes");
	$table_webUserSetting = $modx->getFullTableName("web_user_settings");
	$table_activeUsers = $modx->getFullTableName("active_users");

	$sql = "SELECT $table_webUsers.*, $table_webUserAttrib.* FROM $table_webUsers, $table_webUserAttrib WHERE BINARY $table_webUsers.username = '".$username."' and $table_webUserAttrib.internalKey=$table_webUsers.id;";
	$ds = $modx->db->query($sql);
	$limit = $modx->db->getRecordCount($ds);

	if($limit==0 || $limit>1)
	{
		return;
	}
	$row = $modx->db->getRow($ds);

	$internalKey			 = $row['internalKey'];
	$dbasePassword			   = $row['password'];
	$failedlogins			  = $row['failedlogincount'];
	$blocked				 = $row['blocked'];
	$blockeduntildate		 = $row['blockeduntil'];
	$blockedafterdate		 = $row['blockedafter'];
	$registeredsessionid	= $row['sessionid'];
	$role					 = $row['role'];
	$lastlogin				  = $row['lastlogin'];
	$nrlogins				 = $row['logincount'];
	$fullname				 = $row['fullname'];
	$email					   = $row['email'];

	// load user settings
	if($internalKey)
	{
		$result = $modx->db->query("SELECT setting_name, setting_value FROM $table_webUserSetting WHERE webuser='$internalKey'");
		while ($row = $modx->fetchRow($result, 'both')) $modx->config[$row[0]] = $row[1];
	}

	if(!isset($_SESSION['webValidated']))
	{
		$sql = "update $table_webUserAttrib SET failedlogincount=0, logincount=logincount+1, lastlogin=thislogin, thislogin=".time().", sessionid='$currentsessionid' where internalKey=$internalKey";
		$ds = $modx->db->query($sql);
	}

	$_SESSION['webShortname']=$username;
	$_SESSION['webFullname']=$fullname;
	$_SESSION['webEmail']=$email;
	$_SESSION['webValidated']=1;
	$_SESSION['webInternalKey']=$internalKey;
	$_SESSION['webValid']=base64_encode($givenPassword);
	$_SESSION['webUser']=base64_encode($username);
	$_SESSION['webFailedlogins']=$failedlogins;
	$_SESSION['webLastlogin']=$lastlogin;
	$_SESSION['webnrlogins']=$nrlogins;
	$_SESSION['webUserGroupNames'] = ''; // reset user group names

	// get user's document groups
	$dg='';
	$i=0;
	$table_webGroups = $modx->getFullTableName("web_groups");
	$table_webGroupA = $modx->getFullTableName("webgroup_access");
	$sql = "SELECT uga.documentgroup
			FROM $table_webGroups ug
			INNER JOIN $table_webGroupA uga ON uga.webgroup=ug.webgroup
			WHERE ug.webuser =".$internalKey;
	$ds = $modx->db->query($sql);
	while ($row = $modx->db->getRow($ds,'num')) $dg[$i++]=$row[0];
	$_SESSION['webDocgroups'] = $dg;

	include_once($modx->config['base_path'] . "manager/includes/log.class.inc.php");
	$log = new logHandler;
	$log->initAndWriteLog("Logged in", $_SESSION['webInternalKey'], $_SESSION['webShortname'], "58", "-", "WebLogin");			  

	// web users are stored with negative id
	$sql = "REPLACE INTO $table_activeUsers (internalKey, username, lasthit, action, id, ip) values(-".$_SESSION['webInternalKey'].", '".$_SESSION['webShortname']."', '".time()."', 'ObojoboLogin', '', '".$_SERVER['REMOTE_ADDR']."')";
	if(!$ds = $modx->db->query($sql))
	{
		$output = "error replacing into active users! SQL: ".$sql;
		return;
	}		 

}

function duckPack_logInExternalUserAsManager($username)
{
	global $modx;

	$table_manUsers = $modx->getFullTableName("manager_users");
	$table_userAttrib = $modx->getFullTableName("user_attributes");
	$table_userSetting = $modx->getFullTableName("user_settings");
	$table_activeUsers = $modx->getFullTableName("active_users");

	$sql = "SELECT $table_manUsers.*, $table_userAttrib.* FROM $table_manUsers, $table_userAttrib WHERE BINARY $table_manUsers.username = '".$username."' and $table_userAttrib.internalKey = $table_manUsers.id;";
	$rs = mysql_query($sql);
	$limit = mysql_num_rows($rs);

	if($limit==0 || $limit>1) {
		return;
	}
	
	
		
	$row = mysql_fetch_assoc($rs);

	$internalKey			 = $row['internalKey'];
	$dbasePassword			   = $row['password'];
	$failedlogins			  = $row['failedlogincount'];
	$blocked				 = $row['blocked'];
	$blockeduntildate		 = $row['blockeduntil'];
	$blockedafterdate		 = $row['blockedafter'];
	$registeredsessionid	= $row['sessionid'];
	$role					 = $row['role'];
	$lastlogin				  = $row['lastlogin'];
	$nrlogins				 = $row['logincount'];
	$fullname				 = $row['fullname'];
	$email					   = $row['email'];

	// load user settings
	$sql = "SELECT setting_name, setting_value FROM $table_userSetting WHERE user='".$internalKey."' AND setting_value!=''";
	$rs = mysql_query($sql);
	while ($row = mysql_fetch_assoc($rs)) {
		${$row['setting_name']} = $row['setting_value'];
	}


	if(!isset($_SESSION['mgrValidated'])) {
		$sql = "update $dbase.`".$table_prefix."user_attributes` SET failedlogincount=0, logincount=logincount+1, lastlogin=thislogin, thislogin=".time().", sessionid='$currentsessionid' where internalKey=$internalKey";
		$rs = mysql_query($sql);
	}
	# Added by Raymond:
	$_SESSION['usertype'] = 'manager'; // user is a backend user

	// get permissions
	$_SESSION['mgrShortname']=$username;
	$_SESSION['mgrFullname']=$fullname;
	$_SESSION['mgrEmail']=$email;
	$_SESSION['mgrValidated']=1;
	$_SESSION['mgrInternalKey']=$internalKey;
	$_SESSION['mgrFailedlogins']=$failedlogins;
	$_SESSION['mgrLastlogin']=$lastlogin;
	$_SESSION['mgrLogincount']=$nrlogins; // login count
	$_SESSION['mgrRole']=$role;
	
	$table_userRoles = $modx->getFullTableName("user_roles");
	
	$sql="SELECT * FROM $table_userRoles WHERE id=".$role.";";
	$rs = mysql_query($sql);
	$row = mysql_fetch_assoc($rs);
	$_SESSION['mgrPermissions'] = $row;

	// get user's document groups
	$table_memberGroups = $modx->getFullTableName("member_groups");
	$table_memberGroupAcc = $modx->getFullTableName("membergroup_access");
	$dg='';
	$i=0;
	$sql = "SELECT uga.documentgroup
			FROM $table_memberGroups ug
			INNER JOIN $table_memberGroupAcc uga ON uga.membergroup=ug.user_group
			WHERE ug.member =".$internalKey;
	$rs = mysql_query($sql);
	while ($row = mysql_fetch_row($rs)) $dg[$i++]=$row[0];
	$_SESSION['mgrDocgroups'] = $dg;

	include_once($modx->config['base_path'] . "manager/includes/log.class.inc.php");
	$log = new logHandler;
	$log->initAndWriteLog("Logged in", $_SESSION['webInternalKey'], $_SESSION['webShortname'], "58", "-", "WebLogin");
}

?>