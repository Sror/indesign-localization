<?php
$access = array("system","superadmin");
require_once(MODULES.'mod_authorise.php');

if(!empty($_POST['form'])) {
	if($_POST['form']=="cancel") {
		header("Location: index.php?layout=$layout");
	}
	if($_POST["form"]=="save" || $_POST["form"]=="apply") {
		$items = array();
		foreach($_POST['item'] as $teim_id) {
			$items[$teim_id] = $_POST['credits'][$teim_id];
		}
		$DB->create_service_package($_SESSION['userID'],$_POST['name'],$items);
		switch($_POST["form"]) {
			case "apply":
				header("Location: index.php?layout=$layout&task=edit&id=$newID");
				break;
			case "save":
				header("Location: index.php?layout=$layout");
				break;
		}
	}
	exit();
}
?>