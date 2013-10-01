<?php
$access = array("system","campaigns");
require_once(MODULES.'mod_authorise.php');

$id = (isset($_GET['id'])) ? (int)$_GET['id'] : 0;
if(!$DB->check_campaign_acl($id,$_SESSION['companyID'],$_SESSION['userID'])) access_denied();

if(!empty($_POST['form'])) {
	if($_POST['form']=="autolookup") {
		$IM = new ImageManager();
		foreach($_POST['id'] as $artwork_id) {
			$IM->AutoLookup($_SESSION['userID'], $artwork_id);
		}
		header("Location: index.php?layout=$layout&id=$id");
	}

	if($_POST['form']=="trash") {
		foreach($_POST['id'] as $artwork_id) {
			$DB->TrashArtwork($artwork_id);
		}
		header("Location: index.php?layout=$layout&id=$id");
	}
	if($_POST['form']=="restore") {
		foreach($_POST['id'] as $artwork_id) {
			$DB->RestoreArtwork($artwork_id);
		}
		header("Location: index.php?layout=$layout&id=$id");
	}
	if($_POST['form']=="delete") {
		foreach($_POST['id'] as $artwork_id) {
			$DB->DeleteArtwork($artwork_id);
		}
		header("Location: index.php?layout=$layout&id=$id");
	}
	exit();
}

if (!empty($_GET["do"])) {
	if($_GET["do"] == "pauseall") {
		$query = sprintf("SELECT taskID FROM tasks
						LEFT JOIN artworks ON tasks.artworkID = artworks.artworkID
						LEFT JOIN campaigns ON artworks.campaignID = campaigns.campaignID
						WHERE campaigns.campaignID = %d AND tasks.taskStatus IN (6,8,9,10)",
						$id);
		$result = mysql_query($query, $conn) or die(mysql_error());
		while($row = mysql_fetch_assoc($result)) {
			$DB->PauseTask($row['taskID']);
		}
	}
	if($_GET["do"] == "startall") {
		$query = sprintf("SELECT taskID FROM tasks
						LEFT JOIN artworks ON tasks.artworkID = artworks.artworkID
						LEFT JOIN campaigns ON artworks.campaignID = campaigns.campaignID
						WHERE campaigns.campaignID = %d AND tasks.taskStatus IN (5,7)",
						$id);
		$result = mysql_query($query, $conn) or die(mysql_error());
		while($row = mysql_fetch_assoc($result)) {
			$DB->StartTask($row['taskID']);
		}
	}
	header("Location: index.php?layout=campaign&id=$id");
	exit();
}

