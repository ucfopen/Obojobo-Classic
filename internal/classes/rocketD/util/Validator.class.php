<?php
namespace rocketD\util;
class Validator
{
    /**
     * Checks to see if $var is a whole integer this includes negative numbers.
     * 23 		-> true
     * 23.5 	-> false
     * -2		-> true
     * "23" 	-> true
     * "23a"	-> false
     * NULL		-> false
     * ""		-> false
     * "-2"		-> true
     * 0		-> true
     *
     * @param mixed $var
     * @return true if $var is an whole integer
     * @return false if $var is not an whole integer
     */
    static function isInt(&$var)
    {
        if(!is_numeric($var))
		{
			return false;
		}
		$var = floatval($var);
		return $var == floor($var);
	}

	static function isUserArr($user)
	{
		return self::isInt($user['userID']) && self::isString($user['login']) && self::isString($user['first']) && self::isString($user['last']) && self::isEmail($user['email']);
	}

    /**
     * Checks to see if $var is a positive whole integer.
     * 23 		-> true
     * 23.5 	-> false
     * -2		-> false
     * "23" 	-> true
     * "23a"	-> false
     * NULL		-> false
     * ""		-> false
     * "-2"		-> false
     * 0		-> false
     *
     * @param mixed $var
     * @return true if $var is an positve whole integer
     * @return false if $var is not an whole integer or it is negative
     */
    static function isPosInt(&$var, $zero = false)
    {
		return self::isInt($var) && ($zero ? $var >= 0: $var > 0);
    }

	static function isBoolean($var)
	{
		return $var === true || $var === false;
	}

	static function isPermItemType($type)
	{
		return $type == \cfg_core_Perm::TYPE_INSTANCE || $type == \cfg_core_Perm::TYPE_LO || $type == \cfg_core_Perm::ßTYPE_MEDIA;
	}

	static function isPerm2($perm)
	{
		// use relection to get a list of all class constants
		$oClass = new \ReflectionClass('\cfg_core_Perm');
		$consts = array_values($oClass->getConstants());
		return in_array($perm, $consts);
	}

	static function isString($var)
	{
		return is_string($var) && strlen($var) > 0;
	}

	static function isMD5($var)
	{
		return (bool) preg_match("/^[[:alnum:]]{32}$/i", $var);
	}

	static function isSHA1($var)
	{
		return (bool) preg_match("/^[[:alnum:]]{40}$/i", $var);
	}

	static function isOneOrZero($num)
	{
	    return self::isInt($num) && ($num == 0 || $num == 1);
	}

	static function isEmail($email)
	{
	    return self::isString($email);
	}

	static function isPhoneNumber($phoneNum)
	{
	    return self::isString($phoneNum);
	}
}
