<?php
/**
* 
*/
class nm_los_Course
{
	public $courseID;
	public $regKey;
	public $prefix;
	public $number;
	public $section;
	public $title;
	public $college;
	public $dept;
	public $userID;
	public $pluginName;
	public $semesterID;	
	
	function __construct($courseID=0, $regKey='', $prefix='', $number=0, $section='', $title='', $college='', $dept ='', $userID = '', $semesterID = '', $pluginName = '')
	{
		if(is_object($courseID))
		{
			$this->courseID = $courseID->{cfg_obo_Course::ID};
			$this->regKey = $courseID->{cfg_obo_Course::REG_KEY};
			$this->prefix = $courseID->{cfg_obo_Course::PREFIX};
			$this->number = $courseID->{cfg_obo_Course::NUM};
			$this->section = $courseID->{cfg_obo_Course::SECTION};
			$this->title = $courseID->{cfg_obo_Course::NAME};
			$this->college = $courseID->{cfg_obo_Course::COLLEGE};
			$this->dept = $courseID->{cfg_obo_Course::DEPARTMENT};
			$this->pluginName = $courseID->{cfg_obo_Course::PLUGIN};
			$this->semesterID = $courseID->{cfg_obo_Semester::ID};
			$this->userID = $courseID->{cfg_core_User::ID};
		}
		else
		{
			$this->courseID = $courseID;
			$this->regKey = $regKey;
			$this->prefix = $prefix;
			$this->number = $number;
			$this->section = $section;
			$this->title = $title;
			$this->college = $college;
			$this->dept = $dept;
			$this->userID = $userID;
			$this->semesterID = $semesterID;
			$this->pluginName = $pluginName;
		}

	}
	
}

?>