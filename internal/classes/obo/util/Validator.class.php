<?php
namespace obo\util;
class Validator extends \rocketD\util\Validator
{

	static function isScore(&$var)
	{
		return self::isInt($var) && $var <= 100 && $var >= 0;
	}

	static function isScoreMethod($var)
	{
		return in_array($var, array(\cfg_obo_Instance::SCORE_METHOD_HIGHEST, \cfg_obo_Instance::SCORE_METHOD_MEAN, \cfg_obo_Instance::SCORE_METHOD_RECENT));
	}

	static function isRoleName($var)
	{
		// use relection to get a list of all class constants
		$oClass = new \ReflectionClass('\cfg_obo_Role');
		$consts = array_values($oClass->getConstants());
		return in_array($var, $consts);
	}

	static function isUserArray(&$var)
	{
		if(is_array($var))
		{
			if(count($var) > 0)
			{
				foreach($var AS &$user)
				{
					if(!self::isPosInt($user)) return false;
				}
				return true;
			}
			return false; // do not allow empty array
		}
		$var = array();
		return false;
	}

	static function isRoleArray(&$var)
	{
		if(is_array($var))
		{
			if(count($var) > 0)
			{
				foreach($var AS &$role)
				{
					if(!self::isRoleName($role)) return false;
				}
				return true;
			}
			return false; // do not allow empty array
		}
		$var = array();
		return false;
	}

	static function isInstanceObj($instance)
	{
		if(is_array($instance))
		{
			return self::isString($instance['name']) && self::isString($instance['courseID']) &&
			self::isInt($instance['startTime']) && self::isInt($instance['endTime']) &&
			self::isPosInt($instance['attemptCount']) && self::isPosInt($instance['instID'], true);
		}
		return false;
	}

	static function isPermObj($perm)
	{
		if(is_array($perm))
		{
			return self::isInt($perm['userID'], true) && self::isOneOrZero($perm['read']) &&
			self::isOneOrZero($perm['write']) && self::isOneOrZero($perm['copy']) &&
			self::isOneOrZero($perm['publish']) && self::isOneOrZero($perm['giveRead']) &&
			self::isOneOrZero($perm['giveWrite']) && self::isOneOrZero($perm['giveCopy']) &&
			self::isOneOrZero($perm['givePublish']) && self::isOneOrZero($perm['giveGlobal']);
		}
		return false;
	}

	static function isItemType($itemType)
	{
		return (bool) preg_match("/^[lqmi]$/i", $itemType);
	}

	static function isFeedbackType($feedbackType)
	{
		return (bool) preg_match("/^[fbr]$/i", $feedbackType);
	}

	static function isPerm($perm)
	{
		return self::isString($perm) && ($perm == 'read' || $perm == 'write' || $perm == 'copy' || $perm == 'publish' ||
		$perm == 'giveRead' || $perm == 'giveWrite' || $perm == 'giveCopy' || $perm == 'givePublish' || $perm == 'giveGlobal');
	}

	static function isSection($section)
	{
		return self::isInt($section) && $section >= 0 && $section <= 3;
	}

	/* @author: Zachary Berry */
	static function isClientType($clientType)
	{
		return (bool) preg_match("/^[rcv]$/i", $clientType);
	}

	static function isMediaProperty($property)
	{
		switch($property)
		{
			case 'size':
			case 'length':
			case 'height':
			case 'width':
			case 'version':
			case 'thumb':
			case 'copyright':
			case 'desc':
			case 'itemType':
				return true;
				break;
			default:
				return false;
		}
	}
}
