<?php
$credits_available = 1000;
$access = array("system","login");
require_once(MODULES.'mod_authorise.php');
$taskID = (isset($_GET['id'])) ? $_GET['id'] : 0;
$page = (isset($_GET['page'])) ? $_GET['page'] : 1;
$pl = (isset($_GET['pl'])) ? $_GET['pl'] : 0;

if(isset($_POST['task_search_type'])) $_SESSION['task_search_type'] = $_POST['task_search_type'];
$task_search_type = (isset($_POST['task_search_type'])) ? $_POST['task_search_type'] : (isset($_SESSION['task_search_type']) ? $_SESSION['task_search_type'] : TYPE_ORIGINAL);
if(isset($_POST['task_search_keyword'])) $_SESSION['task_search_keyword'] = $_POST['task_search_keyword'];
$task_search_keyword = (isset($_POST['task_search_keyword'])) ? $_POST['task_search_keyword'] : (isset($_SESSION['task_search_keyword']) ? $_SESSION['task_search_keyword'] : "");

$query_task = sprintf("SELECT tasks.*,
					artworks.*,
					campaigns.*,
					service_engines.name AS serviceName, service_engines.ext AS serviceExt,
					subjects.*,
					pages.uID AS pageID, pages.*,
					brands.*,
					languages.*,
					U1.userID AS cuid, U1.forename AS cforename, U1.surname AS csurname, U1.email AS cemail,
					U2.userID AS tuid, U2.forename AS tforename, U2.surname AS tsurname, U2.email AS temail
					FROM tasks
					LEFT JOIN artworks ON tasks.artworkID = artworks.artworkID
					LEFT JOIN campaigns ON artworks.campaignID = campaigns.campaignID
					LEFT JOIN service_engines ON artworks.artworkType = service_engines.id
					LEFT JOIN subjects ON artworks.subjectID = subjects.subjectID
					LEFT JOIN pages ON (artworks.artworkID = pages.ArtworkID AND pages.Page = %d)
					LEFT JOIN brands ON campaigns.brandID = brands.brandID
					LEFT JOIN languages ON campaigns.sourceLanguageID = languages.languageID
					LEFT JOIN users U1 ON tasks.creatorID = U1.userID
					LEFT JOIN users U2 ON tasks.translatorID = U2.userID
					WHERE tasks.taskID = %d
					LIMIT 1",
					$page,
					$taskID);
$result_task = mysql_query($query_task, $conn) or die(mysql_error());
if(!mysql_num_rows($result_task)) access_denied();
$row_task = mysql_fetch_assoc($result_task);
$campaignID = $row_task['campaignID'];
$artworkID = $row_task['artworkID'];
$artworkName = $row_task['artworkName'];
$previewFile = $row_task['PreviewFile'];
$pages = $row_task['pageCount'];
$creatorID = $row_task['creatorID'];
$creatorName = $row_task['forename'].' '.$row_task['surname'];
if($page<0 || $page>$pages)  access_denied();
if(!($acl->acl_check("artworks","viewtasks",$_SESSION['companyID'],$_SESSION['userID']) && $DB->check_campaign_acl($campaignID,$_SESSION['companyID'],$_SESSION['userID'])) && !in_array($_SESSION['userID'],$DB->get_task_acl_user_ids($taskID))) access_denied();

$result_proofread = $DB->get_proofreaders($taskID);
$found_proofread = mysql_num_rows($result_proofread);
$alert = (empty($row_task['translatorID']) || empty($found_proofread));

$thumbnail = POSTVIEW_DIR.BareFilename($previewFile)."-".$taskID.".jpg";
//check if preview has been updated
if(!file_exists(ROOT.$thumbnail)) {
	@copy(ROOT.PREVIEW_DIR.$previewFile,ROOT.$thumbnail);
}
list($img_width,$img_height) = @getimagesize(ROOT.$thumbnail);

if(isset($_POST['filter_page'])) $_SESSION['filter_page'] = $_POST['filter_page'];
$page_id = (isset($_POST['filter_page'])) ? $_POST['filter_page'] : (isset($_SESSION['filter_page']) ? $_SESSION['filter_page'] : 0);
$page_id = $DB->reset_page($artworkID,$page_id);
if(isset($_POST['filter_layer'])) $_SESSION['filter_layer'] = $_POST['filter_layer'];
$layer_id = (isset($_POST['filter_layer'])) ? $_POST['filter_layer'] : (isset($_SESSION['filter_layer']) ? $_SESSION['filter_layer'] : 0);
$layer_id = $DB->reset_layer($artworkID,$layer_id);

