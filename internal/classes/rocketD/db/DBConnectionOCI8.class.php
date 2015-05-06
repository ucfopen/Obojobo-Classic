<?php
namespace rocketD\db;
class DBConnectionOCI8 extends DBConnection
{

	public function db_connect($host='', $user='', $pw='', $type='oci8')
	{
		// dont connect if already connected
		if($this->connected == false)
		{
			$connID = @oci_connect($this->connData->user, $this->connData->pass, $this->connData->host);
			if($connID)
			{
				$this->connected = true;
				$this->connData->connID = $connID;
				$this->connData->host = 0;
				$this->connData->user = 0;
				$this->connData->pass = 0;
				return true;
			}
		}
		trace('could not connect to OCI8 Database');
		return false;
	}

	public function db_select($db_name='')
	{
		//@mysql_select_db($db_name);
	}

	public function parse($query)
	{
		$stid = @oci_parse($this->connData->connID, $query);
		if (!$stid)
		{
			trace($e);
			return false;
		}
		return $stid;
	}

	/**
	Can pass oracle resources returnd from parse() (oci_parse) or just string queries which will be auto-parsed
	**/

	public function query($query)
	{
		// $query is a string, not a resource returned from oci_parse
		if(!is_resource($query))
		{
			$res = $this->parse($query);
		}
		else
		{
			$res = $query;
		}
		// execute the query
		$r = @oci_execute($res, OCI_DEFAULT);
		if (!$r)
		{
			trace(oci_error($res));
			return false;
		}
		return $res;
	}
/*
	public function startTransaction(){
		$this->query('START TRANSACTION');
	}

	public function rollBack(){
		$this->query('ROLLBACK');
	}

	public function commit(){
		$this->commit;

	}
	*/

	protected function smartQuote($value)
	{
	   // Stripslashes
		if (get_magic_quotes_gpc())
		{
			$value = stripslashes($value);
		}
	   // Quote if not integer
		if (!is_numeric($value) || $value[0] == '0')
		{
			if(function_exists("mysql_real_escape_string"))
			{
				$value = mysql_real_escape_string($value);
			}
			else if(function_exists("mysql_escape_string"))
			{
				$value = mysql_escape_string($value);
			}
	   }
	   return $value;
	}

	public function querySafe($query)
	{
	  $args  = func_get_args();
	  $query = array_shift($args);
	  $query = str_replace("?", "%s", $query);
	  $args = array_map( array($this, 'smartQuote'), $args);
	  array_unshift($args, $query);
	  $query = call_user_func_array('sprintf',$args);
	  return $this->query($query);
	}

	public function affected_rows()
	{
		return @oci_num_rows($res);
	}

	public function fetch_array($res)
	{
		$row = @oci_fetch_array($res);
		if(!$row)
		{
			trace(oci_error($res));
			return false;
		}
		return $row;
	}

	public function fetch_assoc($res)
	{
		$row = @oci_fetch_assoc($res);
		if (!$row)
		{
			trace(oci_error($res));
			return false;
		}
		return $row;
	}
/*
	function fetch_num($res){
		//return mysql_num_rows($res);
	}
*/
	public function fetch_obj($res)
	{
		$row = @oci_fetch_object($res);
		if (!$row)
		{
			trace(oci_error($res));
		}
		return $row;
	}

	// quickly fetch a single row in object format
	public function qfetch_obj($query)
	{
		$q = $this->query($query);
		return $this->fetch_obj($q);
	}

	// quickly fetch a single row in assoc format
	public function qfetch_assoc($query)
	{
		$q = $this->query($query);
		return $this->fetch_assoc($q);
	}

	// quickly fetch a single row in array format
	public function qfetch_array($query)
	{
		$q = $this->query($query);
		return $this->fetch_array($query);
	}
}