if (!empty($_POST["update"])) {
	if($_POST["update"] == "camp_cache_form") {
		$result_art = $DB->get_campaign_artworks($id);
		if($result_art !== false) {
			require_once(CLASSES."services.php");
			while($row_art = mysql_fetch_assoc($result_art)) {
				$artwork_id = $row_art['artworkID'];
				$file_name = $row_art['fileName'];
				$Service = new EngineService($artwork_id);
				if(!empty($_POST['artwork_cache'])) $Service->EmptyCache($file_name,0);
				if(!empty($_POST['task_cache'])) {
					$result_task = $DB->get_artwork_tasks($artwork_id);
					if($result_task === false) continue;
					while($row_task = mysql_fetch_assoc($result_task)) {
						$task_id = $row_task['taskID'];
						$Service->EmptyCache($file_name,$task_id);
					}
				}
			}
		}
		header("Location: index.php?layout=campaign&id=$id");
		exit();
	}
	
	if($_POST["update"] == "edit_camp_form") {
		$option = !empty($_POST['lang_id']) ? sprintf("sourceLanguageID = %d,",$_POST['lang_id']) : "";
		$camp_name = RestrictName($_POST['campaign_name']);
		$update = sprintf("UPDATE campaigns SET
							campaignName = '%s',
							brandID = %d,
							ref = '%s',
							$option
							campaignStatus = %d,
							default_sub_font_id = %d,
							default_img_dir = '%s',
							lastEdit = NOW()
							WHERE campaignID = %d",
							mysql_real_escape_string($camp_name),
							$_POST['brand_id'],
							mysql_real_escape_string($_POST['ref']),
							$_POST['status'],
							$_POST['default_sub_font_id'],
							mysql_real_escape_string($_POST['default_img_dir']),
							$id);
		
		require_once(CLASSES.'Font_Substitution.php');
		if(!empty($_POST['default_sub_font_id'])){
			#Font_Substitution::set_default_font($_POST['default_sub_font_id'],NULL,$campaignID);
			Font_Substitution::set_default_font($_POST['default_sub_font_id'],$id,'campaign');
		}else{
			#Font_Substitution::remove_font_substitution(0,NULL,$campaignID);
			Font_Substitution::remove_font_substitution(0,$id,'campaign');
		}
		
		$result = mysql_query($update, $conn) or die(mysql_error());
		$DB->update_campaign_acl($id, $_POST['acl']);
		$DB->LogSystemEvent($_SESSION['userID'],"edited campaign: $camp_name",$id);
		switch($_POST['redirect']) {
			case "campaigns":
				header("Location: index.php?layout=campaigns");
				break;
			case "campaign":
				header("Location: index.php?layout=campaign&id=$id");
				break;
			default:
				header("Location: index.php?layout=campaign&id=$id");
		}
		exit();
	}
	if($_POST["update"] == "new_art_form") {
		// can be disabled due to the implementation of auto-detector
		require_once(CLASSES."services.php");
		if(!empty($_POST["artworkType"])) {
			$Service = new EngineService($_POST["artworkType"],true);
			if(!$Service->IsServerRunning(10)) server_busy();
		}
		
		if(empty($_SESSION['TmpFile'])) {
			$FTP = count($_POST['ftpFile']);
			if($FTP) {
				foreach($_POST['ftpFile'] as $k => $files) {
					$artworkFile['name'][$k] = basename($files);
					$artworkFile['tmp_name'][$k] = $files;
				}
			} else {
				$artworkFile = $_FILES['artworkFile'];
			}
			$_SESSION['TmpFile'] = array('name'=>array(), 'tmp_name'=>array());
		} else {
			$artworkFile = $_SESSION['TmpFile'];
		}
		foreach($artworkFile['name'] as $k=>$v) {
			if(empty($v)) unset($artworkFile['name'][$k]);
		}
		$count = count($artworkFile['name']);
		if($count == 0) error_uploading_file();
		$upload_id = $DB->start_upload($id,$_SESSION['userID']);
		$token_log = ROOT.TMP_DIR.$_POST['token'];
		foreach($artworkFile['name'] as $k => $artworkFileName) {
			if(empty($artworkFileName)) continue;
			$log_id = $DB->add_upload_log($upload_id,$artworkFileName);
			$FileTmp = $artworkFile['tmp_name'][$k];
			$TmpFile = ROOT.TMP_DIR.$artworkFileName;
			if($FTP && empty($_POST['keep'])) {
				@rename($FileTmp,$TmpFile);
			} else {
				@copy($FileTmp,$TmpFile);
			}
			$DB->update_upload_log($log_id,array("progress"=>10));
			if(!file_exists($TmpFile) || !is_file($TmpFile)) {
				$DB->update_upload_log($log_id,array("error_id"=>1));
				continue;
			}
			$_SESSION['TmpFile']['name'][$k] = $artworkFileName;
			$_SESSION['TmpFile']['tmp_name'][$k] = $TmpFile;
			$FileBasename = RestrictName(BareFilename($artworkFileName,false));
			$FileBasename = strlen($FileBasename)>50 ? substr($FileBasename,50) : $FileBasename;
			$FileName = time()."_".$FileBasename;
			#$FileName = md5($FileBasename.time().rand());
			if(empty($_POST['artworkType'])) {
				require_once(CLASSES."File_Detector.php");
				$FileInfo = new File_Detector(FILE_TYPE_DETECTOR,$TmpFile,$artworkFileName);
				$FileSure = $FileInfo->getSure();
				if($FileSure < 50) {
					$DB->update_upload_log($log_id,array("error_id"=>2));
					log_error($artworkFileName.' - '.$FileInfo->getSignature()." [$FileSure%]","Auto-Detect");
					continue;
				}
				$FileExt = strtoupper($FileInfo->getCode());
				$FileNote = $FileInfo->getExt()." ".$FileInfo->getVersion();
				$query = sprintf("SELECT id FROM service_engines WHERE ext = '%s' LIMIT 1", mysql_real_escape_string($FileExt));
				$result = mysql_query($query, $conn) or die(mysql_error());
				if(!mysql_num_rows($result)) {
					$DB->update_upload_log($log_id,array("error_id"=>3));
					continue;
				}
				$row = mysql_fetch_assoc($result);
				$FileType = $row['id'];
			} else {
				$FileType = $_POST['artworkType'];
				$query = sprintf("SELECT ext FROM service_engines WHERE id = %d LIMIT 1", $FileType);
				$result = mysql_query($query, $conn) or die(mysql_error());
				if(!mysql_num_rows($result)) {
					$DB->update_upload_log($log_id,array("error_id"=>3));
					continue;
				}
				$row = mysql_fetch_assoc($result);
				$FileExt = $row['ext'];
				$FileNote = NULL;
			}
			// check credit
			$service_process_id = $DB->get_service_process_id($FileType,SERVICE_UPLOAD,TYPE_ORIGINAL);
			if($service_process_id === false) {
				$DB->update_upload_log($log_id,array("error_id"=>3));
				continue;
			}
			$transaction = $DB->get_service_process_transaction($service_process_id);
			if($service_process_id === false) {
				$DB->update_upload_log($log_id,array("error_id"=>3));
				continue;
			}

			$credits_ask = $DB->get_credit_config($_SESSION['packageID'],$service_process_id);
			if($credits_ask > $credits_available) {
				$DB->update_upload_log($log_id,array("error_id"=>7));
				continue;
			}

			$Service = new EngineService($FileType,true);
			if(!$Service->IsServerRunning(10)) {
				$DB->update_upload_log($log_id,array("error_id"=>4));
				continue;
			}
			$FileName .= ".$FileExt";
			file_put_contents($token_log,$FileName);
			$Storage = $Service->GetStorage();
			$DestFile = $Storage.$FileName;
			$moveFile = @copy($TmpFile,$DestFile);
			if($moveFile === false) {
				$DB->update_upload_log($log_id,array("error_id"=>5));
				continue;
			}
			$DB->update_upload_log($log_id,array("progress"=>20));
			ignore_user_abort(true);
			set_time_limit(0);
			$artworkName = empty($_POST['artworkName']) ? $FileBasename : RestrictName($_POST['artworkName']);
			if($count>1 && !empty($_POST['artworkName'])) {
				$artworkName .= "_".($k+1);
			}
			$extra = array(
				"campaignID" => $id,
				"artworkName" => $artworkName,
				"subjectID" => $_POST['subjectID'],
				"artworkType" => $FileType,
				"parse_type" => $_POST['parse_type'],
				"uploaderID" => $_SESSION['userID'],
				"parent" => 0,
				"version" => $_POST['version'],
				"live" => 1,
				"default_sub_font_id" => $_POST['default_sub_font_id'],
				"default_img_dir" => $_POST['default_img_dir'],
				"note" => $FileNote
			);

			if(!$Service->isValidFile($FileName)) {
				if($DestFile!=""){
					@unlink($DestFile);
					do_rmdir(OUTPUT_DIR.$DestFile);
				}
				$DB->update_upload_log($log_id,array("error_id"=>6));
				continue;
			}
			$DB->update_upload_log($log_id,array("progress"=>30));
			$aID = $DB->EditArtworkDetails($Service->getDocInfo(), $extra);
			$DB->update_upload_log($log_id,array("artwork_id"=>$aID,"progress"=>40));
			$Service->SetPreviewOutputPath(PREVIEW_DIR);
			$DB->update_upload_log($log_id,array("progress"=>50));
			$process = new ProcessService($service_process_id);
			$ProcessEngine = $process->getProcessEngine();
			if(!empty($_POST['break_softreturn'])) $ProcessEngine->addConfig('softreturn',true);
			$uploaded = $ProcessEngine->UploadFile($aID,$FileName);
			if($uploaded === false) {
				$DB->update_upload_log($log_id,array("artwork_id"=>0,"error_id"=>8));
				$DB->DeleteArtwork($aID,false);
				continue;
			}
			$DB->update_upload_log($log_id,array("progress"=>60));
			//store artwork fonts
			$fileFontIDs = $Service->GetFileFonts(); //font_id array
			$DB->AddFileFonts($aID,$fileFontIDs);
			$DB->update_upload_log($log_id,array("progress"=>70));
			//initialise box orders
			$DB->InitialiseBoxOrders($aID);
			$DB->update_upload_log($log_id,array("progress"=>80));
			//create thumbnails
			$DB->RebuildPageThumbnail(PREVIEW_DIR,$aID);
			$DB->update_upload_log($log_id,array("progress"=>90));

			if (!empty($_POST['desiredLanguageID']) && !empty($_SESSION['translators']) && !empty($_POST['deadline'])) {
				$translatorIDs = array();
				foreach($_SESSION['translators'] as $translatorID) {
					$translatorID = (int)$translatorID;
					$tdeadline = $_POST['tdeadline'][$translatorID];
					$translatorIDs[$translatorID] = $tdeadline;
				}
				$proofreaderIDs = array();
				if(!empty($_SESSION['proofreaders'])) {
					foreach($_SESSION['proofreaders'] as $proofreaderID) {
						$proofreaderID = (int)$proofreaderID;
						$order = $_POST['order'][$proofreaderID];
						$pdeadline = $_POST['pdeadline'][$proofreaderID];
						$proofreaderIDs[$proofreaderID]['order'] = $order;
						$proofreaderIDs[$proofreaderID]['deadline'] = $pdeadline;
					}
				}
				$attachment = null;
				if(!empty($_FILES["attachment"]['name'])) {
					$attachment = time()."_".$_FILES["attachment"]['name'];
					move_uploaded_file($_FILES["attachment"]['tmp_name'],REPOSITORY_DIR.$attachment);
				}
				$start = empty($_POST["startOption"]) ? true : false;
				$DB->TenderTask($aID,$_POST['desiredLanguageID'],$translatorIDs,$proofreaderIDs,$_POST['deadline'],$_POST['brief'],$attachment,$_POST["trial"],$start);
				unset($_SESSION['translators']);
				unset($_SESSION['proofreaders']);
			}
			if (!empty($_POST['targetLangID']) && !empty($_POST['agentID']) && !empty($_POST['timescale'])) {
				$attachment = null;
				if(!empty($_FILES['jobattachment']['name'])) {
					$attachment = time()."_".$_FILES['jobattachment']['name'];
					move_uploaded_file($_FILES['jobattachment']['tmp_name'],REPOSITORY_DIR.$attachment);
				}
				foreach($_POST['targetLangID'] as $targetLangID) {
					$DB->TenderAgency($aID,$targetLangID,$_POST['agencyID'],$_POST['agentID'],$_POST['timescale'],$_POST['jobbrief'],$_POST["trialOption"]);
				}
			}
			unset($Service);
			unset($_SESSION['TmpFile']['name'][$k]);
			unset($_SESSION['TmpFile']['tmp_name'][$k]);
			@unlink($TmpFile);
			$DB->end_upload_log($log_id);
			$DB->log_credit_transaction($_SESSION['companyID'],$_SESSION['userID'],$id,$aID,0,$transaction['notes'],$credits_ask);
		}
		@unlink($token_log);
		$DB->LastUpdateCampaign($id);
		$DB->end_upload($upload_id);
		unset($_SESSION['TmpFile']);
		/*
		 *
		if($count == 1) {
			$_SESSION['PrevUrl'] = "index.php?layout=artwork&id=$aID";
		} else {
			$_SESSION['PrevUrl'] = "index.php?layout=campaign&id=$id";
		}
		 *
		 */
		$_SESSION['PrevUrl'] = "index.php?layout=campaign_upload&id=$upload_id";
		header("Location: index.php?layout=system&id=9");
		exit;
	}
}

