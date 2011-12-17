<?php
namespace obo\lo\media;
class SWF
{
	public $compressed = false;
	public $version = 1;
	public $fileSize = 0;
	public $dimensions = false;
	public $frameRate = 12;
	public $totalFrames = 1;
	public $asVersion = 2;
	public $usesNetwork = false;
	public $backgroundColor = 0xFFFFFF;
	public $protectedFromImport = false;
	public $debuggerEnabled = true;
	public $metadata = false;
	public $recursionLimit = 15;
	public $scriptTimeoutLimit = 256;
	public $hardwareAcceleration = 0;
	public $width = 0;
	public $height = 0;
	
	public $parsed = false;
	
	private $bytes;
	private $bytePosition = 0;
	private $currentByte;
	private $bitPosition = 0;
	private $currentTag;
	
	private $bgColorFound;
	
	const GET_DATA_SIZE = 5;
	const TWIPS_TO_PIXELS = 0.05; // 20 twips in a pixel
	const TAG_HEADER_ID_BITS = 6;
	const TAG_HEADER_MAX_SHORT = 0x3F;
	
	const SWF_C = 0x43; // header characters
	const SWF_F = 0x46;
	const SWF_W = 0x57;
	const SWF_S = 0x53;
	
	const TAG_ID_EOF = 0; // recognized SWF tags
	const TAG_ID_BG_COLOR = 9;
	const TAG_ID_PROTECTED = 24;
	const TAG_ID_DEBUGGER1 = 58;
	const TAG_ID_DEBUGGER2 = 64;
	const TAG_ID_SCRIPT_LIMITS = 65;
	const TAG_ID_FILE_ATTS = 69;
	const TAG_ID_META = 77;
	
	const TAG_ID_SHAPE_1 = 2;
	const TAG_ID_SHAPE_2 = 22;
	const TAG_ID_SHAPE_3 = 32;
	const TAG_ID_SHAPE_4 = 83;
	
	function __construct($fileURL)
	{
		$this->parse($fileURL);
	}
	
	function parse($fileURL)
	{
		// Start reading the file
		$this->bytes = $this->readFile($fileURL);
		
		// is compressed or not?
		$tmp = $this->readBytes(3);
		if($tmp != 'CWS' && $tmp != 'FWS')
		{
			$this->parsed = false;
		}
		else
		{
			try
			{
				$this->compressed = ($tmp == 'CWS');
				$this->version = ord($this->readByte());
				$this->fileSize = $this->readUnsignedInt();
			
				$this->bytes = substr($this->bytes, 8);
				$this->bytePosition = 0;
				if($this->compressed)
				{
					$this->bytes = gzuncompress($this->bytes);
				}
			
				$this->parsed = true;
				
				// get the dimentsions
				$dimensions = $this->readRect();
				$this->width = $dimensions['width'];
				$this->height = $dimensions['height'];
				
				$this->bytePosition++;
			
				$this->frameRate = $this->readByte();
				$this->totalFrames = $this->readUnsignedShort();
			}
			catch(Exception $error)
			{
				//@TODO
				trace($error, true);
				trace('SWFReader Failed', true);
			}
			$curTag = 0;
			// dig through the file tags to get the asversion if the swf is above v8 
			if($this->version >= 9)
			{
				try
				{
					// NOTICE - if you want to read more tags, change this
					// read each tag till the file attributes is found
					while($this->readTag() && $this->currentTag != self::TAG_ID_FILE_ATTS)
					{
					}
				}
				catch(Exception $error)
				{
					trace($error, true);
					trace('SWFReader Failed', true);
				}
			}

			flush();
		}
	}
	
	private function readRect()
	{
		$this->nextBitByte();
		$dataSize = $this->readBits(self::GET_DATA_SIZE);
		//echo 'dataSize = ['.$dataSize.']';
		$rect = array(	'left' => $this->readBits($dataSize, true) * self::TWIPS_TO_PIXELS,
		              				'right' => $this->readBits($dataSize, true) * self::TWIPS_TO_PIXELS,
		              				'top' => $this->readBits($dataSize, true) * self::TWIPS_TO_PIXELS,
		              				'bottom' => $this->readBits($dataSize, true) * self::TWIPS_TO_PIXELS);
		$rect['height'] = $rect['bottom'] - $rect['top'];
		$rect['width'] = $rect['right'] - $rect['left'];
		return $rect;
	}
	
	private function readMatrix()
	{
		return '';
	}
	
