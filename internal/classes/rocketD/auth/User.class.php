<?php
namespace rocketD\auth;
/**
 * This class defines the User data type
 * @author Jacob Bates <jbates@mail.ucf.edu>
 */

/**
 * This class defines the User data type
 * It is used simply for representing data in memory, and has no methods.
 */
class User
{
	public $userID;			//Number: database id of user
	public $login;			//String: name used at a login screen
	public $first;			//String: firstt name
	public $last;			//String: last name
	public $mi;				//String: middle initial (1 char, optional)
	public $email;			//String: email address
	public $createTime;	//Unix Timestamp
	public $lastLogin;		//Unix Timestamp
	
	public function __construct($userID=0, $login='', $first='', $last='', $mi='', $email='', $createTime=0, $lastLogin=0)
	{
        if(func_num_args() == 1)
        {
       		$usrObj = func_get_arg(0);
			$this->userID = $usrObj['userID'];
			$this->first = $usrObj['first'];
			$this->last = $usrObj['last'];
			$this->mi = $usrObj['mi'];
			$this->email = $usrObj['email'];
        }
        else
        {
        	$this->userID = $userID;
			$this->login = $login;
			$this->first = $first;
			$this->last = $last;
			$this->mi = $mi;
			$this->email = $email;
			$this->createTime = $createTime;
			$this->lastLogin = $lastLogin;
        	
        }
	}
}
?>