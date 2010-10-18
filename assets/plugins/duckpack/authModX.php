<?php
include_once('authModX.inc.php');// these only need to be loaded once

$e = &$modx->Event;
switch($e->name)
{
	
	case 'OnWebAuthentication':
		duckPack_loadAPI();
		$oboAPI = nm_los_API::getInstance();
		if($oboAPI->getSessionValid() === true) $e->_output = true;
		break;

	case 'OnManagerAuthentication':
		duckPack_loadAPI();
		$oboAPI = nm_los_API::getInstance();
		if($oboAPI->getSessionValid(nm_los_Role::SUPER_USER)) $e->_output = true;
		break;

	case 'OnBeforeManagerLogin':
		duckPack_loadAPI();
		$oboAPI = nm_los_API::getInstance();
		$validSession = $oboAPI->getSessionValid(nm_los_Role::SUPER_USER);
		if($validSession != true) // no session yet
		{
			if($oboAPI->doLogin($e->params['username'], $e->params['userpassword']) === true) // log in
			{
				$validSession = $oboAPI->getSessionValid(nm_los_Role::SUPER_USER); // make sure user is SU
			}
		}
		if($validSession == true) // already has a session
		{
			$externUser = $oboAPI->getUser();
			
			// check for external user group requirements
			if(duckPack_externalUserIsLocalManager($externUser->login) ) 
			{
				// modx user exists, sync local data from external
				duckPack_updateLocalManager($externUser->login, md5(time()), $externUser->first.' '.$externUser->last, !$isSU , $externUser->email);
			}
			else{
				// no modx user, make one
				duckPack_createLocalManager($externUser->login, md5(time()), $externUser->first.' '.$externUser->last, $externUser->email);
			}
		}		 
		break;

	case 'OnManageLogout':
	case 'OnWebLogout':
		duckPack_loadAPI();
		$oboAPI = nm_los_API::getInstance();
		$oboAPI->doLogout();
		break;

	case 'OnBeforeWebLogin':
		duckPack_loadAPI();
		$oboAPI = nm_los_API::getInstance();
		$validSession = $oboAPI->getSessionValid();
		if($validSession != true) // no session yet
		{
			if($oboAPI->doLogin($e->params['username'], $e->params['userpassword']) === true)
			{
				$validSession = true; // switch to true 
			}
		}
		if($validSession == true) // already has a session
		{
			$externUser = $oboAPI->getUser();
			if(duckPack_externalUserIsLocalWebUser($externUser->login) ) 
			{
				// modx user exists, sync local data from external
				duckPack_updateLocalWebUser($externUser->login, md5(time()), $externUser->first.' '.$externUser->last, false, $externUser->email);
			}
			else{
				// no modx user, make one
				duckPack_createLocalWebUser($externUser->login, md5(time()) , $externUser->first.' '.$externUser->last, $externUser->email);
			}
		}
		break;
		
	case 'OnWebPagePrerender':
		// check to make sure they are not already logged in
		if( !isset($_SESSION['webValidated']) || !isset($_SESSION['mgrValidated']) )
		{
			duckPack_loadAPI();
			$oboAPI = nm_los_API::getInstance();
			if($oboAPI->getSessionValid())
			{
				// // valid session, get user info
				// $externUser = $oboAPI->getUser();
				// // check for SU status
				// $roles = $oboAPI->getUserRoles($externUser->userID);
				// $isSU = false;
				// if(count($roles) > 0)
				// {
				// 	foreach($roles AS $role)
				// 	{
				// 		if($role->name == nm_los_Role::SUPER_USER)
				// 		{
				// 			$isSU = true;
				// 			break;
				// 		}
				// 	}
				// } 
				//if($isSU) duckPack_logInExternalUserAsManager($externUser->login); // su
				//else
				duckPack_logInExternalUserAsWebUser($externUser->login); // non su
			}
		}
		break;
}
?>