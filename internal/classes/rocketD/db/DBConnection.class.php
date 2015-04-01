<?php
namespace rocketD\db;
class DBConnection
{

	public $connData;
	public $connected = false;

	public function __construct($connData)
	{
		$this->connData = $connData;
	}

	public function checkRequirements()
	{
		return true;
	}

	public function queryFailure($doRollBack=false)
	{
		// throw error, must extend this function
	}

}
