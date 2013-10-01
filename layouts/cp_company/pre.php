<?php
$access = array("system","cpanel");
require_once(MODULES.'mod_authorise.php');

if(!empty($_POST['form'])) {
	if($_POST['form']=="new") {
		header("Location: index.php?layout=$layout&task=new");
	}
	
	if($_POST['form']=="view") {
		$id = $_POST['id'][0];
		header("Location: index.php?layout=company&id=$id");
	}
	
	if($_POST['form']=="edit") {
		$id = $_POST['id'][0];
		header("Location: index.php?layout=$layout&task=edit&id=$id");
	}

	if($_POST['form']=="delete") {
		require_once(ACL.'acl_api.class.php');
		$acl_api = new acl_api($acl_options);
		foreach($_POST['id'] as $id) {
			if($id != ADMIN_COMPANYID) {
				$DB->DeleteCompany($acl_api,$id);
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
	$update = sprintf("UPDATE companies SET `%s` = %d WHERE companyID = %d", $option, $v, $id);
	$result = mysql_query($update, $conn) or die(mysql_error());
	header("Location: index.php?layout=$layout");
	exit();
}

$by = isset($_POST['by'])?$_POST['by']:"id";
$order = isset($_POST['order'])?$_POST['order']:"ASC";
$keyword = isset($_POST['keyword'])?$_POST['keyword']:"";
$pre = ($order=="ASC")?"DESC":"ASC";

$sub = $issuperadmin ? "" : sprintf("(companies.companyID=%d OR companies.parentCompanyID=%d) AND",$_SESSION['companyID'],$_SESSION['companyID']);
$query = sprintf("SELECT companies.companyID
					FROM companies
					LEFT JOIN service_packages ON companies.packageID = service_packages.id
					WHERE %s
					(companies.companyName LIKE '%s'
					OR companies.companyWeb LIKE '%s'
					OR companies.systemName LIKE '%s'
					OR service_packages.name LIKE '%s')",
					mysql_real_escape_string($sub),
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