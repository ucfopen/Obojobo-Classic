<?php
class core_db_DBManager
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
	
	static function getInstance($conData = false)
	{
		if(self::$instance == NULL)
		{
			self::$instance = new core_db_DBManager($conData);
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
					return $DBM->loadConnectData($conData);	
					break;
				case 'object':
					return $DBM->loadConnectObj($conData);
					break;
			}
		}
		
	}

	// load a connection object by passing the class name
	public function loadConnectData($conData)
	{
		$dbcd = new $conData();
		return $this->loadConnectObj($dbcd);
	}
	
	// load a connection object by passing a dbConnectData object reference
	public function loadConnectObj($conObj)
	{
		if($conn = $this->locateConnection($conObj->host, $conObj->user, $conObj->pass, $conObj->type, $conObj->db))
		{
			return $conn;
		}
		else
		{
			return $this->createNewConnection($conObj);
		}
	}

	protected function locateConnection($host, $user, $pw, $type, $db='')
	{
		// look for connection already made
		if(count($this->connections) > 0 )
		{
			foreach($this->connections AS $conn)
			{
				if($conn->connData->type == $type && $conn->connData->user == $user && $conn->connData->pass == $pw && $conn->connData->db == $db)
				{
					return $conn;
				}
			}
		}
		return false;
	}

	protected function createNewConnection($conObj)
	{
		switch($conObj->type)
		{
			case 'oci8':
				$newConn = new core_db_DBConnectionOCI8($conObj);
				if($newConn->checkRequirements() )
				{
					if(!$newConn->db_connect())
					{
						trace('couldnt connect to oci8', true);
					}
				}
				break;
				
			case 'mysql':
				$newConn = new core_db_DBConnectionMYSQL($conObj);
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
		
		if($newConn){
			$this->connections[] = $newConn;

			return $newConn;
		}
		
		return false;
	}
}
?>