<?php
$access = array("system","cpanel");
require_once(MODULES.'mod_authorise.php');

if(!empty($_POST['form'])) {
	if($_POST['form']=="new") {
		header("Location: index.php?layout=$layout&task=new");
	}
	
	if($_POST['form']=="Connect") {
		$id = $_POST['id'][0];
		header("Location: index.php?layout=cp_file&id=$id");
	}
	
	if($_POST['form']=="edit") {
		$id = $_POST['id'][0];
		header("Location: index.php?layout=$layout&task=edit&id=$id");
	}

	if($_POST['form']=="delete") {
		foreach($_POST['id'] as $id) {
			$DB->DeleteFTP($id);
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
	$update = sprintf("UPDATE ftps
					SET `%s` = %d
					WHERE id = %d",
					$option,
					$v,
					$id);
	$result = mysql_query($update, $conn) or die(mysql_error());
	header("Location: index.php?layout=$layout");
	exit();
}

$by = isset($_POST['by'])?$_POST['by']:"id";
$order = isset($_POST['order'])?$_POST['order']:"ASC";
$keyword = isset($_POST['keyword'])?$_POST['keyword']:"";
$pre = ($order=="ASC")?"DESC":"ASC";

if(isset($_POST['filter_company'])) $_SESSION['filter_company'] = $_POST['filter_company'];
$company_id = $_SESSION['companyID'];
$filter_company_id = (isset($_POST['filter_company'])) ? $_POST['filter_company'] : (isset($_SESSION['filter_company']) ? $_SESSION['filter_company'] : $company_id);


$query = sprintf("SELECT ftps.id
					FROM ftps
					LEFT JOIN companies ON ftps.company_id = companies.companyID
					WHERE ftps.company_id = %d
					AND (companies.companyName LIKE '%s'
					OR companies.systemName LIKE '%s'
					OR ftps.ftp_host LIKE '%s'
					OR ftps.ftp_memo LIKE '%s'
					OR ftps.ftp_username LIKE '%s')",
					$filter_company_id,
					"%".mysql_real_escape_string($keyword)."%",
					"%".mysql_real_escape_string($keyword)."%",
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