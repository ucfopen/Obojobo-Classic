<?php
/**
 * This class contains all logic for handling LO Visits
 * @author Luis Estrada <lestrada@mail.ucf.edu>
 */

/**
 * This class contains all logic for handling LO Visits
 * In this case, a visit is a unique record of when a user viewed an LO instance.
 * All scores and statistics for this login session are recorded under this LO visit.
 * When a user views an instance, a visit is created.  When the user logs out, the visit is closed.
 */
namespace obo;
class VisitManager extends \rocketD\db\DBEnabled
{
	private static $instance;

	static public function getInstance()
	{
		if(!isset(self::$instance))
		{
			$selfClass = __CLASS__;
			self::$instance = new $selfClass();
		}
		return self::$instance;
	}
	
	/**
	 * Creates a new session for a given instance
	 * @param $instID (number) Instance ID
	 */
	public function createVisit($instID = 0)
	{
		$qstr = "INSERT INTO ".\cfg_obo_Visit::TABLE." SET `".\cfg_core_User::ID."` = '?',	`".\cfg_obo_Visit::TIME."` = UNIX_TIMESTAMP(), `".\cfg_obo_Visit::IP."` = '?', `".\cfg_obo_Instance::ID."` = '?'";
		
		if(!($q = $this->DBM->querySafe($qstr, $_SESSION['userID'], $_SERVER['REMOTE_ADDR'], $instID)))
		{
			trace(mysql_error(), true);
			$this->DBM->rollback();
			return false;
		}		
		// locate the correct session
		if(is_array($_SESSION['OPEN_INSTANCE_DATA']))
		{
			foreach($_SESSION['OPEN_INSTANCE_DATA'] AS $key => $value)
			{
				if($value['instID'] == $GLOBALS['CURRENT_INSTANCE_DATA']['instID'])
				{
					$_SESSION['OPEN_INSTANCE_DATA'][$key]['visitID'] = $this->DBM->insertID;
					$GLOBALS['CURRENT_INSTANCE_DATA'] = $_SESSION['OPEN_INSTANCE_DATA'][$key];
				}
			}
			
		}
		
		$trackingMan = \obo\log\LogManager::getInstance();
		$trackingMan->trackVisit();
	}

	/**
	 * Returns session data for a given session id
	 * This information is stored in the database to keep from storing a lot of information in $_SESSION
	 * @param $visitID (number) the session ID to get information from
	 * @return (StdObject) The complete record of that row in the database (refer to the table definition for element names)
	 * @return (bool) False if no session was found.
	 */
	// TODO: FIX RETURN FOR DB ABSTRACTION
	public function getVisit($visitID=0)
	{

		if($visitID == 0) // for shorter code, instead of getVisit($_SESSION['visitID']), use getVisit()
		{
			$visitID = $thi->getCurrentVisitID();
		}
		$qstr = "SELECT * FROM ".\cfg_obo_Visit::TABLE." WHERE ".\cfg_obo_Visit::ID."='?' LIMIT 1";
		
		if( !($q = $this->DBM->querySafe($qstr, $visitID)) )
		{
			trace(mysql_error(), true);
			return false;
		}

		if(($visit = $this->DBM->fetch_obj($q)))
		{
			return $visit;
		}
		else
		{
			return false;
		}
	}
	
	public function getCurrentVisitID()
	{
		if($GLOBALS['CURRENT_INSTANCE_DATA']['visitID'] < 1) //exit if they do not have an open instance
		{
			return false;
		}
		return $GLOBALS['CURRENT_INSTANCE_DATA']['visitID'];
	}

