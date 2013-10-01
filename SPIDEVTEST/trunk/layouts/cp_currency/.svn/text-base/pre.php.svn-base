<?php
$access = array("system","cpanel");
require_once(MODULES.'mod_authorise.php');

if(!empty($_POST['form'])) {
	
	if($_POST['form']=="new") {
		header("Location: index.php?layout=$layout&task=new");
	}
	
	if($_POST['form']=="edit") {
		$id = $_POST['id'][0];
		header("Location: index.php?layout=$layout&task=edit&id=$id");
	}

	if($_POST['form']=="delete") {
		foreach($_POST['id'] as $id) {
			$update = sprintf("DELETE FROM currencies WHERE currencyID = %d", $id);
			$result = mysql_query($update, $conn) or die(mysql_error());
			$DB->LogSystemEvent($_SESSION['userID'],"deleted currency [$id]");
		}
		header("Location: index.php?layout=$layout");
	}
	exit();
}

$by = isset($_POST['by'])?$_POST['by']:"currencyName";
$order = isset($_POST['order'])?$_POST['order']:"ASC";
$keyword = isset($_POST['keyword'])?$_POST['keyword']:"";
$pre = ($order=="ASC")?"DESC":"ASC";

$query = sprintf("SELECT currencyID
				FROM currencies
				WHERE currencyName LIKE '%s'
				OR currencyAb LIKE '%s'
				OR currencySymbol LIKE '%s'",
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