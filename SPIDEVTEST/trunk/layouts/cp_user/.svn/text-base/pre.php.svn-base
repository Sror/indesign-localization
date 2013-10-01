<?php
$access = array("system","cpanel");
require_once(MODULES.'mod_authorise.php');

if(!empty($_POST['form'])) {
	
	if($_POST['form']=="new") {
		header("Location: index.php?layout=$layout&task=new");
	}
	
	if($_POST['form']=="view") {
		$id = $_POST['id'][0];
		header("Location: index.php?layout=user&id=$id");
	}
	
	if($_POST['form']=="edit") {
		$id = $_POST['id'][0];
		header("Location: index.php?layout=$layout&task=edit&id=$id");
	}

	if($_POST['form']=="delete") {
		require_once(ACL.'acl_api.class.php');
		$acl_api = new acl_api($acl_options);
		foreach($_POST['id'] as $id) {
			if($id != ADMIN_USERID) {
				$DB->DeleteUser($acl_api,$id);
			}
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
	$update = sprintf("UPDATE users
					SET `%s` = %d
					WHERE userID = %d",
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

$query = sprintf("SELECT users.userID
					FROM users
					LEFT JOIN aro_groups ON users.userGroupID = aro_groups.id
					LEFT JOIN companies ON users.companyID = companies.companyID
					WHERE users.companyID = %d
					AND (users.username LIKE '%s'
					OR users.forename LIKE '%s'
					OR users.surname LIKE '%s'
					OR users.email LIKE '%s'
					OR aro_groups.name LIKE '%s'
					OR companies.companyName LIKE '%s')",
					$filter_company_id,
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