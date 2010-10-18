<?php
//-----------------------------------------------------------------------------
// SWF HEADER - version 1.0
// Small utility class to determine basic data from a SWF file header
// Does not need any php-flash extension, based on raw binary data reading
//-----------------------------------------------------------------------------
//   SWFHEADER CLASS - PHP SWF header parser
//   Copyright (C) 2004  Carlos Falo Hervás
//   This library is free software; you can redistribute it and/or
//   modify it under the terms of the GNU Lesser General Public
//   License as published by the Free Software Foundation; either
//   version 2.1 of the License, or (at your option) any later version.
//-----------------------------------------------------------------------------
//
//VGR09062006 MOD function name & returned value of loadswf()
//            version 1.0.1
//
// TODO : Nil
//

class nm_los_media_SWF
{
	
	public $version;
	public $height;
	public $width;
	public $size;

	public function getVersion($filename)
	{
		$fp = @fopen($filename,"rb") ;
		if ($fp)
		{
			$magic = fread($fp,3) ;
			if ($magic=="FWS" || $magic=="CWS")
			{
				$this->version = ord(fread($fp,1));
				fclose($fp);
				return $this->version;
			}
		}
		return 0;
	}

	public function getSize($filename)
	{
		$fp = @fopen($filename,"rb") ;
		if ($fp)
		{
			$magic = fread($fp,3) ;
			fread($fp,1);
			if ($magic=="FWS" || $magic=="CWS")
			{
				// 4 LSB-MSB
				$lg = 0;
				for ($i=0;$i<4;$i++)
				{
 					$t = ord(fread($fp,1)) ;
					$lg += ($t<<(8*$i)) ;
				}
				fclose($fp);
				return $lg;
			}
		}
		return 0;
	}

	public function getDimensions($filename)
	{
		$fp = @fopen($filename,"rb") ;
		if ($fp)
		{
			$magic = fread($fp,3) ;
			if ($magic == "FWS" || $magic == "CWS") 
			{
				$this->version = ord(fread($fp,1)); // read the version while we're here
				// 4 LSB-MSB
				$lg = 0;
				for ($i=0;$i<4;$i++)
				{
					$t = ord(fread($fp,1)) ;
					$lg += ($t<<(8*$i)) ;
				}
				// if its compressed
				if (substr($magic,0,1)=="C")
				{
					if(ini_get('memory_limit') == false || $lg < $this->return_bytes(ini_get('memory_limit')))
					{
						$buffer = fread($fp, $lg);
						$buffer = gzuncompress($buffer, $lg);
					}
					else
					{
						return 0;
					}
				}
				else
				{
					$buffer = fread($fp,8);
				}
				$b   = ord(substr($buffer,0,1)) ;
				$buffer = substr($buffer,1) ;
				$cbyte    = $b ;
				$bits    = $b>>3 ;
				$cval    = "" ;
				// Current byte
				$cbyte &= 7 ;
				$cbyte<<= 5 ;
				// Current bit (first byte starts off already shifted)
				$cbit    = 2 ;
				// Must get all 4 values in the RECT
				$w = 0;
				$h = 0;
				for ($vals=0;$vals<4;$vals++)
				{
					$bitcount = 0 ;
					while ($bitcount<$bits)
					{
						if ($cbyte&128)
						{
							$cval .= "1" ;
						}
						else
						{
							$cval.="0" ;
						}
						$cbyte<<=1 ;
						$cbyte &= 255 ;
						$cbit-- ;
						$bitcount++ ;
						// We will be needing a new byte if we run out of bits
						if ($cbit<0)
						{
							$cbyte   = ord(substr($buffer,0,1)) ;
							$buffer = substr($buffer,1) ;
							$cbit = 7 ;
						}
					}
					// O.k. full value stored... calculate
					$c       = 1 ;
					$val    = 0 ;
					// Reverse string to allow for SUM(2^n*$atom)
					$tval = strrev($cval) ;
					for ($n=0;$n<strlen($tval);$n++)
					{
						$atom = substr($tval,$n,1) ;
						if ($atom=="1")
						{
							$val+=$c ;
						}
						// 2^n
						$c*=2 ;
					}
					// TWIPS to PIXELS
					$val/=20 ;
					switch ($vals)
					{
						case 0:
							// tmp value
							$w = $val;
							break ;
						case 1:
							$w = $val - $w;
							break;
						case 2:
							// tmp value
							$h = $val;
							break;
						case 3:
							$h = $val - $h;
							break;
					}
					$cval = "";
				}
				
				// get fps and total frames
				// $fps = Array() ;
				//                 for ($i=0;$i<2;$i++) {
				//                     $t = ord(substr($buffer,0,1)) ;
				//                     $buffer = substr($buffer,1) ;
				//                     $fps[] = $t ;
				//                 }
				//                 // Frames
				//                 $frames = 0 ;
				//                 for ($i=0;$i<2;$i++) {
				//                     $t = ord(substr($buffer,0,1));
				//                     $buffer = substr($buffer,1);
				//                     $frames += ($t<<(8*$i));
				//                 }
				// echo "FRAMES: $frames , FPS: $fps[1].$fps[0] ";

	 			fclose($fp);

				
				$this->height = $h;
				$this->width = $w;
				return array('width' => $w, 'height' => $h);
			}
			return 0;
		}
		return 0;
	}

	private function return_bytes($val)
	{
		$val = trim($val);
		$last = strtolower($val{strlen($val)-1});
		switch($last)
		{
			// The 'G' modifier is available since PHP 5.1.0
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}
	    return $val;
	}
}
?>