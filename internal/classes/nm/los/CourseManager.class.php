<?php
class nm_los_CourseManager extends core_db_dbEnabled
{
	private static $instance;
	
	public function __construct()
	{
	    $this->defaultDBM();
	}
	
	static public function getInstance()
	{
		if(!isset(self::$instance))
		{
			$selfClass = __CLASS__;
			self::$instance = new $selfClass();
		}
		return self::$instance;
	}
	
	
	public function getCurrentSemester()
	{
		
		if($semesters = core_util_Cache::getInstance()->getCurrentSemester())
		{
			return $semesters;
		}
		else
		{
			$result = $this->getSemesterForDate(time());
			core_util_Cache::getInstance()->setCurrentSemester($result);
			return $result;
		}
	}
	
	public function getSemesterForDate($date)
	{
		if($date>0)
		{
			trace($date);
			$q = $this->DBM->querySafe("SELECT * FROM ".cfg_obo_Semester::TABLE." WHERE  ".cfg_obo_Semester::END_TIME." > '?' ORDER BY ".cfg_obo_Semester::END_TIME." ASC", $date);
			if($r = $this->DBM->fetch_obj($q))
			{
				$semester = new nm_los_Semester($r);
				return $semester;
			}
		}

		return new nm_los_Semester();
	}
	
	public function getCoursePlugins()
	{
		$plugins = explode(',', AppCfg::COURSE_PLUGINS);
		foreach($plugins AS &$plugin)
		{
			$plugin = 'plg_'.$plugin.'_'.$plugin; // translate plugin name into actual class name
		}
		return $plugins;
	}
	
	public function getMyCourses()
	{
		$roleMan = nm_los_RoleManager::getInstance();
		if(!$roleMan->isSuperUser()) // if the current user is not SuperUser
		{
			if(!$roleMan->isLibraryUser())
			{
				
				return core_util_Error::getError(4);
			}
		}
		// passed perm requirements
		
		if($courses = core_util_Cache::getInstance()->getMyCourses($_SESSION['userID']))
		{
			return $courses;
		}
		
		$courses = array();
		
		// get all existing internal courses for this term
		
		$q = $this->DBM->querySafe("SELECT * FROM ".cfg_obo_Course::TABLE." WHERE ".cfg_core_User::ID." = '?'", $_SESSION['userID']);
		while($r = $this->DBM->fetch_obj($q))
		{
			$c = new nm_los_Course($r);
			$courses[$c->courseID] = $c;
		}
		
		// search the course plugins for courses
		$cPluginNames = $this->getCoursePlugins();
		
		// get each of the plugins courses, save them if they are new or different, and add them to the return array
		foreach($cPluginNames AS $cPluginName)
		{
			$cPlugin = call_user_func(array($cPluginName, 'getInstance'));
			$pluginCourses = $cPlugin->getMyCourses();
			if(is_array($pluginCourses))
			{
				foreach($pluginCourses AS &$pluginCourse)
				{
					// check if the plugin course has an internal record and if it needs to be updated
					if( ($pluginCourse->courseID == 0) || !isset($courses[$pluginCourse->courses]) || ($pluginCourse !== $courses[$pluginCourse->courses]) )
					{
						$this->saveCourse($pluginCourse);
					}
					$courses[$pluginCourse->courseID] = $pluginCourse;
				}
			}
		}
		return $courses;
	}
	
	public function getSemesters()
	{
		
		if($semesters = core_util_Cache::getInstance()->getSemesters())
		{
			return $semesters;
		}
		else
		{
			$semesters = array();
			$q = $this->DBM->query("SELECT * FROM ".cfg_obo_Semester::TABLE." ORDER BY ".cfg_obo_Semester::START_TIME." ASC");
			if($r = $this->DBM->fetch_obj($q))
			{
				$semesters[] = new nm_los_Semester($r);
			}
			core_util_Cache::getInstance()->setSemesters($semesters);
			return $semesters;
		}
	}
	
	public function getCourse($courseID)
	{
		if( !(nm_los_Validator::isPosInt($courseID)) )
		{
			return false;
		}
		
		
		// if we cant get it from cache
		if(!($course = core_util_Cache::getInstance()->getCourse($courseID)))
		{
			$q = $this->DBM->querySafe("SELECT * FROM ".cfg_obo_Course::TABLE." WHERE ".cfg_obo_Course::ID." = '?'", $courseID);
			if($r = $this->DBM->fetch_obj($q))
			{
				$course = new nm_los_Course($r);
			}
		}
		// use the plugin to get the course
		if(strlen($course->pluginName) > 0)
		{
			$plugM = core_plugin_PlubinManager::getInstance();
			// let the plugin moderate it's own cache AND update our internal course info
			$plugCourse = $plugM->call($r->{cfg_obo_Course::PLUGIN}, 'getCourse', $r->{cfg_obo_Course::ID});
			if($plugCourse instanceof nm_los_Course)
			{
				if($plugCourse !== $course)
				{
					$this->saveCourse($plugCourse);
				}
			}
		}
		core_util_Cache::getInstance()->setCourse($course);
		return $course;
	}

	
	public function saveCourse(&$course)
	{
		if(!($course->userID > 0))
		{
			$course->userID = $_SESSION['userID'];
		}
		// update
		if($course->courseID > 0)
		{
			$this->DBM->querySafe("UPDATE " . cfg_obo_Course::TABLE .
									" SET ".
										cfg_obo_Course::PLUGIN ." = '?',".
										cfg_obo_Course::REG_KEY ." = '?',".
										cfg_obo_Course::PREFIX ." = '?',".
										cfg_obo_Course::NUM ." = '?',".
										cfg_obo_Course::SECTION ." = '?',".
										cfg_obo_Course::NAME ." = '?',".
										cfg_obo_Course::COLLEGE ." = '?',".
										cfg_obo_Course::DEPARTMENT ." = '?',".
										cfg_core_User::ID . " =  '?',".
										cfg_obo_Semester::ID . " = '?'".
									"WHERE ".cfg_obo_Course::ID." = '?'", $course->pluginName, $course->regKey, $course->prefix, $course->number, $course->section, $course->title, $course->college, $course->dept, $course->userID, $course->semesterID, $course->courseID);
		}
		// new
		else
		{
			if(!($course->userID > 0))
			{
				$course->userID = $_SESSION['userID'];
			}
			$this->DBM->querySafe("INSERT INTO ".cfg_obo_Course::TABLE.
								" SET ".
									cfg_obo_Course::PLUGIN ." = '?',".
									cfg_obo_Course::REG_KEY ." = '?',".
									cfg_obo_Course::PREFIX ." = '?',".
									cfg_obo_Course::NUM ." = '?',".
									cfg_obo_Course::SECTION ." = '?',".
									cfg_obo_Course::NAME ." = '?',".
									cfg_obo_Course::COLLEGE ." = '?',".
									cfg_obo_Course::DEPARTMENT ." = '?',".
									cfg_core_User::ID . " =  '?',".
									cfg_obo_Semester::ID . " = '?'", $course->pluginName, $course->regKey, $course->prefix, $course->number, $course->section, $course->title, $course->college, $course->dept, $course->userID, $course->semesterID);
			// update the courseID
			$course->courseID = $this->DBM->insertID;
		}

		return $course;
	}
}
?>