<?php
$code = (isset($_GET['id'])) ? $_GET['id'] : 0;
if($code==1) {
	$access = array("system","login");
	require_once(MODULES.'mod_authorise.php');
	
	$userID = (isset($_SESSION['userID'])) ? $_SESSION['userID'] : 0;
	
	$query_user = sprintf("SELECT username, lastLogin, lastIP
						FROM users WHERE userID = %d",
						$userID);
	$result_user = mysql_query($query_user, $conn) or die(mysql_error());
	$row_user = mysql_fetch_assoc($result_user);
	
	$currentIP = $_SERVER['REMOTE_ADDR'];
	$update = sprintf("UPDATE users SET
					lastLogin = NOW(),
					lastIP = '%s',
					lastActive = NOW()
					WHERE userID = %d",
					mysql_real_escape_string($currentIP),
					$userID);
	$result = mysql_query($update, $conn) or die(mysql_error());
	$DB->LogSystemEvent($userID,"logged in from $currentIP");
}

$query_code = sprintf("SELECT * FROM codes WHERE codeIndex = %d", $code);
$result_code = mysql_query($query_code, $conn) or die(mysql_error());
if(!mysql_num_rows($result_code)) access_denied();
$row_code = mysql_fetch_assoc($result_code);
$restrictGoTo = $row_code['redirect'];

if($restrictGoTo=="index.php" && isset($_GET['redirect'])) {
	$restrictGoTo .= (strpos($restrictGoTo, '?')) ? "&" : "?";
	$restrictGoTo .= "redirect=".urlencode($_GET['redirect']);
}

if(isset($_SESSION['PrevUrl'])) {
  $restrictGoTo = $_SESSION['PrevUrl'];
  $_SESSION['PrevUrl'] = NULL;
  unset($_SESSION['PrevUrl']);
}