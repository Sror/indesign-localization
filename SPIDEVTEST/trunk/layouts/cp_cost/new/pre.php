<?php
$access = array("system","superadmin");
require_once(MODULES.'mod_authorise.php');

if(!empty($_POST['form'])) {
	
	if($_POST['form']=="cancel") {
		header("Location: index.php?layout=$layout");
	}
	
	if($_POST["form"]=="save" || $_POST["form"]=="apply") {
		$update = sprintf("INSERT INTO page_rates
		  					(client_id, agency_id, currency_id, rate, date)
							VALUES (%d, %d, %d, %f, '%s')",
							$_POST['client_id'],
							$_POST['agency_id'],
							$_POST['currency_id'],
							(float)$_POST['rate'],
							mysql_real_escape_string($_POST['date']));
		$result = mysql_query($update, $conn) or die(mysql_error());
		$newID = mysql_insert_id($conn);
		$DB->LogSystemEvent($_SESSION['userID'],"added a new page rate [$newID]");
		switch($_POST["form"]) {
			case "apply":	header("Location: index.php?layout=$layout&task=edit&id=$newID");
							break;
			case "save":	header("Location: index.php?layout=$layout");
							break;
			default:		header("Location: index.php?layout=$layout");
		}
	}
	exit();
}