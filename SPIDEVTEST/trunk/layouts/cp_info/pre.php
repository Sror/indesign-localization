<?php
$access = array("system","cpanel");
require_once(MODULES.'mod_authorise.php');

if(!empty($_POST['form'])) {
	
	if($_POST['form']=="refresh") {
		header("Location: index.php?layout=$layout");
	}
	
	if($_POST['form']=="start") {
		foreach($_POST['id'] as $id) {
			$update = sprintf("UPDATE service_engines
								SET status = 1
								WHERE id = %d",
								$id);
			$result = mysql_query($update, $conn) or die(mysql_error());
		}
		header("Location: index.php?layout=$layout");
	}
	
	if($_POST['form']=="pause") {
		foreach($_POST['id'] as $id) {
			$update = sprintf("UPDATE service_engines
								SET status = 0
								WHERE id = %d",
								$id);
			$result = mysql_query($update, $conn) or die(mysql_error());
		}
		header("Location: index.php?layout=$layout");
	}
	exit();
}

if(isset($_GET["option"]) && isset($_GET["do"]) && isset($_GET["id"])) {
	$option = $_GET["option"];
	$do = $_GET["do"];
	$id = $_GET["id"];
	switch($do) {
		case "enable":	$v=1;break;
		case "disable":	$v=0;break;
		default:		$v=0;
	}
	$update = sprintf("UPDATE service_engines
					SET `%s` = %d
					WHERE id = %d",
					$option,
					$v,
					$id);
	$result = mysql_query($update,$conn) or die(mysql_error());
	header("Location: index.php?layout=$layout");
	exit();
}

//only for quark and indesign
$serviceIDs = array(1,7);
require_once(CLASSES."services.php");

foreach($serviceIDs as $serviceID) {
	$Service = new EngineService($serviceID,true);
	if($Service->IsServerRunning(10)) {
		$ServerVersion = $Service->GetServerVersion();
		if($ServerVersion === false) continue;
		$update = sprintf("UPDATE service_engines SET status = 1, version = '%s' WHERE id = %d", $ServerVersion, $serviceID);
		$result = mysql_query($update, $conn) or die(mysql_error());
	} else {
		$update = sprintf("UPDATE service_engines SET status = 0, version = '-' WHERE id = %d", $serviceID);
		$result = mysql_query($update, $conn) or die(mysql_error());
	}
}

$by = isset($_POST['by'])?$_POST['by']:"name";
$order = isset($_POST['order'])?$_POST['order']:"ASC";
$keyword = isset($_POST['keyword'])?$_POST['keyword']:"";
$pre = ($order=="ASC")?"DESC":"ASC";

$query = sprintf("SELECT id
					FROM service_engines
					WHERE
					name LIKE '%s'
					OR ext LIKE '%s'
					OR version LIKE '%s'",
					"%".mysql_real_escape_string($keyword)."%",
					"%".mysql_real_escape_string($keyword)."%",
					"%".mysql_real_escape_string($keyword)."%");
$result = mysql_query($query, $conn) or die(mysql_error());
$total = mysql_num_rows($result);

$limit = isset($_POST['limit'])?(int)$_POST['limit']:RPP;
$pages = (ceil($total/$limit)==0)?1:ceil($total/$limit);
$page = isset($_POST['page'])?(int)$_POST['page']:1;
$offset = $limit*($page-1);
?>