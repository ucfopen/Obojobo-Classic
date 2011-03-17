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

	public function getLOStat($los, $stat, $start, $end)
	{
		switch($stat)
		{
			case 1: // instances created
				$sql = "SELECT COUNT(InstID) AS num, DAY(FROM_UNIXTIME(createTime)) AS day, MONTH(FROM_UNIXTIME(createTime)) AS month, YEAR(FROM_UNIXTIME(createTime)) AS year FROM obo_lo_instances WHERE createTime > '?' AND createTime < '?' GROUP BY year, month, day ORDER BY year, month, day";
				$q = $this->DBM->querySafe($sql, $start, $end);
				$results = $this->DBM->getAllRows($q);
				return $results;
				break;
			case 2:
				break;
			case 3:
				break;
			case 4:
				break;
			case 5:
				break;	
			case 6:
				break;
			case 7:
				break;
			case 8:
				break;
			case 9:
				break;
			case 10:
				break;
		}
		return array($los, $stat,$start,$end);
	}
	
}