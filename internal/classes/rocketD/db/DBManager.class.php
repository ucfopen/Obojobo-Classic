<?php
namespace rocketD\db;

class DBManager
{
	static $instance;
	private $transactionNestLevel = 0;
	private $transactionOnRollBack = false;
	private $connections = array();

	function __construct($conData = false)
	{

		if($conData != false)
		{
			// $conData can be a string name of the class, or an object ref to a dbconndata class
			$conDataType = gettype($conData);
			switch($conDataType)
			{
				case 'string':
					$this->loadConnectData($conData);
					break;
				case 'object':
					$this->loadConnectObj($conData);
					break;
			}
		}
	}

	static public function getInstance($conData = false)
	{
		if(!isset(self::$instance))
		{
			$selfClass = __CLASS__;
			self::$instance = new $selfClass($conData);
		}
		return self::$instance;
	}

	static function getConnection($conData = false)
	{
		$DBM = self::getInstance();
		if($conData != false)
		{
			// $conData can be a string name of the class, or an object ref to a dbconndata class
			$conDataType = gettype($conData);
			switch($conDataType)
			{
				case 'string':
					$conn = $DBM->loadConnectData($conData);
					break;

				case 'object':
					$conn = $DBM->loadConnectObj($conData);
					break;

				default:
					$conn = false;
			}
			return $conn;
		}
	}

	// load a connection object by passing the class name
	public function loadConnectData($conData)
	{
		$dbcd = new $conData();
		return $this->loadConnectObj($dbcd);
	}

	// load a connection object by passing a DBConnectData object reference
	public function loadConnectObj($conObj)
	{
		if($conn = $this->locateConnection($conObj))
		{
			return $conn;
		}
		else
		{
			return $this->createNewConnection($conObj);
		}
	}

	protected function locateConnection($conObj)
	{
		// look for connection already made
		if(count($this->connections) > 0 )
		{
			if ( ! empty($this->connections[$conObj->key]))
			{
				return $this->connections[$conObj->key];
			}
		}
		return false;
	}

	protected function createNewConnection($conObj)
	{
		switch($conObj->type)
		{
			case 'oci8':
				$newConn = new DBConnectionOCI8($conObj);
				if($newConn->checkRequirements() )
				{
					if(!$newConn->db_connect())
					{
						trace('couldnt connect to oci8', true);
					}
				}
				break;

			case 'mysql':
				$newConn = new DBConnectionMYSQL($conObj);
				if($newConn->checkRequirements())
				{
					if(!$newConn->db_connect())
					{
						trace('couldnt connect to mySQL', true);
					}
				}
				break;
			default :
				trace('DBManager4 createNewConnection call, no matching connection type defined.', true);
				break;
		}

		if($newConn)
		{
			$this->connections[$conObj->key] = $newConn;
			return $newConn;
		}

		return false;
	}
}
