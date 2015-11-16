<?php
##########################
#### Created By: Dubz ####
##########################

  ########################
#### This file contains ####
####  a few basic php   ####
####     functions      ####
  ########################

//Define BASICFUNCTIONS so users can add a check for this file and avoid calling it twice
define("BASICFUNCTIONS", true);

/*
* @credit 3nvisi0n
*
* Searches the given string and returns the inside of the left and right paremeters (case-sensitive)
*
* @return A string of text between $deliLeft and $deliRight
*/
function strbet($inputstr, $deliLeft, $deliRight)
{
	$posLeft = strpos($inputstr, $deliLeft) + strlen($deliLeft);
	$posRight = strpos($inputstr, $deliRight, $posLeft);
	return substr($inputstr, $posLeft, $posRight - $posLeft);
}


/*
* @credit 3nvisi0n
*
* Searches the given string and returns the inside of the left and right paremeters (case-insensitive)
*
* @return A string of text between $deliLeft and $deliRight
*/
function stribet($inputstr, $deliLeft, $deliRight)
{
	$posLeft = stripos($inputstr, $deliLeft) + strlen($deliLeft);
	$posRight = stripos($inputstr, $deliRight, $posLeft);
	return substr($inputstr, $posLeft, $posRight - $posLeft);
}


/*
* @credit 3nvisi0n
*
* Opens a webpage and grabs the sites source code (html)
*
* @param $headers_additional An array of additional headers, also overwrites headers listed
* @param $headers_return Determines if you want headers returned in the results
* @param $headers_parse Whether or not you want the returned data split up and parsed to an array or not
* @return A string of the source code
*/
function get($url, $headers_additional = array(), $headers_return = false, $headers_parse = false)
{
	$urlp = parse_url($url);
	$fp = fsockopen($urlp['host'], getURLPort($urlp));
	$path = explode('/', $url, 4);
	$cp = count($path);
	$path = ($cp >= 4) ? $path[3] : "";
	#Set default request headers
	$headers_main = array(
		'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
		'Accept' => '',
		'Accept-Charset' => 'ISO-8859-1,utf-8;q=0.7,*;q=0.7',
		'Host' => $urlp['host'],
		'Accept-Language' => 'en-us,en;q=0.5',
		'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0) Gecko/20100101 Firefox/6.0',
		'Connection' => 'close',
	);
	#Add/replace headers with user defined variables
	$headers_main = array_merge($headers_main, $headers_additional);
	#Build request headers
	$request_headers = "GET /$path ".strtoupper($urlp['scheme'])."/1.0\r\n";
	foreach($headers_main as $key => $value)
		$request_headers .= $key.':'.$value."\r\n";
	$request_headers .= "\r\n";
	fputs($fp, $request_headers);
	stream_set_timeout($fp, 4);
	$res = stream_get_contents($fp);
	fclose($fp);
	if($headers_return)
	{
		if($headers_parse)
		{
			$data = explode("\r\n\r\n", $res, 2);
			$data[0] = explode("\r\n", $data[0]);
			$headers = array();
			foreach($data[0] as $header)
			{
				@list($key, $value) = explode(':', $header, 2);
				$headers[trim($key)] = trim($value);
			}
			return array('headers' => $headers, 'content' => $data[1]);
		}
		else
			return $res;
	}
	else
	{
		$res = explode("\r\n\r\n", $res, 2);
		return $res[1];
	}
}


