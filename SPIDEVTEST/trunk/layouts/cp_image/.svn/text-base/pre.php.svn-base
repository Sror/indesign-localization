<?php
$access = array("system","cpanel");
require_once(MODULES.'mod_authorise.php');

if(!empty($_POST['form'])) {
	
	if($_POST['form']=="new") {
		$id = $_POST['id'][0];
		header("Location: index.php?layout=$layout&task=new");
	}
	
	if($_POST['form']=="edit") {
		$id = $_POST['id'][0];
		header("Location: index.php?layout=$layout&task=edit&id=$id");
	}

	if($_POST['form']=="delete") {
		$IM = new ImageManager();
		foreach($_POST['id'] as $id) {
			$IM->DeleteImage($id);
		}
		header("Location: index.php?layout=$layout");
	}
	exit();
}

$by = isset($_POST['by'])?$_POST['by']:"time";
$order = isset($_POST['order'])?$_POST['order']:"DESC";
$keyword = isset($_POST['keyword'])?$_POST['keyword']:"";
$pre = ($order=="ASC")?"DESC":"ASC";

if(isset($_POST['filter_company'])) $_SESSION['filter_company'] = $_POST['filter_company'];
$company_id = (isset($_POST['filter_company'])) ? $_POST['filter_company'] : (isset($_SESSION['filter_company']) ? $_SESSION['filter_company'] : $_SESSION['companyID']);

$query = sprintf("SELECT images.id
					FROM images
					LEFT JOIN users ON users.userID = images.user_id
					LEFT JOIN companies ON users.companyID = companies.companyID
					LEFT JOIN subjects ON subjects.subjectID = images.subject_id
					LEFT JOIN brands ON brands.brandID = images.brand_id
					LEFT JOIN languages ON languages.languageID = images.lang_id
					WHERE users.companyID = %d
					AND images.type_id = %d
					AND (users.username LIKE '%s'
					OR subjects.subjectTitle LIKE '%s'
					OR brands.brandName LIKE '%s'
					OR languages.languageName LIKE '%s')",
					$company_id,
					IMG_LIBRARY,
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