<?php
abstract class nm_los_CoursePlugin extends core_db_DBEnabled
{
	protected $internalUser;
	abstract static public function getInstance();
	
	abstract public function getCourse($courseID);
	abstract public function getMyCourses();
}
?>