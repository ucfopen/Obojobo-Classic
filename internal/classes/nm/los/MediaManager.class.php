<?php
/**
 * This class contains all the logic having to do with media
 * @author Jacob Bates <jbates@mail.ucf.edu>
 * @author Luis Estrada <lestrada@mail.ucf.edu>
 */

/**
 * This class contains all the logic having to do with media
 * This includes creating, retrieving, and deleting of data.
 */
class nm_los_MediaManager extends core_db_dbEnabled
{
	private static $instance;
	
	function __construct()
	{
	  $this->defaultDBM();
	}

	static public function getInstance()
	{
		if(!isset(self::$instance))
		{
			$selfClass = __CLASS__;
			self::$instance = new $selfClass();
		}
		return self::$instance;
	}
		
	/**
	 * Gets the full data for an existing media object
	 * @param $mid (number) media id
	 * @return (Media) full media object (includes URL)
	 * @return (bool) False if error or no login
	 */
	public function getMedia($mid = 0)
	{
	    if(!is_numeric($mid) || $mid < 1)
		{
			return false;
		}
		
		
		if($media = core_util_Cache::getInstance()->getMedia($mid))
		{
			return $media;
		}

		if(!($q = $this->DBM->querySafe("SELECT * FROM ".cfg_obo_Media::TABLE." WHERE ".cfg_obo_Media::ID."='?' LIMIT 1", $mid)))
		{
            $this->DBM->rollback();
			return false;
		}
		
		$r = $this->DBM->fetch_obj($q);

		$media = new nm_los_Media($r->{cfg_obo_Media::ID}, $r->{cfg_core_User::ID}, $r->{cfg_obo_Media::TITLE}, $r->{cfg_obo_Media::TYPE}, $r->{cfg_obo_Media::DESC}, $r->{cfg_obo_Media::TIME}, $r->{cfg_obo_Media::COPYRIGHT}, $r->{cfg_obo_Media::THUMB}, $r->{cfg_obo_Media::URL}, $r->{cfg_obo_Media::SIZE}, $r->{cfg_obo_Media::LENGTH}, 0, 0, $r->{cfg_obo_Media::WIDTH}, $r->{cfg_obo_Media::HEIGHT}, $r->{cfg_obo_Media::VER});
		core_util_Cache::getInstance()->setMedia($media);
		return $media;
	}

	/**
	 * Gets all media for the currently logged in user
	 * @return (Array<Media>) array of minimum media objects (does not include URL)
	 * @return (bool) False if error or no login
	 * @deprecated This function is no longer used Use getReadableMedia instead
	 */
	public function getAllMedia($optMediaIDArray=false)
	{
		if(is_array($optMediaIDArray))
		{
			foreach($optMediaIDArray AS $eachMediaID)
			{
				if(!nm_los_Validator::isPosInt($eachMediaID))
				{
					
			       
			        return core_util_Error::getError(2);
				}
			}
			// force getLO on an array to use meta
			$mediaArr = $optMediaIDArray;
		}

		$result = array();
		$result = $this->getMediaWithPerm('read');
		if(count($mediaArr) > 0) // remove unwanted items
		{
			$result2 = array();
			foreach($result AS $media)
			{
				if(in_array($media->mediaID, $mediaArr))
				{
					$result2[] = $media;
				}
			}
			$result = $result2;
		}

		return $result;
	}

	/** 
	 * Gets list of all Objects the user has a certain permission for
	 * @param $userID (number) user id
	 * @param $perm (string) permission (values: 'read', 'write', 'copy', 'publish', 'giveRead', 'giveWrite', 'giveCopy', 'givePublish', 'giveGlobal')
	 * @return (Array<Media>) an array of metadata learning objects
	 */
	public function getMediaWithPerm($perm)
	{
		$permMan = nm_los_PermissionsManager::getInstance();
		$mediaIDs = $permMan->getItemsWithPerm(cfg_obo_Perm::TYPE_MEDIA, $perm);
		
		$mediaArr = array();
		if(count($mediaIDs) > 0)
		{
			foreach($mediaIDs AS $mediaID)
			{
				if($media = $this->getMedia($mediaID)){
					$mediaArr[] = $media;
				}
			}
		}
		return $mediaArr;
	}
	
