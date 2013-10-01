<?php
$access = array("system","campaigns");
require_once(MODULES.'mod_authorise.php');

$id = (isset($_GET['id'])) ? (int)$_GET['id'] : 0;
$info = $DB->get_upload_info($id);
$campaign_id = $info['campaign_id'];
if(!$DB->check_campaign_acl($campaign_id,$_SESSION['companyID'],$_SESSION['userID'])) access_denied();

if(!empty($_POST['form'])) {
	if($_POST['form']=="close") {
		header("Location: index.php?layout=campaign&id=$campaign_id");
		exit;
	}
}

$by = isset($_POST['by']) ? $_POST['by'] : "id";
$order = isset($_POST['order']) ? $_POST['order'] : "ASC";
$keyword = isset($_POST['keyword']) ? $_POST['keyword'] : "";
$pre = ($order=="ASC") ? "DESC" : "ASC";

$query = sprintf("SELECT id
				FROM artwork_upload_log
				WHERE upload_id = %d
				ORDER BY id ASC",
				$id);
$result = mysql_query($query, $conn) or die(mysql_error());
$total = mysql_num_rows($result);

$limit = isset($_POST['limit']) ? (int)$_POST['limit'] : RPP;
$pages = (ceil($total/$limit)==0) ? 1 : ceil($total/$limit);
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$offset = $limit*($page-1);