/*
* @credit jmj001
* @credit Dubz
*
* Posts data to a website using the curl method
*
* @param $url The URL to post to
* @param $post_data The array of data to be posted
* @param $headers Additional headers to be sent
* @param $proxy The proxy address to be used
* @param $proxyport Port to connect to proxy
* @param $proxywd Password to access proxy
* @param $proxtype Type of proxy connection
* @param $timeout Time to wait for proxy connection
* @return An array of strings containing the status and content (html)
*/
function curl_post($url, $post_data, $headers = array(), $proxy = null, $proxyport = null, $proxypwd = false, $proxytype = CURLPROXY_HTTP, $timeout = 30)
{
	$curl = curl_init();
	if($curl)
	{
		curl_setopt($curl, CURLOPT_URL, $url);
		if($proxy)
		{
			curl_setopt($curl, CURLOPT_PROXY, $proxy);
			curl_setopt($curl, CURLOPT_PROXYPORT, $proxyport);
			if($proxypwd)
				curl_setopt($curl, CURLOPT_PROXYUSERPWD, $proxypwd);
			curl_setopt($curl, CURLOPT_PROXYTYPE, $proxytype);
		}
		curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.153 Safari/537.36');
		curl_setopt($curl, CURLOPT_HEADER, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
		#Lets merge the headers with some defaults
		$headers = array_merge(array('Content-Type' => 'application/x-www-form-urlencoded'), $headers);
		#Change the array to add the key to the values and set
		$headers = array_map(function($v, $k) { return ($v ? $k.': '.$v : ''); }, $headers, array_keys($headers));
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLINFO_HEADER_OUT, true);
		$html = curl_exec($curl);
		curl_close($curl);
		return array(
			"status" => "ok",
			"content" => $html
		);
	}
	else
	{
		return array(
			"status" => "error",
		);
	}
}


/*
* @credit Dubz
* Determines if the file is ran by command line
*
* @return A boolean regarding execution by command line
*/
function commandLine()
{
	return (in_array(php_sapi_name(), array('cgi', 'cgi-fcgi', 'cli', 'cli-server')) OR defined('STDIN'));
}


/*
* @credit Dubz
*
* Gets the IP of the user requesting the file
*
* @return A string containing the user's IP
*/
function getIP()
{
	#Catch for CLI executions
	if(commandLine())
		return false;
	if(key_exists('HTTP_CF_CONNECTING_IP', $_SERVER))	   #Cloudflare handler
		$ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
	elseif(key_exists('HTTP_X_FORWARDED_FOR', $_SERVER))
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	else
		$ip = $_SERVER['REMOTE_ADDR'];
	$ip = trim($ip);
	if($ip == '::1')
		$ip = 'localhost';
	return $ip;
}


/*
* @credit Dubz
*
* Generates a random string
*
* @param $length The length of the string to return
* @param $lower Include lower-case characters
* @param $upeer Include upper-case characters
* @param $numeric Include numeric characters
* @return A randomly generated string or false if empty
*/
function generateRandom($length = 8, $lower = true, $upper = true, $numeric = true)
{
	$length = (String)floor($length);
	if(!ctype_digit($length) || $length < 1)
		return false;
	$array = array();
	$string = '';
	if($lower)
		$array = array_merge($array, range('a', 'z'));
	if($upper)
		$array = array_merge($array, range('A', 'Z'));
	if($numeric)
		$array = array_merge($array, range('0', '9'));
	if(empty($array))
		return null;
	while(strlen($string) < $length)
	{
		$string .= $array[array_rand($array)];
	}
	return $string;
}


/*
* @credit Dubz
*
* Converts a numerical time() to a 'datetime' format
*
* @return A string with mysql 'datetime' stamp
*/
function mysql_datetime($time = null)
{
	if(!$time)
		$time = time();
	$stamp = date("Y-m-d H:i:s", $time);
	return $stamp;
}


/*
* @credit Dubz
*
* Opens and saves an image from a website or local location
*
* @param $inPath Location to get image
* @param $directory Location to save image
* @return Returns a boolean of if the image was saved
*/
function save_image($inPath, $directory = '')
{
	//Download images from remote server
	$file = explode('/', $inPath);
	$file = array_pop($file);
	if(substr($directory, -1) != '/' || empty($directory))
		$directory .= '/';
	if(!is_dir($directory))
		mkdir($directory);
	$outPath = $directory.$file;
	$in = @fopen($inPath, "rb");
	if($in)
	{
		$out = fopen($outPath, "wb");
		while($chunk = fread($in, 8192))
		{
			fwrite($out, $chunk, 8192);
		}
		fclose($in);
		fclose($out);
		return true;
	}
	else
		return false;
}


