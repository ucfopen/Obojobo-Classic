<?php

namespace obo\lo;
class MediaManager extends \rocketD\db\DBEnabled
{
	use \rocketD\Singleton;

	/**
	 * Gets the full data for an existing media object
	 * @param $mediaID (number) media id
	 * @return (Media) full media object (includes URL)
	 * @return (bool) False if error or no login
	 */
	public function getMedia($mediaID = 0)
	{
	    if(!is_numeric($mediaID) || $mediaID < 1)
		{
			return false;
		}


		if($media = \rocketD\util\Cache::getInstance()->getMedia($mediaID))
		{
			return $media;
		}

		if(!($q = $this->DBM->querySafe("SELECT * FROM ".\cfg_obo_Media::TABLE." WHERE ".\cfg_obo_Media::ID."='?' LIMIT 1", $mediaID)))
		{
            $this->DBM->rollback();
			return false;
		}

		$r = $this->DBM->fetch_obj($q);



		$media = new \obo\lo\Media($r->{\cfg_obo_Media::ID}, $r->{\cfg_core_User::ID}, $r->{\cfg_obo_Media::TITLE}, $r->{\cfg_obo_Media::TYPE}, $r->{\cfg_obo_Media::DESC}, $r->{\cfg_obo_Media::TIME}, $r->{\cfg_obo_Media::COPYRIGHT}, $r->{\cfg_obo_Media::THUMB}, $r->{\cfg_obo_Media::URL}, $r->{\cfg_obo_Media::SIZE}, $r->{\cfg_obo_Media::LENGTH}, 0, $r->{\cfg_obo_Media::WIDTH}, $r->{\cfg_obo_Media::HEIGHT}, unserialize(base64_decode($r->{\cfg_obo_Media::META})), $r->{\cfg_obo_Media::ATTRIBUTION});
		\rocketD\util\Cache::getInstance()->setMedia($media);
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
				if(!\obo\util\Validator::isPosInt($eachMediaID))
				{
			        return \rocketD\util\Error::getError(2);
				}
			}
			// force getLO on an array to use meta
			$mediaArr = $optMediaIDArray;
		}

		$result = array();
		$result = $this->getMediaWithPerm('read');
		if(!empty($mediaArr) && count($mediaArr) > 0) // remove unwanted items
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
		$permMan = \obo\perms\PermissionsManager::getInstance();
		$mediaIDs = $permMan->getItemsWithPerm(\cfg_obo_Perm::TYPE_MEDIA, $perm);

		$mediaArr = array();
		if(count($mediaIDs) > 0)
		{
			foreach($mediaIDs AS $mediaID)
			{
				if($media = $this->getMedia($mediaID))
				{
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
	    if(! $media instanceof \obo\lo\Media)
		{
			return \rocketD\util\Error::getError(2);
		}
		if( $media->mediaID > 0)
		{
			return \rocketD\util\Error::getError(2);
		}

		// if auth is set to someone that isnt you, you have to be super user
		if(!\obo\util\Validator::isPosInt($media->auth) || $media->auth == $_SESSION['userID'])
		{
			$media->auth = $_SESSION['userID'];
		}
		else
		{
			// to make media for someone else, you must be su

			$roleMan = \obo\perms\RoleManager::getInstance();
			if(!$roleMan->isSuperUser())
			{
				return \rocketD\util\Error::getError(2);
			}
		}

		if( ! \obo\util\Validator::isString($media->title) )
		{
			return \rocketD\util\Error::getError(2);
		}

		if( ! \obo\util\Validator::isString($media->itemType) )
		{
			return \rocketD\util\Error::getError(2);
		}

		if( ! \obo\util\Validator::isString($media->copyright) )
		{
			return \rocketD\util\Error::getError(2);
		}


	    // TODO: THIS SHOULDNT BE HARD CODED- ANY PLUGIN SHOULD BE ABLE REGISTER FOR THIS EVENT
	    /*if($media->itemType == 'kogneato')
	    {
			$PM = \rocketD\plugin\PluginManager::getInstance();
			$result = $PM->callAPI('Kogneato', 'getKogneatoWidgetInfo', $media->url, true);

			// Fail if we cant talk to Kogneato
			if($result === false)
			{
				return false;
			}

			// copy all of our data into Obojobo
			$media->title = $result['title'];
			$media->height = $result['height'];
			$media->width = $result['width'];
			$media->meta['version'] = $result['flashVersion'];
			$media->meta['owner'] = $result['owner'];
			$media->meta['widget'] = $result['type'];
			$media->meta['asVersion'] = 3;
			$media->meta['container'] = 'flash';
	    }*/


		$media->createTime = time();
		$qstr = "INSERT INTO ".\cfg_obo_Media::TABLE."
			SET
				".\cfg_core_User::ID."='?',
				".\cfg_obo_Media::TITLE."='?',
				".\cfg_obo_Media::TYPE."='?',
				`".\cfg_obo_Media::DESC."`='?',
				".\cfg_obo_Media::URL."='?',
				".\cfg_obo_Media::TIME."='?',
				".\cfg_obo_Media::COPYRIGHT."='?',
				".\cfg_obo_Media::THUMB."='?',
				".\cfg_obo_Media::SIZE."='?',
				".\cfg_obo_Media::LENGTH."='?',
				".\cfg_obo_Media::HEIGHT."='?',
				".\cfg_obo_Media::WIDTH."='?',
				".\cfg_obo_Media::META."='?',
				".\cfg_obo_Media::ATTRIBUTION."='?'";
		if( !($q = $this->DBM->querySafe($qstr, $media->auth, $media->title, $media->itemType,
		$media->descText, $media->url, $media->createTime , $media->copyright, $media->thumb,
		$media->size, $media->length, $media->height, $media->width, base64_encode(serialize($media->meta)), $media->attribution)))
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

		    $hashedFile = \AppCfg::DIR_BASE.\AppCfg::DIR_MEDIA.md5($fileName);
			if(file_exists($hashedFile))
			{
	            rename($hashedFile, \AppCfg::DIR_BASE.\AppCfg::DIR_MEDIA.$media->mediaID.".".$extension);
			}
			else
			{
				\rocketD\util\Error::getError(0);
				return false;
			}
		}

		$media->perms = new \obo\perms\Permissions($media->auth, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0);

		//Add owner permissions to this object for this user
		$PM = \obo\perms\PermissionsManager::getInstance();
		$PM->setFullPermsForItem($media->mediaID, \cfg_obo_Perm::TYPE_MEDIA);

		\rocketD\util\Cache::getInstance()->setMedia($media);

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

	public function handleFileDataUpload($fileData, $filename, $title, $description, $copyright, $length=0)
	{
		// TODO: Make sure file is less than or equal to max size, and a title has been sent

		$roleMan = \obo\perms\RoleManager::getInstance();
		if(!$roleMan->isLibraryUser())
		{
			\rocketD\util\Error::getError(4);
			return false;
		}

		//explode it by . and then combine to compare
		$fileNameArr =  explode('.', $filename);
		$baseName = $filename;
		$lastDot = strrpos($baseName, '.');
		$fileName = substr($baseName, 0, $lastDot);
		$extension = strtolower(substr($baseName, $lastDot+1));
		switch($extension)
		{
			case 'jpg':
			case 'jpeg':
				$extension = 'jpg'; // fallthrough
			case 'png':
			case 'gif':
				$fileType = 'pic';
				break;
			case 'swf':
				$fileType = 'swf';
				break;
			case 'flv':
				$fileType = 'flv';
				break;
			case 'mp3':
				$fileType = 'mp3';
				break;
			default:
				// no other file types allowed
				break;
		}

		if($fileType != false)
		{
			$newFileLocation = \AppCfg::DIR_BASE.\AppCfg::DIR_MEDIA . md5($fileName);

			try
			{

				file_put_contents($newFileLocation, $fileData->data);
				//$fp = fopen($newFileLocation, "w");
				//fwrite($fp, $fileData);
				//fclose($fp);

				$size = filesize($newFileLocation);


				if(file_exists($newFileLocation))
				{
					$media = new \obo\lo\Media();
					$media->attribution = 1; //@TODO
					// get swf dimensions and size
					if($fileType == 'swf')
					{
						$swf = new \obo\lo\media\SWF($newFileLocation);

						$media->width = $swf->width;
						$media->height = $swf->height;
						$media->meta = array(
							'version' => $swf->version,
							'asVersion' => $swf->asVersion
						);
						$length = $swf->totalFrames;
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
					$media->size = $size;
					$media->length = $length;

					$result = $this->newMedia($media);

					if( !($result instanceof nm_los_Media) )
					{
						return false;
					}

					return true;
				}
				else
				{
					return false;
				}
			}
			catch(Exception $e)
			{
				trace($e->getMessage());
			}
		}
		return false;
	}

	public function handleMediaUpload($fileData, $title, $description, $copyright, $length=0)
	{
		// TODO: Make sure file is less than or equal to max size, and a title has been sent

		$roleMan = \obo\perms\RoleManager::getInstance();
		if(!$roleMan->isLibraryUser())
		{
			\rocketD\util\Error::getError(4);
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

		$newFileLocation = \AppCfg::DIR_BASE.\AppCfg::DIR_MEDIA . md5($fileName);

		move_uploaded_file($fileData['tmp_name'], $newFileLocation);

		if(file_exists($newFileLocation))
		{
			$media = new \obo\lo\Media();
			// get swf dimensions and size
			if($fileType == 'swf')
			{
				$swf = new \obo\lo\media\SWF($newFileLocation);

				$media->width = $swf->width;
				$media->height = $swf->height;
				$media->meta = array(
					'version' => $swf->version,
					'asVersion' => $swf->asVersion
				);
				$length = $swf->totalFrames;
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

			$result = $this->newMedia($media);
			if( !($result instanceof \obo\lo\Media) )
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

        if(!\obo\util\Validator::isInt($mediaObj->mediaID))
		{
            return false;
		}
	    if($mediaObj->mediaID == 0)
		{
	        return newMedia($mediaObj);
		}
	    if(!\obo\util\Validator::isPosInt($mediaObj->mediaID))
		{
            return false;
	    }
		// check for changes to media properties
		$serverMedia = $this->getMedia($mediaObj->mediaID);
		if(	$serverMedia->title != $mediaObj->title || $serverMedia->descText != $mediaObj->descText ||
			$serverMedia->copyright != $mediaObj->copyright || $serverMedia->length != $mediaObj->length ||
			$serverMedia->height != $mediaObj->height || 	$serverMedia->width != $mediaObj->width)
		{
		    $qstr = "UPDATE ".\cfg_obo_Media::TABLE." SET
				".\cfg_obo_Media::TITLE."='?',
				`".\cfg_obo_Media::DESC."`='?',
				".\cfg_obo_Media::COPYRIGHT."='?',
				".\cfg_obo_Media::LENGTH."='?',
				".\cfg_obo_Media::HEIGHT."='?',
				".\cfg_obo_Media::WIDTH."='?',
				".\cfg_obo_Media::URL."='?',
				".\cfg_obo_Media::META."='?'
				WHERE ".\cfg_obo_Media::ID."='?' LIMIT 1";
			if( !($q = $this->DBM->querySafe($qstr, $mediaObj->title, $mediaObj->descText, $mediaObj->copyright, $mediaObj->length, $mediaObj->height, $mediaObj->width, $mediaObj->url, base64_encode(serialize($mediaObj->meta)), $mediaObj->mediaID)))
	        {
			    $this->DBM->rollback();
				return false;
			}

			\rocketD\util\Cache::getInstance()->setMedia($mediaObj);
			// clear cache for loid's containing this media
			$los = $this->locateLOsWithMedia($mediaObj->mediaID);
			if(is_array($los) && count($los) > 0)
			{

				foreach($los AS $loID)
				{
					\rocketD\util\Cache::getInstance()->clearLO($loID);
				}
			}
		}
		return $mediaObj;
	}

	/**
	 * Find any learning objects using a specific media
	 *
	 * @param string $mediaID
	 * @return array 	Array of loid's
	 * @author Ian Turgeon
	 */
	// TODO: this probably can be simplified to only use one query, maybe cache for a short time
	public function locateLOsWithMedia($mediaID)
	{
	    if(!\obo\util\Validator::isPosInt($mediaID))
		{
            return false;
	    }

		$qstr = "SELECT DISTINCT L.".\cfg_obo_LO::ID." FROM ".\cfg_obo_LO::TABLE." AS L, ".\cfg_obo_Media::MAP_TABLE." AS M WHERE L.".\cfg_obo_LO::ID." = M.".\cfg_obo_LO::ID." AND M.".\cfg_obo_Media::ID." = '?'";
		if(!($q = $this->DBM->querySafe($qstr, $mediaID)))
		{
		    $this->DBM->rollback();
        	trace($this->DBM->error(), true);
			return false;
		}

		$los = array();
		while($r = $this->DBM->fetch_obj($q))
		{
			$los[] = $r->{\cfg_obo_LO::ID};
		}

		return $los;
	}

	/**
	 * Register a media object to an LO for tracking purposes
	 *
	 * @param string $mediaID
	 * @param string $loID
	 * @return void
	 * @author Ian Turgeon
	 */
	public function associateMediaWithLO($mediaID, $loID)
	{
		if(!\obo\util\Validator::isPosInt($mediaID))
		{
            return false;
	    }
	    if(!\obo\util\Validator::isPosInt($loID))
		{
            return false;
	    }
		if(!$this->DBM->querySafe("INSERT INTO ".\cfg_obo_Media::MAP_TABLE." SET ".\cfg_obo_Media::ID." = '?', ".\cfg_obo_LO::ID." = '?'", $mediaID, $loID))
		{
		    $this->DBM->rollback();
        	trace($this->DBM->error(), true);
			return false;
		}

		return true;
	}

	/**
	 * Deletes an existing media object from the database
	 * @param $mediaID (number) media ID
	 * @return (bool) True if delete was successful, False if error
	 */
	function deleteMedia($mediaID = 0)
	{
		if(!is_numeric($mediaID) || $mediaID < 1)
		{
			return false;
		}

		//See if a map exists between this media object.
		$qstr = "SELECT ".\cfg_obo_Media::ID." FROM ".\cfg_obo_Media::MAP_TABLE." WHERE ".\cfg_obo_Media::ID." = '?'";
		if(!($q = $this->DBM->querySafe($qstr, $mediaID)))
		{
		    $this->DBM->rollback();
        	trace($this->DBM->error(), true);
			return false;
		}

		//If a map exists...
		if($this->DBM->fetch_num($q) != 0)
		{
			//See if we can find the LOs mapped to this media object.  If not, then the LO has been deleted but the orphan map hasn't been cleaned.
			$los = $this->locateLOsWithMedia($mediaID);

			if($los === false || !(is_array($los) && count($los) == 0))
			{
				return false;
			}
		}

		$qstr = "SELECT * FROM ".\cfg_obo_Media::TABLE." WHERE ".\cfg_obo_Media::ID."='?'";
		if(!($q = $this->DBM->querySafe($qstr, $mediaID)))
		{
		    $this->DBM->rollback();
        	trace($this->DBM->error(), true);
			return false;
		}


		$lastDot = strrpos($r->{\cfg_obo_Media::URL}, '.');
		$extension = strtolower(substr($r->{\cfg_obo_Media::URL}, $lastDot+1));
		$file = \AppCfg::DIR_BASE.\AppCfg::DIR_MEDIA.$r->{\cfg_obo_Media::ID}.".$extension";

		$r = $this->DBM->fetch_obj($q);
		if(\obo\util\Validator::isString($extension))
		{

			if(file_exists($file))
			{
				if(!unlink($file))
				{
					return false;
				}
			}
		}

		$qstr = "DELETE FROM ".\cfg_obo_Media::TABLE." WHERE ".\cfg_obo_Media::ID."='?'";
		if(!($q = $this->DBM->querySafe($qstr, $mediaID)))
		{
		    $this->DBM->rollback();
			return false;
		}
		$permMan = \obo\perms\PermissionsManager::getInstance();
		if(!$permMan->removeAllPermsForItem($mediaID, \cfg_obo_Perm::TYPE_MEDIA))
		{
		    $this->DBM->rollback();
			return false;
		}

		\rocketD\util\Cache::getInstance()->clearMedia($mediaID);

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
