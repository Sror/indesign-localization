<?php
$access = array("system","campaigns");
require_once(MODULES.'mod_authorise.php');

if(isset($_SESSION['tasks'])) unset($_SESSION['tasks']);

$artworkID = (isset($_GET['id'])) ? $_GET['id'] : 0;
$by = (isset($_POST['by'])) ? $_POST['by'] : "deadline";
$order = (isset($_POST['order'])) ? $_POST['order'] : "ASC";
$preorder = ($order == "ASC") ? "DESC" : "ASC";
$artwork_query = sprintf("SELECT artworks.*,
						service_engines.name AS serviceName, service_engines.ext AS serviceExt,
						pages.*,
						campaigns.campaignID, campaigns.campaignName, campaigns.sourceLanguageID, campaigns.campaignStatus,
						languages.*,
						users.forename, users.surname, users.companyID,
						subjects.subjectTitle,
						fonts.name AS default_sub_font_name
						FROM artworks
						LEFT JOIN service_engines ON artworks.artworkType = service_engines.id
						LEFT JOIN pages ON (artworks.artworkID = pages.ArtworkID AND pages.Page = 1)
						LEFT JOIN campaigns ON artworks.campaignID = campaigns.campaignID
						LEFT JOIN languages ON campaigns.sourceLanguageID = languages.languageID
						LEFT JOIN users ON artworks.uploaderID = users.userID
						LEFT JOIN subjects ON artworks.subjectID = subjects.subjectID
						LEFT JOIN fonts ON artworks.default_sub_font_id = fonts.id
						WHERE artworks.artworkID = %d
						LIMIT 1",
						$artworkID);
$artwork_result = mysql_query($artwork_query, $conn) or die(mysql_error());
if(!mysql_num_rows($artwork_result)) access_denied();
$artwork_row = mysql_fetch_assoc($artwork_result);

/*
Does this artwork contain multiple translations?
*/
$mt_query = "SELECT uID FROM paragraphs WHERE ArtworkID = '".$artwork_row['artworkID']."' AND mt_flag = 1";
$mt_result = mysql_query($mt_query, $conn) or die(mysql_error());
if($mt_flag_count = mysql_num_rows($mt_result)) {
	$artwork_row['mt_flag'] = $mt_flag_count;
} else {
	$artwork_row['mt_flag'] = 0;
}

$campaignID = $artwork_row['campaignID'];
$campaignName = $artwork_row['campaignName'];
$artworkName = $artwork_row['artworkName'];
if(!$DB->check_campaign_acl($campaignID,$_SESSION['companyID'],$_SESSION['userID'])) access_denied();
$sourceLangID = $artwork_row['sourceLanguageID'];
$artwork_editable = ($artwork_row['campaignStatus'] == STATUS_ACTIVE) && ($acl->acl_check("artworks","edit",$_SESSION['companyID'],$_SESSION['userID']));
$artwork_trashable = ($artwork_row['campaignStatus'] == STATUS_ACTIVE) && ($acl->acl_check("artworks","trash",$_SESSION['companyID'],$_SESSION['userID']));

