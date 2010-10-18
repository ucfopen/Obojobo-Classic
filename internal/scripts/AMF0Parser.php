<?php  
/**
 * AMF0 Parser
 * 
 * Based (pretty far) on the AMFPHP serializer. I actually started using the AMFPHP serializer and
 * mostly rewrote it.
 *
 * @author        Tommy Lacroix <tlacroix@quantiksolutions.com>
 * @copyright   Copyright (c) 2006-2008 Tommy Lacroix
 * @license        LGPL
 */

class AMF0Parser {
    const TYPE_NUMBER = 0x00;
    const TYPE_BOOLEAN = 0x01;
    const TYPE_STRING = 0x02;
    const TYPE_OBJECT = 0x03;
    const TYPE_MOVIECLIP = 0x04;
    const TYPE_NULL = 0x05;
    const TYPE_UNDEFINED = 0x06;
    const TYPE_REFERENCE = 0x07;
    const TYPE_MIXEDARRAY = 0x08;
    const TYPE_OBJECT_TERM = 0x09;
    const TYPE_ARRAY = 0x0a;
    const TYPE_DATE = 0x0b;
    const TYPE_LONGSTRING = 0x0c;
    const TYPE_RECORDSET = 0x0e;
    const TYPE_XML = 0x0f;
    const TYPE_TYPED_OBJECT = 0x10;
    const TYPE_AMF3 = 0x11;
    
    /**
     * Endianess, 0x00 for big, 0x01 for little
     *
     * @var int
     */
    private $endian;
    
    /**
     * AMF0 Data
     *
     * @var string (binary)
     */
    private $data;
    
    /**
     * Index in data
     *
     * @var int
     */
    private $index;
    
    /**
     * Data length
     *
     * @var int
     */
    private $dataLength;
    
    /**
     * Constructor
     *
     * @return AMF0Parser
     */
    function AMF0Parser() {    
        /**
         * Proceed to endianess detection. This will be needed by
         * double decoding because unpack doesn't allow the selection
         * of endianess when decoding doubles.
         */
        
        // Pack 1 in machine order
        $tmp = pack("L", 1);
        
        // Unpack it in big endian order
        $tmp2 = unpack("None",$tmp);
        
        // Check if it's still one
        if ($tmp2['one'] == 1) $this->endian = 0x00; // Yes, big endian
            else $this->endian = 0x01;    // No, little endian
    }
    
    /**
     * Initialize data
     *
     * @param string    AMF0 Data
     */
    function initialize($str) {
        $this->data = $str;
        $this->dataLength = strlen($str);
        $this->index = 0;
    }
    
    
    /**
     * Read all packets
     * 
     * @param string    AMF0 data (optional, uses the initialized one if not given)
     * @return array
     */
    function readAllPackets($str = false) {
        // Initialize if needed
        if ($str !== false) $this->initialize($str);
        
        // Parse each packet
        $ret = array();
        while ($this->index < $this->dataLength)
            $ret[] = $this->readPacket();
            
        // Return it
        return $ret;
    }
    
    /**
     * Read a packet at current index
     *
     * @return mixed
     */
    function readPacket() {    
        // Get data code
        $dataType = ord($this->data[$this->index++]);
        // Interpret
        switch($dataType) {    
            case self::TYPE_NUMBER:        // Number 0x00
                return $this->readNumber();
            case self::TYPE_BOOLEAN:     // Boolean 0x01
                return $this->readBoolean();
            case self::TYPE_STRING:        // String 0x02
                return $this->readString();
                break;
            case self::TYPE_OBJECT:        // Object 0x03
                return $this->readObject();
                break;
            case self::TYPE_MOVIECLIP:        // MovieClip
                throw new Exception("Unhandled AMF type: MovieClip (04)");
                break;
            case self::TYPE_NULL:        // NULL 0x05
                return NULL;
            case self::TYPE_UNDEFINED:        // Undefined 0x06
                return 'undefined';
            case self::TYPE_REFERENCE:        // Reference
                throw new Exception("Unhandled AMF type: Reference (07)");
                break;
            case self::TYPE_MIXEDARRAY :     // Mixed array 0x08
                return $this->readMixedArray();
                break;
            case self::TYPE_OBJECT_TERM:     // ObjectTerm
                throw new Exception("Unhandled AMF type: ObjectTerm (09) -- should only happen in the getObject function");
                break;
            case self::TYPE_ARRAY:    // Array 0x0a
                return $this->readArray();
                break;
            case self::TYPE_DATE:     // Date
                return $this->readDate();
                break;
            case self::TYPE_LONGSTRING:     // LongString
                return $this->readLongString();
                break;
            case TYPE_RECORDSET:     // Recordset
                throw new Exception("Unhandled AMF type: Unsupported (0E)");
                break;
            case self::TYPE_XML:     // XML
                return $this->readLongString();
                break;
            case self::TYPE_TYPED_OBJECT:     // Typed Object
                return $this->readTypedObject();
                break;
            case TYPE_AMF3:     // AMF3
                throw new Exception("Unhandled AMF type: AMF3 (11)");
                break;
            default:
                throw new Exception("Unhandled AMF type: unknown (0x".dechex($dataType).") at offset ".$this->index-1);
        }
    }    
    