/*
* @credit Dubz
*
* Checks if the given string is base64 valid
*
* @param $base64String String to be tested for base64 validity
* @return Returns a boolean of if it is base64 valid
*/
function is_base64($base64String)
{
	return (base64_encode(base64_decode($base64String)) == $base64String);
}


/*
* @credit Dubz
*
* Checks if the given string is a serialized array
*
* @param $serializedString String to be tested
* @return Returns a boolean of if it is a serialized array
*/
function is_serialized($serializedString)
{
	return (@unserialize($serializedString) !== false);
}



/*
* @credit Dubz
*
* Checks if the port is open for the ip address
*
* @param $ip IP adress of server checking
* @param $port Port number to check if open
* @return Returns a boolean of the port's status
*/
function checkPort($ip, $port = 80, $timeout = 2)
{
	if(!$ip || !$port)
		return false;
	$conn = @fsockopen($ip, $port, $errno, $errstr, $timeout);
	if($conn)
	{
		fclose($conn);
		return true;
	}
}


/*
* @credit kbluhm
*
* Turns an array into a string to be saved to a php file
* Used to save an array to a php file instead of a text file, which will not show up if ran
*
* @param $array Array to be converted to string
* @param $arrayName Name to be used for array in return
* @return Returns a string of a php array in standard php format
*
*/
function arraytotext($array, $name = 'array')
{
	$text = '$'.$name.' = '.var_export($array, true).';';
	return $text;
}

/*
* @credit Dubz
*
* Checks if the user is on a proxy network
* NOTE: This is not 100% guaranteed and may return false positives if the client is running a webserver
*
* @return A boolean regarding if they are on a proxy
*/
function usingProxy()
{
	$blockedPorts = array('80', '443', '8080');
	foreach($blockedPorts as $port)
	{
		if(checkPort(getIP(), $port))
			return true;
	}
	return false;
}


/*
* @credit Dubz
*
* Converts an array to a comma separated string following proper grammar rules
*
* @param $list The array to list
* @param $lastIdentifier The word used before the last list item
* @return A string of a properly formatted, comma separated list
*/
function array2commaList($list, $lastIdentifier = 'and')
{
	switch(count($list))
	{
		case 0:
			$string = false;
		break;
		case 1:
			$string = reset($list);
		break;
		case 2:
			$string = implode(' '.$lastIdentifier.' ', $list);
		break;
		default:
			$lastItem = array_pop($list);
			$string = implode(', ', $list).', '.$lastIdentifier.' '.$lastItem;
	}
	return $string;
}


/*
* @credit Dubz
*
* Converts error numbrs to the string used to define them
*
* @param $errno The error number provided
* @return A string that is used to define the error number
*/
function errtostr($errno)
{
	switch($errno)
	{
		case E_ERROR: return 'E_ERROR'; break;							#1
		case E_WARNING: return 'E_WARNING'; break;						#2
		case E_PARSE: return 'E_PARSE'; break;							#4
		case E_NOTICE: return 'E_NOTICE'; break;						#8
		case E_CORE_ERROR: return 'E_CORE_ERROR'; break;				#16
		case E_CORE_WARNING: return 'E_CORE_WARNING'; break;			#32
		case E_COMPILE_ERROR: return 'E_COMPILE_ERROR'; break;			#64
		case E_COMPILE_WARNING: return 'E_COMPILE_WARNING'; break;		#128
		case E_USER_ERROR: return 'E_USER_ERROR'; break;				#256
		case E_USER_WARNING: return 'E_USER_WARNING'; break;			#512
		case E_USER_NOTICE: return 'E_USER_NOTICE'; break;				#1024
		case E_STRICT: return 'E_STRICT'; break;						#2048
		case E_RECOVERABLE_ERROR: return 'E_RECOVERABLE_ERROR'; break;	#4096
		case E_DEPRECATED: return 'E_DEPRECATED'; break;				#8192
		case E_USER_DEPRECATED: return 'E_USER_DEPRECATED'; break;		#16384
		case E_ALL: return 'E_ALL'; break;								#32767
		default:
			return false;
	}
}


