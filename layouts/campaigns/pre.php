<?php
$access = array("system","campaigns");
require_once(MODULES.'mod_authorise.php');
if(!empty($_POST['form'])) {
	
	if($_POST['form']=="complete") {
		foreach($_POST['id'] as $id) {
			$DB->CompleteCampaign($id);
		}
		header("Location: index.php?layout=$layout");
	}
	
	if($_POST['form']=="archive") {
		foreach($_POST['id'] as $id) {
			$DB->ArchiveCampaign($id);
		}
		header("Location: index.php?layout=$layout");
	}
	
	if($_POST['form']=="unarchive") {
		foreach($_POST['id'] as $id) {
			$DB->UnarchiveCampaign($id);
		}
		header("Location: index.php?layout=$layout");
	}
	
	if($_POST['form']=="trash") {
		foreach($_POST['id'] as $id) {
			$DB->TrashCampaign($id);
		}
		header("Location: index.php?layout=$layout");
	}
	
	if($_POST['form']=="restore") {
		foreach($_POST['id'] as $id) {
			$DB->RestoreCampaign($id);
		}
		header("Location: index.php?layout=$layout");
	}
	
	if($_POST['form']=="delete") {
		foreach($_POST['id'] as $id) {
			$DB->DeleteCampaign($id);
		}
		header("Location: index.php?layout=$layout");
	}
	exit();
}

if (!empty($_POST["update"]) && ($_POST["update"] == "new_camp_form")) {
	$camp_name = RestrictName($_POST['campaign_name']);
	$update = sprintf("INSERT INTO campaigns (campaignName, brandID, ref, ownerID, sourceLanguageID, lastEdit, campaignStatus, default_sub_font_id, default_img_dir)
						VALUES ('%s', %d, '%s', %d, %d, NOW(), %d, %d, '%s')",
						mysql_real_escape_string($camp_name),
						$_POST['brand_id'],
						mysql_real_escape_string($_POST['ref']),
						$_SESSION['userID'],
						$_POST['lang_id'],
						STATUS_ACTIVE,
						$_POST['default_sub_font_id'],
						mysql_real_escape_string($_POST['default_img_dir']));
	$result = mysql_query($update, $conn) or die(mysql_error());
	$camp_id = mysql_insert_id($conn);
	$DB->update_campaign_acl($camp_id, $_POST['acl']);
	
	require_once(CLASSES.'Font_Substitution.php');
	if(!empty($_POST['default_sub_font_id'])){
		#Font_Substitution::set_default_font($_POST['default_sub_font_id'],NULL,$campaignID);
		Font_Substitution::set_default_font($_POST['default_sub_font_id'],$camp_id,'campaign');
	}else{
		#Font_Substitution::remove_font_substitution(0,NULL,$campaignID);
		Font_Substitution::remove_font_substitution(0,$camp_id,'campaign');
	}

	$DB->LogSystemEvent($_SESSION['userID'],"created a new campaign: $camp_name",$camp_id);
	header("Location: index.php?layout=campaign&id=$camp_id");
	exit();
}

$by = isset($_POST['by']) ? $_POST['by'] : "lastEdit";
$order = isset($_POST['order']) ? $_POST['order'] : "DESC";
$keyword = isset($_POST['keyword']) ? $_POST['keyword'] : "";
$pre = ($order=="ASC") ? "DESC" : "ASC";

if(isset($_POST['campaign_status'])) $_SESSION['campaign_status'] = $_POST['campaign_status'];
$status = (isset($_POST['campaign_status'])) ? $_POST['campaign_status'] : (isset($_SESSION['campaign_status']) ? $_SESSION['campaign_status'] : STATUS_ACTIVE);

if(isset($_POST['filter_view'])) $_SESSION['filter_view'] = $_POST['filter_view'];
$view = (isset($_POST['filter_view'])) ? $_POST['filter_view'] : (isset($_SESSION['filter_view']) ? $_SESSION['filter_view'] : DEFAULT_VIEW);

if(isset($_POST['filter_lang'])) $_SESSION['filter_lang'] = $_POST['filter_lang'];
$lang_id = (isset($_POST['filter_lang'])) ? $_POST['filter_lang'] : (isset($_SESSION['filter_lang']) ? $_SESSION['filter_lang'] : 0);

if(isset($_POST['filter_brand'])) $_SESSION['filter_brand'] = $_POST['filter_brand'];
$brand_id = (isset($_POST['filter_brand'])) ? $_POST['filter_brand'] : (isset($_SESSION['filter_brand']) ? $_SESSION['filter_brand'] : 0);

$sub = "";
$sub .= !empty($lang_id) ? " AND campaigns.sourceLanguageID = $lang_id" : "";
$sub .= !empty($brand_id) ? " AND campaigns.brandID = $brand_id" : "";
$query = sprintf("SELECT campaigns.campaignID
					FROM campaigns
					LEFT JOIN users ON campaigns.ownerID = users.userID
					LEFT JOIN companies ON users.companyID = companies.companyID
					LEFT JOIN languages ON campaigns.sourceLanguageID = languages.languageID
					LEFT JOIN brands ON campaigns.brandID = brands.brandID
					LEFT JOIN status ON campaigns.campaignStatus = status.statusID
					WHERE campaigns.campaignStatus = %d
					%s
					AND (campaigns.campaignName LIKE '%s'
					OR campaigns.ref LIKE '%s'
					OR languages.languageName LIKE '%s'
					OR companies.companyName LIKE '%s'
					OR brands.brandName LIKE '%s'
					OR users.username LIKE '%s'
					OR users.forename LIKE '%s'
					OR users.surname LIKE '%s'
					OR status.statusInfo LIKE '%s')",
					$status,
					mysql_real_escape_string($sub),
					"%".mysql_real_escape_string($keyword)."%",
					"%".mysql_real_escape_string($keyword)."%",
					"%".mysql_real_escape_string($keyword)."%",
					"%".mysql_real_escape_string($keyword)."%",
					"%".mysql_real_escape_string($keyword)."%",
					"%".mysql_real_escape_string($keyword)."%",
					"%".mysql_real_escape_string($keyword)."%",
					"%".mysql_real_escape_string($keyword)."%",
					"%".mysql_real_escape_string($keyword)."%");
$result = mysql_query($query, $conn) or die(mysql_error());
$total = 0;
while($row = mysql_fetch_assoc($result)) {
	if(!$DB->check_campaign_acl($row['campaignID'],$_SESSION['companyID'],$_SESSION['userID'])) continue;
	$total++;
}

$limit = isset($_POST['limit']) ? (int)$_POST['limit'] : RPP;
$pages = (ceil($total/$limit)==0) ? 1 : ceil($total/$limit);
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$offset = $limit*($page-1);
?>