	/**
	 * Creates a new media object in the database
	 * @param $mediaObj (Media) media object
	 * @param $file (bool) FALSE if the call is coming from remoting, TRUE if coming from php.
	 * @return (Media) full media object (including new ID)
	 * @return (bool) False if error
	 */
	// TODO: FIX RETURN FOR DB ABSTRACTION
	public function newMedia($media)
	{
		
	    if(! $media instanceof nm_los_Media)
		{
			
			
			return core_util_Error::getError(2);
		}
		if( $media->mediaID > 0)
		{
			
			
			return core_util_Error::getError(2);
		}
		
		// if auth is set to someone that isnt you, you have to be super user
		if(!nm_los_Validator::isPosInt($media->auth) || $media->auth == $_SESSION['userID'])
		{
			$media->auth = $_SESSION['userID'];
		}
		else
		{
			// to make media for someone else, you must be su
			
			$roleMan = nm_los_RoleManager::getInstance();
			if(!$roleMan->isSuperUser())
			{
				
				
				return core_util_Error::getError(2);
			}
		}
		
		if( ! nm_los_Validator::isString($media->title) )
		{
			
			
			return core_util_Error::getError(2);
		}
		
		if( ! nm_los_Validator::isString($media->itemType) )
		{
			
			
			return core_util_Error::getError(2);
		}
		
		// if( ! is_bool($media->scorable) )
		// {
		// 	
		// 	
		// 	return core_util_Error::getError(2);
		// }
		
		if( ! nm_los_Validator::isString($media->copyright) )
		{
			
			
			return core_util_Error::getError(2);
		}
	    
		$media->createTime = time();
		$qstr = "INSERT INTO ".cfg_obo_Media::TABLE." 
			SET 
				".cfg_core_User::ID."='?', 
				".cfg_obo_Media::TITLE."='?', 
				".cfg_obo_Media::TYPE."='?',
				".cfg_obo_Media::SCORABLE."='?', 
				`".cfg_obo_Media::DESC."`='?', 
				".cfg_obo_Media::URL."='?', 
				".cfg_obo_Media::TIME."='?',
				".cfg_obo_Media::COPYRIGHT."='?',
				".cfg_obo_Media::THUMB."='?',
				".cfg_obo_Media::SIZE."='?',
				".cfg_obo_Media::LENGTH."='?',
				".cfg_obo_Media::HEIGHT."='?',
				".cfg_obo_Media::WIDTH."='?',
				".cfg_obo_Media::VER."='?'";
		
		if( !($q = $this->DBM->querySafe($qstr, $media->auth, $media->title, $media->itemType,
		$media->scorable, $media->descText, $media->url, $media->createTime , $media->copyright, $media->thumb, 
		$media->size, $media->length, $media->height, $media->width, $media->version)) )
		{
		    $this->DBM->rollback();
			return false;
		}
		
		$media->mediaID = $this->DBM->insertID;
		
		
		// uploaded asset types
		if(in_array($media->itemType, array('pic', 'swf', 'flv', 'mp3')))
		{
			$baseName = basename($media->url);
			$lastDot = strrpos($baseName, '.');
		    $fileName = substr($baseName, 0, $lastDot);
		    $extension = strtolower(substr($baseName, $lastDot+1));
	    
		    $hashedFile = AppCfg::DIR_BASE.AppCfg::DIR_MEDIA.md5($fileName);
			if(file_exists($hashedFile))
			{
	            rename($hashedFile, AppCfg::DIR_BASE.AppCfg::DIR_MEDIA.$media->mediaID.".".$extension);
			}
			else
			{
				core_util_Error::getError(0);
				return false;
			}
		}
		
		$media->perms = new nm_los_Permissions($media->auth, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0);
		
		//Add owner permissions to this object for this user
		$permman = nm_los_PermissionsManager::getInstance();
		$permman->setNewUserPerms($media->mediaID, cfg_obo_Perm::TYPE_MEDIA, $media->perms);
		
		
		core_util_Cache::getInstance()->setMedia($media);
		
		return $media;
	}
	