	private function readTag()
	{
		$currentTagPosition = $this->bytePosition;

		// read tag header
		$tagHeader = $this->readUnsignedShort();

		$this->currentTag = $tagHeader >> self::TAG_HEADER_ID_BITS;
		$tagLength = $tagHeader & self::TAG_HEADER_MAX_SHORT;
		
		if($tagLength == self::TAG_HEADER_MAX_SHORT)
		{
			$tagLength = $this->readUnsignedInt();
		}
		
		$nextTagPosition = $this->bytePosition + $tagLength;
		
		$moreTags = $this->readTagData($tagLength, $currentTagPosition, $nextTagPosition);
		if(!$moreTags)
		{
			return false;
		}
		
		$this->bytePosition = $nextTagPosition;
		return true;
	}
	
	private function readTagData($tagLength, $start, $end)
	{
		switch($this->currentTag)
		{
			case self::TAG_ID_FILE_ATTS:
				$this->nextBitByte();
				$this->readBit();
				$this->hardwareAcceleration = $this->readBits(2);
				$this->readBit();
				$this->asVersion = ($this->readBit() && $this->version >= 9) ? 3 : 2;
				$this->readBits(2);
				$this->usesNetwork = $this->readBit() == 1;
				break;
				
			case self::TAG_ID_META:
				$this->metadata = $this->readString();
				break;
				
			case self::TAG_ID_BG_COLOR:
				if(!$this->bgColorFound)
				{
					$this->bgColorFound = true;
					$this->backgroundColor = $this->readRGB();
				}
				break;
				
			case self::TAG_ID_PROTECTED:
				$this->protectedFromImport = ord($this->readByte()) != 0;
				break;
				
			case self::TAG_ID_DEBUGGER1:
				if($this->version == 5)
				{
					$this->debuggerEnabled = true;
				}
				break;
				
			case self::TAG_ID_DEBUGGER2:
				if($this->version > 5)
				{
					$this->debuggerEnabled = true;
				}
				break;
				
			case self::TAG_ID_SCRIPT_LIMITS:
				$this->recursionLimit = $this->readUnsignedShort();
				$this->scriptTimeoutLimit = $this->readUnsignedShort();
				break;
				
			case self::TAG_ID_EOF:
				return false;
				break;
				
			default:
				break;
		}
		
		return true;
	}
	
	private function nextBitByte()
	{
		$this->currentByte = ord($this->readByte());
		$this->bitPosition = 0;
	}
	
	private function readFile($file, $from = 0, $to = 0)
	{
		if($to == 0)
		{
			$to = filesize($file);
		}
		
		return file_get_contents($file, false, NULL, $from, $to);
	}
	
	private function readByte()
	{
		return $this->readBytes(1);
	}
	
	private function readBytes($num)
	{
		$r = substr($this->bytes, $this->bytePosition, $num);
		$this->bytePosition += $num;
		
		return $r;
	}
	
	private function readUnsignedShort()
	{
		$a = unpack('Sa', $this->readBytes(2));
		return (int)$a['a'];
	}
	
	private function readUnsignedInt()
	{
		//@TODO - this said it was machine dependant
		$a = unpack('Ia', $this->readBytes(4));
		return (int)$a['a'];
	}
	
	private function readBit()
	{
		return $this->readBits(1);
	}
	
	private function readBits($numBits, $signed = false)
	{	
		$value = 0;
		$remaining = 8 - $this->bitPosition;
		$mask;
		
		if($numBits <= $remaining)
		{
			$mask = (1 << $numBits) - 1;
			$value = ($this->currentByte >> ($remaining - $numBits)) & $mask;
			if($numBits == $remaining)
			{
				$this->nextBitByte();
			}
			else
			{
				$this->bitPosition += $numBits;
			}
		}
		else
		{
			$mask = (1 << $remaining) - 1;
			$firstValue = $this->currentByte & $mask;
			$over = $numBits - $remaining;
			$this->nextBitByte();
			$value = ($firstValue << $over) | $this->readBits($over);
		}
		
		if($signed && $value >> ($numBits - 1) == 1)
		{
			$remaining = 32 - $numBits;
			$mask = (1 << $remaining) - 1;
			return (int)($mask << $numBits | $value);
		}
		
		return (int)$value;
	}
	
	private function readString()
	{
		return '';
	}
	
	private function readRGB()
	{
		return (ord($this->readByte()) << 16) | (ord($this->readByte()) << 8) | ord($this->readByte());
	}
}

?>