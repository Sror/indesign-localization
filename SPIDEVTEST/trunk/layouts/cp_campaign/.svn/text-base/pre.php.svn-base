<?php
$access = array("system","cpanel");
require_once(MODULES.'mod_authorise.php');

if(!empty($_POST['form'])) {
	
	if($_POST['form']=="new") {
		header("Location: index.php?layout=$layout&task=new");
	}
	
	if($_POST['form']=="view") {
		$id = $_POST['id'][0];
		header("Location: index.php?layout=campaign&id=$id");
	}
	
	if($_POST['form']=="edit") {
		$id = $_POST['id'][0];
		header("Location: index.php?layout=$layout&task=edit&id=$id");
	}
	
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

$by = isset($_POST['by'])?$_POST['by']:"id";
$order = isset($_POST['order'])?$_POST['order']:"DESC";
$keyword = isset($_POST['keyword'])?$_POST['keyword']:"";
$pre = ($order=="ASC")?"DESC":"ASC";

if(isset($_POST['filter_company'])) $_SESSION['filter_company'] = $_POST['filter_company'];
$company_id = (isset($_POST['filter_company'])) ? $_POST['filter_company'] : (isset($_SESSION['filter_company']) ? $_SESSION['filter_company'] : $_SESSION['companyID']);

if(isset($_POST['campaign_status'])) $_SESSION['campaign_status'] = $_POST['campaign_status'];
$status = (isset($_POST['campaign_status'])) ? $_POST['campaign_status'] : (isset($_SESSION['campaign_status']) ? $_SESSION['campaign_status'] : STATUS_ACTIVE);

if(isset($_POST['filter_lang'])) $_SESSION['filter_lang'] = $_POST['filter_lang'];
$lang_id = (isset($_POST['filter_lang'])) ? $_POST['filter_lang'] : (isset($_SESSION['filter_lang']) ? $_SESSION['filter_lang'] : 0);

$sub = !empty($lang_id) ? " AND campaigns.sourceLanguageID = $lang_id" : "";
$query = sprintf("SELECT campaigns.campaignID
					FROM campaigns
					LEFT JOIN users ON campaigns.ownerID = users.userID
					LEFT JOIN companies ON users.companyID = companies.companyID
					LEFT JOIN languages ON campaigns.sourceLanguageID = languages.languageID
					LEFT JOIN brands ON campaigns.brandID = brands.brandID
					LEFT JOIN status ON campaigns.campaignStatus = status.statusID
					WHERE campaigns.campaignStatus = %d
					AND users.companyID = %d
					%s
					AND (campaigns.campaignName LIKE '%s'
					OR campaigns.ref LIKE '%s'
					OR languages.languageName LIKE '%s'
					OR companies.companyName LIKE '%s'
					OR brands.brandName LIKE '%s'
					OR users.username LIKE '%s'
					OR status.statusInfo LIKE '%s')",
					$status,
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