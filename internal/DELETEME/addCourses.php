<pre>
<?php
require_once(dirname(__FILE__)."/../app.php");


$DBM = core_db_DBManager::getConnection(new core_db_dbConnectData(AppCfg::DB_HOST, AppCfg::DB_USER, AppCfg::DB_PASS, AppCfg::DB_NAME, AppCfg::DB_TYPE));
$DBM->startTransaction();

$courses = array();
$AM = core_auth_AuthManager::getInstance();
$CM = nm_los_CourseManager::getInstance();
$q = $DBM->query("SELECT * FROM ".cfg_obo_Instance::TABLE."");

$instQ = "UPDATE ".cfg_obo_Instance::TABLE. " SET courseID = '?' WHERE instID = '?'";

while($r = $DBM->fetch_obj($q))
{
	$semester = $CM->getSemesterForDate($r->createTime);
	$course = new nm_los_Course(0, '', '', '', '', $r->courseName, '', '', $r->userID, $semester->semesterID);
	if(count($courses) == 0)
	{
		$CM->saveCourse($course);
		$courses[] = $course;
	}
	else
	{
		$match = false;
		foreach($courses AS $c)
		{
			if(str_replace(" ", "", strtolower($c->title)) == str_replace(" ", "", strtolower($course->title)) && $c->userID == $course->userID && $course->semesterID == $c->semesterID)
			{
				// skip 
				$match = true;
				$course->courseID = $c->courseID;
				continue;
			}
		}
		if($match == false)
		{
			if($_REQUEST['doit'] == true) echo "$course->title \n";
			$CM->saveCourse($course);
			$courses[] = $course;
		}
	}
	// save the course id back to instance table
	$DBM->querySafe($instQ, $course->courseID, $r->instID);
}

if($_REQUEST['doit'] == true)
{
	echo count($courses) . " courses created";
	$DBM->commit();
}
else
{
	echo "<a href=\"{$_SERVER['PHP_SELF']}?doit=true\">Click to add ". count($courses) ." courses</a>";
	$DBM->rollback();
}

?>