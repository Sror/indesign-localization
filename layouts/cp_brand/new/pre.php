<?php
$access = array("system","cpanel");
require_once(MODULES.'mod_authorise.php');

if(!empty($_POST['form'])) {
	
	if($_POST['form']=="cancel") {
		header("Location: index.php?layout=$layout");
	}
	
	if($_POST["form"]=="save" || $_POST["form"]=="apply") {
		
		$query = sprintf("SELECT brandID
							FROM brands
							WHERE brandName='%s'
							AND parentBrandID = %d
							AND companyID = %d
							LIMIT 1",
							mysql_real_escape_string($_POST['name']),
							$_POST['parentID'],
							$_POST['companyID']);
		$result = mysql_query($query, $conn) or die(mysql_error());
		$found = mysql_num_rows($result);
		
		if ($found) {
		  header("Location: index.php?layout=system&id=10");
		  exit;
		} else {
			$update = sprintf("INSERT INTO brands
			  					(brandName, parentBrandID, companyID)
								VALUES ('%s', %d, %d)",
								mysql_real_escape_string($_POST['name']),
								$_POST['parentID'],
								$_POST['companyID']);
			$result = mysql_query($update, $conn) or die(mysql_error());
			$newID = mysql_insert_id($conn);
			$DB->LogSystemEvent($_SESSION['userID'],"created a new brand: {$_POST['name']}");
		}
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