    public function resumeVisit($instID = 0)
    {
        if(!is_numeric($instID) || $instID < 1)
		{
			
			
			return \rocketD\util\Error::getError(1);
		}
		//check to see if the instance exists
		$qstr = "SELECT * FROM ".\cfg_obo_Instance::TABLE." WHERE `".\cfg_obo_Instance::ID."`='?' LIMIT 1";		
		if(!($q = $this->DBM->querySafe($qstr, $instID)))
		{
		    trace(mysql_error(), true);
			$this->DBM->rollback();
			//die();
			return false;
		}

		if($r = $this->DBM->fetch_obj($q))
		{
			$curtime = time();
			//Verify that the instance is currently active
			if($r->{\cfg_obo_Instance::START_TIME} <= $curtime && $curtime <= $r->{\cfg_obo_Instance::END_TIME})
			{
				$loMan = \obo\lo\LOManager::getInstance();
				$rootID = $loMan->getRootId($r->{\cfg_obo_LO::ID});
				$permMan = \obo\perms\PermissionsManager::getInstance();
				$roleMan = \obo\perms\RoleManager::getInstance();

				if($roleMan->isSuperUser() || $permMan->getMergedPerm($rootID, \cfg_obo_Perm::TYPE_LO, \cfg_obo_Perm::READ, $_SESSION['userID']))
				{
					//$_SESSION['INSTANCE_ID'] = $instID;
					$this->createVisit($instID);
					return true;
				}
			}
		}
		
		return false;
    }

	
	public function getInstanceViewKey($instID)
	{
		return $_SESSION['OPEN_INSTANCE_DATA'][$instID]['VIEW_KEY'];
	}
	
	public function getInstIDFromViewKey($viewKey)
	{
		if(is_array($_SESSION['OPEN_INSTANCE_DATA']))
		{
			foreach($_SESSION['OPEN_INSTANCE_DATA'] as $instID => $inst_data)
			{
				if($inst_data['VIEW_KEY'] == $viewKey) return $instID;
			}
		}
		
		
		\rocketD\util\Error::getError(4005);
		return false;
	}
	
	public function registerCurrentViewKey($viewKey)
	{
		$instID = $this->getInstIDFromViewKey($viewKey);
		if($instID)
		{
			// makes the current_instance_data variable = to the open instance data so other functions know what the current instance is
			$GLOBALS['CURRENT_INSTANCE_DATA'] = $_SESSION['OPEN_INSTANCE_DATA'][$instID];
			$GLOBALS['CURRENT_INSTANCE_DATA']['instID'] = $instID;
			return true;
		}
		return false;
	}
	
	public function getCurrentViewKeyInstID()
	{
		return $GLOBALS['CURRENT_INSTANCE_DATA']['instID'];
	}
	
	public function startInstanceView($instID)
	{
		
		if(!is_array($_SESSION['OPEN_INSTANCE_DATA'])){
			$_SESSION['OPEN_INSTANCE_DATA'] = array();
		}
		
		if(!is_array($_SESSION['OPEN_INSTANCE_DATA'][$instID]))
		{
			$_SESSION['OPEN_INSTANCE_DATA'][$instID] = array('instID' => $instID, 'VIEW_KEY' => -1, 'attemptID' => -1, 'visitID' => -1);
		}
		// store new key
		$_SESSION['OPEN_INSTANCE_DATA'][$instID]['VIEW_KEY'] = md5(uniqid(rand(), true));
		// place this view in the current data slot
		$GLOBALS['CURRENT_INSTANCE_DATA'] = $_SESSION['OPEN_INSTANCE_DATA'][$instID];
	}
	
	public function calculateVisitTimes()
	{
		$LM = \obo\log\LogManager::getInstance();
		$prev_instID = 0;
		$count = 0;
		$time = time() - 21600; 
		// get all the visits that have not been calculated yet AND are over 6 hours old
		$sql = "SELECT * FROM obo_log_visits WHERE overviewTime IS NULL AND createTime < $time LIMIT 50";
		$q = $this->DBM->query($sql);
		while($r = $this->DBM->fetch_obj($q))
		{
			$visit = $r;
			if($prev_instID != $visit->instID)
			{
				$track = $LM->getInteractionLogByInstance($visit->instID, true);
			}
			$prev_instID = $visit->instID;
			
			if(is_array($track))
			{
				foreach($track['visitLog'] AS $vLog)
				{
					if($vLog['visitID'] == $visit->visitID)
					{
						// update the db
						$count++;
						$this->DBM->querySafe("UPDATE obo_log_visits SET overviewTime = '?', contentTime = '?', practiceTime = '?', assessmentTime = '?' WHERE visitID = '?'", $vLog['sectionTime']['overview'], $vLog['sectionTime']['content'], $vLog['sectionTime']['practice'], $vLog['sectionTime']['assessment'], $visit->visitID);
						break;
					}
				}
			}
		}
		return $count;
	}
}
?>