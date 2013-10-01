<?php
require_once dirname(__FILE__).'/../config.php';
// Fun with unicode, quite possibly not needed, at least most of the time
mb_internal_encoding('UTF-8');
mb_language('uni');

/******************************************************************************
	File name handling code
******************************************************************************/
// Disallowed NTFS characters plus an escape (%) and . in ascending ASCII order
// the . is removed to stop thanes like .htaccess and also space as file names
// can't end with a . or space, nice. still can make a bad name like COM7 so
// something to work on. Have a function that removes any invalid chars/strs but
// non-recoverable names
define('FILENAME_ESCAPES',"\0\"%*./:<>?\\| ");
function filename_encode($name){
	$newname = '';
	$e = strlen($name);
	for($i=0; $i<$e; $i++){
		$c=substr($name,$i,1);
		if(($n=strpos(FILENAME_ESCAPES,$c))!==FALSE){
			$c = sprintf('%%%x',$n);
		}
		$newname .= $c;
	}
	return $newname;
}
// could error if a filenames are not in the encoded format
function filename_decode($name){
	$newname = '';
	$e = strlen($name);
	for($i=0;$i<$e;){
		if(($c=substr($name,$i++,1))=='%'){
			$c = substr(FILENAME_ESCAPES,hexdec(substr($name,$i++,1)),1);
		}
		$newname .= $c;
	}
	return $newname;
}

/******************************************************************************
	Miscelanious functions - should probably be globally availible
******************************************************************************//*
function ByteNormalise($bytes){
	$mul = array('B','KiB','MiB','GiB','TiB','PiB','YiB');
	$lim = 1024;
	foreach($mul as $n=>$suffix){
		if($bytes < $lim)break;
		$lim *= 1024;
	}
	return rtrim(rtrim(sprintf("%.3f",$bytes/($lim/1024)),'0'),'.').$suffix;
}
*/
/******************************************************************************
	Lists the joboption files located in the JOBOPTIONS_DIRECTORY, return
	them in an associative array where the key is the decoded name of the
	file and the value is the name of the stored file with the extension.
	The location is relative to the JOBOPTIONS_DIRECTORY so a URL or path
	can be created with either the JOBOPTIONS_SITE | JOBOPTIONS_DIRECTORY
	prefix addition. Every one of these lines is exactly the same length!
******************************************************************************/
function list_joboptions($ascending=TRUE){
	$dir = opendir(JOBOPTIONS_DIR);
	$files = array();
	while(($entry=readdir($dir))!==FALSE){
		//$entry = JOBOPTIONS_DIRECTORY . $entry;
		if(is_dir(JOBOPTIONS_DIR . $entry))
			continue;
		$info = pathinfo($entry);
		if(isset($info['extension']) && $info['extension']!='joboptions')
			continue;
		
		$filename = filename_decode($info['filename']);
		$files[$filename] = $entry;
	}
	// Not entierly natural because of unicode
	if($ascending)
		uasort($files,function($a,$b){
			return strnatcasecmp($a,$b);
		});
	else
		uasort($files,function($a,$b){
			return -strnatcasecmp($a,$b);
		});
	return $files;
}