	public function getMediaType($extension)
	{
		switch($extension){
			case 'jpg':
			case 'jpeg':
			case 'png':
			case 'gif':
				return 'pic';
			case 'swf':
				return 'swf';
			case 'flv':
				return 'flv';
			case 'mp3':
				return 'mp3';
			default:
				trace('unknown file type '.$fileName.' uploaded and no action taken', true);
				return false; //This will represent an invalid file type.
		}
	}
	
	public function handleMediaUpload($fileData, $title, $description, $copyright, $length=0)
	{
		// TODO:
		//Make sure user is logged in, file is less than or equal to max size, and a title has been sent
		$roleMan = nm_los_RoleManager::getInstance();
		if(!$roleMan->isLibraryUser())
		{
			
			
			core_util_Error::getError(4);
			return false;
		}

		$baseName = basename($fileData['name']);
		$lastDot = strrpos($baseName, '.');
		$fileName = substr($baseName, 0, $lastDot);
		$extension = strtolower(substr($baseName, $lastDot+1));
		$fileNameArr = explode('.', basename($fileData['name']));

		if( !($fileType = $this->getMediaType($extension)) )
		{
			return false; // signifys failure to the upload scripts
		}
		
		$newFileLocation = AppCfg::DIR_BASE.AppCfg::DIR_MEDIA . md5($fileName);
		
		if($config->isUnitTest)
		{
			// in test, just move the test file
			copy($fileData['tmp_name'], $newFileLocation);
		}
		else
		{
			move_uploaded_file($fileData['tmp_name'], $newFileLocation);
		}
		
		if(file_exists($newFileLocation))
		{
			$media = new nm_los_Media();
			// get swf dimensions and size
			if($fileType == 'swf')
			{
				$swf = new nm_los_media_SWF();
				$swf->getDimensions($newFileLocation);
				$media->width = $swf->width;
				$media->height = $swf->height;
				$media->version = $swf->version;
			}
			// get the image dimensions
			else if($fileType == 'pic')
			{
				$data = getimagesize($newFileLocation);
				$media->width = $data[0];
				$media->height = $data[1];
			}
			@chmod($testName, 0755);
			
			$media->title = $title;
			$media->auth = $_SESSION['userID'];
			$media->descText = $description;
			$media->copyright = $copyright;
			$media->itemType = $fileType;
			$media->thumb = 0;
			$media->url = $fileName.".".$extension; // same as basename?
			$media->size = $fileData['size'];
			$media->length = $length;
			$lor = nm_los_API::getInstance();
			$result = $this->newMedia($media);
			if( !($result instanceof nm_los_Media) )
			{
				return false;
			}
			//Send the error code.  Error 0 means there was no error.
			if($fileData['error'] == 0) return true;
			else return $fileData['error'];
		}
		else
		{
			return false;
		}
	}

	/**
	 * Alters an existing media object in the database
	 * @param $mediaObj (Media) media object
	 * @return (Media) full media object
	 * @return (bool) False if error or no login
	 */
	public function saveMedia($mediaObj = 0)
	{
		
        if(!nm_los_Validator::isInt($mediaObj->mediaID))
		{
            return false;
		}
	    if($mediaObj->mediaID == 0)
		{
	        return newMedia($mediaObj);
		}
	    if(!nm_los_Validator::isPosInt($mediaObj->mediaID))
		{
            return false;
	    }
		// check for changes to media properties
		$serverMedia = $this->getMedia($mediaObj->mediaID);
		if(	$serverMedia->title != $mediaObj->title || $serverMedia->descText != $mediaObj->descText ||
			$serverMedia->copyright != $mediaObj->copyright || $serverMedia->length != $mediaObj->length || 
			$serverMedia->height != $mediaObj->height || 	$serverMedia->width != $mediaObj->width)
		{
		    $qstr = "UPDATE ".cfg_obo_Media::TABLE." SET 
				".cfg_obo_Media::TITLE."='?', 
				`".cfg_obo_Media::DESC."`='?', 
				".cfg_obo_Media::COPYRIGHT."='?', 
				".cfg_obo_Media::LENGTH."='?', 
				".cfg_obo_Media::HEIGHT."='?', 
				".cfg_obo_Media::WIDTH."='?', 
				".cfg_obo_Media::URL."='?' 
				WHERE ".cfg_obo_Media::ID."='?' LIMIT 1";
			if( !($q = $this->DBM->querySafe($qstr, $mediaObj->title, $mediaObj->descText, $mediaObj->copyright, $mediaObj->length, $mediaObj->height, $mediaObj->width, $mediaObj->url, $mediaObj->mediaID)))
	        {
			    $this->DBM->rollback();
				return false;	
			}   
			
			core_util_Cache::getInstance()->setMedia($mediaObj);
			// clear cache for loid's containing this media
			$los = $this->locateLOsWithMedia($mediaObj->mediaID);
			if(is_array($los) && count($los) > 0)
			{
				
				foreach($los AS $loID)
				{
					core_util_Cache::getInstance()->clearLO($loID);
				}
			}
		}
		return $mediaObj;
	}

