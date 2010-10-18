<?php
/**
 * This is the class that defines the Learning Object data type
 * @author Jacob Bates <jbates@mail.ucf.edu>
 */

/**
 * This is the class that defines the Learning Object data type
 * It is used simply for representing data in memory, and has no methods.
 */
class nm_los_InstanceData
{
	public $instID;
	public $loID;
	public $userID;
	public $userName;
	public $name;
	//TODO: future course code: public $course;
	public $courseID; // remove
	public $createTime;
	public $startTime;
	public $endTime;
	public $attemptCount;
	public $scoreMethod;
	public $allowScoreImport;
	public $perms;
	
	function __construct($instID=0, $loID=0, $userID=0, $userName='', $name='', $course='', $createTime=0, $startTime=0, $endTime=0, $attemptCount=0, $scoreMethod=0, $allowScoreImport=0, $perms=array())
	{
		$this->instID = $instID;
		$this->loID = $loID;
		$this->userID = $userID;
		$this->userName = $userName;
		$this->name = $name;
		//TODO: future course code: $this->course = $course;
		$this->courseID = $course; // remove
		$this->createTime = $createTime;
		$this->startTime = $startTime;
		$this->endTime = $endTime;
		$this->attemptCount = $attemptCount;
		$this->scoreMethod = $scoreMethod;
		$this->allowScoreImport = $allowScoreImport;
		$this->perms = $perms;
	}
	
	public function dbGet($DBM, $instID)
	{
		//Generate query string
		$qstr = "SELECT * FROM `".cfg_obo_Instance::TABLE."` WHERE ".cfg_obo_Instance::ID." = ?";
		if($q = $DBM->querySafe($qstr, $instID))
		{
			if($r = $DBM->fetch_obj($q))
			{
				//TODO: future course code: 
				// get the course data
				//$cm = nm_los_CourseManager::getInstance();
				//$course = $cm->getCourse($r->{cfg_obo_Course::ID});
				
				// get the username
				$authMan = core_auth_AuthManager::getInstance();
				$ownerName = $authMan->getName($r->{cfg_core_User::ID});
				
				// construct
				//TODO: future course code:  $this->__construct($r->{cfg_obo_Instance::ID}, $r->{cfg_obo_LO::ID}, $r->{cfg_core_User::ID}, $owner , $r->{cfg_obo_Instance::TITLE}, $course, $r->{cfg_obo_Instance::TIME}, $r->{cfg_obo_Instance::START_TIME}, $r->{cfg_obo_Instance::END_TIME}, $r->{cfg_obo_Instance::ATTEMPT_COUNT}, $r->{cfg_obo_Instance::SCORE_METHOD}, $r->{cfg_obo_Instance::SCORE_IMPORT});
				$this->__construct($r->{cfg_obo_Instance::ID}, $r->{cfg_obo_LO::ID}, $r->{cfg_core_User::ID}, $owner , $r->{cfg_obo_Instance::TITLE}, $r->{cfg_obo_Instance::COURSE}, $r->{cfg_obo_Instance::TIME}, $r->{cfg_obo_Instance::START_TIME}, $r->{cfg_obo_Instance::END_TIME}, $r->{cfg_obo_Instance::ATTEMPT_COUNT}, $r->{cfg_obo_Instance::SCORE_METHOD}, $r->{cfg_obo_Instance::SCORE_IMPORT});
			}
		}
	}
}
?>