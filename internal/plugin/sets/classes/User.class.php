<?php

class plg_sets_User
{
	public $username;
	
	private $sets,
		$user_dir,
		$sets_dir;
	
	public function getSets()
	{
		$user_xml = @file_get_contents($this->user_dir.$this->username.'.xml');
		if (!$user_xml) return $this->sets;
		
		$user_xml = new SimpleXMLElement($user_xml);
		
		for($i = 0; $i < count($user_xml->set); $i++)
		{
			$this->sets[$i]  = (int)$user_xml->set[$i]['id'];
		}
		
		return $this->sets;
	}
	
	public function __construct($username, $user_dir=NULL, $sets_dir=NULL)
	{
		if ($user_dir == NULL)
		{
			$user_dir = "users/";
		}
		if ($sets_dir == NULL)
		{
			$sets_dir = "sets/";
		}
		$this->username = $username;
		$this->user_dir = $user_dir;
		$this->sets_dir = $sets_dir;
		$this->sets = array();
	}
}

?>
