<?php
namespace obo;
class Semester
{
	public $semesterID;
	public $name;
	public $year;
	public $startTime;
	public $endTime;
	
	public function __construct($semesterID=0, $name='', $year=0, $startTime=0, $endTime=0)
	{
		if(is_object($semesterID))
		{
			$this->semesterID = $semesterID->{\cfg_obo_Semester::ID};
			$this->name       = $semesterID->{\cfg_obo_Semester::NAME};
			$this->year       = $semesterID->{\cfg_obo_Semester::YEAR};
			$this->startTime  = $semesterID->{\cfg_obo_Semester::START_TIME};
			$this->endTime    = $semesterID->{\cfg_obo_Semester::END_TIME};
		}
		else
		{
			$this->semesterID = $semesterID;
			$this->name       = $name;
			$this->year       = $year;
			$this->startTime  = $startTime;
			$this->endTime    = $endTime;
		}
	}
}

?>