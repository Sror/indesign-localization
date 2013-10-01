<?php
$access = array("system","cpanel");
require_once(MODULES.'mod_authorise.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$com_query = sprintf("SELECT companies.*, companies.systemName AS ftp,
						systemconfig.*, systemconfig.systemName AS system,
						COUNT(users.userID) AS userno
						FROM companies
						LEFT JOIN systemconfig ON companies.companyID = systemconfig.companyID
						LEFT JOIN users ON companies.companyID = users.companyID
						WHERE companies.companyID = %d
						GROUP BY companies.companyID
						LIMIT 1",
						$id);
$com_result = mysql_query($com_query, $conn) or die(mysql_error());
$com_found = mysql_num_rows($com_result);
if($com_found) {
	$com_row = mysql_fetch_assoc($com_result);
} else {
	access_denied();
}

if(!empty($_POST['form'])) {
	
	if($_POST['form']=="close") {
		header("Location: index.php?layout=$layout");
		exit();
	}
	
	if( ($_POST["form"]=="save" || $_POST["form"]=="apply") && ($id!=ADMIN_COMPANYID || $_SESSION['companyID']==ADMIN_COMPANYID) ) {
		
		$agency = empty($_POST['agency']) ? 0 : $_POST['agency'];
		$company_logo = $_POST['now_companyLogo'];
		if(!empty($_FILES['companyLogo']['name'])) {
			if(!ValidateImage($_FILES['companyLogo']['tmp_name'])) {
				header("Location: index.php?layout=system&id=11");
				exit;
			}
			$company_logo = date("YmdHis")."_".$_FILES['companyLogo']['name'];
			move_uploaded_file($_FILES['companyLogo']['tmp_name'], ROOT.LOGO_PATH.$company_logo);
		}
		$update = sprintf("UPDATE companies SET
		  					parentCompanyID = %d,
		  					companyLogo = '%s',
		  					firstContact = '%s',
		  					addressLine1 = '%s',
		  					addressLine2 = '%s',
		  					addressLine3 = '%s',
		  					town = '%s',
		  					county = '%s',
		  					postcode = '%s',
		  					country = '%s',
		  					companyTelephone = '%s',
		  					companyFax = '%s',
		  					companyEmail = '%s',
		  					companyWeb = '%s',
		  					packageID = %d,
		  					agency = %d
		  					WHERE companyID = %d",
							$_POST['parentID'],
							mysql_real_escape_string($company_logo),
							mysql_real_escape_string($_POST['contact']),
							mysql_real_escape_string($_POST['address1']),
							mysql_real_escape_string($_POST['address2']),
							mysql_real_escape_string($_POST['address3']),
							mysql_real_escape_string($_POST['town']),
							mysql_real_escape_string($_POST['county']),
							mysql_real_escape_string($_POST['postcode']),
							mysql_real_escape_string($_POST['country']),
							mysql_real_escape_string($_POST['telephone']),
							mysql_real_escape_string($_POST['fax']),
							mysql_real_escape_string($_POST['email']),
							mysql_real_escape_string($_POST['web']),
							$_POST['packageID'],
							$agency,
							$id);
		$result = mysql_query($update, $conn) or die(mysql_error());
		
		$cover_logo = $_POST['now_coverLogo'];
		if(!empty($_FILES['logoFile']['name'])) {
			if(!ValidateImage($_FILES['logoFile']['tmp_name'])) {
				header("Location: index.php?layout=system&id=11");
				exit;
			}
			$cover_logo = date("YmdHis")."_".$_FILES['logoFile']['name'];
			move_uploaded_file($_FILES['logoFile']['tmp_name'], ROOT.SYSTEM_LOGO_PATH.$cover_logo);
		}
		$corner_logo = $_POST['now_cornerLogo'];
		if(!empty($_FILES['smallLogoFile']['name'])) {
			if(!ValidateImage($_FILES['smallLogoFile']['tmp_name'])) {
				header("Location: index.php?layout=system&id=11");
				exit;
			}
			$corner_logo = date("YmdHis")."_".$_FILES['smallLogoFile']['name'];
			move_uploaded_file($_FILES['smallLogoFile']['tmp_name'], ROOT.SYSTEM_LOGO_PATH.$corner_logo);
		}
		require_once(CLASSES.'Font_Substitution.php');
		Font_Substitution::set_default_font($_POST['default_sub_font_id'],$id);
		$update = sprintf("UPDATE systemconfig SET
		  					siteURL = '%s',
		  					systemName = '%s',
		  					systemVersion = '%s',
		  					templateName = '%s',
		  					logoFile = '%s',
		  					smallLogoFile = '%s',
		  					currency = %d,
		  					format_date = '%s',
		  					format_time = '%s',
		  					default_sub_font_id = %d,
		  					default_img_dir = '%s'
		  					WHERE companyID = %d",
							mysql_real_escape_string($_POST['siteURL']),
							mysql_real_escape_string($_POST['systemName']),
							mysql_real_escape_string($_POST['systemVersion']),
							mysql_real_escape_string($_POST['templateName']),
							mysql_real_escape_string($cover_logo),
							mysql_real_escape_string($corner_logo),
							$_POST['currency'],
							mysql_real_escape_string($_POST['format_date']),
							mysql_real_escape_string($_POST['format_time']),
							$_POST['default_sub_font_id'],
							mysql_real_escape_string($_POST['default_img_dir']),
							$id);
		$result = mysql_query($update, $conn) or die(mysql_error());
		$DB->update_company_acl($id,$_POST['acl']);
		$DB->LogSystemEvent($_SESSION['userID'],"edited company: {$com_row['companyName']}");
		
		switch($_POST["form"]) {
			case "apply":	header("Location: index.php?layout=$layout&task=$task&id=$id");
							break;
			case "save":	header("Location: index.php?layout=$layout");
							break;
			default:		header("Location: index.php?layout=$layout");
		}
		exit();
	}
	
}
?>