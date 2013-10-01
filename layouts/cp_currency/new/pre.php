<?php
$access = array("system","cpanel");
require_once(MODULES.'mod_authorise.php');

if(!empty($_POST['form'])) {
	
	if($_POST['form']=="cancel") {
		header("Location: index.php?layout=$layout");
	}
	
	if($_POST["form"]=="save" || $_POST["form"]=="apply") {
		
		$update = sprintf("INSERT INTO currencies
		  					(currencyName, currencyAb, currencySymbol)
							VALUES ('%s', '%s', '%s')",
							mysql_real_escape_string($_POST['currencyName']),
							mysql_real_escape_string($_POST['currencyAb']),
							mysql_real_escape_string($_POST['currencySymbol']));
		$result = mysql_query($update, $conn) or die(mysql_error());
		$newID = mysql_insert_id($conn);
		
		$DB->LogSystemEvent($_SESSION['userID'],"added a new currency: {$_POST['currencyName']}");
		
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
?>