$camp_query = sprintf("SELECT campaignName, campaignStatus
					FROM campaigns
					WHERE campaignID = %d
					LIMIT 1",
					$id);
$camp_result = mysql_query($camp_query, $conn) or die(mysql_error());
if(!mysql_num_rows($camp_result)) access_denied();
$camp_row = mysql_fetch_assoc($camp_result);

$by = isset($_POST['by']) ? $_POST['by'] : "lastUpdate";
$order = isset($_POST['order']) ? $_POST['order'] : "DESC";
$keyword = isset($_POST['keyword']) ? $_POST['keyword'] : "";
$pre = ($order=="ASC") ? "DESC" : "ASC";

if(isset($_POST['artwork_status'])) $_SESSION['artwork_status'] = $_POST['artwork_status'];
$status = (isset($_POST['artwork_status'])) ? $_POST['artwork_status'] : (isset($_SESSION['artwork_status']) ? $_SESSION['artwork_status'] : STATUS_ACTIVE);

if(isset($_POST['filter_view'])) $_SESSION['filter_view'] = $_POST['filter_view'];
$view = (isset($_POST['filter_view'])) ? $_POST['filter_view'] : (isset($_SESSION['filter_view']) ? $_SESSION['filter_view'] : DEFAULT_VIEW);

if(isset($_POST['filter_type'])) $_SESSION['filter_type'] = $_POST['filter_type'];
$type = (isset($_POST['filter_type'])) ? $_POST['filter_type'] : (isset($_SESSION['filter_type']) ? $_SESSION['filter_type'] : 0);

