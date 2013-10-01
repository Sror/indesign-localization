<?php
$access = array("system","campaigns");
require_once(MODULES.'mod_authorise.php');

$artworkID = (isset($_GET['id'])) ? $_GET['id'] : 0;
$artwork_row = $DB->get_artwork_info($artworkID);
if($artwork_row === false) access_denied();
$campaignID = $artwork_row['campaignID'];
$campaignName = $artwork_row['campaignName'];
$artworkName = $artwork_row['artworkName'];
if(!$DB->check_campaign_acl($campaignID,$_SESSION['companyID'],$_SESSION['userID'])) access_denied();

if(!empty($_POST['form'])) {

	if($_POST["form"] == "next") {
		$task_type = "default";
		if(!empty($_POST['task_type'])) {
			$_SESSION['task_type'] = $_POST['task_type'];
		}
	}

	header("Location: index.php?layout=artwork&id=$artworkID&task=step2");
	exit;

}