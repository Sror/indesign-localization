<?php
require_once(dirname(__FILE__).'/config.php');
$vCode = (isset($_GET['vCode'])) ? $_GET['vCode'] : "";
$query = sprintf("SELECT * FROM tasks
				WHERE vCode = '%s' LIMIT 0,1",
				mysql_real_escape_string($vCode));
$result = mysql_query($query, $conn) or die(mysql_error());
$row = mysql_fetch_assoc($result);
$found = mysql_num_rows($result);

if($found) {
	$update = sprintf("UPDATE tasks SET taskStatus=5 WHERE taskID=%d", $row['taskID']);
	$result = mysql_query($update, $conn) or die(mysql_error());
	$DB->LogTaskAction($row['taskID'], $row['translatorID'], "Confirmed to be Shortlisted");
	header("Location: index.php?layout=system&id=7");
	exit();
} else {
	access_denied();
}