	/**
	 * Find any learning objects using a specific media 
	 *
	 * @param string $MID 
	 * @return array 	Array of loid's
	 * @author Ian Turgeon
	 */
	// TODO: this probably can be simplified to only use one query, maybe cache for a short time
	public function locateLOsWithMedia($MID)
	{
	    if(!nm_los_Validator::isPosInt($MID))
		{
            return false;
	    }
		// locate items in pages
		$los = array();

		// TODO: write this
		return array_unique($los);
	}

	/**
	 * Deletes an existing media object from the database
	 * @param $mid (number) media ID
	 * @return (bool) True if delete was successful, False if error
	 */
	function deleteMedia($mid = 0) 
	{
		if(!is_numeric($mid) || $mid < 1)
		{
			return false;
		}
		
		//See if a map exists between this media object.
		$qstr = "SELECT ".cfg_obo_Media::ID." FROM ".cfg_obo_Media::MAP_TABLE." WHERE ".cfg_obo_Media::ID." = '?'";
		if(!($q = $this->DBM->querySafe($qstr, $mid)))
		{
		    $this->DBM->rollback();
        	trace(mysql_error(), true);
			return false;	
		}
		
		//If a map exists...
		if($this->DBM->fetch_num($q) != 0)
		{
			//See if we can find the LOs mapped to this media object.  If not, then the LO has been deleted but the orphan map hasn't been cleaned.
			$los = $this->locateLOsWithMedia($mid);
			
			if($los === false || !(is_array($los) && count($los) == 0))
			{
				return false;
			}
		}
		
		$qstr = "SELECT * FROM ".cfg_obo_Media::TABLE." WHERE ".cfg_obo_Media::ID."='?'";
		if(!($q = $this->DBM->querySafe($qstr, $mid)))
		{
		    $this->DBM->rollback();
        	trace(mysql_error(), true);
			return false;	
		}
		
		
		$lastDot = strrpos($r->{cfg_obo_Media::URL}, '.');
		$extension = strtolower(substr($r->{cfg_obo_Media::URL}, $lastDot+1));
		$file = AppCfg::DIR_BASE.AppCfg::DIR_MEDIA.$r->{cfg_obo_Media::ID}.".$extension";
		
		$r = $this->DBM->fetch_obj($q);
		if(nm_los_Validator::isString($extension))
		{
			
			if(file_exists($file))
			{
				if(!unlink($file))
				{
					return false;
				}
			}
		}
        
		$qstr = "DELETE FROM ".cfg_obo_Media::TABLE." WHERE ".cfg_obo_Media::ID."='?'";
		if(!($q = $this->DBM->querySafe($qstr, $mid)))
		{
		    $this->DBM->rollback();
			return false;	
		}
		$permMan = nm_los_PermissionsManager::getInstance();
		if(!$permMan->removeAllPermsForItem($mid, cfg_obo_Perm::TYPE_MEDIA))
		{
		    $this->DBM->rollback();
			return false;		
		}
		
		core_util_Cache::getInstance()->clearMedia($mid);
		
		return true;
	}
	
	//@TODO: Delayed until after 1.2:
	/*
	public function createMediaThumbnail($byteArray)
	{
		$data = $byteArray->data;
		//$data = gzuncompress($data);
		
		file_put_contents("../../media/thumbnails/createmediathumb.jpg", $data);
		
		g true;
	}*/
}
?>
