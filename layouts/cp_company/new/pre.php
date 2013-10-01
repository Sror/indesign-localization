<?php
$access = array("system","superadmin");
require_once(MODULES.'mod_authorise.php');

if(!empty($_POST['form'])) {
	
	if($_POST['form']=="cancel") {
		header("Location: index.php?layout=$layout");
		exit();
	}
	
	if($_POST["form"]=="save" || $_POST["form"]=="apply") {
		
		$query = sprintf("SELECT companyID FROM companies
						WHERE companyName='%s'
						LIMIT 1",
						mysql_real_escape_string($_POST['name']));
		$result = mysql_query($query, $conn) or die(mysql_error());
		if (mysql_num_rows($result)) {
		  header("Location: index.php?layout=system&id=10");
		  exit;
		}
		$word = explode(" ", trim($_POST['name']));
		$systemName = strtolower($word[0]);
		$agency = empty($_POST['agency']) ? 0 : $_POST['agency'];
		$company_logo = "default.jpg";
		if(!empty($_FILES['companyLogo']['name'])) {
			if(!ValidateImage($_FILES['companyLogo']['tmp_name'])) {
				header("Location: index.php?layout=system&id=11");
				exit;
			}
			$company_logo = date("YmdHis")."_".$_FILES['companyLogo']['name'];
			move_uploaded_file($_FILES['companyLogo']['tmp_name'], ROOT.LOGO_PATH.$company_logo);
		}
		$cover_logo = "pagl.gif";
		if(!empty($_FILES['logoFile']['name'])) {
			if(!ValidateImage($_FILES['logoFile']['tmp_name'])) {
				header("Location: index.php?layout=system&id=11");
				exit;
			}
			$cover_logo = date("YmdHis")."_".$_FILES['logoFile']['name'];
			move_uploaded_file($_FILES['logoFile']['tmp_name'], ROOT.SYSTEM_LOGO_PATH.$cover_logo);
		}
		$corner_logo = "s_pagl.gif";
		if(!empty($_FILES['smallLogoFile']['name'])) {
			if(!ValidateImage($_FILES['smallLogoFile']['tmp_name'])) {
				header("Location: index.php?layout=system&id=11");
				exit;
			}
			$corner_logo = date("YmdHis")."_".$_FILES['smallLogoFile']['name'];
			move_uploaded_file($_FILES['smallLogoFile']['tmp_name'], ROOT.SYSTEM_LOGO_PATH.$corner_logo);
		}
		
		$update = sprintf("INSERT INTO companies
							(companyName, parentCompanyID, companyLogo, firstContact, addressLine1, addressLine2, addressLine3, town, county, postcode, country, companyTelephone, companyFax, companyEmail, companyWeb, systemName, packageID, agency)
							VALUES ('%s', %d, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', %d, %d)",
							mysql_real_escape_string(trim($_POST['name'])),
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
							mysql_real_escape_string($systemName),
							$_POST['packageID'],
							$agency);
		$result = mysql_query($update, $conn) or die(mysql_error());
		$newID = mysql_insert_id($conn);
		
		//create company system config
		require_once(CLASSES.'Font_Substitution.php');
		Font_Substitution::set_default_font($_POST['default_sub_font_id'],$newID);
		$update = sprintf("INSERT INTO systemconfig
							(companyID, siteURL, systemName, systemVersion, templateName, logoFile, smallLogoFile, currency, format_date, format_time, default_sub_font_id)
							VALUES
							(%d, '%s', '%s', '%s', '%s', '%s', '%s', %d, '%s', '%s', %d)",
							$newID,
							mysql_real_escape_string($_POST['siteURL']),
							mysql_real_escape_string($_POST['systemName']),
							mysql_real_escape_string($_POST['systemVersion']),
							mysql_real_escape_string($_POST['templateName']),
							mysql_real_escape_string($cover_logo),
							mysql_real_escape_string($corner_logo),
							$_POST['currency'],
							mysql_real_escape_string($_POST['format_date']),
							mysql_real_escape_string($_POST['format_time']),
							$_POST['default_sub_font_id']
							);
		$result = mysql_query($update, $conn) or die(mysql_error());
		
		//create default brand
		$update = sprintf("INSERT INTO brands
							(brandName, companyID)
							VALUES
							('Default', %d)",
							$newID);
		$result = mysql_query($update, $conn) or die(mysql_error());
		
		$acl_api = new acl_api($acl_options);
		//ACL: add company to `aro_sections` table
		$acl_api->add_object_section(mysql_real_escape_string($_POST['name']), $newID, $newID, 0, 'ARO');
		//ACL: add company to `acl_sections` table
		$acl_api->add_object_section(mysql_real_escape_string($_POST['name']), $newID, $newID, 0, 'ACL');
		
		mkdir(ROOT.FTP_DIR.$systemName);
		$DB->update_company_acl($newID,$_POST['acl']);
		$DB->LogSystemEvent($_SESSION['userID'],"created a new company: {$_POST['name']}");
		
		switch($_POST["form"]) {
			case "apply":	header("Location: index.php?layout=$layout&task=edit&id=$newID");
							break;
			case "save":	header("Location: index.php?layout=$layout");
							break;
			default:		header("Location: index.php?layout=$layout");
		}
		exit();
	}
	
}
?>