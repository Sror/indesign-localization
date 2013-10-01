<?php
$access = array("system","superadmin");
require_once(MODULES.'mod_authorise.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$cost_query = sprintf("SELECT *
						FROM page_rates
						WHERE id = %d
						LIMIT 1",
						$id);
$cost_result = mysql_query($cost_query, $conn) or die(mysql_error());
if(!mysql_num_rows($cost_result)) access_denied();
$cost_row = mysql_fetch_assoc($cost_result);

if(!empty($_POST['form'])) {
	if($_POST['form']=="close") {
		header("Location: index.php?layout=$layout");
	}
	if($_POST["form"]=="save" || $_POST["form"]=="apply") {
		$update = sprintf("UPDATE page_rates SET
		  					client_id = %d,
		  					agency_id = %d,
		  					currency_id = %d,
		  					rate = %f,
		  					date = '%s'
		  					WHERE id = %d",
							$_POST['client_id'],
							$_POST['agency_id'],
							$_POST['currency_id'],
							(float)$_POST['rate'],
							mysql_real_escape_string($_POST['date']),
							$id);
		$result = mysql_query($update, $conn) or die(mysql_error());
		$DB->LogSystemEvent($_SESSION['userID'],"edited the page rate [$id]");
		
		switch($_POST["form"]) {
			case "apply":	header("Location: index.php?layout=$layout&task=edit&id=$id");
							break;
			case "save":	header("Location: index.php?layout=$layout");
							break;
			default:		header("Location: index.php?layout=$layout");
		}
	}
	exit();
}