/*
* @Credit Dubz
*
* Tells if a number is within the given range
*
* @param $num The number to check
* @param $low The lower number of the range
* @param $high The higher number of the range
* @return A boolen telling if the number is in the range
*/
function in_range($num, $low, $high)
{
	return (($num >= $low) && ($num <= $high));
}


/*
* @Credit Fou-Lu
* @Credit Dubz
*
* Grabs all of the tag attribue values of the html code given
*
* @param $html The code of an html file
* @param $tag The name of the tags to collect
* @return An array of the names and values of the tags
*/
function get_tag_data($html, $tag)
{
	$dom = new DOMDocument('1.0');
	$dom->loadHTML($html);
	$aResult = array();
	if($input = $dom->getElementsByTagName($tag))
	{
		foreach($input as $item)
		{
			$attributes = $item->attributes;
			if($attributes->getNamedItem('name') != null)
			{
				$aResult[$attributes->getNamedItem('name')->nodeValue] = array();
				foreach($attributes as $attr)
				{
					$aResult[$attributes->getNamedItem('name')->nodeValue][$attr->name] = $attr->value;
				}
			}
		}
	}
	return $aResult;
}


/*
* @Credit Dubz
*
* Returns the extension of a given filename
*
* @param $filename The name of the file
* @return A string of the extension
*/
function getFileExtension($filename)
{
	#Remove any extra queries if from a url (won't affect normal queries)
	$filename = preg_replace('/^(.*)\?.*/', '$1', $filename);
	$parts = explode(".", $filename);
	$ext = array_pop($parts);
	return $ext;
}


/*
* @Credit Dubz
*
* Encodes a string to hex
*
* @param $str The string to encode
* @return A hex encoded version of the string
*/
function strtohex($str)
{
	$hex = '';
	for($i = 0; $i < strlen($str); $i++)
		$hex .= '%'.bin2hex($str{$i});
	return $hex;
}


/*
* @Credit Dubz
*
* Decodes a hex string
*
* @param $hex The string to decode
* @return A decoded version of the hex string
*/
function hextostr($hex)
{
	$str = pack("H*", str_replace('%', '', $hex));
	return $str;
}


/*
* @Credit [Unknown]
*
* Properly adjusts the case for a person's name
*
* @param $string The string to capitalize
* @return A properly capitalized string for names
*/
function ucname($string)
{
	$string = ucwords(strtolower($string));
	foreach(array('-', '\'') as $delimiter)
	{
		if(strpos($string, $delimiter) !== false)
			$string = implode($delimiter, array_map('ucfirst', explode($delimiter, $string)));
	}
	return $string;
}


/*
* @Credit [Unknown]
*
* The equivelant to javascript's charCodeAt() function
*
* @param $str The input string
* @param $index The position in the string
* @return 
*/
function charCodeAt($str, $index)
{
	$char = mb_substr($str, $index, 1, 'UTF-8');
	if(mb_check_encoding($char, 'UTF-8'))
	{
		$ret = mb_convert_encoding($char, 'UTF-32BE', 'UTF-8');
		return hexdec(bin2hex($ret));
	}
	return null;
	return utf8_ord(utf8_charAt($str, $index));
}


/*
*
* @Credit [Unknown]
*
* utf8 supported ord function
*
* @param $char The input character
* @return A numeric representation of the ord
*/
function utf8_ord($char)
{
	$len = strlen($char);
	if($len <= 0)
		return false;
	$h = ord($char{0});
	if($h <= 0x7F)
		return $h;
	if($h < 0xC2)	
		return false;
	if($h <= 0xDF && $len>1)
		return ($h & 0x1F) <<  6 | (ord($char{1}) & 0x3F);
	if($h <= 0xEF && $len>2)
		return ($h & 0x0F) << 12 | (ord($char{1}) & 0x3F) << 6 | (ord($char{2}) & 0x3F);		  
	if($h <= 0xF4 && $len>3)
		return ($h & 0x0F) << 18 | (ord($char{1}) & 0x3F) << 12 | (ord($char{2}) & 0x3F) << 6 | (ord($char{3}) & 0x3F);
	return false;
}


