<?php

namespace Webpatser\Uuid;
 
class Uuid
{
    const MD5 = 3;
    const SHA1 = 5;
    /**
     * 00001111  Clears all bits of version byte with AND
     *
     * @var int
     */
    const clearVer = 15;
    /**
     * 00111111  Clears all relevant bits of variant byte with AND
     *
     * @var int
     */
    const clearVar = 63;
    /**
     * 11100000  Variant reserved for future use
     *
     * @var int
     */
    const varRes = 224;
    /**
     * 11000000  Microsft GUID variant
     *
     * @var int
     */
    const varMS = 192;
    /**
     * 10000000  The RFC 4122 variant (this variant)
     *
     * @var int
     */
    const varRFC = 128;
    /**
     * 00000000  The NCS compatibility variant
     *
     * @var int
     */
    const varNCS = 0;
    /**
     * 00010000
     *
     * @var int
     */
    const version1 = 16;
    /**
     * 00110000
     *
     * @var int
     */
    const version3 = 48;
    /**
     * 01000000
     *
     * @var int
     */
    const version4 = 64;
    /**
     * 01010000
     *
     * @var int
     */
    const version5 = 80;
    /**
     * Time (in 100ns steps) between the start of the UTC and Unix epochs
     *
     * @var int
     */
    const interval = 0x01b21dd213814000;
    /**
     * @var string
     */
    const nsDNS = '6ba7b810-9dad-11d1-80b4-00c04fd430c8';
    /**
     * @var string
     */
    const nsURL = '6ba7b811-9dad-11d1-80b4-00c04fd430c8';
    /**
     * @var string
     */
    const nsOID = '6ba7b812-9dad-11d1-80b4-00c04fd430c8';
    /**
     * @var string
     */
    const nsX500 = '6ba7b814-9dad-11d1-80b4-00c04fd430c8';
    /**
     * @var string
     */
    protected static $randomFunc = 'randomTwister';
    /**
     * @var mixed
     */
    protected static $randomSource = NULL;
    protected $bytes;
    protected $hex;
    protected $string;
    protected $urn;
    protected $version;
    protected $variant;
    protected $node;
    protected $time;
 
    /**
     * @param string $uuid
     * @throws Exception
     */
    protected function __construct ($uuid)
    {
        if (strlen($uuid) != 16) {
            throw new \Exception('Input must be a 128-bit integer.');
        }
 
        $this->bytes = $uuid;
 
        // Optimize the most common use
        $this->string = bin2hex(
            substr($uuid, 0, 4)) . "-" . bin2hex(
            substr($uuid, 4, 2)) . "-" . bin2hex(
            substr($uuid, 6, 2)) . "-" . bin2hex(
            substr($uuid, 8, 2)) . "-" . bin2hex(
            substr($uuid, 10, 6));
    }
 
 
    /**
     * @param int $ver
     * @param unknown_type $node
     * @param unknown_type $ns
     * @return Uuid
     * @throws Exception
     */
    public static function generate ($ver = 1, $node = NULL, $ns = NULL)
    {
        /* Create a new UUID based on provided data. */
        switch ((int) $ver) {
            case 1:
                return new self( self::mintTime( $node ) );
            case 2:
                // Version 2 is not supported
                throw new \Exception( 'Version 2 is unsupported.' );
            case 3:
                return new self( self::mintName( self::MD5, $node, $ns ) );
            case 4:
                return new self( self::mintRand() );
            case 5:
                return new self( self::mintName( self::SHA1, $node, $ns ) );
            default:
                throw new \Exception( 'Selected version is invalid or unsupported.' );
        }
    }
 
    /**
     * Import an existing UUID
     *
     * @param string $uuid
     * @return Uuid
     */
    public static function import ($uuid)
    {
        return new self(self::makeBin($uuid, 16));
    }
 
