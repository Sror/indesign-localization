<?php
$access = array("system","cpanel");
require_once(MODULES.'mod_authorise.php');
require_once(CLASSES.'phpmailer/class.phpmailer.php');

if(!empty($_POST['form'])) {
	
	if($_POST['form']=="start") {
		foreach($_POST['id'] as $id) {
			$DB->StartTask($id);
		}
		header("Location: index.php?layout=$layout");
	}
	
	if($_POST['form']=="pause") {
		foreach($_POST['id'] as $id) {
			$DB->PauseTask($id);
		}
		header("Location: index.php?layout=$layout");
	}

	if($_POST['form']=="signoff") {
		foreach($_POST['id'] as $id) {
			$DB->SignoffTask($id);
		}
		header("Location: index.php?layout=$layout");
	}
	
	if($_POST['form']=="view") {
		$id = $_POST['id'][0];
		header("Location: index.php?layout=task&id=$id");
	}
	
	if($_POST['form']=="edit") {
		$id = $_POST['id'][0];
		header("Location: index.php?layout=$layout&task=edit&id=$id");
	}

	if($_POST['form']=="delete") {
		foreach($_POST['id'] as $id) {
			$DB->DeleteTask($id);
		}
		header("Location: index.php?layout=$layout");
	}
	exit();
}

$by = isset($_POST['by'])?$_POST['by']:"id";
$order = isset($_POST['order'])?$_POST['order']:"ASC";
$keyword = isset($_POST['keyword'])?$_POST['keyword']:"";
$pre = ($order=="ASC")?"DESC":"ASC";

if(isset($_POST['filter_company'])) $_SESSION['filter_company'] = $_POST['filter_company'];
$company_id = (isset($_POST['filter_company'])) ? $_POST['filter_company'] : (isset($_SESSION['filter_company']) ? $_SESSION['filter_company'] : $_SESSION['companyID']);
if(isset($_POST['task_status'])) $_SESSION['task_status'] = $_POST['task_status'];
$task_status = (isset($_POST['task_status'])) ? $_POST['task_status'] : (isset($_SESSION['task_status']) ? $_SESSION['task_status'] : $_SESSION['task_status']);

$sub = !empty($task_status) ? " AND tasks.taskStatus = $task_status" : "";
$query = sprintf("SELECT tasks.taskID
					FROM tasks
					LEFT JOIN artworks ON tasks.artworkID = artworks.artworkID
					LEFT JOIN campaigns ON artworks.campaignID = campaigns.campaignID
					LEFT JOIN users U1 ON campaigns.ownerID = U1.userID
					LEFT JOIN companies ON U1.companyID = companies.companyID
					LEFT JOIN users U2 ON tasks.translatorID = U2.userID
					LEFT JOIN users U3 ON tasks.creatorID = U3.userID
					LEFT JOIN languages L1 ON campaigns.sourceLanguageID = L1.languageID
					LEFT JOIN languages L2 ON tasks.desiredLanguageID = L2.languageID
					LEFT JOIN status ON tasks.taskStatus = status.statusID
					WHERE companies.companyID = %d
					%s
					AND (artworks.artworkName LIKE '%s'
					OR campaigns.campaignName LIKE '%s'
					OR U2.username LIKE '%s'
					OR U3.username LIKE '%s'
					OR L1.languageName LIKE '%s'
					OR L2.languageName LIKE '%s'
					OR status.statusInfo LIKE '%s')",
					$company_id,
					mysql_real_escape_string($sub),
					"%".mysql_real_escape_string($keyword)."%",
					"%".mysql_real_escape_string($keyword)."%",
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