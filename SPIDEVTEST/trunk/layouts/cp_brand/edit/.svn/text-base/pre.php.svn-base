<?php
$access = array("system","cpanel");
require_once(MODULES.'mod_authorise.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$brand_query = sprintf("SELECT brands.*,
						COUNT(campaigns.campaignID) AS campaignno
						FROM brands
						LEFT JOIN campaigns ON brands.brandID = campaigns.brandID
						WHERE brands.brandID = %d
						GROUP BY brands.brandID
						LIMIT 1",
						$id);
$brand_result = mysql_query($brand_query, $conn) or die(mysql_error());
$brand_found = mysql_num_rows($brand_result);
if($brand_found) {
	$brand_row = mysql_fetch_assoc($brand_result);
} else {
	access_denied();
}

if(!empty($_POST['form'])) {
	
	if($_POST['form']=="close") {
		header("Location: index.php?layout=$layout");
	}
	
	if($_POST["form"]=="save" || $_POST["form"]=="apply") {
		
		$update = sprintf("UPDATE brands SET
		  					brandName = '%s',
		  					parentBrandID = %d,
		  					companyID = %d
		  					WHERE brandID = %d",
							mysql_real_escape_string($_POST['name']),
							$_POST['parentID'],
							$_POST['companyID'],
							$id);
		$result = mysql_query($update, $conn) or die(mysql_error());
		$DB->LogSystemEvent($_SESSION['userID'],"edited brand: {$_POST['name']}");
		
		switch($_POST["form"]) {
			case "apply":	header("Location: index.php?layout=$layout&task=$task&id=$id");
							break;
			case "save":	header("Location: index.php?layout=$layout");
							break;
			default:		header("Location: index.php?layout=$layout");
		}
	}
	exit();
}
?>