/*
*
* @Credit [unknown]
*
* utf-8 supported character selection
*
* @param $str The input string
* @param $num The position of the character
* @return The character at the given position
*/
function utf8_charAt($str, $num)
{
	return mb_substr($str, $num, 1, 'UTF-8');
}


/*
*
* @Credit Rizal Almashoor
*
* Performs a 32 bit left shift on 64 bit machines
*
* @param $a Input number
* @param $b Number of steps
* @Return a number that has been shifted left
*/
function bitShiftLeft_32($a, $b)
{
	// convert to binary (string)
	$binary = decbin($a);
	// left-pad with 0's if necessary
	$binary = str_pad($binary, 64, '0', STR_PAD_LEFT);
	// left shift manually
	$binary .= str_repeat("0", $b);
	// get the last 32 bits
	$binary = substr($binary, -32);
	// if it's a negative number return the 2's complement
	// otherwise just return the number
	if($binary{0} == "1")
	{
		return -(pow(2, 31) - bindec(substr($binary, 1)));
	}
	else
	{
		return bindec($binary);
	}
}


/*
*
* @Credit Dubz
*
* Performs a 32 bit right shift on 64 bit machines
*
* @param $a Input number
* @param $b Number of steps
* @Return a number that has been shifted right
*/
function bitShiftRight_32($a, $b, $leadingZeros = false)
{
	$strip = 32;
	$strip -= $b;
	// convert to binary (string)
	$binary = decbin($a);
	// Pad with 0's for 64-bit
	$binary = str_pad($binary, 64, '0', STR_PAD_LEFT);
	// Strip the numbers to 32-bit and those being shifted
	$binary = substr($binary, 32, $strip);
	// Pad with the first digit to 32-bit
	$binary = str_pad($binary, 32, $leadingZeros ? 0 : $binary{0}, STR_PAD_LEFT);
	// if it's a negative number return the 2's complement
	// otherwise just return the number
	if($binary{0} == "1")
	{
		return -(pow(2, 31) - bindec(substr($binary, 1)));
	}
	else
	{
		return bindec($binary);
	}
}

/*
*
* @Credit Dubz
*
* Performs a 32 bit invert on 64 bit machines
*
* @param $a Input number
* @Return an inverted version of the number
*/
function bitInvert_32($a)
{
	$inverted = -1;
	$inverted -= $a;
	return $inverted;
}


/*
*
* @Credit voromax
*
* Return unicode char by its code
*
* @param $u Input number
* @return The character
*/
function unichr($a)
{
	return mb_convert_encoding('&#'.intval($a).';', 'UTF-8', 'HTML-ENTITIES');
}


