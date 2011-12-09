<?php
namespace rocketD\util;
class Log
{
	
	public static function trace($traceText, $force=false, $increaseBackTraceIndex=0)
	{
		
		if($force || \AppCfg::DEBUG_MODE)
		{
			@$dt = debug_backtrace();
			// if traceText is an object, print_r it
			if(is_object($traceText) || is_array($traceText))
			{
				$traceText = print_r($traceText, true);
			}
			
			if(is_array($dt))
			{
				$len = count($dt);
				if($len > 1)
				{
					// called using global trace function from app.php
					if(basename($dt[0]['file']) == 'app.php')
					{
						if($len > 2) // called from a class file
						{
							@self::writeLog($dt[1+$increaseBackTraceIndex]['class'].'->'.$dt[1+$increaseBackTraceIndex]['function'].'#'.$dt[0+$increaseBackTraceIndex]['line'].': '.$traceText, false);
						}
						else // called from a script
						{
							self::writeLog(basename($dt[1]['file']).'#'.$dt[1]['line'].': '.$traceText, false);
						}
						return; // exit here if either of these methods wrote to the log
					}
				}
			}
			// couldnt get backtrace, just export what we have
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
	
	private static function writeLog($output, $fileName=false)
	{	
		try
		{
			// create the log directory if it doesnt exist
			if(!file_exists(\AppCfg::DIR_BASE.\AppCfg::DIR_LOGS))
			{
				mkdir(\AppCfg::DIR_BASE.\AppCfg::DIR_LOGS, 0770, true);
			}
			if($fileName)
			{
				$f = \AppCfg::DIR_BASE.\AppCfg::DIR_LOGS.$fileName.date('m_d_y', time()) .'.txt';
				$isNewFile = !file_exists($f);
				$fh = fopen($f, 'a');
				if($fh)
				{
					fwrite($fh, $output);
					fclose($fh);
					// if this is new, make sure its group writable
					if($isNewFile)
					{
						chmod($f, 0664);
					}
				}
			}
			else
			{
				error_log($output);
			}
		}
		catch(Exception $e)
		{
			@error_log($e);
		}
	}
	
	public static function profile($key, $append)
	{
		// DISABLED FOR LATER THOUGHT
		// Its problematic not to write logs, but writing to the disk so much is slow... maybe a smaller cache
		// or cache it per request and write it when the scrip dies?
		// if(\AppCfg::CACHE_MEMCACHE)
		// {
		// 	$log = Cache::getInstance()->get($key);
		// 	if(strlen($log) < 100000)
		// 	{
		// 		$append = $log . $append; // append if log is less then 100k chars
		// 	}
		// 	else
		// 	{
		// 		self::dumpProfile($key, $log); // dump stored log to file
		// 	}
		// 	Cache::getInstance()->set($key, $append, false, 0);
		// }
		// else
		// {
		// 	self::dumpProfile($key, $append);
		// }
		
		self::dumpProfile($key, $append);
	}
	
	public static function dumpProfile($key, &$value=false)
	{
		if($value == false)
		{
			
			$value = Cache::getInstance()->get($key);
		}
		self::writeLog($value, 'profile_'.$key);
	}

}
?>
