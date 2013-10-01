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

//create task
if(!empty($_POST['form'])) {
	
	if($_POST["form"] == "save") {
		$attachment = null;
		if(!empty($_FILES['attachment']['name'])) {
			$attachment = time()."_".$_FILES['attachment']['name'];
			move_uploaded_file($_FILES['attachment']['tmp_name'],REPOSITORY_DIR.$attachment);
		}

		if($_SESSION["task_type"] == "default") {
			foreach($_SESSION['tasks'] as $target_lang_id => $task_info) {
				$translatorIDs = array();
				foreach($task_info['translators'] as $translatorID=>$translatorInfo) {
					$translatorID = (int)$translatorID;
					$tdeadline = $translatorInfo['deadline'];
					$translatorIDs[$translatorID] = $tdeadline;
				}
                                $storyGroup = $task_info['storyGroup'];
                                
				$proofreaderIDs = $task_info['proofreaders'];
				$deadline = $task_info['deadline'];
				$start = empty($_POST["startOption"]) ? true : false;
				$DB->TenderTask($task_info['taskTypeID'], $artworkID, $task_info['paraID'], $target_lang_id,$translatorIDs,$proofreaderIDs,$deadline,$_POST['brief'],$attachment,$_POST["trial"],$start,$task_info['notes'],$storyGroup);
			}
			unset($_SESSION['tasks']);
		}

		if($_SESSION["task_type"] == "agent") {
			$lang_ids = explode(",", $_SESSION['task_langs']);
			foreach($lang_ids as $lang_id) {
				$DB->TenderAgency($artworkID,$lang_id,$_SESSION['agencyID'],$_SESSION['agentID'],$_SESSION['task_deadline'],$_POST['brief'],$attachment,$_POST["trial"]);
			}
			unset($_SESSION['task_langs']);
			unset($_SESSION['agencyID']);
			unset($_SESSION['agentID']);
			unset($_SESSION['task_deadline']);
			unset($_SESSION['storyGroup']);
			unset($_SESSION['task_type']);
		}

		header("Location: index.php?layout=artwork&id=$artworkID");
		exit;
	}
	
}