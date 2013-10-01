<?php
$access = array("artworks","edit");
require_once(MODULES.'mod_authorise.php');
require_once(CLASSES."phpmailer/class.phpmailer.php");

$taskID = (isset($_GET['id'])) ? $_GET['id'] : 0;
$page = (isset($_GET['page'])) ? $_GET['page'] : 1;
$query_task = sprintf("SELECT *, artworks.version AS version, pages.uID AS pageID, pages.PageScale,
						service_engines.name AS serviceName, service_engines.ext AS serviceExt,
						U1.userID AS cuid, U1.forename AS cforename, U1.surname AS csurname, U1.email AS cemail,
						U2.userID AS tuid, U2.forename AS tforename, U2.surname AS tsurname, U2.email AS temail
						FROM tasks
						LEFT JOIN artworks ON tasks.artworkID = artworks.artworkID
						LEFT JOIN service_engines ON artworks.artworkType = service_engines.id
						LEFT JOIN subjects ON artworks.subjectID = subjects.subjectID
						LEFT JOIN pages ON (artworks.artworkID = pages.ArtworkID AND pages.Page = %d)
						LEFT JOIN campaigns ON artworks.campaignID = campaigns.campaignID
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
if($page<0 || $page>$row_task['pageCount'])  access_denied();
if(!($acl->acl_check("artworks","viewtasks",$_SESSION['companyID'],$_SESSION['userID']) && $DB->check_campaign_acl($row_task['campaignID'],$_SESSION['companyID'],$_SESSION['userID'])) && !in_array($_SESSION['userID'],$DB->get_task_acl_user_ids($taskID))) access_denied();
$artworkID = $row_task['artworkID'];
$previewFile = $row_task['PreviewFile'];
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
if(isset($_POST['filter_box'])) $_SESSION['filter_box'] = $_POST['filter_box'];
$box_type = (isset($_POST['filter_box'])) ? $_POST['filter_box'] : (isset($_SESSION['filter_box']) ? $_SESSION['filter_box'] : 'TEXT');

$query_desiredRs = sprintf("SELECT * FROM languages
							WHERE languageID = %d", $row_task['desiredLanguageID']);
$desiredRs = mysql_query($query_desiredRs, $conn) or die(mysql_error());
$row_desiredRs = mysql_fetch_assoc($desiredRs);
$totalRows_desiredRs = mysql_num_rows($desiredRs);

if (isset($_POST['form'])) {
	
	if($_POST['form']=="close") {
		header("Location: index.php?layout=task&id=$taskID");
		exit;
	}
	
	if($_POST['form']=="refresh") {
		header("Location: index.php?layout=$layout&id=$taskID&page=$page&do=refresh");
		exit;
	}

	if($_POST['form']=="refreshall") {
		header("Location: index.php?layout=$layout&id=$taskID&page=$page&do=refresh&all=1");
		exit;
	}
	
	if($_POST['form']=="amend") {
		$DB->SaveBoxProperties($artworkID,$_POST['box_id'],$taskID,$_POST['lock'],$_POST['resize']);
		$DB->SaveBoxMoves($artworkID,$_POST['box_id'],$taskID,(int)$_POST['left'],(int)$_POST['left']+(int)$_POST['boxwidth'],(int)$_POST['top'],(int)$_POST['top']+(int)$_POST['boxheight'],(int)$_POST['angle']);
		if(!empty($_FILES['img_file']['name']) || !empty($_POST['img_content'])) {
			if(!empty($_FILES['img_file']['name'])) {
				$img_file = time()."_".preg_replace('/[^\da-z_\(\).]+/i', '_', trim(basename($_FILES['img_file']['name'])));
				$content = IMG_LIBRARY_DIR.$img_file;
				move_uploaded_file($_FILES['img_file']['tmp_name'], $content);
			} else {
				$content = $_POST['img_content'];
			}
			$IM = new ImageManager();
			$IM->ReplaceImage($_SESSION['userID'], $row_task['desiredLanguageID'], $content, $artworkID, $_POST['box_id'], $taskID);
		}
		$DB->AddChangedItem($artworkID,$_POST['box_id'],$taskID);
		header("Location: index.php?layout=$layout&id=$taskID&page=$page&do=refresh");
		exit;
	}
	
	if($_POST['form']=="restore") {
		$DB->RestoreBoxMoves($artworkID,$_POST['box_id'],$taskID);
		$IM = new ImageManager();
		$IM->RestoreImage($artworkID,$_POST['box_id'],$taskID);
		$DB->AddChangedItem($artworkID,$_POST['box_id'],$taskID);
		header("Location: index.php?layout=$layout&id=$taskID&page=$page&do=refresh");
		exit;
	}
}

if(isset($_GET['do'])) {
	if($_GET['do']=="refresh") {
		$refresh_page = empty($_GET['all']) ? $page : 0;
		require_once(CLASSES."services.php");
		$Service = new EngineService($artworkID);
		if(!$Service->IsServerRunning(10)) server_busy();
		$rebuild = $Service->RebuildFile($artworkID,$taskID,$refresh_page,ROOT.POSTVIEW_DIR,"JPG");
		if($rebuild === false) error_creating_file();
		$Service->CheckOverflow($artworkID,$taskID);
		$DB->RebuildPageThumbnail(POSTVIEW_DIR,$artworkID,$refresh_page,$taskID);
		header("Location: index.php?layout=$layout&id=$taskID&page=$page");
		exit;
	}
}

if(isset($_POST["update"])) {
	if($_POST["update"] == "upForm") {
		// check credit
		$credits_ask = $DB->get_credit_config($_SESSION['packageID'],$_POST['service_tID']);
		if($credits_ask > $credits_available) no_credit_available();
		$transaction = $DB->get_service_process_transaction($_POST['service_tID']);
		if($transaction === false) error_creating_file();

		$FTP = count($_POST['ftpFile']);
		if($FTP) {
			$artworkFile['name'] = basename($_POST['ftpFile']);
			$artworkFile['tmp_name'] = $_POST['ftpFile'];
		} else {
			$artworkFile = $_FILES['artworkFile'];
		}

		$artworkFileName = $artworkFile['name'][0];
		$artworkFileTempName = $artworkFile['tmp_name'][0];
		require_once(CLASSES."services.php");
		$Service = new EngineService($artworkID);
		if(!$Service->IsServerRunning(10)) server_busy();
		if(empty($artworkFileName)) {
			header("Location: index.php?layout=system&id=11");
			exit;
		}
		$FileName = date("YmdHis")."_".RestrictName(BareFilename(basename($artworkFileName))).".tmp";
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
		if($Service->isValidFile($FileName)) {
			$process = new ProcessService($_POST['service_tID']);
			$ProcessEngine = $process->getProcessEngine();
			$tweaked = $ProcessEngine->TweakFile($artworkID,$taskID,$FileName);
			if($tweaked === false) error_creating_file();
			$DB->LogTaskAction($taskID,$_SESSION['userID'],"Customise","Tweak Artwork");
			$Service->RebuildFile($artworkID,$taskID,0,ROOT.POSTVIEW_DIR,"JPG",0);
			$DB->log_credit_transaction($_SESSION['companyID'],$_SESSION['userID'],$campaignID,$artworkID,$taskID,$transaction['notes'],$credits_ask);
			$DB->LogTaskAction($taskID,$_SESSION['userID'],$transaction['notes'],$transaction['ext']);
			header("Location: index.php?layout=$layout&id=$taskID");
			exit;
		} else {
			if($DestFile!=""){
				@unlink($DestFile);
				@do_rmdir(OUTPUT_DIR.$DestFile);
			}
			header("Location: index.php?layout=system&id=11");
			exit;
		}
	}
	
	if($_POST["update"] == "fontform") {
		require_once(CLASSES.'Font_Substitution.php');
		foreach($_POST['substitute'] as $font_id=>$sub_font_id) {
			if(!$sub_font_id || $font_id==$sub_font_id)
				Font_Substitution::remove_font_substitution($font_id,NULL,NULL,NULL,$taskID);
			else
				Font_Substitution::set_font_substitution($font_id,$sub_font_id,NULL,NULL,NULL,$taskID);
		}
	}
		header("Location: index.php?layout=$layout&id=$taskID&page=$page&do=refresh");
		exit;
}
?>