<?php
namespace rocketD\db;
class DBEnabled
{
	public $DBM;

	public function __construct()
	{
		$this->defaultDBM();
	}	
	
	protected function defaultDBM()
	{
		// use this to set a default DBM class within this object, 
		if(!$this->DBM) // if DBM isnt set use the default
		{
			$con = new DBConnectData(\AppCfg::DB_HOST, \AppCfg::DB_USER, \AppCfg::DB_PASS, \AppCfg::DB_NAME, \AppCfg::DB_TYPE);
			$this->DBM = DBManager::getConnection($con);
		}
	}
	
	protected function db_serialize($obj)
	{
		return  base64_encode(serialize($obj));
	}

	protected function db_unserialize($obj)
	{
		return unserialize(base64_decode($obj));
	}
}
?>