    /**
     * Read a string at current index
     *
     * @return string
     */
    function readString() {
        // Get length
        $len = unpack('nlen', substr($this->data,$this->index,2));
        $this->index+=2;
        
        // Get string
        $val = substr($this->data, $this->index, $len['len']);
        $this->index += $len['len'];
        
        // Return it
        return $val;
    }    
    
    /**
     * Read a long string at current index
     *
     * @return string
     */
    function readLongString() {
        // Get length
        $len = unpack('Nlen', substr($this->data,$this->index,2));
        $this->index+=4;
        
        // Get string
        $val = substr($this->data, $this->index, $len['len']);
        $this->index += $len['len'];
        
        // Return it
        return $val;
    }
    
    /**
     * Read a number (double) at current index
     *
     * @return double
     */
    function readNumber() {    
        // Get the packet, big endian (8 bytes long)
        $packed = substr($this->data, $this->index, 8);
        $this->index += 8;
        
        // Reverse it if we're little endian
        if ($this->endian == 0x01) $packed = strrev($packed);

        // Unpack it
        $tmp = unpack("dnumber", $packed);
        
        // Return it
        return $tmp['number'];
    }
    
    /**
     * Read a boolean at current index
     *
     * @return boolean
     */
    function readBoolean() {
        return ord($this->data[$this->index++]) == 1;
    }
    
    /**
     * Read an object at current index
     *
     * @return stdClass
     */
    function readObject() {    
        // Create return object we will add data to
        $ret = new stdClass();
        
        do {
            // Get key
            $key = $this->readString();
            
            // Check if we reached ObjectTerm (09)
            $dataType = ord($this->data[$this->index]);
            
            // If it's not an Object Term, read the packet
            if ($dataType != self::TYPE_OBJECT_TERM) {
                // Get data
                $val = $this->readPacket();
                
                // Store it
                $ret->$key = $val;
            }
        } while ($dataType != 0x09);

        // Skip the Object Term
        $this->index += 1; 
        
        // Return object
        return $ret;
    }
    
    /**
     * Read a typed object at current index
     *
     * @return stdClass
     */
    function readTypedObject() {
        $className = $this->readString();
        $object = $this->readObject();
        
        $object->__className = $className;
        return $object;
    }

    /**
     * Read a mixed array at current position
     * 
     * Note: A mixed array is basically an object, but with a long integer describing its highest index at first.
     *
     * @return array
     */
    function readMixedArray() {    
        // Skip the index
        $this->index += 4;
        
        // Parse the object, but return it as an array
        return get_object_vars($this->readObject());
    }
    
    /**
     * Get an indexed array ([0,1,2,3,4,...])
     *
     * @return array
     */
    function readArray() {    
        // Get item count
        $len = unpack('Nlen',substr($this->data,$this->index,4));
        $this->index+=4;
        
        // Get each packet
        $ret = array();
        for($i=0;$i<$len['len'];$i++) $ret[] = $this->readPacket();
        
        // Return the array
        return $ret;
    }

    /**
     * Read a date at current index
     *
     * @return double
     */
    function readDate() {    
        // Get the packet, big endian (8 bytes long)
        $packed = substr($this->data, $this->index, 8);
        $this->index += 8;
        
        // Reverse it if we're little endian
        if ($this->endian == 0x01) $packed = strrev($packed);

        // Unpack it
        $tmp = unpack("dnumber", $packed);
        $epoch = $tmp['number'];

        // Get timezone
        $tmp = unpack('nnumber', substr($this->data, $this->index, 2));
        $this->index += 2;
        $timezone = $tmp['number'];
        if ($timezone & 32768 == 32768) {
            $timezone = $timezone-65536;
        }
        
        // make epoch GMT, and then convert to local time
        $time = $epoch/1000;
        $time += $timezone*60;    // Timezone is in seconds
        $time += date('Z',$time);
        
        // Return it
        return date('r',$time);
    }
    
    
    /**
     * Writers
     * 
     * 
     */
    function writePacket($value, $type=false) {    
        if ($type === false) {
            if (($value === true) || ($value === false)) $type = self::TYPE_BOOLEAN;
                if (is_numeric($value)) $type = self::TYPE_NUMBER;
                    else if (is_array($value)) {
                        $type = self::TYPE_ARRAY;
                        foreach (array_keys($value) as $k) {
                            if (preg_match(',[^0-9],',$k)) {
                                $type = self::TYPE_MIXEDARRAY;
                                break;
                            }
                        }
                        // Test for mixed/indexed
                    } else if (is_object($value)) {
                        $type = self::TYPE_OBJECT;
                    } else if (is_string($value)) {
                        if (strlen($value) < 65535) {
                            $type = self::TYPE_STRING;
                        } else {
                            $type = self::TYPE_LONGSTRING;
                        }
                    } else if (is_null($value)) {
                        $type = self::TYPE_NULL;
                    }
        }
        
        switch ($type) {
            case self::TYPE_NUMBER:
                return $this->writeNumber($value);
            case self::TYPE_STRING:
                return $this->writeString($value);
            case self::TYPE_LONGSTRING:
                return $this->writeLongString($value);
            case self::TYPE_NULL:
                return $this->writeNull();
            case self::TYPE_BOOLEAN:
                return $this->writeBoolean($value);
            case self::TYPE_ARRAY:
                return $this->writeArray($value);
            case self::TYPE_MIXEDARRAY:
                return $this->writeMixedArray($value);
            case self::TYPE_OBJECT:
                return $this->writeObject($value);
            default:
                throw new Exception('Unhandled AMF0 type: 0x'.dechex($type));
        }
    }
    
