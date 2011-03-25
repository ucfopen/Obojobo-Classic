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

	public function getLOStat($los, $stat, $start, $end, $resolution)
	{
		
		$year = "YEAR(FROM_UNIXTIME(%)) AS year";
		$month = "MONTH(FROM_UNIXTIME(%)) AS month";
		$day = "DAY(FROM_UNIXTIME(%)) AS day";
		$hour = "HOUR(FROM_UNIXTIME(%)) AS hour";
		
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

		
		switch($stat)
		{
			case 1: // instances created
				$los = implode(',', $los);
				$select = str_replace('%', 'createTime', $select);
				$sql = "SELECT COUNT(InstID) AS INSTANCES, COUNT(DISTINCT userID) AS OWNERS $select FROM obo_lo_instances WHERE loID IN (?) AND createTime > '?' AND createTime < '?' ".(strlen($group) ? " GROUP BY $group" : '') . (strlen($order) ? " ORDER BY $order" : '');
				$q = $this->DBM->querySafe($sql, $los, $start, $end);
				$results = $this->DBM->getAllRows($q);
				return $results;
				break;
			case 2: // student views
				$select = str_replace('%', 'createTime', $select);
				$IM = \obo\lo\InstanceManager::getInstance();
				$instIDs = array();
				$instances = $IM->getInstancesFromLOID($los);
				foreach($instances AS $inst)
				{
					$instIDs[] = $inst->instID;
				}
				// trace($instIDs);
				$sql = "SELECT COUNT(createTime) AS VISITS, COUNT(DISTINCT userID) AS VISITORS $select FROM obo_logs WHERE createTime > '?' AND createTime < '?' AND itemType = 'Visited' AND instID IN (?) ".(strlen($group) ? " GROUP BY $group" : '') . (strlen($order) ? " ORDER BY $order" : '');
				$q = $this->DBM->querySafe($sql,  $start, $end, implode(',', $instIDs));
				$results = $this->DBM->getAllRows($q);
				return $results;
				break;
			case 3: // Derrivatives Created
				break;
			case 4: // Assessments Completed
				$los = implode(',', $los);
				$select = str_replace('%', 'endTime', $select);
				$sql = "SELECT COUNT(DISTINCT A.attemptID) AS COMPLETED_ASSESSMENTS, COUNT(DISTINCT A.userID) AS USERS $select FROM obo_log_attempts AS A JOIN obo_los AS O ON  O.aGroupID = A.qGroupID WHERE endTime > '?' AND endTime < '?' AND A.loID IN (?) AND endTime !='0' ".(strlen($group) ? " GROUP BY $group" : '') . (strlen($order) ? " ORDER BY $order" : '');
				$q = $this->DBM->querySafe($sql, $start, $end, $los);
				$results = $this->DBM->getAllRows($q);
				return $results;
				break;
			case 5: // Who created instances of these los
				$los = implode(',', $los);
				$select = str_replace('%', 'I.createTime', $select);
				$sql = "SELECT U.last AS LAST, U.first AS First, U.email AS EMAIL, COUNT(I.InstID) AS INSTANCES $select FROM obo_lo_instances AS I JOIN obo_users AS U ON U.userID = I.userID WHERE I.loID IN (?) AND I.createTime > '?' AND I.createTime < '?' ".(strlen($group) ? " GROUP BY I.userID, $group" : ' GROUP BY I.userID') . (strlen($order) ? " ORDER BY $order, I.userID " : ' ORDER BY I.userID');
				$q = $this->DBM->querySafe($sql, $los, $start, $end);
				$results = $this->DBM->getAllRows($q);
				return $results;
				break;	
			case 6: // Which Courses are instances used in
				$los = implode(',', $los);
				$select = str_replace('%', 'I.createTime', $select);
				$sql = "SELECT I.courseName, IF(MAX(GC.sectionID), MAX(GC.sectionID), 'none') AS WEBCOURSES_SECTION, Count(I.courseName) AS COUNT $select FROM obo_lo_instances AS I LEFT JOIN plg_wc_grade_columns AS GC ON GC.instID = I.instID  WHERE I.loID IN (?) AND I.createTime > '?' AND I.createTime < '?' ".(strlen($group) ? " GROUP BY I.courseName, $group" : ' GROUP BY I.courseName') . (strlen($order) ? " ORDER BY $order, I.courseName" : ' ORDER BY I.courseName');
				$q = $this->DBM->querySafe($sql, $los, $start, $end);
				$results = $this->DBM->getAllRows($q);
				return $results;
				break;
			case 7:
				break;
			case 8:
				$IM = \obo\lo\InstanceManager::getInstance();
				$instIDs = array();
				$instances = $IM->getInstancesFromLOID($los);
				foreach($instances AS $inst)
				{
					$instIDs[] = $inst->instID;
				}
				$select = str_replace('%', 'createTime', $select);
				$sql = "SELECT COUNT(*) AS TOTAL_PAGE_VIEWS $select FROM obo_logs WHERE itemType ='PageChanged' AND createTime > '?' AND createTime < '?' AND instID IN (?) ".(strlen($group) ? " GROUP BY $group" : '') . (strlen($order) ? " ORDER BY $order" : '');
				trace($sql);
				$q = $this->DBM->querySafe($sql, $start, $end, implode(',', $instIDs));
				$results = $this->DBM->getAllRows($q);
				return $results;
				break;
			case 9:
				break;
			case 10:
				break;
		}
		return array($los, $stat,$start,$end);
	}
	
}