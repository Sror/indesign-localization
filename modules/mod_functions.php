<?php
function get_line_style($colour, $dash, $pixels) {
	$style = array();
	for($i=1;$i<=$pixels;$i++) {
		$style[] = hexdec($colour);
	}
	for($i=1;$i<=$pixels;$i++) {
		$style[] = hexdec($dash);
	}
	return $style;
}

/**
 * Get the degree of tangent of $x and $y
 *
 * @param float $x as the dividend (opposite)
 * @param float $y as the divisor (adjacent)
 * @return float
 */
function get_degree($x,$y) {
	if($y == 0) return false;
	$deg = rad2deg(atan(abs($x/$y)));
	if($x >= 0) {
		$deg = $y < 0 ? 180 - $deg : $deg;
	} else {
		$deg = $y < 0 ? 180 + $deg : 360 - $deg;
	}
	return $deg;
}

function get_points($x1, $y1, $x2, $y2, $angle) {
	while($angle < 0) {
		$angle += 360;
	}
	$angle = $angle % 360;
	if($angle > 180) $angle -= 180;
	$width = $x2 - $x1;
	$height = $y2 - $y1;
	$a = sqrt((pow($width,2)+pow($height,2))/4) * sin(deg2rad($angle/2)) * 2;
	$c1 = get_degree($width/2,$height/2);
	$d1 = (180-$angle)/2 - $c1;
	$xd1 = sin(deg2rad($d1)) * $a;
	$xo1 = $x1 - $xd1;
	$yd1 = cos(deg2rad($d1)) * $a;
	$yo1 = $y1 + $yd1;
	$c2 = 90 - $c1;
	$d2 = (180-$angle)/2 - $c2;
	$xd2 = cos(deg2rad($d2)) * $a;
	$xo2 = $x1 + $xd2;
	$yd2 = sin(deg2rad($d2)) * $a;
	$yo2 = $y2 + $yd2;
	$xo3 = $x2 + $xd1;
	$yo3 = $y2 - $yd1;
	$xo4 = $x2 - $xd2;
	$yo4 = $y1 - $yd2;
	return array($xo1, $yo1, $xo2, $yo2, $xo3, $yo3, $xo4, $yo4);
}

function html_display_para($para) {
	return nl2br(str_replace("\t","&nbsp;&nbsp;&nbsp;&nbsp;",htmlspecialchars($para)."&nbsp;"));
}

function do_rmdir($dir) {
	if(is_dir($dir)) {
		$objects = scandir($dir);
		foreach($objects as $object) {
			if($object != "." && $object != "..") {
				if(filetype($dir."/".$object) == "dir") {
					do_rmdir($dir."/".$object);
				} else {
					unlink($dir."/".$object);
				}
			}
		}
		reset($objects);
		rmdir($dir);
	}
}

function log_error($error, $type="Unspecified") {
	file_put_contents(ERROR_LOG,date(FORMAT_TIME)." [$type] $error \n",FILE_APPEND);
}

function no_credit_available() {
	header("Location: index.php?layout=system&id=18");
	exit;
}

function server_busy() {
	header("Location: index.php?layout=system&id=3");
	exit;
}

function access_denied() {
	header("Location: index.php?layout=system&id=4");
	exit();
}

function error_creating_file($data='') {
        log_error($data, 'error_creating_file');
	header("Location: index.php?layout=system&id=8");
	exit();
}

function error_uploading_file() {
	header("Location: index.php?layout=system&id=11");
	exit;
}

function GetFileMIME($filename) {
	$finfo = finfo_open(FILEINFO_MIME_TYPE);
	$result = finfo_file($finfo, $filename);
	finfo_close($finfo);
	return $result;
}

function ValidateImage($filename) {
	return in_array(GetFileMIME($filename),array(
		'image/pjpeg',
		'image/jpeg',
		'image/jpg',
		'image/gif',
		'image/x-gif',
		'image/png',
		'image/x-png'
		)
	);
}

function ValidateMedia($filename) {
	return in_array(GetFileMIME($filename),array(
		'audio/mpeg',
		'audio/x-ms-wma',
		'audio/x-wav',
		'video/mpeg',
		'video/mp4',
		'video/x-ms-asf',
		'video/x-ms-wm',
		'video/x-ms-wmv',
		'video/x-msvideo',
		'video/quicktime',
		'application/octet-stream'
		)
	);
}

