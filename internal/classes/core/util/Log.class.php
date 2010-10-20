<?php
class core_util_Log
{
	
	public static function trace($traceText, $force=false, $increaseBackTraceIndex=0)
	{
		
		if($force || AppCfg::DEBUG_MODE)
		{
			@$dt = debug_backtrace();
			if(count($dt) > 1)
			{
				if(is_object($traceText) || is_array($traceText))
				{
					self::writeLog($dt[1+$increaseBackTraceIndex]['class'].'->'.$dt[1+$increaseBackTraceIndex]['function'].'#'.$dt[$increaseBackTraceIndex]['line'].' printr: '. print_r($traceText, true));
				}
				else
				{
					self::writeLog($dt[1+$increaseBackTraceIndex]['class'].'->'.$dt[1+$increaseBackTraceIndex]['function'].'#'.$dt[$increaseBackTraceIndex]['line'].': '.$traceText);
				}
			}
			else
			{
				if(is_object($traceText) || is_array($traceText))
				{
					self::writeLog('printr: ' .print_r($traceText, true));
				}
				else
				{
					self::writeLog('trace: ' .$traceText);
				}
				
			}
		}
	}
	
	private static function writeLog($output, $fileName=false)
	{		
		if($fileName)
		{
			$f = AppCfg::DIR_BASE.AppCfg::DIR_LOGS.$fileName.date('m_d_y', time()) .'.txt';
			$fh = fopen($f, 'a');
			fwrite($fh, $output);
			fclose($fh);
		}
		else
		{
			@error_log($output);
		}
	}
	
	public static function profile($key, $append)
	{
		if(AppCfg::CACHE_MEMCACHE)
		{
			$log = core_util_Cache::getInstance()->get($key);
			if(strlen($log) < 100000)
			{
				$append = $log . $append; // append if log is less then 100k chars
			}
			else
			{
				self::dumpProfile($key, $log); // dump stored log to file
			}
			core_util_Cache::getInstance()->set($key, $append, false, 0);
		}
		else
		{
			self::dumpProfile($key, $append);
		}
	}
	
	public static function dumpProfile($key, &$value=false)
	{
		if($value == false)
		{
			
			$value = core_util_Cache::getInstance()->get($key);
		}
		self::writeLog($value, 'profile_'.$key);
	}

}
?>