<?php

namespace obo\lo;
class InstanceData
{
	public $instID;
	public $loID;
	public $userID;
	public $userName;
	public $name;
	public $courseID; // TODO: remove, use $course instead
	public $createTime;
	public $startTime;
	public $endTime;
	public $attemptCount;
	public $scoreMethod;
	public $allowScoreImport;
	public $perms;
	public $courseData;
	public $externalLink;
	public $originalID;

	function __construct($instID=0, $loID=0, $userID=0, $userName='', $name='', $course='', $createTime=0, $startTime=0, $endTime=0, $attemptCount=0, $scoreMethod=0, $allowScoreImport=0, $courseData=0, $perms=array(), $externalLink='', $originalID=0)
	{
		$this->instID = $instID;
		$this->loID = $loID;
		$this->userID = $userID;
		$this->userName = $userName;
		$this->name = $name;
		$this->courseID = $course; // remove
		$this->createTime = $createTime;
		$this->startTime = $startTime;
		$this->endTime = $endTime;
		$this->attemptCount = $attemptCount;
		$this->scoreMethod = $scoreMethod;
		$this->allowScoreImport = $allowScoreImport;
		$this->perms = $perms;
		$this->courseData = (object) array('type' => 'none');
		$this->externalLink = $externalLink;
		$this->originalID = $originalID;
	}

	public function dbGetCourseData()
	{
		// get courseData
		// TODO: this should use the system events system
		//$PM = \rocketD\plugin\PluginManager::getInstance();
		// Link item to course
		$this->courseData = (object) array('type' => 'none');
	}

	public function dbGet($DBM, $instID)
	{
		//Generate query string
		$qstr = "SELECT * FROM `".\cfg_obo_Instance::TABLE."` WHERE ".\cfg_obo_Instance::ID." = ?";
		if($q = $DBM->querySafe($qstr, $instID))
		{
			if($r = $DBM->fetch_obj($q))
			{
				// get the username
				$authMan = \rocketD\auth\AuthManager::getInstance();
				$ownerName = $authMan->getName($r->{\cfg_core_User::ID});

				// construct
				$this->__construct($r->{\cfg_obo_Instance::ID}, $r->{\cfg_obo_LO::ID}, $r->{\cfg_core_User::ID}, $ownerName , $r->{\cfg_obo_Instance::TITLE}, $r->{\cfg_obo_Instance::COURSE}, $r->{\cfg_obo_Instance::TIME}, $r->{\cfg_obo_Instance::START_TIME}, $r->{\cfg_obo_Instance::END_TIME}, $r->{\cfg_obo_Instance::ATTEMPT_COUNT}, $r->{\cfg_obo_Instance::SCORE_METHOD}, $r->{\cfg_obo_Instance::SCORE_IMPORT}, $r->{\cfg_obo_Instance::ORIGINAL_ID});

				// get course data
				// TODO: use system events to do this
				$this->dbGetCourseData($DBM);
			}
		}
	}
}
