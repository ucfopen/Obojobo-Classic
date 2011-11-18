<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP version 4 |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license, |
// | that is bundled with this package in the file LICENSE, and is |
// | available through the world-wide-web at |
// | http://www.php.net/license/2_02.txt. |
// | If you did not receive a copy of the PHP license and are unable to |
// | obtain it through the world-wide-web, please send a note to |
// | license@php.net so we can mail you a copy immediately. |
// +----------------------------------------------------------------------+
// | Authors: Original Author <alessandro@sephiroth.it> |
// | 			Zachary Berry (modifications)
// +----------------------------------------------------------------------+

/**
* This is SwfPeek, a modification of File_SWF originally created by Alessandro Crugnola
* SwfPeek removes PEAR dependencies, removes setters and adds the getActionScriptVersion() method.
*
* Read the SWF header informations and return an
* associative array with the property of the SWF File, the result array
* will contain framerate, framecount, background color, compression, filetype
* version and movie size.
* @author Zachary Berry (modifications), Alessandro Crugnola <alessandro@sephiroth.it>
* @access public
* @package SwfPeek
*/
namespace obo\lo\media;
class SWF
{
	/**
	* current unpacked binary string
	* @var mixed
	*/
	var $current = "";
	/**
	* internal pointer
	* @var integer
	*/
	var $position = 0;
	/**
	* use zlib compression
	* @var boolean
	*/
	var $compression = 0;
	/**
	* current position
	* @var integer
	*/
	var $point = 0;
	/**
	* is a valid swf
	* @var boolean
	* @access private
	*/
	var $isValid = 0;
	/**
	* stirng file name to parse
	* @var string
	*/
	var $file = "";
	/**
	* determine if file is protected
	* @var boolean
	*/
	var $protected = false;
	/**
	* password for protected files
	* @var mixed
	*/
	var $password;
	
	var $version = 0;
	var $width = 0;
	var $height = 0;
	var $actionScriptVersion = 0;
	
	function readFile($file, $from = 0, $to = 0)
	{
		if($to == 0)
		{
			$to = filesize($file);
		}
		
		return file_get_contents($file, false, NULL, $from, $to);
	}

	/**
	* Costructor
	* creates a new SWF object
	* reads the given file and parse it
	* @param string $file file to parse
	* @access public
	*/
	function __construct($file="")
	{
		$this->compression = 0;
		$this->isValid = 0;
		$this->point = 0;
		$this->file = $file;
		//$head = File::read($this->file, 3);
		$head = $this->readFile($this->file, 0, 3);
		//if(PEAR::isError($head)){
		//	return $head;
		//}
		//File::rewind($this->file, "rb");
		if($head == "CWS"){
			//$data = File::read($this->file, 8);
			$data = $this->readFile($this->file, 0, 8);
			//$_data = gzuncompress(File::read($this->file, filesize($this->file)));
			$_data = gzuncompress($this->readFile($this->file, 8));
			$data = $data . $_data;
			$this->data = $data;
			$this->compression = 1;
			$this->isValid = 1;
		} else if ($head == "FWS"){
			//$this->data = File::read($this->file, filesize($this->file));
			$this->data = $this->readFile($this->file, 0);
			$this->isValid = 1;
		} else {
			/**
			* invalid SWF file, or invalid head tag found
			*/
			$this->isValid = 0;
		}
		
		if($this->isValid)
		{
			$this->version = $this->getVersion();
			$r = $this->getMovieSize();
			$this->width = $r['width'];
			$this->height = $r['height'];
			$this->actionScriptVersion = $this->getActionScriptVersion();
		}
		//File::close($this->file, "rb");
	}

	/**
	* Is a valid SWF file
	* @return boolean
	* @access public
	*/
	function is_valid()
	{
		return $this->isValid;
	}

	/**
	* Return if swf file is protected from import
	* @return boolean
	* @access public
	*/
	function getProtected()
	{
		if($this->getVersion() >= 8){
			$this->_seek(31);
		} else {
			$this->_seek(26);
		}
		$data = $this->_readTag();
		$tag  = $data[0];
		$this->protected = $tag == 24;
		return $this->protected;
	}
	