//task manager
if(!empty($_POST['form'])) {
	
	if($_POST['form']=="merge") {
            $query = sprintf('INSERT INTO `tasks` (`artworkID`, `desiredLanguageID`, `agentID`, `translatorID`, `tdeadline`, `creatorID`, `lastUpdate`, 
                `deadline`, `taskStatus`, `currencyID`, `serviceCurrencyID`, `cache`) 
                VALUES 
                (%d, "0", "0", NULL, NULL, "10", "0000-00-00 00:00:00", CURDATE(), "0", "3", "3", "0")',$artworkID);
            $result = mysql_query($query, $conn) or die(mysql_error());
            $viewTaskID = mysql_insert_id();
		foreach($_POST['id'] as $id) {
                    $query = sprintf('SELECT artwork_storygroup_id FROM `tasks` WHERE `tasks`.`taskID`=%d LIMIT 1',$id);
                    $result = mysql_query($query, $conn) or die(mysql_error());
                    while($row = mysql_fetch_assoc($result)){
                        $query = sprintf('SELECT * FROM `artwork_story_group_items` WHERE `artwork_story_groups_id`=%d',$row['artwork_storygroup_id']);
                        $asi_result = mysql_query($query, $conn) or die(mysql_error());
                        while($asi_row = mysql_fetch_assoc($asi_result)){
                            #echo "*";var_dump($asi_row['story_files_id']);
                            #echo "task_id:$id\n";
                            #echo "story_file_id:{$asi_row['story_files_id']}\n";
                            #echo "parent_task_id:$mTaskID";
                            
                            $query = sprintf('INSERT INTO `story_files_task` (`task_id`, `story_file_id`, `parent_task_id`) VALUES (%d, %d, %d)', $viewTaskID,$asi_row['story_files_id'],$id);
                            $result_insert = mysql_query($query, $conn) or die(mysql_error());
                        }
                    }
		}
		header(sprintf("Location: /index.php?layout=task&id=%d&do=refresh&all=1",$viewTaskID));
	}
	
	if($_POST['form']=="start") {
		foreach($_POST['id'] as $id) {
			$DB->StartTask($id);
		}
		header("Location: index.php?layout=artwork&id=$artworkID");
		exit;
	}
	
	if($_POST['form']=="pause") {
		foreach($_POST['id'] as $id) {
			$DB->PauseTask($id);
		}
		header("Location: index.php?layout=artwork&id=$artworkID");
		exit;
	}
	
	if($_POST['form']=="view") {
		$id = $_POST['id'][0];
		header("Location: index.php?layout=task&id=$id");
		exit;
	}
	
	if($_POST['form']=="edit") {
		$id = $_POST['id'][0];
		header("Location: index.php?layout=task&id=$id");
		exit;
	}

	if($_POST['form']=="delete") {
		foreach($_POST['id'] as $id) {
			$DB->DeleteTask($id);
		}
		header("Location: index.php?layout=artwork&id=$artworkID");
		exit;
	}
	
	if($_POST['form']=="save" || $_POST['form']=="restore") {
		if($_POST['form']=="save") {
			if(!empty($_FILES['img_file']['name']) || !empty($_POST['img_content'])) {
				if(!empty($_FILES['img_file']['name'])) {
					require_once(CLASSES.'FTP_Local.php');
					$ftp_local = new FTP_Local();
					$system_name = $DB->get_system_name($_SESSION['companyID']);
					$local_path_to_ftp = ROOT.FTP_DIR.$system_name;
					$local_ftp_dir = '/'.$campaignName.'/';
					$local_ftp_dir = $ftp_local->format_ftp_dir($local_ftp_dir);
					if(!file_exists($local_path_to_ftp)) mkdir($local_path_to_ftp);
					$dir = $local_path_to_ftp.$local_ftp_dir;
					$img_file = $_FILES['img_file']['name'];
					$content = $dir.$img_file;
					if(!file_exists($dir)) mkdir($dir);
					@move_uploaded_file($_FILES['img_file']['tmp_name'], $content);
					$ftp_local->rebuild_local_ftp_cache($_SESSION['companyID'],$local_ftp_dir,$ftp_local->local_list_dir_contents($local_path_to_ftp.$local_ftp_dir));
				} else {
					$content = $_POST['img_content'];
				}
				$IM = new ImageManager();
				$IM->ReplaceImage($_SESSION['userID'],$sourceLangID,$content,$artworkID,$_POST['box_id']);
			}
		}
		
		if($_POST['form']=="restore") {
			$IM = new ImageManager();
			$IM->RestoreImage($artworkID,$_POST['box_id']);
		}

		$DB->LastUpdateArtwork($artworkID);
		
		if($_POST['refresh']) {
			header("Location: index.php?layout=$layout&id=$artworkID&do=refresh");
		} else {
			header("Location: index.php?layout=$layout&id=$artworkID");
		}
		exit;
	}
	
	if($_POST["form"] == "layers") {
		foreach($_POST['id'] as $id) {
			$visible = !empty($_POST['visible'][$id]) ? $_POST['visible'][$id] : 0;
			$locked = !empty($_POST['locked'][$id]) ? $_POST['locked'][$id] : 0;
			$query = sprintf("UPDATE artwork_layers SET
							colour = '%s',
							visible = %d,
							locked = %d
							WHERE id = %d",
							mysql_real_escape_string($_POST['colour'][$id]),
							$visible,
							$locked,
							$id);
			$result = mysql_query($query, $conn) or die(mysql_error());
		}
		$DB->LastUpdateArtwork($artworkID);
		header("Location: index.php?layout=artwork&id=$artworkID");
		exit;
	}

}