$query_desiredRs = sprintf("SELECT * FROM languages
							WHERE languageID = %d", $row_task['desiredLanguageID']);
$desiredRs = mysql_query($query_desiredRs, $conn) or die(mysql_error());
$row_desiredRs = mysql_fetch_assoc($desiredRs);

//log translator check in
if( $row_task['tuid'] == $_SESSION['userID'] && !is_null($row_task['vCode']) ) {
	$update = sprintf("UPDATE tasks SET vCode = NULL WHERE taskID = %d", $taskID);
	$result = mysql_query($update, $conn) or die(mysql_error());
	$DB->LogTaskAction($taskID, $_SESSION['userID'], "Checked in for Translation", "Check-in");
}

if(isset($_POST['form'])) {
	if($_POST['form']=="refresh") {
		header("Location: index.php?layout=$layout&id=$taskID&page=$page&do=refresh");
		exit;
	}

	if($_POST['form']=="refreshall") {
		header("Location: index.php?layout=$layout&id=$taskID&page=$page&do=refresh&all=1");
		exit;
	}

	if($_POST['form']=="reset") {
		$task_search_type = TYPE_ORIGINAL;
		$_SESSION['task_search_type'] = TYPE_ORIGINAL;
		$task_search_keyword = "";
		$_SESSION['task_search_keyword'] = "";
	}
}

if(isset($_GET['do'])) {
	if($_GET['do']=="version_control") {
            $FTP = count($_POST['ftpFile']);
            if ($FTP) {
                $artworkFileTempName = $_POST['ftpFile'][0];
                if (empty($artworkFileTempName) && isset($_POST['ftpFile'][1]))
                    $artworkFileTempName = $_POST['ftpFile'][1];
                $artworkFileName = basename($artworkFileTempName);
            } else {
                $artworkFileTempName = $_FILES['artworkFile']['tmp_name'][0];
                $artworkFileName = $_FILES['artworkFile']['name'][0];

                if (empty($artworkFileTempName) && isset($_FILES['artworkFile']['tmp_name'][1]))
                    $artworkFileTempName = $_FILES['artworkFile']['tmp_name'][1];
                if (empty($artworkFileName) && isset($_FILES['artworkFile']['name'][1]))
                    $artworkFileName = $_FILES['artworkFile']['name'][1];
            }

            $VC = ($_POST['restore2'] != $artworkID);
            if ($VC || !empty($artworkFileName)) {

                require_once(CLASSES . "services.php");
                $Service = new EngineService($artworkID);
                if (!$Service->IsServerRunning(10)) server_busy();

                if ($VC && empty($artworkFileName)) {
                    //restore to another version v2
                    $aID = (int) $_POST['restore2'];

                    //Read from artwork Version
                    $update = sprintf("UPDATE artworks SET `live` = 1 WHERE artworkID = %d", $aID);
                    $result = mysql_query($update, $conn) or die(mysql_error());
                }
                if (!empty($artworkFileName)) {
                    //upload new version v2
                    require_once(CLASSES . "indesign_vc.php");
                    $VersionControl = new indesign_versioncontrol($Service, $DB);
                    $VersionControl->setCredits_available($credits_available);
                    $VersionControl->setFTP($FTP);
                    $VersionControl->setFTP_keep(!empty($_POST['keep']));
                    $VersionControl->setParse_type($_POST['parse_type']);
                    //$VersionControl->setArtworkName($artworkFileName);
                    $VersionControl->setVersion($_POST['new_version2']);
                    $VersionControl->setCampaignID($campaignID);
                    $taskID = $_POST['task_id'];
                    $VersionControl->uploadVersionControl($artworkID, $artworkFileTempName, $artworkFileName, $row_task['artworkType'], $taskID);
                }

                //disable old version
                #$update = sprintf("UPDATE artworks SET `live` = 0 WHERE artworkID = %d", $artworkID);
                #$result = mysql_query($update, $conn) or die(mysql_error());
                header("Location: index.php?layout=task&id=$taskID");
                exit;
            }
                
            exit();
        }
	if($_GET['do']=="refresh") {
		require_once(CLASSES."translator.php");
		$Translator = new Translator();
		$Translator->CheckProgress($taskID);
		require_once(CLASSES."services.php");
		$Service = new EngineService($artworkID);
		if(!$Service->IsServerRunning(10)) server_busy();
		$refresh_page = empty($_GET['all']) ? $page : 0;
		$rebuild = $Service->RebuildFile($artworkID,$taskID,$refresh_page,ROOT.POSTVIEW_DIR,"JPG");
		if($rebuild === false) error_creating_file();
		$Service->CheckOverflow($artworkID,$taskID);
		$DB->RebuildPageThumbnail(POSTVIEW_DIR,$artworkID,$refresh_page,$taskID);
		header("Location: index.php?layout=$layout&id=$taskID&page=$page");
		exit;
	}
	
	if($_GET['do']=="submit") {
		$array_users = $DB->get_next_proofreaders($taskID);
		require_once(CLASSES.'Mailer.php');
		$mailer = new Mailer();
		foreach($array_users as $user_id=>$deadline) {
			$query = sprintf("SELECT forename, surname, email
							FROM users
							WHERE userID = %d
							LIMIT 1",
							$user_id);
			$result = mysql_query($query, $conn) or die(mysql_error());
			echo $query;
			if(!mysql_num_rows($result)) continue;
			$row = mysql_fetch_assoc($result);
			$name = $row['forename']." ".$row['surname'];
			$body = "Dear $name,";
			$body .= "\n\nThe following task has been submitted and awaiting for approval:";
			$body .= "\n\nArtwork Title: ".$artworkName;
			$body .= "\nLanguages: ".$row_task['languageName']." -> ".$row_desiredRs['languageName'];
			$body .= "\nWord Count: ".$row_task['wordCount'];
			$body .= "\nDeadline: ".$deadline;
			$body .= "\n\nYou may access it from the link below:";
			$body .= "\n\n".SITE_URL."index.php?layout=task&id=".$taskID;
			$body .= "\n\nKind Regards,";
			$body .= "\n\n".COMPANY_NAME;
			$subject = SYSTEM_NAME.": Task Submission Notification";
			$ccs = array();
			$ccs[$row_task['cemail']] = $row_task['cforename']." ".$row_task['csurname'];
			$done = $mailer->send_mail($name,$row['email'],$subject,$body,$ccs);
		}
		$update = sprintf("UPDATE tasks SET
						taskStatus = 8,
						lastUpdate = NOW()
						WHERE taskID = %d",
						$taskID);
		$result = mysql_query($update, $conn) or die(mysql_error());
		$DB->LogTaskAction($taskID, $_SESSION['userID'], "Submitted Translation Task", "Submission");
		header("Location: index.php?layout=system&id=12");
		exit;
	}
	
	if($_GET['do']=="revert") {
		$update = sprintf("UPDATE tasks SET
							taskStatus = 6,
							lastUpdate = NOW()
							WHERE taskID = %d",
							$taskID);
		$result = mysql_query($update, $conn) or die(mysql_error());
		$DB->LogTaskAction($taskID, $_SESSION['userID'], "Reverted Translation Task", "Revert");
		
		$body = "Dear ".$row_task['tforename']." ".$row_task['tsurname'].",";
		$body .= "\n\nThe following task has been reverted for translation:";
		$body .= "\n\nArtwork Title: ".$artworkName;
		$body .= "\nLanguages: ".$row_task['languageName']." -> ".$row_desiredRs['languageName'];
		$body .= "\nWord Count: ".$row_task['wordCount'];
		$body .= "\nDeadline: ".$row_task['deadline'];
		$body .= "\n\nYou may access it from the link below:";
		$body .= "\n\n".SITE_URL."index.php?layout=task&id=".$taskID;
		$body .= "\n\nKind Regards,";
		$body .= "\n\n".COMPANY_NAME;
		$name = $row_task['tforename']." ".$row_task['tsurname'];
		$address = $row_task['temail'];
		$subject = SYSTEM_NAME.": Task Revert Notification";
		require_once(CLASSES.'Mailer.php');
		$mailer = new Mailer();
		$mailer->send_mail($name,$address,$subject,$body);
		header("Location: index.php?layout=system&id=16");
		exit;
	}
	
	if($_GET['do']=="approve") {
		//set to done
		$update = sprintf("UPDATE task_proofreaders SET
						done = 1
						WHERE task_id = %d
						AND user_id = %d",
						$taskID,
						$_SESSION['userID']);
		$result = mysql_query($update, $conn) or die(mysql_error());
		//
		if($creatorID != $_SESSION['userID']){
			//check if there's next proofreader
			$query = sprintf("SELECT task_proofreaders.order, users.forename, users.surname
							FROM task_proofreaders
							LEFT JOIN users ON task_proofreaders.user_id = users.userID
							WHERE task_proofreaders.task_id = %d
							AND task_proofreaders.user_id = %d
							LIMIT 1",
							$taskID,
							$_SESSION['userID']);
			$result = mysql_query($query, $conn) or die(mysql_error());
			if(!mysql_num_rows($result)) access_denied();
			$row = mysql_fetch_assoc($result);
			$proofreader_name = $creatorName;
		}else{ 
			$proofreader_name = $row['cforename'].' '.$row['csurname'];
		}
		
		$array_users = $DB->get_next_proofreaders($taskID,$row['order']);
		$counter = 0;
		require_once(CLASSES.'Mailer.php');
		$mailer = new Mailer();
		foreach($array_users as $user_id=>$deadline) {
			$query = sprintf("SELECT forename, surname, email
							FROM users
							WHERE userID = %d
							LIMIT 1",
							$user_id);
			$result = mysql_query($query, $conn) or die(mysql_error());
			if(!mysql_num_rows($result)) continue;
			$row = mysql_fetch_assoc($result);
			$name = $row['forename']." ".$row['surname'];
			$body = "Dear $name,";
			$body .= "\n\nThe following task has been approved by $proofreader_name and awaiting for your further approval:";
			$body .= "\n\nArtwork Title: ".$artworkName;
			$body .= "\nLanguages: ".$row_task['languageName']." -> ".$row_desiredRs['languageName'];
			$body .= "\nWord Count: ".$row_task['wordCount'];
			$body .= "\nDeadline: ".$deadline;
			$body .= "\n\nYou may access it from the link below:";
			$body .= "\n\n".SITE_URL."index.php?layout=task&id=".$taskID;
			$body .= "\n\nKind Regards,";
			$body .= "\n\n".COMPANY_NAME;
			$subject = SYSTEM_NAME.": Task Submission Notification";
			$ccs = array();
			$ccs[$row_task['cemail']] = $row_task['cforename']." ".$row_task['csurname'];
			$mailer->send_mail($name,$row['email'],$subject,$body,$ccs);
			$counter++;
		}
		if($counter==0) {
			$update = sprintf("UPDATE tasks SET
							taskStatus = 9,
							lastUpdate = NOW()
							WHERE taskID = %d",
							$taskID);
			$result = mysql_query($update,$conn) or die(mysql_error());
			$manager_name = $row_task['cforename']." ".$row_task['csurname'];
			$body = "Dear $manager_name,";
			$body .= "\n\nThe following task has been approved and awaiting for sign-off:";
			$body .= "\n\nArtwork Title: ".$artworkName;
			$body .= "\nLanguages: ".$row_task['languageName']." -> ".$row_desiredRs['languageName'];
			$body .= "\nWord Count: ".$row_task['wordCount'];
			$body .= "\nDeadline: ".$row_task['deadline'];
			$body .= "\n\nYou may access it from the link below:";
			$body .= "\n\n".SITE_URL."index.php?layout=task&id=".$taskID;
			$body .= "\n\nKind Regards,";
			$body .= "\n\n".COMPANY_NAME;
			$address = $row_task['cemail'];
			$subject = SYSTEM_NAME.": Task Approval Notification";
			$mailer->send_mail($manager_name,$address,$subject,$body);
		}
		$DB->LogTaskAction($taskID, $_SESSION['userID'], "Approved Translation Task", "Approval");
		header("Location: index.php?layout=system&id=14");
		exit;
	}
	
	if($_GET['do']=="signoff") {
		$DB->SignoffTask($taskID);
		header("Location: index.php?layout=system&id=15");
		exit;
	}
}

if(isset($_POST["update"])) {
	if($_POST["update"] == "editTask") {
		$proofreaders_info = array();
		foreach($_POST['order'] as $id=>$order) {
			$proofreaders_info[$id]['order'] = $order;
			$proofreaders_info[$id]['deadline'] = $_POST['pdeadline'][$id];
			$proofreaders_info[$id]['done'] = !empty($_POST['done'][$id]) ? $_POST['done'][$id] : 0;
		}
		$attachment = null;
		if(!empty($_FILES["attachment"]['name'])) {
			$attachment = time()."_".$_FILES["attachment"]['name'];
			move_uploaded_file($_FILES["attachment"]['tmp_name'],REPOSITORY_DIR.$attachment);
		}
		$trial = !empty($_POST['trial']) ? $_POST['trial'] : 0;
		$DB->EditTask($taskID,$_POST['tdeadline'],$proofreaders_info,$_POST['deadline'],$_POST['brief'],$attachment,$trial);
		$Translator = new Translator();
		$Translator->CheckProgress($taskID);
		switch($_POST['target']) {
			case "artwork":
				$location = "index.php?layout=artwork&id=$artworkID";
				break;
			case "task":
				$location = "index.php?layout=task&id=$taskID";
				break;
		}
		header("Location: $location");
		exit;
	}
	
	if($_POST['update']=="exForm") {
		// check credit
		$credits_ask = $DB->get_credit_config($_SESSION['packageID'],$_POST['service_tID']);
		if($credits_ask > $credits_available) no_credit_available();
		$transaction = $DB->get_service_process_transaction($_POST['service_tID']);
		if($transaction === false) error_creating_file();
		
		require_once('download.php');
		$file = GetExportFile($artworkID,$taskID,$_POST['service_tID']);
		if($file === false) error_creating_file();

		// send file as attachment to recipients
		if(!empty($_POST['emails'])) {
			$DB->send_exported_file($_SESSION['username'],$_POST['emails'],$_POST['brief'],ROOT.TMP_DIR.$file);
		}

		$DB->log_credit_transaction($_SESSION['companyID'],$_SESSION['userID'],$campaignID,$artworkID,$taskID,$transaction['notes'],$credits_ask);
		$DB->LogTaskAction($taskID,$_SESSION['userID'],$transaction['notes'],$transaction['ext']);
		$url = "download.php?File=".$file."&SaveAs=".$file."&temp&bin";
		header("Location: $url");
		exit;
	}
	
	if($_POST['update'] == "imForm") {
		// check credit
		$credits_ask = $DB->get_credit_config($_SESSION['packageID'],$_POST['service_tID']);
		if($credits_ask > $credits_available) no_credit_available();
		$transaction = $DB->get_service_process_transaction($_POST['service_tID']);
		if($transaction === false) error_creating_file();
		
		$option = !empty($_POST['option']) ? (int)$_POST['option'] : 0;
		$CS = empty($_POST['loose']) ? true : false;
		require_once(CLASSES."services.php");
		$Service = new EngineService($artworkID);
		$process = new ProcessService($_POST['service_tID']);
		$ProcessEngine = $process->getProcessEngine();
		$import_id = $ProcessEngine->import($artworkID,$taskID,$_FILES['ImportFile']['tmp_name'],$option,$CS);
		if($import_id === false){
			$filename =(!empty($_FILES['ImportFile']['name']))?$_FILES['ImportFile']['name']:"[Empty]";
			$DB->LogSystemEvent($_SESSION['userID'],"failed to import '$filename'",$campaignID,$artworkID,$taskID);
			header("Location: index.php?layout=system&id=11");
			exit;
		}
		if($Service->IsServerRunning(10)) $Service->RebuildFile($artworkID,$taskID,0,ROOT.POSTVIEW_DIR, "JPG");
		$DB->log_credit_transaction($_SESSION['companyID'],$_SESSION['userID'],$campaignID,$artworkID,$taskID,$transaction['notes'],$credits_ask);
		$DB->LogTaskAction($taskID,$_SESSION['userID'],$transaction['notes'],$transaction['ext']);
		header("Location: index.php?layout=task_import&id=$import_id");
		exit;
	}
	
	if($_POST['update']=="dlForm") {
		// check credit
		$credits_ask = $DB->get_credit_config($_SESSION['packageID'],$_POST['service_tID']);
		if($credits_ask > $credits_available) no_credit_available();
		$transaction = $DB->get_service_process_transaction($_POST['service_tID']);
		if($transaction === false) error_creating_file();
		
		require_once('download.php');
		$File = GetDownloadFile($artworkID,$taskID,$_POST['service_tID'],0,true,$_POST['PDFOption']);
		if($File === false) error_creating_file();
		$DB->log_credit_transaction($_SESSION['companyID'],$_SESSION['userID'],$campaignID,$artworkID,$taskID,$transaction['notes'],$credits_ask);
		$DB->LogTaskAction($taskID,$_SESSION['userID'],$transaction['notes'],$transaction['ext']);
		$File = basename($File);
		header("Location: download.php?File=$File&SaveAs=$File&temp&bin");
		exit;
	}
}
?>