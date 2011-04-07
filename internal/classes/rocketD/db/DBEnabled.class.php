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
	
	public function db_serialize($obj)
	{
		return  base64_encode(serialize($obj));
	}
	
	public function db_unserialize($obj)
	{
		// TODO: get rid of this - its a temporary crutch to bypass actually updating the database
		$data = base64_decode($obj);
		$data = preg_replace('/9:"nm_los_LO/', '9:"obo\lo\LO', $data);
		$data = preg_replace('/20:"nm_los_QuestionGroup/', '20:"obo\lo\QuestionGroup', $data);
		
		$data = preg_replace('/13:"nm_los_Answer/', '13:"obo\lo\Answer', $data);
		$data = preg_replace('/15:"nm_los_Question/', '15:"obo\lo\Question', $data);
		$data = preg_replace('/11:"nm_los_Page/', '11:"obo\lo\Page', $data);
		$data = preg_replace('/15:"nm_los_PageItem/', '15:"obo\lo\PageItem', $data);
		$data = preg_replace('/12:"nm_los_Media/', '12:"obo\lo\Media', $data);
		return unserialize($data);
		//return unserialize(base64_decode($obj));
	}
}
?>