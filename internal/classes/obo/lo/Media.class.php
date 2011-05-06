<?php
/**
 * This class defines the Media data type
 * @author Jacob Bates <jbates@mail.ucf.edu>
 */

/**
 * This class defines the Media data type
 * It is used simply for representing data in memory, and has no methods.
 */
namespace obo\lo;
class Media
{
	public $mediaID;				//Number:  database id
	public $auth;			//User:  creator of media
	public $title;			//String:  The name of the piece of media
	public $itemType;			//String:  What type of media it is (pic, swf, vid)
	public $descText;			//String:  in-depth description of contents of package
	public $createTime;			//Number:  date media was created
	public $copyright;		//String:
	public $thumb;			//String:  URL of the thumbnail image
	public $url;			//String:  URL of the actual media
	public $size;			//Number:  the size of the media (in bytes) (for video files)
	public $length;			//Number:  the length of the media file (in seconds) (for audio files)
	public $perms;			//Permissions object:  merged from global and user
	public $height;			//height in pixels of the media (works for swf and images)
	public $width;			//width in pixels of media
	public $version;		//version of the file (swf version for instance)
	public $attribution;	//Tinyint: If true (1), the image requires CC attribution

	function __construct($mediaID=0, $auth=0, $title='', $itemType='pic', $descText='', $createTime=0, $copyright='', $thumb='', $url='', $size=0, $length=0, $perms=0, $width=0, $height=0, $version='', $attribution=0)
	{
	    if(func_num_args() == 1)
        {   	
       		$mediaObj = func_get_arg(0);
       		$this->mediaID = $mediaObj['mediaID'];
//			$this->auth = $mediaObj[''];
			$this->title = $mediaObj['title'];
			$this->itemType = $mediaObj['itemType'];
			$this->descText = $mediaObj['descText'];
			$this->copyright = $mediaObj['copyright'];
			$this->thumb = $mediaObj['thumb'];
			$this->url = $mediaObj['url'];
			$this->size = $mediaObj['size'];
			$this->length = $mediaObj['length'];
			$this->width = $mediaObj['width'];
			$this->height = $mediaObj['height'];
			$this->version = $mediaObj['version'];
			$this->attribution = $mediaObj['attribution'];
        }
        else
        {	
			$this->mediaID = $mediaID;
			$this->auth = $auth;
			$this->title = $title;
			$this->itemType = $itemType;
			$this->descText = $descText;
			$this->createTime = $createTime;
			$this->copyright = $copyright;
			$this->thumb = $thumb;
			$this->url = $url;
			$this->size = $size;
			$this->length = $length;
			$this->perms = $perms;
			$this->width = $width;
			$this->height = $height;
			$this->version = $version;
			$this->attribution = $attribution;
        }
	}
}
?>