if(isset($_POST['filter_subject'])) $_SESSION['filter_subject'] = $_POST['filter_subject'];
$subject = (isset($_POST['filter_subject'])) ? $_POST['filter_subject'] : (isset($_SESSION['filter_subject']) ? $_SESSION['filter_subject'] : 0);

$sub = "";
$sub .= !empty($type) ? " AND artworks.artworkType = $type" : "";
$sub .= !empty($subject) ? " AND artworks.subjectID = $subject" : "";
$query = sprintf("SELECT artworks.artworkID
				FROM artworks
				LEFT JOIN users ON artworks.uploaderID = users.userID
				LEFT JOIN companies ON users.companyID = companies.companyID
				LEFT JOIN subjects ON artworks.subjectID = subjects.subjectID
				LEFT JOIN service_engines ON artworks.artworkType = service_engines.id
				WHERE artworks.campaignID = %d
				AND artworks.live = %d
				%s
				AND (artworks.artworkName LIKE '%s'
				OR companies.companyName LIKE '%s'
				OR subjects.subjectTitle LIKE '%s'
				OR users.username LIKE '%s'
				OR users.forename LIKE '%s'
				OR users.surname LIKE '%s'
				OR service_engines.name LIKE '%s'
				OR service_engines.ext LIKE '%s')",
				$id,
				$status,
				mysql_real_escape_string($sub),
				"%".mysql_real_escape_string($keyword)."%",
				"%".mysql_real_escape_string($keyword)."%",
				"%".mysql_real_escape_string($keyword)."%",
				"%".mysql_real_escape_string($keyword)."%",
				"%".mysql_real_escape_string($keyword)."%",
				"%".mysql_real_escape_string($keyword)."%",
				"%".mysql_real_escape_string($keyword)."%",
				"%".mysql_real_escape_string($keyword)."%");
$result = mysql_query($query, $conn) or die(mysql_error());
$total = mysql_num_rows($result);

$limit = isset($_POST['limit']) ? (int)$_POST['limit'] : RPP;
$pages = (ceil($total/$limit)==0) ? 1 : ceil($total/$limit);
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$offset = $limit*($page-1);
?>