/*
*
* @Credit Dubz
*
* Return mime type of file extension
* Data obtained from http://www.iana.org/assignments/media-types/media-types.xhtml
*
* @param $file Direct path to a file
* @param $use_fileinfo Whether or not fileinfo (finfo) should be used (if available)
* @return A string of the mime type
*/
function getMimeType($file, $use_fileinfo = true)
{
	#First see if we can use the file info functions
	if($use_fileinfo && function_exists('finfo_open') && function_exists('finfo_file'))
	{
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$type = finfo_file($finfo, $file);
		finfo_close($finfo);
		return $type;
	}
	#If not, fall back to a table of types by extension
	switch(getFileExtension($file))
	{
		#Applications
		case '7z':		return 'application/x-7z-compressed';
		case 'eot': 	return 'application/vnd.ms-fontobject';
		case 'exe':		return 'application/exe';
		case 'gz':		return 'application/gzip';
		case 'js':		return 'application/javascript';
		case 'json':	return 'application/json';
		case 'swf':		return 'application/x-shockwave-flash';
		case 'ttf':		return 'application/x-font-ttf';
		case 'woff':	return 'application/font-woff';
		case 'xcs':		return 'application/calendar+xml';
		case 'zip':		return 'application/zip';
		#Images
		case 'bmp':		return 'image/bmp';
		case 'ico':		return 'image/ico';
		case 'gif':		return 'image/gif';
		case 'jpg':		return 'image/jpeg';
		case 'jpeg':	return 'image/jpeg';
		case 'png':		return 'image/png';
		case 'svg':		return 'image/svg+xml';
		case 'tiff':	return 'image/tiff';
		#Text
		case 'css':		return 'text/css';
		case 'csv':		return 'text/csv';
		case 'html':	return 'text/html';
		case 'rtf':		return 'text/rtf';
		case 'txt':		return 'text/plain';
		case 'xml':		return 'text/xml';
		#Videos
		case '3gp':		return 'video/3gpp';
		case '3gpp':	return 'video/3gpp';
		case 'mp4':		return 'video/mp4';
		#Default/unknown
		default:		return 'text/plain';
	}
}


/*
*
* @Credit Dubz
*
* Replace words in a string with strings matching the regex with a given array or set constants
*
* @param $data A string (or array of strings) to replace
* @param $replacement An array of variables to use instead of constants
* @param $pattern The regex pattern to search for using $1 as the word filler (default is between brackets {$1})
* @return A string of the mime type
*/
function replace($data, $replacement = null, $pattern = '/\{$1\}/')
{
	#Make sure the variable is in the pattern, else append it
	if(strpos($pattern, '$1') === false)
		$pattern .= '$1';
	#Are we doing this with an array? If so, handle it with recursion and return it
	if(is_array($data))
	{
		foreach($data as $k => $v)
			$data[$k] = replace($v, $replacement, $pattern);
		return $data;
	}
	#Find the constants by the pattern
	if(is_null($replacement))
	{
		preg_match_all(preg_replace('/\$1/', '([a-zA-Z\_]{1}\w*)', $pattern), $data, $matches);
		#Remove duplicates in the array
		$matches = array_unique($matches[1]);
		#Loop through the pattern and replace any matches that are defined as constants
		foreach($matches as $match)
		{
			if(defined($match))
				$data = preg_replace(preg_replace('/\$1/', $match, $pattern), constant($match), $data);
		}
	}
	#Use the array of replacement data to replace
	else
	{
		foreach($replacement as $key => $value)
		{
			if(!is_array($value))
				$data = preg_replace(preg_replace('/\$1/', $key, $pattern), $value, $data);
		}
	}
	#Return the data
	return $data;
}


/*
*
* @Credit xdazz
*
* Convert a Unicode number into a character
*
* This function simulates JavaScripts version of string.fromCharCode()
*
* @return A string of the characters combined
*/
function str_fromCharCode()
{
	return implode(array_map('chr', func_get_args()));
}


/*
*
* @Credit Dubz
*
* Index a directory
*
* This takes a directory and creates an array
* where the key is the parent folder and the
* values are the files and sub-folders
*
* @param $dir The directory to index
* @param $sort Whether or not to sort sub-files/folders
* @param $sort2 Whether or not to sort the final array
* @return An array of the directory indexed
*
*/
function builddir($dir, $sort = true, $sort2 = true)
{
	if(substr($dir, -1) != DIRECTORY_SEPARATOR)
		$dir .= DIRECTORY_SEPARATOR;
	$files = array();
	$tmp = array();
	$handler = opendir($dir);
	while(($file = readdir($handler)) !== false)
	{
		#Skip special folders
		if(in_array($file, array('.', '..')))
			continue;
		$tmp[] = $file;
	}
	if($sort)
		sort($tmp);
	foreach($tmp as $file)
	{
		if(is_dir($dir.$file))
			$files[$file] = builddir($dir.$file, $sort, $sort2);
		else
			$files[] = $file;
	}
	if($sort2)
		ksort($files);
	return $files;
}


