<?php
/**
 * This script returns a media file from the server
 * It takes one parameter 'id', which is the id number of a piece of media.
 */
$time = microtime(true);
require_once(dirname(__FILE__)."/../internal/app.php");
$lor = \obo\API::getInstance();

$verifyReturn = $lor->getSessionValid();

if($verifyReturn)// verify login
{
	$uid = $_SESSION['userID'];
	$mediaID = $_GET['id'];
	$roleMan = \obo\perms\RoleManager::getInstance();
	if(true || $roleMan->isSuperUser() || $roleMan->isSuperViewer())
	{
		
        $DBM = \rocketD\db\DBManager::getConnection(new \rocketD\db\dbConnectData(\AppCfg::DB_HOST, \AppCfg::DB_USER, \AppCfg::DB_PASS, \AppCfg::DB_NAME, \AppCfg::DB_TYPE));
		$q = $DBM->querySafe("SELECT ".\cfg_obo_Media::ID.", ".\cfg_obo_Media::URL.", ".\cfg_obo_Media::SIZE.", ".\cfg_obo_Media::TIME."  FROM ".\cfg_obo_Media::TABLE." WHERE ".\cfg_obo_Media::ID."='?' LIMIT 1", $mediaID);
		if(($r = $DBM->fetch_obj($q)))
		{
		   $file = \AppCfg::DIR_BASE.\AppCfg::DIR_MEDIA.$r->{\cfg_obo_Media::URL};
		   $fileinfo = pathinfo($file);
           $file = \AppCfg::DIR_BASE.\AppCfg::DIR_MEDIA.$r->{\cfg_obo_Media::ID} . "." .strtolower($fileinfo['extension']);
		}
	    else{
			trace('media file not found: '.$file);
	        header('HTTP/1.0 404 Not Found');
		}
	}
	else
        header('HTTP/1.0 404 Not Found');

	if(file_exists($file))
	{
		$file_info = pathinfo($file);
		$ext = $file_info['extension'];
		$ext = strtolower($ext);
		switch($ext)
		{
			case 'jpg':
			case 'jpeg':
			case 'png':
			case 'gif':
				$MimeType = "image/$ext";
				break;
			/*case 'xml':
				$MimeType = 'application/xml';
				break;*/
			case 'swf':
				$MimeType = 'application/x-shockwave-flash';
				break;
			case 'flv':
				$MimeType = 'video/x-flv';
				break;
		}
		session_write_close();
		header('Content-Type: '.$MimeType,true);
        header('Content-Disposition: inline; filename="' . $r->{\cfg_obo_Media::URL} . '"'); 
		//header('Cache-Control: max-age=172800', true); //Adjust maxage appropriately
        header('Last-Modified: ' . date('D, d M Y H:i:s \G\M\T' , $r->{\cfg_obo_Media::TIME}));
        header('Expires: ' . date('D, d M Y H:i:s \G\M\T' , (time() + 31536000)));
		header('Pragma: public', true);
		header('Content-Length: '. ($r->{\cfg_obo_Media::SIZE} > 0 ? $r->{\cfg_obo_Media::SIZE} : filesize($file)) . "\r\n");
		// track file requests
		if(isset($_SESSION['INSTANCE_ID']) && $_SESSION['INSTANCE_ID'] > 0)
		{
			$trackingMan = \obo\log\LogManager::getInstance();
			$trackingMan->trackMediaRequested($mediaID);
		}
		
		if($fd = fopen($file, 'rb'))
		{
			$_timeLimit = 0;
			while (true)
			{
		    	$bits = fread($fd, 65535);
			    if (strlen($bits) == 0)
				{
					break;
			    }
			    print $bits;
				flush();
				$limit = 30;
				$now = time();
				if (empty($_timeLimit) || ($_timeLimit - $now < $limit))
				{
				    /* Make sure that we extend at least a minimum of 30 seconds */
				    $_timeLimit = $now + max($limit, 30);
				    @set_time_limit($_timeLimit - $now);
				}
			}
			fclose($fd);
	    }		
		if(isset($_SESSION['INSTANCE_ID']) && $_SESSION['INSTANCE_ID'] > 0)
		{
			$trackingMan->trackMediaRequestCompleted($mediaID);
		}
		exit();
	}
	else{
		trace('file doesnt exist: '.$file);
		header('HTTP/1.0 404 Not Found');
	} 

}
else header('HTTP/1.0 404 Not Found');
?>