if(isset($_GET['do'])) {	
	if($_GET['do']=="refresh") {
		require_once(CLASSES."services.php");
		$Service = new EngineService($artworkID);
		if(!$Service->IsServerRunning(10)) server_busy();
		$rebuild = $Service->RebuildFile($artworkID,0,0,ROOT.PREVIEW_DIR,"JPG");
		if($rebuild === false) error_creating_file('RebuildFile failed');
		header("Location: index.php?layout=$layout&id=$artworkID");
		exit;
	}
	
	if($_GET['do']=="rebuild") {
		// check credit
		$service_process_id = $DB->get_service_process_id($artwork_row['artworkType'],SERVICE_REBUILD,TYPE_ORIGINAL);
		if($service_process_id === false) error_creating_file();
		$transaction = $DB->get_service_process_transaction($service_process_id);
		if($transaction === false) error_creating_file();
		$credits_ask = $DB->get_credit_config($_SESSION['packageID'],$service_process_id);
		if($credits_ask > $credits_available) no_credit_available();
		
		require_once(CLASSES."services.php");
		$process = new ProcessService($service_process_id);
		$ProcessEngine = $process->getProcessEngine();
		$rebuild = $ProcessEngine->RebuildBase($artworkID,$artwork_row['fileName']);
		if($rebuild === false) error_creating_file();
		$DB->LastUpdateArtwork($artworkID);
		
		$DB->log_credit_transaction($_SESSION['companyID'],$_SESSION['userID'],$campaignID,$artworkID,0,$transaction['notes'],$credits_ask);
		header("Location: index.php?layout=$layout&id=$artworkID&do=refresh");
		exit;
	}
	
	if($_GET['do']=="autolookup") {
		$IM = new ImageManager();
		$IM->AutoLookup($_SESSION['userID'], $artworkID);
		header("Location: index.php?layout=$layout&id=$artworkID");
		exit;
	}
	
	if($_GET['do']=="reset") {
		$IM = new ImageManager();
		$IM->RestoreImage($artworkID);
		header("Location: index.php?layout=$layout&id=$artworkID");
		exit;
	}
}