function ForceDownload($FileName="", $NewName="file.rtf", $Exit=true) {
	if(empty($FileName)) return false;
	if(!file_exists($FileName)) return false;
	@ob_end_clean();
	ob_start();
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.basename($NewName));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: '.filesize($FileName));
    echo readfile_chunked($FileName);
	flush();
    if($Exit) exit;
}

function readfile_chunked($filename,$retbytes=true) {
	$chunksize = 1*(1024*1024); // how many bytes per chunk
	$buffer = '';
	$cnt =0;
	// $handle = fopen($filename, 'rb');
	$handle = fopen($filename, 'rb');
	if ($handle === false) {
		return false;
	}
	while(!feof($handle)) {
		$buffer = fread($handle, $chunksize);
		echo $buffer;
		ob_flush();
		flush();
		if ($retbytes) {
			$cnt += strlen($buffer);
		}
	}
	$status = fclose($handle);
	if($retbytes && $status) {
		return $cnt; // return num. bytes delivered like readfile() does.
	}
	return $status;
}

//restrict name
function RestrictName($name) {
	return preg_replace('/[^\w\.\-_\(\)]+/iu', '_', trim($name));
}

function BareFilename($Filename, $base=true) {
	if($base === true) $Filename = basename($Filename);
	if (preg_match('/(.*?)(?:\..{0,4})?$/iu', $Filename, $regs)) {
		return preg_replace('/[.]/iu', '_', $regs[1]);
	} else {
		return false;
	}
}

function DisplayString($string) {
	return strlen($string)>MAX_STRING_LENGTH ? substr($string,0,MAX_STRING_LENGTH).'...' : $string;
}

/**
 * Convert byte to kilobytes
 * Format it with thousand break
 *
 * @param integer $bytes
 * @return string
 */
function convert_byte($bytes) {
	return number_format(ceil($bytes/1024),0,'.',',')." KB";
}

function MMtoPX($mm=0, $dpi=72) {
	$px = ($mm / 25.4) * $dpi;
	return $px;
}
	
function PXtoMM($px=0, $dpi=72) {
	$mm = ($px * 25.4) / $dpi;
	return $mm;
}

function ListFiles($from = '.') {
	if(!is_dir($from))
		return false;
	
	$files = array();
	$dirs = array($from);
	while( NULL !== ($dir = array_pop($dirs))) {
		if( $dh = opendir($dir)) {
			while( false !== ($file = readdir($dh))) {
				if( $file == '.' || $file == '..')
					continue;
				$path = $dir . '/' . $file;
				if(is_dir($path)) {
					$dirs[] = $path;
				} else {
					$files[] = $path;
				}
			}
			closedir($dh);
		}
	}
	return $files;
}

//currency converter via google.com
function GoogleConvert($amount, $currFrom, $currInto) {
	if (trim($amount)=="" ||!is_numeric($amount))
	{
		trigger_error("Please enter a valid amount",E_USER_ERROR);             
	}
	$gurl="http://www.google.com/search?&q=$amount+$currFrom+in+$currInto";
	$data = file_get_contents($gurl);
	if (preg_match('#<h2 class=r style="font-size:\d*%"><b>(.*?)</b></h2>#Usim', $data,$Matches)) {
		preg_match('/=(.*?)[a-z]/sim', $Matches[1], $Matches);
		$Rate = (float)preg_replace('/[^\d.]/sim', '', $Matches[1]);
	} else {
		$Rate = 0;
	}
	return $Rate;
}

//currency converter via xe.com
function XeConvert($amount, $currFrom, $currInto) {
	$ch = curl_init("http://www.xe.com/ucc/convert.cgi");
	curl_setopt($ch, CURLOPT_POSTFIELDS,"Amount=$amount&From=$currFrom&To=$currInto");
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
	curl_setopt($ch, CURLOPT_HEADER, 0);  // DO NOT RETURN HTTP HEADERS
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);  // RETURN THE CONTENTS OF THE CALL
	$Data = curl_exec($ch);
	preg_match_all('/align="(?:right|left)" class="XEenlarge"><h2 class="XE">(.*?)</sim', $Data, $result, PREG_PATTERN_ORDER);
	$Rate = (float)preg_replace('/[^\d.]/sim', '', $result[1][1]);
	return $Rate;
}