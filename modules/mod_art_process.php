<?php
@session_start();
if(empty($_SESSION['joblog'])) exit();
$log_file = $_SESSION['joblog'];
if(file_exists($log_file) && is_file($log_file)){
	$log_file = file_get_contents($log_file);
	$log_file= formatlog($log_file);
}
exit($log_file);
function formatlog($log_file){
	return nl2br($log_file);
}
//$tmp_dir = dirname(__FILE__)."/../tmp/";
//$output_dir = "C:/Server Documents/Output/";
//require_once "../config.php";
// token may be either a real token from artwork uploading or artwork filename

/*
// token may be either a real token from artwork uploading or artwork filename
$token = !empty($_GET['token']) ? (string)$_GET['token'] : '';
if(empty($token)) {
	$output = 'Invalid Token';
} else {
	$tmp_token_file = ROOT.TMP_DIR.$token;
	if(file_exists($tmp_token_file)) {
		$filename = file_get_contents($tmp_token_file);
	} else {
		$filename = $token;
	}
	$log_file = OUTPUT_DIR."$filename/Progress.log";
	if(file_exists($log_file)) {
		$content = file_get_contents(OUTPUT_DIR."$filename/Progress.log");
		if(empty($content)) {
			$output = 'Retrieving Process Log...';
		} else {
			$output = nl2br($content);
		}
	} else {
		$output = 'No Process Log';
	}
}
echo $output;
exit;
*/