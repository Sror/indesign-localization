<?php
$access = array("system","login");
require_once(MODULES.'mod_authorise.php');

$id = (isset($_GET['id'])) ? $_GET['id'] : 0;
$query_import = sprintf("SELECT *
						FROM task_imports
						WHERE id = %d
						LIMIT 1",
						$id);
$result_import = mysql_query($query_import, $conn) or die(mysql_error());
if(!mysql_num_rows($result_import)) access_denied();
$row_import = mysql_fetch_assoc($result_import);
$artwork_id = $row_import['artwork_id'];
$task_id = $row_import['task_id'];

$by = isset($_POST['by'])?$_POST['by']:"id";
$order = isset($_POST['order'])?$_POST['order']:"ASC";
$keyword = isset($_POST['keyword'])?$_POST['keyword']:"";
$pre = ($order=="ASC")?"DESC":"ASC";

if(isset($_POST['filter_import'])) $_SESSION['filter_import'] = $_POST['filter_import'];
$filter_import = (isset($_POST['filter_import'])) ? $_POST['filter_import'] : (isset($_SESSION['filter_import']) ? $_SESSION['filter_import'] : 2);
switch($filter_import) {
	case 0:
		$sub = " AND imported = 0";
		break;
	case 1:
		$sub = " AND imported = 1";
		break;
	default:
		$sub = "";
}

$query = sprintf("SELECT id
				FROM task_import_rows
				WHERE import_id = %d
				%s
				AND ( source LIKE '%s'
				OR target LIKE '%s' )",
				$id,
				$sub,
				"%".mysql_real_escape_string($keyword)."%",
				"%".mysql_real_escape_string($keyword)."%");
$result = mysql_query($query, $conn) or die(mysql_error());
$total = mysql_num_rows($result);

$limit = isset($_POST['limit'])?(int)$_POST['limit']:RPP;
$pages = (ceil($total/$limit)==0)?1:ceil($total/$limit);
$page = isset($_POST['page'])?(int)$_POST['page']:1;
$offset = $limit*($page-1);
?>