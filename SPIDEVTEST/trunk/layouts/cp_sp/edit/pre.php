<?php
$access = array("system","superadmin");
require_once(MODULES.'mod_authorise.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$sp_query = sprintf("SELECT service_packages.id, service_packages.name,
					COUNT(companies.companyID) AS companyno
					FROM service_packages
					LEFT JOIN companies ON service_packages.id = companies.packageID
					WHERE service_packages.id = %d
					GROUP BY service_packages.id
					LIMIT 1",
					$id);
$sp_result = mysql_query($sp_query, $conn) or die(mysql_error());
if(!mysql_num_rows($sp_result)) access_denied();
$sp_row = mysql_fetch_assoc($sp_result);

if(!empty($_POST['form'])) {
	if($_POST['form']=="close") {
		header("Location: index.php?layout=$layout");
	}
	if($_POST["form"]=="save" || $_POST["form"]=="apply") {
		$items = array();
		foreach($_POST['item'] as $teim_id) {
			$items[$teim_id] = $_POST['credits'][$teim_id];
		}
		$DB->update_service_package($_SESSION['userID'],$id,$_POST['name'],$items);
		switch($_POST["form"]) {
			case "apply":
				header("Location: index.php?layout=$layout&task=$task&id=$id");
				break;
			case "save":
				header("Location: index.php?layout=$layout");
				break;
		}
	}
	exit();
}