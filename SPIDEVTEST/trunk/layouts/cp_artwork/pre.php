<?php
$access = array("system","cpanel");
require_once(MODULES.'mod_authorise.php');

if(!empty($_POST['form'])) {
	
	if($_POST['form']=="view") {
		$id = $_POST['id'][0];
		header("Location: index.php?layout=artwork&id=$id");
	}
	
	if($_POST['form']=="edit") {
		$id = $_POST['id'][0];
		header("Location: index.php?layout=$layout&task=edit&id=$id");
	}

	if($_POST['form']=="trash") {
		foreach($_POST['id'] as $id) {
			$DB->TrashArtwork($id);
		}
		header("Location: index.php?layout=$layout");
	}
	
	if($_POST['form']=="restore") {
		foreach($_POST['id'] as $id) {
			$DB->RestoreArtwork($id);
		}
		header("Location: index.php?layout=$layout");
	}

	if($_POST['form']=="delete") {
		foreach($_POST['id'] as $id) {
			$DB->DeleteArtwork($id);
		}
		header("Location: index.php?layout=$layout");
	}

	exit();
}

$by = isset($_POST['by'])?$_POST['by']:"id";
$order = isset($_POST['order'])?$_POST['order']:"DESC";
$keyword = isset($_POST['keyword'])?$_POST['keyword']:"";
$pre = ($order=="ASC")?"DESC":"ASC";

if(isset($_POST['filter_company'])) $_SESSION['filter_company'] = $_POST['filter_company'];
$company_id = (isset($_POST['filter_company'])) ? $_POST['filter_company'] : (isset($_SESSION['filter_company']) ? $_SESSION['filter_company'] : $_SESSION['companyID']);

if(isset($_POST['artwork_status'])) $_SESSION['artwork_status'] = $_POST['artwork_status'];
$status = (isset($_POST['artwork_status'])) ? $_POST['artwork_status'] : (isset($_SESSION['artwork_status']) ? $_SESSION['artwork_status'] : STATUS_ACTIVE);

$query = sprintf("SELECT artworks.artworkID
					FROM artworks
					LEFT JOIN campaigns ON artworks.campaignID = campaigns.campaignID
					LEFT JOIN users ON artworks.uploaderID = users.userID
					LEFT JOIN companies ON users.companyID = companies.companyID
					LEFT JOIN subjects ON artworks.subjectID = subjects.subjectID
					LEFT JOIN service_engines ON artworks.artworkType = service_engines.id
					WHERE companies.companyID = %d
					AND artworks.live = %d
					AND (artworks.artworkName LIKE '%s'
					OR campaigns.campaignName LIKE '%s'
					OR users.username LIKE '%s'
					OR subjects.subjectTitle LIKE '%s'
					OR service_engines.ext LIKE '%s')",
					$company_id,
					$status,
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