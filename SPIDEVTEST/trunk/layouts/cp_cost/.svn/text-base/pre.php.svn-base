<?php
$access = array("system","superadmin");
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
			$update = sprintf("DELETE FROM page_rates WHERE id = %d", $id);
			$result = mysql_query($update, $conn) or die(mysql_error());
			$DB->LogSystemEvent($_SESSION['userID'],"deleted cost per page rate [$id]");
		}
		header("Location: index.php?layout=$layout");
	}
	exit();
}

$by = isset($_POST['by'])?$_POST['by']:"date";
$order = isset($_POST['order'])?$_POST['order']:"DESC";
$keyword = isset($_POST['keyword'])?$_POST['keyword']:"";
$pre = ($order=="ASC")?"DESC":"ASC";

if(isset($_POST['filter_company'])) $_SESSION['filter_company'] = $_POST['filter_company'];
$company_id = (isset($_POST['filter_company'])) ? $_POST['filter_company'] : (isset($_SESSION['filter_company']) ? $_SESSION['filter_company'] : $_SESSION['companyID']);

$query = sprintf("SELECT id
				FROM page_rates
				LEFT JOIN companies ON page_rates.client_id = companies.companyID
				WHERE page_rates.agency_id = %d
				AND (page_rates.rate LIKE '%s'
				OR companies.companyName LIKE '%s')",
				$company_id,
				"%".mysql_real_escape_string($keyword)."%",
				"%".mysql_real_escape_string($keyword)."%");
$result = mysql_query($query, $conn) or die(mysql_error());
$total = mysql_num_rows($result);

$limit = isset($_POST['limit'])?(int)$_POST['limit']:RPP;
$pages = (ceil($total/$limit)==0)?1:ceil($total/$limit);
$page = isset($_POST['page'])?(int)$_POST['page']:1;
$offset = $limit*($page-1);
?>