    /**
     * Write a string
     *
     * @param string $str
     */
    function writeString($str) {
        // Write type
        $value = chr(self::TYPE_STRING);
        
        // Write length
        $value .= pack('n', strlen($str));
        
        // Write string
        $value .= $str;
        
        // Return it
        return $value;
    }        
    
    /**
     * Write a long string
     *
     * @param string $str
     */
    function writeLongString($str) {
        // Write type
        $value = chr(self::TYPE_LONGSTRING);
        
        // Write length
        $value .= pack('N', strlen($str));
        
        // Write string
        $value .= $str;
        
        // Return it
        return $value;
    }        
    
    /**
     * Write a XML
     *
     * @param string $str
     */
    function writeXML($str) {
        // Write type
        $value = chr(self::TYPE_XML);
        
        // Write length
        $value .= pack('N', strlen($str));
        
        // Write string
        $value .= $str;
        
        // Return it
        return $value;
    }    
    
    /**
     * Write a number
     *
     * @param integer $number
     */
    function writeNumber($number) {
        // Write type
        $value = chr(self::TYPE_NUMBER);
        
        // Build packed
        $packed = pack('d', $number);
        
        // Reverse it if we're little endian
        if ($this->endian == 0x01) $packed = strrev($packed);
        
        // Write packed
        $value .= $packed;
        
        // Return it
        return $value;
    }
    
    
    /**
     * Write a null
     * 
     */
    function writeNull() {
        // Write type
        $value = chr(self::TYPE_NULL);

        // Return it
        return $value;
    }
    
    /**
     * Write a boolean
     * 
     * @param bool    $boolean
     */
    function writeBoolean($boolean) {
        // Write type
        $value = chr(self::TYPE_BOOLEAN);
        
        // Write value
        $value .= ($boolean ? chr(1) : chr(0));
        
        // Return it
        return $value;
    }    
    
    /**
     * Write a mixed array
     * 
     * @param array    $array
     */
    function writeMixedArray($array) {
        // Write type
        $value = chr(self::TYPE_MIXEDARRAY);
        
        // Write index
        $value .= pack('N',count($array));
        
        // Write as object
        $value .= $this->writeObjectSub($array);
        
        // Return it
        return $value;
    }

    /**
     * Write an object
     * 
     * @param stdClass|array    $object
     */
    function writeObject($object) {
        // Write type
        $value = chr(self::TYPE_OBJECT);
        
        // Write as object
        $value .= $this->writeObjectSub(get_object_vars($object));
        
        // Return it
        return $value;
    }    
    
    /**
     * Write a typed object
     * 
     * @param stdClass|array    $object
     */
    function writeTypedObject($object, $className = false) {
        // Write type
        $value = chr(self::TYPE_TYPED_OBJECT);
        
        // Write class
        if ($className === false) {
            if (isset($object->__className)) $className = $object->__className;
                else $className = get_class($object);
        }
        $value .= $this->writeString($className);
        
        // Write as object
        $value .= $this->writeObjectSub(get_object_vars($object));
        
        // Return it
        return $value;
    }    
    
    /**
     * Write an object, without the leading type
     * 
     * @param stdClass|array    $object
     */
    function writeObjectSub($object) {
        $output = '';
        
        // Write each element
        foreach ($object as $key=>$value) {
            // Write key
            $output .= pack('n',strlen($key)).$key;
            
            // Write value
            $output .= $this->writePacket($value);
        }
        
        // Write object term
        $output .= pack('n',0);
        $output .= chr(0x09);
        
        // Return it
        return $output;
    }
    
    /**
     * Write an index array
     * 
     * @param array    $array
     */
    function writeArray($array) {
        // Write type
        $value = chr(self::TYPE_ARRAY);
        
        // Write length
        $value .= pack('N', count($array));
        
        // Write elements
        foreach ($array as $value) {
            $value .= $this->writePacket($value);
        }
        
        // Return it
        return $value;
    }
    
    /**
     * Write a date
     *
     * @param int $date        Local epoch
     */
    function writeDate($number) {
        // Write type
        $value = chr(self::TYPE_DATE);
        
        // Build packed
        $packed = pack('d', $number*1000);
        
        // Reverse it if we're little endian
        if ($this->endian == 0x01) $packed = strrev($packed);
        
        // Write packed
        $value .= $packed;
        
        // Pack timezone
        $timezone = date('Z');
        if ($timezone < 0) $timezone += 65536;
        $value .= pack('n',$timezone);
        
        // Return it
        return $value;
    }    
    
}

?>
