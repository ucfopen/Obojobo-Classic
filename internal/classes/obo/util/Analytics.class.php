<?php
namespace obo\util;
class Analytics extends \rocketD\db\DBEnabled
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

	function __construct()
	{
	    $this->defaultDBM();
	}
	
	public function getMyStatMasters()
	{
		$LOM = \obo\lo\LOManager::getInstance();
		$loArr = array();
		$RM = \obo\perms\RoleManager::getInstance();
		if($RM->isSuperStats())
		{
			
			$qstr = "SELECT ".\cfg_obo_LO::ID." FROM ".\cfg_obo_LO::TABLE." WHERE ".\cfg_obo_LO::VER." > 0 AND ".\cfg_obo_LO::SUB_VER." = 0";
			if(!($q = $this->DBM->query($qstr)))
			{
				return false;   
			}
			while($r = $this->DBM->fetch_obj($q))
			{
				$loArr[] = $LOM->getLO($r->{\cfg_obo_LO::ID}, 'meta');
			}
		}
		return $loArr;
	}

	public function getLOStat($los, $stat, $start, $end, $resolution, $preview = true)
	{
		$RM = \obo\perms\RoleManager::getInstance();
		if($RM->isSuperStats())
		{
			$year = "YEAR(FROM_UNIXTIME(%)) AS year";
			$month = "MONTH(FROM_UNIXTIME(%)) AS month";
			$day = "DAY(FROM_UNIXTIME(%)) AS day";
			$hour = "HOUR(FROM_UNIXTIME(%)) AS hour";
			
			$limit = $preview ? ' LIMIT 10' : '';
			switch($resolution)
			{
				case 'month':
					$group = "year, month";
					$order = "year, month";
					$select = ', ' . $year . ', ' . $month;
					break;
				case 'day':
					$group = "year, month, day";
					$order = "year, month, day";
					$select = ', ' . $year . ', ' . $month . ', ' . $day;
					break;
				case 'hour':
					$group = "year, month, day, hour";
					$order = "year, month, day, hour";
					$select = ', ' . $year . ', ' . $month . ', ' . $day . ', ' . $hour;
					break;
				case 'year':
					$group = "year";
					$order = "year";
					$select = ', ' . $year;
					break;
				case 'all':
				default:
					$group = "";
					$order = "";
					break;
			}
			$results = '';
			$t = microtime(1);
			switch($stat)
			{
				case 10: // instances created
					$los = implode(',', $los);
					$select = str_replace('%', 'createTime', $select);
					$sql = "SELECT COUNT(InstID) AS INSTANCES, COUNT(DISTINCT userID) AS OWNERS, COUNT(DISTINCT loID) AS MASTERS $select FROM obo_lo_instances WHERE loID IN (?) AND createTime > '?' AND createTime < '?' ".(strlen($group) ? " GROUP BY $group" : '') . (strlen($order) ? " ORDER BY $order" : '' ). $limit;
					$q = $this->DBM->querySafe($sql, $los, $start, $end);
					$results = $this->DBM->getAllRows($q);
					break;
				case 20: // student views
					$select = str_replace('%', 'createTime', $select);
					$los = implode(',', $los);
					$sql = "SELECT COUNT(createTime) AS VISITS, COUNT(DISTINCT userID) AS VISITORS, COUNT(DISTINCT instID) AS INSTANCES, COUNT(DISTINCT loID) AS MASTERS $select FROM obo_logs WHERE loID IN (?) AND itemType = 'Visited' AND createTime > '?' and createTime < '?' ".(strlen($group) ? " GROUP BY $group" : '' ) . (strlen($order) ? " ORDER BY $order" : '') . $limit;
					$q = $this->DBM->querySafe($sql, $los, $start, $end);
					$results = $this->DBM->getAllRows($q);
					break;
				case 30: // View Time by Section
					// $LM = new \obo\log\LogManager();
					// $output = array('OVERVIEW_TIME' => 0, 'CONTENT_TIME' => 0, 'PRACTICE_TIME' => 0, 'ASSESSMENT_TIME' => 0);
					// 
					// foreach($los AS $lo)
					// {
					// 	$logs = $LM->getInteractionLogByMaster($lo, true);
					// 	$output['OVERVIEW_TIME'] += $logs['sectionTime']['overview'];
					// 	$output['CONTENT_TIME'] += $logs['sectionTime']['content'];
					// 	$output['PRACTICE_TIME'] += $logs['sectionTime']['practice'];
					// 	$output['ASSESSMENT_TIME'] += $logs['sectionTime']['assessment'];
					// }
					// trace((object)$output);
					// return array((object)$output);
					$select = str_replace('%', 'createTime', $select);
					$los = implode(',', $los);
					$sql = "SELECT COUNT(*) AS VISITS, COUNT(DISTINCT instID) AS INSTANCES, COUNT(DISTINCT userID) AS USERS, COUNT(DISTINCT loID) AS MASTERS,  SEC_TO_TIME(SUM(overviewTime)) AS OVERVIEW_TOTAL_HMS, SEC_TO_TIME(AVG(overviewTime)) AS OVERVIEW_AVG, SEC_TO_TIME(STD(overviewTime)) AS OVERVIEW_STD, SEC_TO_TIME(SUM(contentTime)) AS CONTENT_TOTAL_HMS, SEC_TO_TIME(AVG(contentTime)) AS CONTENT_AVG, SEC_TO_TIME(STD(contentTime)) AS CONTENT_STD, SEC_TO_TIME(SUM(practiceTime)) AS PRACTICE_TOTAL_HMS, SEC_TO_TIME(AVG(practiceTime)) AS PRACTICE_AVE, SEC_TO_TIME(STD(practiceTime)) AS PRACTICE_STD, SEC_TO_TIME(SUM(assessmentTime)) AS ASSESSMENT_TOTAL_HMS, SEC_TO_TIME(AVG(assessmentTime)) AS ASSESSMENT_AVE, SEC_TO_TIME(STD(assessmentTime)) AS ASSESSMENT_STD $select FROM obo_log_visits  WHERE loID in (?) ".(strlen($group) ? " GROUP BY $group" : '') . (strlen($order) ? " ORDER BY $order" : '' ) . $limit;
					$q = $this->DBM->querySafe($sql, $los);
					$results = $this->DBM->getAllRows($q);

					break;
				case 40: // Assessments Completed
					$los = implode(',', $los);
					$select = str_replace('%', 'endTime', $select);
					$sql = "SELECT COUNT(DISTINCT A.attemptID) AS COMPLETED_ASSESSMENTS, COUNT(DISTINCT A.instID) AS INSTANCES, COUNT(DISTINCT A.loID) AS MASTERS, AVG(A.score) AS AVE_SCORE, STD(A.score) AS STD_SCORE, COUNT(DISTINCT A.userID) AS USERS $select FROM obo_log_attempts AS A JOIN obo_los AS O ON  O.aGroupID = A.qGroupID WHERE endTime > '?' AND endTime < '?' AND A.loID IN (?) ".(strlen($group) ? " GROUP BY $group" : '') . (strlen($order) ? " ORDER BY $order" : '' ). $limit;
					$q = $this->DBM->querySafe($sql, $start, $end, $los);
					$results = $this->DBM->getAllRows($q);
					break;
				case 50: // count score import usage
					$los = implode(',', $los);
					$select = str_replace('%', 'endTime', $select);
					$sql = "SELECT COUNT(DISTINCT A.attemptID) AS IMPORTS_USED, COUNT(DISTINCT A.userID) AS USERS, COUNT(DISTINCT A.InstID) AS INSTANCES, COUNT(DISTINCT A.loID) AS MASTERS $select FROM obo_log_attempts AS A JOIN obo_los AS O ON  O.aGroupID = A.qGroupID WHERE endTime > '?' AND endTime < '?' AND A.loID IN (?) AND A.linkedAttemptID > 0 ".(strlen($group) ? " GROUP BY $group" : '') . (strlen($order) ? " ORDER BY $order" : '') . $limit;
					$q = $this->DBM->querySafe($sql, $start, $end, $los);
					$results = $this->DBM->getAllRows($q);
					break;	
				case 60: // Who created instances of these los
					$los = implode(',', $los);
					$select = str_replace('%', 'I.createTime', $select);
					$sql = "SELECT U.login AS USERNAME, U.last AS LAST, U.first AS First, U.email AS EMAIL, COUNT(I.InstID) AS INSTANCES, COUNT(DISTINCT I.loID) AS UNIQUE_LOS $select FROM obo_lo_instances AS I JOIN obo_users AS U ON U.userID = I.userID WHERE I.loID IN (?) AND I.createTime > '?' AND I.createTime < '?' ".(strlen($group) ? " GROUP BY I.userID, $group" : ' GROUP BY I.userID') . (strlen($order) ? " ORDER BY $order, I.userID " : ' ORDER BY I.userID') . $limit;
					$q = $this->DBM->querySafe($sql, $los, $start, $end);
					$results = $this->DBM->getAllRows($q);
					break;	
				case 65: // Who created the los
					$los = implode(',', $los);
					$select = str_replace('%', 'L.createTime', $select);
					$sql = "SELECT U.login AS USERNAME, U.last AS LAST, U.first AS First, U.email AS EMAIL $select FROM obo_map_authors_to_lo AS MA JOIN obo_users AS U ON U.userID = MA.userID JOIN obo_los L ON L.loID = MA.loID WHERE L.loID IN (?) AND L.createTime > '?' AND L.createTime < '?' ".(strlen($group) ? " GROUP BY MA.userID, $group" : ' GROUP BY MA.userID') . (strlen($order) ? " ORDER BY $order, MA.userID " : ' ORDER BY MA.userID') . $limit;
					$q = $this->DBM->querySafe($sql, $los, $start, $end);
					$results = $this->DBM->getAllRows($q);
					break;
				case 70: // Which Courses are instances used in
					$los = implode(',', $los);
					$select = str_replace('%', 'I.createTime', $select);
					$sql = "SELECT I.courseName, IF(MAX(GC.sectionID), MAX(GC.sectionID), 'none') AS WEBCOURSES_SECTION, Count(I.courseName) AS COUNT, COUNT(DISTINCT I.loID) AS UNIQUE_LOS $select FROM obo_lo_instances AS I LEFT JOIN plg_wc_grade_columns AS GC ON GC.instID = I.instID  WHERE I.loID IN (?) AND I.createTime > '?' AND I.createTime < '?' ".(strlen($group) ? " GROUP BY I.courseName, $group" : ' GROUP BY I.courseName') . (strlen($order) ? " ORDER BY $order, I.courseName" : ' ORDER BY I.courseName') . $limit;
					$q = $this->DBM->querySafe($sql, $los, $start, $end);
					$results = $this->DBM->getAllRows($q);
					break;
				case 75: // Who visited the instance
					$los = implode(',', $los);
					$select = str_replace('%', 'V.createTime', $select);
					$sql = "SELECT U.login AS USERNAME, U.last AS LAST, U.first AS First, U.email AS EMAIL, GROUP_CONCAT( DISTINCT R.name) AS ROLES,  COUNT(DISTINCT V.visitID) AS VISITS, COUNT(DISTINCT V.instID) AS INSTANCES $select FROM obo_log_visits AS V JOIN obo_users AS U ON U.userID = V.userID LEFT JOIN obo_map_roles_to_user MR ON MR.userID = V.userID LEFT JOIN obo_user_roles R ON R.roleID = MR.roleID  WHERE  V.loID IN (?) AND V.createTime > '?' AND V.createTime < '?' ".(strlen($group) ? " GROUP BY V.userID, $group" : ' GROUP BY V.userID') . (strlen($order) ? " ORDER BY $order, V.userID " : ' ORDER BY V.userID') . $limit;
					// $sql = "SELECT U.login AS USERNAME, U.last AS LAST, U.first AS First, U.email AS EMAIL, GROUP_CONCAT( DISTINCT R.name) AS ROLES,  COUNT(DISTINCT V.visitID) AS VISITS, COUNT(DISTINCT V.instID) AS INSTANCES, COUNT( DISTINCT A.attemptID) AS COMPLETED_ASSESSMENT $select FROM obo_log_visits AS V JOIN obo_users AS U ON U.userID = V.userID LEFT JOIN obo_log_attempts AS A ON A.loID = V.loID AND A.userID = V.userID LEFT JOIN obo_map_roles_to_user MR ON MR.userID = V.userID LEFT JOIN obo_user_roles R ON R.roleID = MR.roleID  WHERE A.endTime != 0 AND V.loID IN (?) AND V.createTime > '?' AND V.createTime < '?' ".(strlen($group) ? " GROUP BY V.userID, $group" : ' GROUP BY V.userID') . (strlen($order) ? " ORDER BY $order, V.userID " : ' ORDER BY V.userID');
					$q = $this->DBM->querySafe($sql, $los, $start, $end);
					$results = $this->DBM->getAllRows($q);
					break;
				case 80: // Question Scores Per Student
					$los = implode(',', $los);
					$select = str_replace('%', 'V.createTime', $select);
					$sql = "SELECT * FROM obo_log_qscores S WHERE S.loID IN (?) GROUP BY attemptID ORDER BY attemptID" . $limit;
					$q = $this->DBM->querySafe($sql, $los, $start, $end);
					$results = $this->DBM->getAllRows($q);
					break;
				case 90: // Content Page Views
					$los = implode(',', $los);
					$select = str_replace('%', 'createTime', $select);
					$sql = "SELECT COUNT(*) AS TOTAL_PAGE_VIEWS, SUM(IF(valueB = 1, 1, 0)) AS CONTENT_PAGE_VIEWS, SUM(IF(valueB = 2, 1, 0)) AS PRACTICE_PAGE_VIEWS,SUM(IF(valueB = 3, 1, 0)) AS ASSESSMENT_PAGE_VIEWS $select FROM obo_logs WHERE loID IN (?) AND itemType ='PageChanged' AND createTime > '?' AND createTime < '?' ".(strlen($group) ? " GROUP BY $group" : '') . (strlen($order) ? " ORDER BY $order" : '') . $limit;
					$q = $this->DBM->querySafe($sql, $los, $start, $end);
					$results = $this->DBM->getAllRows($q);
					break;
				case 100: // Question Answer Stats
					$los = implode(',', $los);
					$select = str_replace('%', 'V.createTime', $select);
					$sql = "SELECT * FROM obo_log_qscores S WHERE S.loID IN (?) GROUP BY attemptID ORDER BY attemptID" . $limit;
					$q = $this->DBM->querySafe($sql, $los, $start, $end);
					$results = $this->DBM->getAllRows($q);
					break;
				case 110:
					break;
			}
			\rocketD\util\Log::profile('analytics', "'$stat','$preview','$t','{$_SESSION['userID']}','$los','".round((microtime(true) - $t),5)."','".time()."'\n");
			return $results;
		}
		return false;
	}
	
}