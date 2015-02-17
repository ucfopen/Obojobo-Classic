<?php
namespace rocketD\db;
class DBConnectData
{

	public $key='';
	public $type='';
	public $host='';
	public $user='';
	public $pass='';
	public $db='';
	public $connID = false;

	function __construct($host, $user, $pass, $db, $type='mysql')
	{
		$this->host = $host;
		$this->user = $user;
		$this->pass = $pass;
		$this->db = $db;
		$this->type = $type;
		$this->key = md5("{$host}:{$user}:{$type}:{$db}");
	}
}
