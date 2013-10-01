<?php
$access = array("system","cpanel");
require_once(MODULES.'mod_authorise.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$currency_query = sprintf("SELECT *
						FROM currencies
						WHERE currencyID = %d
						LIMIT 1",
						$id);
$currency_result = mysql_query($currency_query, $conn) or die(mysql_error());
if(!mysql_num_rows($currency_result)) access_denied();
$currency_row = mysql_fetch_assoc($currency_result);

if(!empty($_POST['form'])) {
	
	if($_POST['form']=="close") {
		header("Location: index.php?layout=$layout");
	}
	
	if($_POST["form"]=="save" || $_POST["form"]=="apply") {
		
		$update = sprintf("UPDATE currencies SET
		  					currencyName = '%s',
		  					currencyAb = '%s',
		  					currencySymbol = '%s'
		  					WHERE currencyID = %d",
							mysql_real_escape_string($_POST['currencyName']),
							mysql_real_escape_string($_POST['currencyAb']),
							mysql_real_escape_string($_POST['currencySymbol']),
							$id);
		$result = mysql_query($update, $conn) or die(mysql_error());
		
		$DB->LogSystemEvent($_SESSION['userID'],"edited currency: {$_POST['currencyName']}");
		
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
?>