/*
*
* @Credit Dubz
*
* Index a directory to one array with real file paths
*
* This takes a directory and creates an array
* of the files real paths
*
* @param $dir The directory to index
* @return An array of the directory indexed
*
*/
function buildpaths($dir)
{
	if(substr($dir, -1) != DIRECTORY_SEPARATOR)
		$dir .= DIRECTORY_SEPARATOR;
	$files = array();
	$tmp = array();
	$handler = opendir($dir);
	while(($file = readdir($handler)) !== false)
	{
		#Skip special folders
		if(in_array($file, array('.', '..')))
			continue;
		$tmp[] = $file;
	}
	foreach($tmp as $file)
	{
		if(is_dir($dir.$file))
			$files = array_merge($files, buildpaths($dir.$file));
		else
			$files[] = $dir.$file;
	}
	return $files;
}


/*
*
* @Credit Dubz
*
* Fills an IP address and adds missing 0's
* IPv4: 127.0.0.1 = 127.000.000.001
* IPv6: ::1 = 0000:0000:0000:0000:0000:0000:0000:0001
*
* @param $ip  The IP address to convert (defaults to the getIP() function above)
* @return A string of the IP converted
*/
function full_ip($ip = false)
{
	if(!$ip)
		$ip = getIP();
	if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
	{
		#I there a bunch of 0's to be filled in this?
		if(stripos($ip, '::') !== false)
		{
			#Get the data on either side of the shortener
			list($left, $right) = explode('::', $ip);
			#How much do we need to insert?
			$total = ($left ? count(explode(':', $left)) : 0) + ($right ? count(explode(':', $right)) : 0);
			#Generate the middle contents
			$middle = implode(':', array_fill(0, 8 - $total, '0000'));
			#Pad everything
			$ip = array();
			#Fill left and add
			if($left)
			{
				$l = explode(':', $left);
				foreach($l as $k => $v)
					$l[$k] = str_pad($v, 4, '0', STR_PAD_LEFT);
				$ip[] = implode(':', $l);
			}
			#Add the middle
			$ip[] = $middle;
			#Fill right and add
			if($right)
			{
				$r = explode(':', $right);
				foreach($r as $k => $v)
					$r[$k] = str_pad($v, 4, '0', STR_PAD_LEFT);
				$ip[] = implode(':', $r);
			}
			#Stitch it back together
			$ip = implode(':', $ip);
		}
		#Otherwise, just 0 pad everything
		else
		{
			$ip = explode(':', $ip);
			foreach($ip as $k => $v)
				$ip[$k] = str_pad($v, 4, '0', STR_PAD_LEFT);
			$ip = implode(':', $ip);
		}
	}
	else
	{
		$ip = explode('.', $ip);
		foreach($ip as $k => $v)
			$ip[$k] = str_pad($v, 3, '0', STR_PAD_LEFT);
		$ip = implode('.', $ip);
	}
	return $ip;
}


/*
*
* @Credit Dubz
*
* Equivalent of JavaScript's parseInt()
*
* @param $str  The input string to parse
* @return An integer of the numbers in the input string
*/
function parseInt($str)
{
	return (int)preg_replace('/\D/', '', $str);
}


/*
*
* @Credit vdklah
*
* Returns a port to use from a parsed url
*
* @param $urlInfo  An array obtained from parse_url()
* @return An integer of the port to be used
*/
function getURLPort($urlInfo)
{
	if(isset($urlInfo['port']))
		return $urlInfo['port'];
	else
	{
		//No port specified; get default port
		if(isset($urlInfo['scheme']))
		{
			switch($urlInfo['scheme'])
			{
				case 'http':
					return 80; //Default for http
				case 'https':
					return 443; //Default for https
				case 'ftp':
					return 21; //Default for ftp
				case 'ftps':
					return 990; //Default for ftps
                default:
					return 0; //Error; Unsupported scheme
			}
		}
		else
			return 0; //Error; Unknown scheme
	}
}