if (isset($_POST["update"])) {
	
	if($_POST["update"] == "fontform") {
		require_once(CLASSES.'Font_Substitution.php');
		foreach($_POST['font'] as $n=>$font_id) {
			$sub_font_id = $_POST['substitute'][$n];
			$X = Font_Substitution::set_font_substitution($font_id,$sub_font_id,$artworkID,'artwork');
		}
		$DB->LastUpdateArtwork($artworkID);
		header("Location: index.php?layout=artwork&id=$artworkID");
		exit;
	}
	
	if($_POST["update"] == "edit_art_form") {
		$parent = ($artwork_row['parent']==0) ? $artwork_row['artworkID'] : $artwork_row['parent'];
		$parent_query = sprintf("SELECT artworkID, parent
								FROM artworks
								WHERE artworkID = %d
								LIMIT 1",
								$artworkID);
		$parent_result = mysql_query($parent_query, $conn) or die(mysql_error());
		if(!mysql_num_rows($parent_result)) access_denied();
		$parent_row = mysql_fetch_assoc($parent_result);
		$parent_id = ($parent_row['parent']==0) ? $parent_row['artworkID'] : $parent_row['parent'];
		
		$ArtworkName = RestrictName($_POST['ArtworkName']);
		//just edit artwork details
		$update = sprintf("UPDATE artworks SET
						artworkName = '%s',
						version = '%s',
						subjectID = %d,
						default_sub_font_id = %d,
						default_img_dir = '%s',
						lastUpdate = NOW()
						WHERE artworkID = %d",
						mysql_real_escape_string($ArtworkName),
						mysql_real_escape_string($_POST['version']),
						$_POST['SubjectID'],
						$_POST['default_sub_font_id'],
						mysql_real_escape_string($_POST['default_img_dir']),
						$artworkID);
		$result = mysql_query($update, $conn) or die(mysql_error());
		$DB->LastUpdateCampaign($campaignID);
		$DB->LogSystemEvent($_SESSION['userID'],"edited artwork: $ArtworkName",0,$artworkID);
		
		$FTP = count($_POST['ftpFile']);
		if($FTP) {
			$artworkFileTempName = $_POST['ftpFile'][0];
			if(empty($artworkFileTempName) && isset($_POST['ftpFile'][1])) $artworkFileTempName = $_POST['ftpFile'][1];
			$artworkFileName = basename($artworkFileTempName);
		} else {
			$artworkFileTempName = $_FILES['artworkFile']['tmp_name'][0];
			$artworkFileName = $_FILES['artworkFile']['name'][0];
			
			if(empty($artworkFileTempName) && isset($_FILES['artworkFile']['tmp_name'][1])) $artworkFileTempName = $_FILES['artworkFile']['tmp_name'][1];
			if(empty($artworkFileName) && isset($_FILES['artworkFile']['name'][1])) $artworkFileName = $_FILES['artworkFile']['name'][1];
		}
		
		$useVC = 0;
		if(!empty($_POST['new_version'])) $useVC = 1;
		if(!empty($_POST['new_version2'])) $useVC = 2;
                $useVC = 1;
		switch($useVC){
			case 1:
			{
				$VC = ($_POST['restore']!=$artworkID) ;
				if($VC || !empty($artworkFileName)) {
					require_once(CLASSES."services.php");
					$Service = new EngineService($artworkID);
					if(!$Service->IsServerRunning(10)) server_busy();

					if($VC && empty($artworkFileName)) {
						//restore to another version v1
						$aID = (int)$_POST['restore'];
						$update = sprintf("UPDATE artworks SET `live` = 1 WHERE artworkID = %d", $aID);
						$result = mysql_query($update, $conn) or die(mysql_error());
					}
					
					
					if(!empty($artworkFileName) && !$VC) {
						//upload new version v1
						if(empty($artworkFileName)) {
							header("Location: index.php?layout=system&id=11");
							exit;
						}
						// check credit
						$service_process_id = $DB->get_service_process_id($artwork_row['artworkType'],SERVICE_UPLOAD,TYPE_ORIGINAL);
						if($service_process_id === false) error_creating_file();
						$transaction = $DB->get_service_process_transaction($service_process_id);
						if($transaction === false) error_creating_file();
						$credits_ask = $DB->get_credit_config($_SESSION['packageID'],$service_process_id);
						if($credits_ask > $credits_available) no_credit_available();

						$FileBasename = RestrictName(BareFilename($artworkFileName,false));
						$FileBasename = strlen($FileBasename)>50 ? substr($FileBasename,50) : $FileBasename;
						$FileName = time()."_".$FileBasename;
						#$FileName = md5($FileBasename.time().rand());
						$query = sprintf("SELECT ext FROM service_engines WHERE id = %d LIMIT 0,1", $artwork_row['artworkType']);
						$result = mysql_query($query, $conn) or die(mysql_error());
						$row = mysql_fetch_assoc($result);
						$FileName .= ".".$row['ext'];

						$Storage = $Service->GetStorage();
						$DestFile = $Storage.$FileName;
						if($FTP) {
							if(empty($_POST['keep'])) {
								$moveFile = rename($artworkFileTempName, $DestFile); //AKA move
							} else {
								$moveFile = copy($artworkFileTempName, $DestFile);
							}
						}else{
							$moveFile = move_uploaded_file($artworkFileTempName, $DestFile);
						}
						if($moveFile === false) access_denied();
						ignore_user_abort(true);
						set_time_limit(0);
						if(!empty($_POST['new_version'])) {
							$version = $_POST['new_version'];
						} else {
							$version = "-";
						}

						$extra = array(
							"campaignID" => $campaignID,
							//"artworkName" => $ArtworkName,
							"subjectID" => $_POST['subjectID'],
							"artworkType" => $artwork_row['artworkType'],
							"parse_type" => $_POST['parse_type'],
							"uploaderID" => $_SESSION['userID'],
							"parent" => $parent,
							"version" => $version,
							"live" => "1"
						);

						if($Service->isValidFile($FileName)) {
							$aID = $DB->EditArtworkDetails($Service->getDocInfo(), $extra);
							$Service->SetPreviewOutputPath(PREVIEW_DIR);
							$process = new ProcessService($service_process_id);
							$ProcessEngine = $process->getProcessEngine();
							$uploaded = $ProcessEngine->UploadFile($aID,$FileName);
							if($uploaded === false) error_creating_file();
							//store artwork fonts
							$fileFontIDs = $Service->GetFileFonts(); //font_id array
							$DB->AddFileFonts($aID,$fileFontIDs);
							//initialise box orders
							$DB->InitialiseBoxOrders($aID);
							//create thumbnails
							$DB->RebuildPageThumbnail(PREVIEW_DIR,$aID);
							$DB->log_credit_transaction($_SESSION['companyID'],$_SESSION['userID'],$campaignID,$aID,0,$transaction['notes'],$credits_ask);
						} else {
							if($DestFile!=""){
								@unlink($DestFile);
								do_rmdir(OUTPUT_DIR.$DestFile);
							}
							header("Location: index.php?layout=system&id=11");
							exit;
						}
					}
					//apply to both version restore and new version upload
					$update = sprintf("UPDATE tasks SET artworkID = %d WHERE artworkID = %d", $aID, $artworkID);
					$result = mysql_query($update, $conn) or die(mysql_error());
					//update prework records
					if(!empty($_POST['update_prework2'])) $DB->UpdatePL($artworkID,$aID);
					//rebuild preview
					$query_utask = sprintf("SELECT taskID
											FROM tasks
											WHERE artworkID = %d
											ORDER BY taskID ASC",
											$aID);
					$result_utask = mysql_query($query_utask, $conn) or die(mysql_error());
					while($row_utask = mysql_fetch_assoc($result_utask)) {
						$taskID = $row_utask['taskID'];
						//update translation records
						if(!empty($_POST['update_task2'])) $DB->UpdatePL($artworkID,$aID,$taskID);
						$rebuild = $Service->RebuildFile($aID,$taskID,0,ROOT.POSTVIEW_DIR,"JPG",0);
						if($rebuild === false) continue;
					}
					//disable old version
					$update = sprintf("UPDATE artworks SET `live` = 0 WHERE artworkID = %d", $artworkID);
					$result = mysql_query($update, $conn) or die(mysql_error());
					header("Location: index.php?layout=artwork&id=$aID&do=refresh");
					exit;
				}
			}
			break;
			case 2:
				$VC = ($_POST['restore2']!=$artworkID) ;
				if($VC || !empty($artworkFileName)) {
					
					require_once(CLASSES."services.php");
					$Service = new EngineService($artworkID);
					if(!$Service->IsServerRunning(10)) server_busy();

					if($VC && empty($artworkFileName)) {
						//restore to another version v2
						$aID = (int)$_POST['restore2'];
						
						//Read from artwork Version
						$update = sprintf("UPDATE artworks SET `live` = 1 WHERE artworkID = %d", $aID);
						$result = mysql_query($update, $conn) or die(mysql_error());
					}
					
					if(!empty($artworkFileName) && !$VC) {
						//upload new version v2
						require_once(CLASSES . "indesign_vc.php");
						$VersionControl = new indesign_versioncontrol($Service, $DB);
						$VersionControl->setCredits_available($credits_available);
						$VersionControl->setFTP($FTP);
						$VersionControl->setFTP_keep( !empty($_POST['keep']) );
						$VersionControl->setParse_type($_POST['parse_type']);
						//$VersionControl->setArtworkName($artworkFileName);
						$VersionControl->setVersion($_POST['new_version2']);
						$VersionControl->setCampaignID($campaignID);
                                                $taskID = null;
						$VersionControl->uploadVersionControl($artworkID, $artworkFileTempName, $artworkFileName, $artwork_row['artworkType'],$taskID);
					}
                                        
                                        //disable old version
                                        $update = sprintf("UPDATE artworks SET `live` = 0 WHERE artworkID = %d", $artworkID);
                                        $result = mysql_query($update, $conn) or die(mysql_error());
                                        header("Location: index.php?layout=artwork&id=$aID&do=refresh");
                                        exit;
				}
			break;
			default:
				die("VC Error 001");
			break;
		}
		
		
		switch($_POST['redirect']) {
			case "campaign":
				header("Location: index.php?layout=campaign&id=$campaignID");
				break;
			case "artwork":
				header("Location: index.php?layout=artwork&id=$artworkID");
				break;
			default:
				header("Location: index.php?layout=artwork&id=$artworkID");
		}
		exit;
	}
	
	if($_POST['update']=="dlForm") {
		$transaction = $DB->get_service_process_transaction($_POST['service_tID']);
		if($transaction === false) error_creating_file();
		// check credit
		$credits_ask = $DB->get_credit_config($_SESSION['packageID'],$_POST['service_tID']);
		if($credits_ask > $credits_available) no_credit_available();
		require_once('download.php');
		$File = GetDownloadFile($artworkID,0,$_POST['service_tID'],0,true,$_POST['PDFOption']);
		if($File === false) error_creating_file();
		$DB->log_credit_transaction($_SESSION['companyID'],$_SESSION['userID'],$campaignID,$artworkID,0,$transaction['notes'],$credits_ask);
		$File = basename($File);
		header("Location: download.php?File=$File&SaveAs=$File&temp&bin");
		exit;
	}
}
?>