	/**
	* Return the current SWF frame rate
	* @return mixed interger frame rate in fps or Error if invalid file
	* @access public
	*/
	function getFrameRate()
	{
		if(!$this->is_valid()){
			//return PEAR::raiseError("Invalid SWF head TAG found in " . $this->file, PEAR_SWF_ID_ERR);
			return false;
		}
		if($this->getVersion() >= 8){
			$this->_seek(16);
		} else {
			$this->_seek(17);
		}
		$fps = unpack('vrate',$this->_read(2));
		return $fps['rate']/256;
	}

	/**
	* Return the current number of frames
	* @return mixed interger or error if invalid file format
	* @access public
	*/
	function getFrameCount()
	{
		if(!$this->is_valid()){
			//return PEAR::raiseError("Invalid SWF head TAG found in " . $this->file, PEAR_SWF_ID_ERR);
			return false;
		}
		if($this->getVersion() >= 8){
			$this->_seek(18);
		} else {
			$this->_seek(19);
		}
		return $this->_readshort();
	}

	/**
	* Return the current movie size in pixel
	* @return mixed array or error if invalid file format
	* @access public
	*/
	function getMovieSize()
	{
		if(!$this->is_valid()){
			//return PEAR::raiseError("Invalid SWF head TAG found in " . $this->file, PEAR_SWF_ID_ERR);
			return false;
		}
		$this->_seek(8);
		return $this->_readRect();
	}

	/**
	* Return the current file type (CWS, FWS)
	* @return mixed string or error if invalid file format
	* @access public
	*/
	function getFileType()
	{
		if(!$this->is_valid()){
			//return PEAR::raiseError("Invalid SWF head TAG found in " . $this->file, PEAR_SWF_ID_ERR);
			return false;
		}
		$this->_seek(0);
		return $this->_read(3);
	}

	/**
	* Return the current compression used
	* @return mixed interger or error if invalid file format
	* @access public
	*/
	function getCompression()
	{
		if(!$this->is_valid()){
			//return PEAR::raiseError("Invalid SWF head TAG found in " . $this->file, PEAR_SWF_ID_ERR);
			return false;
		}
		return $this->compression;
	}

	/**
	* Return the current version of player used
	* @return mixed interger or error if invalid file format
	* @access public
	*/
	function getVersion()
	{
		if(!$this->is_valid()){
			//return PEAR::raiseError("Invalid SWF head TAG found in " . $this->file, PEAR_SWF_ID_ERR);
			return false;
		}
		$this->_seek(3);
		return $this->_readbyte();
	}

	/**
	* Return the current SWF file size
	* @return mixed interger or error if invalid file format
	* @access public
	*/
	function filesize()
	{
		if(!$this->is_valid()){
			//return PEAR::raiseError("Invalid SWF head TAG found in " . $this->file, PEAR_SWF_ID_ERR);
			return false;
		}
		$this->_seek(4);
		$real_size = unpack( "Vnum", $this->_read(4) );
		if( $this->getCompression() ){
			$n = $this->data;
			$n = "CWS" . substr($n, 3, 8) . gzcompress(substr($n, 8, strlen($n)));
			$file_size = strlen( $n ) -3;
		} else {
			$file_size = strlen( $this->data )-3;
		}
		return array($file_size, $real_size['num'], "compressed" => $file_size, "real" => $real_size['num']);
	}
	
	function getActionScriptVersion()
	{
		if(!$this->is_valid()){
			return false;
		}
		
		$this->_seek(21);
		return $this->_readData();
	}
	
	/**
	* Return the current background color
	* @return mixed array or error if invalid file format
	* @access public
	*/
	function getBackgroundColor()
	{
		if(!$this->is_valid()){
			//return PEAR::raiseError("Invalid SWF head TAG found in " . $this->file, PEAR_SWF_ID_ERR);
			return false;
		}
		if($this->getVersion() >= 8){
			$this->_seek(26);
		} else {
			$this->_seek(21);
		}
		return $this->_readData();
	}

	/**
	* reads the SWF header
	* @return mixed associative array or error on fault
	* @access private
	*/
	function stat()
	{
		if(!$this->is_valid()){
			//return PEAR::raiseError("Invalid SWF head TAG found in " . $this->file, PEAR_SWF_ID_ERR);
			return false;
		}
		$filetype = $this->getFileType();
		$version = $this->getVersion();
		$filelength = $this->filesize();
		$rect = $this->getMovieSize();
		$framerate = $this->getFrameRate();
		$framecount = $this->getFrameCount();
		$background = $this->getBackgroundColor();
		$protection = $this->getProtected();
		return array(
			"zlib-compression" => $this->getCompression(),
			"fileType" => $filetype,
			"version" => $version,
			"fileSize" => $filelength,
			"frameRate" => $framerate,
			"frameCount" => $framecount,
			"movieSize" => $rect,
			"background" => $background,
			"protected" => $protection,
		);
	}