    /**
     * Compares the binary representations of two UUIDs.
     * The comparison will return true if they are bit-exact,
     * or if neither is valid.
     *
     * @param string $a
     * @param string $b
     * @return string|string
     */
    public static function compare ($a, $b)
    {
        /*  */
        if (self::makeBin($a, 16) == self::makeBin($b, 16)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
 
    /**
     * Echo the uuid
     */
    public function __toString ()
    {
        return $this->string;
    }
 
 
    /**
     * @param string $var
     * @return string|string|number|number|number|number|number|NULL|number|NULL|NULL
     */
    public function __get ($var)
    {
        switch ($var) {
            case "bytes":
                return $this->bytes;
            case "hex":
                return bin2hex($this->bytes);
            case "string":
                return $this->__toString();
            case "urn":
                return "urn:uuid:" . $this->__toString();
            case "version":
                return ord($this->bytes[6]) >> 4;
            case "variant":
                $byte = ord(
                $this->bytes[8]);
                if ($byte >= self::varRes) return 3;
                if ($byte >= self::varMS) return 2;
                if ($byte >= self::varRFC) {
                    return 1;
                } else {
                    return 0;
                }
            case "node":
                if (ord($this->bytes[6]) >> 4 == 1) {
                    return bin2hex(substr($this->bytes,10));
                } else {
                    return NULL;
                }
            case "time":
                if (ord($this->bytes[6]) >> 4 == 1) {
                    // Restore contiguous big-endian byte order
                    $time = bin2hex( $this->bytes[6] . $this->bytes[7] . $this->bytes[4] . $this->bytes[5] .
                                     $this->bytes[0] . $this->bytes[1] . $this->bytes[2] . $this->bytes[3]);
                    // Clear version flag
                    $time[0] = "0";
                    // Do some reverse arithmetic to get a Unix timestamp
                    $time = (hexdec($time) - self::interval) / 10000000;
                    return $time;
                } else
                    return NULL;
            default:
                return NULL;
        }
    }
 
    /**
     * Generates a Version 1 UUID.
     * These are derived from the time at which they were generated.
     *
     * @param unknown_type $node
     * @return unknown
     */
    protected static function mintTime ($node = NULL)
    {
 
        /** Get time since Gregorian calendar reform in 100ns intervals
         * This is exceedingly difficult because of PHP's (and pack()'s)
         * integer size limits.
         * Note that this will never be more accurate than to the microsecond.
         */
        $time = microtime(1) * 10000000 + self::interval;
 
        // Convert to a string representation
        $time = sprintf("%F", $time);
 
        //strip decimal point
        preg_match("/^\d+/", $time, $time);
 
        // And now to a 64-bit binary representation
        $time = base_convert( $time[0], 10, 16);
        $time = pack("H*", str_pad($time, 16, "0", STR_PAD_LEFT) );
 
        // Reorder bytes to their proper locations in the UUID
        $uuid = $time[4] . $time[5] . $time[6] . $time[7] . $time[2] . $time[3] . $time[0] . $time[1];
 
        // Generate a random clock sequence
        $uuid .= self::randomBytes(2);
 
        // set variant
        $uuid[8] = chr(ord($uuid[8]) & self::clearVar | self::varRFC);
 
        // set version
        $uuid[6] = chr(ord($uuid[6]) & self::clearVer | self::version1);
 
        // Set the final 'node' parameter, a MAC address
        if ($node) {
            $node = self::makeBin($node, 6);
        }
 
 
        // If no node was provided or if the node was invalid,
        //  generate a random MAC address and set the multicast bit
        if (! $node) {
            $node = self::randomBytes(6);
            $node[0] = pack("C", ord($node[0]) | 1 );
        }
        $uuid .= $node;
        return $uuid;
    }
    /**
     * Generate a Version 4 UUID.
     * These are derived soly from random numbers.
     * generate random fields
     *
     * @return Uuid
     */
    protected static function mintRand ()
    {
        $uuid = self::randomBytes(16);
        // set variant
        $uuid[8] = chr(ord($uuid[8]) & self::clearVar | self::varRFC);
        // set version
        $uuid[6] = chr(ord($uuid[6]) & self::clearVer | self::version4);
        return $uuid;
    }
    /**
     * Generates a Version 3 or Version 5 UUID.
     * These are derived from a hash of a name and its namespace, in binary form.
     *
     * @param unknown_type $ver
     * @param unknown_type $node
     * @param unknown_type $ns
     * @return Uuid
     * @throws Exception
     */
    protected static function mintName ($ver, $node, $ns)
    {
        if (! $node) {
            throw new \Exception('A name-string is required for Version 3 or 5 UUIDs.');
        }
 
        // if the namespace UUID isn't binary, make it so
        $ns = self::makeBin($ns, 16);
        if (! $ns) {
            throw new \Exception('A binary namespace is required for Version 3 or 5 UUIDs.');
        }
 
        switch ($ver) {
            case self::MD5:
                $version = self::version3;
                $uuid = md5($ns . $node, 1);
                break;
            case self::SHA1:
                $version = self::version5;
                $uuid = substr(sha1($ns . $node, 1), 0, 16);
                break;
        }
 
        // set variant
        $uuid[8] = chr( ord($uuid[8]) & self::clearVar | self::varRFC);
 
        // set version
        $uuid[6] = chr( ord($uuid[6]) & self::clearVer | $version);
        return ($uuid);
    }
    /**
     * Insure that an input string is either binary or hexadecimal.
     * Returns binary representation, or false on failure.
     *
     * @param unknown_type $str
     * @param unknown_type $len
     * @return Uuid|string
     */
    protected static function makeBin ($str, $len)
    {
        if ($str instanceof self)
            return $str->bytes;
        if (strlen($str) == $len) {
            return $str;
        } else {
            // strip URN scheme and namespace
            $str = preg_replace('/^urn:uuid:/is', '', $str);
        }
        // strip non-hex characters
        $str = preg_replace('/[^a-f0-9]/is', '', $str);
        if (strlen($str) != ($len * 2)) {
            return FALSE;
        } else {
            return pack("H*", $str);
        }
    }
 
    /**
     * Look for a system-provided source of randomness, which is usually crytographically secure.
     * /dev/urandom is tried first simply out of bias for Linux systems.
     */
    public static function initRandom ()
    {
        if (is_readable('/dev/urandom')) {
            self::$randomSource = fopen( '/dev/urandom', 'rb');
            self::$randomFunc = 'randomFRead';
        } else
            // See http://msdn.microsoft.com/en-us/library/aa388182(VS.85).aspx
            if (class_exists('COM', 0)) {
                try {
                    self::$randomSource = new COM('CAPICOM.Utilities.1');
                    self::$randomFunc = 'randomCOM';
                } catch (\Exception $e) {
                    throw new \Exception ('Cannot initialize windows random generator');
                }
            }
        return self::$randomFunc;
    }
 
    public static function randomBytes ($bytes)
    {
        return call_user_func(array('self', self::$randomFunc), $bytes);
    }
    /**
     * Get the specified number of random bytes, using mt_rand().
     * Randomness is returned as a string of bytes.
     *
     * @param unknown_type $bytes
     * @return string
     */
    protected static function randomTwister ($bytes)
    {
        $rand = "";
        for ($a = 0; $a < $bytes; $a ++) {
            $rand .= chr(mt_rand(0, 255));
        }
        return $rand;
    }
 
    /**
     * Get the specified number of random bytes using a file handle
     * previously opened with UUID::initRandom().
     * Randomness is returned as a string of bytes.
     *
     * @param unknown_type $bytes
     */
    protected static function randomFRead ($bytes)
    {
        return fread(self::$randomSource, $bytes);
    }
    /**
     * Get the specified number of random bytes using Windows'
     * randomness source via a COM object previously created by UUID::initRandom().
     * Randomness is returned as a string of bytes.
     *
     * Straight binary mysteriously doesn't work, hence the base64
     *
     * @param unknown_type $bytes
     */
    protected static function randomCOM ($bytes)
    {
        return base64_decode(self::$randomSource->GetRandom($bytes, 0));
    }
}