	/**
	* read tag type, tag length
	* @return array
	* @access private
	*/
	function _readTag()
	{
		$n = $this->_readshort();
		if($n == 0)
		{
			return false;
		}
		$tagn = $n>>6;
		$length = $n&0x3F;
		if($length == 0x3F)
		{
			$length = $this->_readlong();
		}
		return array($tagn,$length);
	}

	/**
	* read long
	* @access private
	*/
	function _readlong(){
		$ret = unpack("Nnum", $this->_read(4));
		return $ret['num'];
	}

	/**
	* read data of next tag
	* @return array
	* @access private
	*/
	function _readData()
	{
		$tag = $this->_readTag();
		$tagn = $tag[0];
		$length = $tag[1];
		if($tagn == 9)
		{
			$r = $this->_readbyte();
			$g = $this->_readbyte();
			$b = $this->_readbyte();
			$data = array($r,$g,$b, "hex" => sprintf("#%X%X%X", $r, $g, $b));
			return $data;
		} else if($tagn == 24)
		{
			if($this->_readbyte() == 0x00){
				$this->_readbyte();
				$this->password = $this->_readstring();
			} else {
				$this->password = 0;
			}
			return true;
		} else if($tagn == 69) //Actionscript Version
		{
			$this->_begin();
			$this->_readBits(3);
			return $this->_readBits(2);
		}
		return array();
	}

	/**
	* read a string
	* @return string
	* @access private
	*/
	function _readstring()
	{
		$s = "";
		while(true){
			$ch = $this->_read(1);
			if($this->point > strlen($this->data)){
				break;
			}
			if($ch == "\x00"){
				break;
			}
			$s .= $ch;
		}
		return $s;
	}


	/**
	* read internal data file
	* @param integer $n number of byte to read
	* @return array
	* @access private
	*/
	function _read($n)
	{
		$ret = substr($this->data, $this->point, $n);
		$this->point += $n;
		return $ret;
	}

	/**
	* move the internal pointer
	* @param integer $num
	* @access private
	*/
	function _seek($num){
		if($num < 0){
			$num = 0;
		} else if($num > strlen($this->data)){
			$num = strlen($this->data);
		}
		$this->point = $num;
	}

	/**
	* read short
	* @return string
	* @access private
	*/
	function _readshort(){
		$pack = unpack('vshort',$this->_read(2));
		return $pack['short'];
	}

	/**
	* read single byte
	* @return string
	* @access private
	*/
	function _readByte(){
		$ret = unpack("Cbyte",$this->_read(1));
		return $ret['byte'];
	}
	/**
	* read a rect type
	* @return rect
	* @access private
	*/
	function _readRect(){
		$this->_begin();
		$l = $this->_readbits(5);
		$xmin = $this->_readbits($l)/20;
		$xmax = $this->_readbits($l)/20;
		$ymin = $this->_readbits($l)/20;
		$ymax = $this->_readbits($l)/20;
		$rect = array(
			$xmax,
			$ymax,
			"width" => $xmax,
			"height" => $ymax
		);
		return $rect;
	}

	/**
	* read position internal to rect
	* @access private
	*/
	function _incpos(){
		$this->position += 1;
		if($this->position>8){
			$this->position = 1;
			$this->current = $this->_readbyte();
		}
	}
	/**
	* read bites
	* @param integer $nbits number of bits to read
	* @return string
	* @access private
	*/
	function _readbits($nbits){
		$n = 0;
		$r = 0;
		while($n < $nbits){
			$r = ($r<<1) + $this->_getbits($this->position);
			$this->_incpos();
			$n += 1;
		}
		return $r;
	}

	/**
	* getbits
	* @param integer $n
	* @return long
	* @access private
	*/
	function _getbits($n){
		return ($this->current>>(8-$n))&1;
	}

	/**
	* begin reading of rect object
	* @access private
	*/
	function _begin(){
		$this->current = $this->_readbyte();
		$this->position = 1;
	}
}
?>