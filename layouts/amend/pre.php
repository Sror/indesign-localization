<?php
//disabled for guests access
#$access = array("artworks","edit");
#require_once(MODULES.'mod_authorise.php');
require_once(ACL.'acl.class.php');
require_once(ACL.'acl_api.class.php');
require_once(ACL.'admin/acl_admin.inc.php');
$acl = new acl($acl_options);

$artworkID = isset($_GET['id']) ? $_GET['id'] : 0;
$is_guest = false;
$token = isset($_GET['token']) ? $_GET['token'] : (isset($_SESSION['token'])?$_SESSION['token']:"");
$_SESSION['token'] = $token;
$query = sprintf("SELECT id, name, email
				FROM artwork_guests
				WHERE artwork_id = %d
				AND token = '%s'
				LIMIT 1",
				$artworkID,
				mysql_real_escape_string($token));
$result = mysql_query($query, $conn) or die(mysql_error());
if(mysql_num_rows($result)) {
	$is_guest = true;
	$row = mysql_fetch_assoc($result);
	$guest_name = $row['name'];
	$guest_email = $row['email'];
	$query = sprintf("SELECT users.userID, users.username, users.password,
					users.userGroupID, users.companyID,
					companies.packageID,
					language_options.acronym
					FROM users
					LEFT JOIN companies ON companies.companyID = users.companyID
					LEFT JOIN language_options ON users.langID = language_options.id
					WHERE users.email = '%s'
					LIMIT 1",
					mysql_real_escape_string($guest_email));
	$result = mysql_query($query, $conn) or die(mysql_error());
	if(mysql_num_rows($result)==1) {
		$is_guest = false;
		$row = mysql_fetch_assoc($result);
		$_SESSION['userID'] = $row['userID'];
		$_SESSION['username'] = $row['username'];
		$_SESSION['ugID'] = $row['userGroupID'];
		$_SESSION['companyID'] = $row['companyID'];
		$_SESSION['packageID'] = $row['packageID'];
		$_SESSION['lang'] = $row['acronym'];
		#setcookie("companyID", $row['companyID'], time()+60*60*24*7);
		unset($_SESSION['token']);
	}
}

if($is_guest===false) {
	$access = array("system","login");
	require_once(MODULES.'mod_authorise.php');
}

$page = isset($_GET['page']) ? $_GET['page'] : 1;
if(isset($_POST['filter_page'])) $_SESSION['filter_page'] = $_POST['filter_page'];
$page_id = (isset($_POST['filter_page'])) ? $_POST['filter_page'] : (isset($_SESSION['filter_page']) ? $_SESSION['filter_page'] : 0);
$page_id = $DB->reset_page($artworkID,$page_id);
if(isset($_POST['filter_layer'])) $_SESSION['filter_layer'] = $_POST['filter_layer'];
$layer_id = (isset($_POST['filter_layer'])) ? $_POST['filter_layer'] : (isset($_SESSION['filter_layer']) ? $_SESSION['filter_layer'] : 0);
$layer_id = $DB->reset_layer($artworkID,$layer_id);
if(isset($_POST['filter_box'])) $_SESSION['filter_box'] = $_POST['filter_box'];
$box_type = (isset($_POST['filter_box'])) ? $_POST['filter_box'] : (isset($_SESSION['filter_box']) ? $_SESSION['filter_box'] : 'TEXT');

$artwork_query = sprintf("SELECT *, artworks.version AS version,
						service_engines.name AS serviceName, service_engines.ext AS serviceExt,
						pages.uID AS pageID, pages.PageScale
						FROM artworks
						LEFT JOIN service_engines ON artworks.artworkType = service_engines.id
						LEFT JOIN subjects ON artworks.subjectID = subjects.subjectID
						LEFT JOIN pages ON (artworks.artworkID = pages.ArtworkID AND pages.Page = %d)
						LEFT JOIN campaigns ON artworks.campaignID = campaigns.campaignID
						LEFT JOIN brands ON campaigns.brandID = brands.brandID
						LEFT JOIN languages ON campaigns.sourceLanguageID = languages.languageID
						WHERE artworks.artworkID = %d
						LIMIT 1",
						$page,
						$artworkID);
$artwork_result = mysql_query($artwork_query, $conn) or die(mysql_error());
if(!mysql_num_rows($artwork_result)) access_denied();
$artwork_row = mysql_fetch_assoc($artwork_result);
$campaignID = $artwork_row['campaignID'];
$sourceLangID = $artwork_row['sourceLanguageID'];
$artworkName = $artwork_row['artworkName'];
$pages = $artwork_row['pageCount'];
if($page<0 || $page>$pages) access_denied();
if(!$is_guest && !$DB->check_campaign_acl($campaignID,$_SESSION['companyID'],$_SESSION['userID'])) access_denied();
$previewFile = $artwork_row['PreviewFile'];
$thumbnail = PREVIEW_DIR.$previewFile;
list($img_width,$img_height) = @getimagesize(ROOT.$thumbnail);

if (isset($_POST['form'])) {
	
	if($_POST['form']=="close") {
		header("Location: index.php?layout=artwork&id=$artworkID");
		exit;
	}
	
	if($_POST['form']=="refresh") {
		header("Location: index.php?layout=$layout&id=$artworkID&page=$page&do=refresh");
		exit;
	}

	if($_POST['form']=="refreshall") {
		header("Location: index.php?layout=$layout&id=$artworkID&page=$page&do=refresh&all=1");
		exit;
	}
	
	if($_POST['form']=="amend") {
		// save box changes
		$DB->SaveBoxProperties($artworkID,$_POST['box_id'],0,$_POST['lock'],$_POST['resize']);
		$DB->SaveBoxMoves($artworkID,$_POST['box_id'],0,(int)$_POST['left'],(int)$_POST['left']+(int)$_POST['boxwidth'],(int)$_POST['top'],(int)$_POST['top']+(int)$_POST['boxheight'],(int)$_POST['angle']);

		$Translator = new Translator();
		if(!empty($_POST['para'])){
			foreach($_POST['para'] as $PL=>$Para) {
				$story_info = $Translator->GetStoryInfoByPL($PL);
				if($story_info === false) continue;
				$SG = $story_info['StoryGroup'];
				$SO = $story_info['order'];
				$PageRef = $story_info['PageRef'];
				$SegRef = $story_info['SegRef'];
				if(count($Para) == 1) {
					// edit para
					$ParaText = $Para[0];
					$source_para_row = $Translator->GetParaByPL($PL,false);
					if($source_para_row === false) continue;
					$new_para_row = $Translator->AddParagraph($ParaText,$sourceLangID,0,$_SESSION['userID'],PARA_USER,$artwork_row['brandID'],$artwork_row['subjectID'],$SG,$SO,$PageRef,$SegRef);
					if($new_para_row === false) continue;
					$new_para_id = $new_para_row['ParaID'];
					$amended_para_row = $Translator->GetAmendedPara($PL);
					if($amended_para_row !== false) {
						$amended_para_id = $amended_para_row['ParaID'];
						if($amended_para_id == $new_para_id) continue;
					}
					$Translator->AmendPara($PL,$new_para_id,$_SESSION['userID']);
				} else {
					// split para
					$DB->DeactivatePL($PL);
					foreach($Para as $k=>$ParaText) {
						$split_para_row = $Translator->AddParagraph($ParaText,$sourceLangID,$_POST['box_id'],$_SESSION['userID'],PARA_USER,$artwork_row['brandID'],$artwork_row['subjectID'],$SG,$SO,$PageRef,$SegRef);
						if($split_para_row !== false) {
							$split_PL = $split_para_row['PL'];
							$DB->MarkPLType($split_PL,TYPE_SPLIT);
						}
					}
					$DB->ResetStoryOrder($SG);
				}
			}
		}
		// merge
		$PLs = array();
		$SGs = array();
		$orders = array();
		if(is_array($_POST['merge'])){
		  foreach($_POST['merge'] as $PL=>$order) {
			  $story_info = $Translator->GetStoryInfoByPL($PL);
			  if($story_info === false) continue;
			  $SGs[] = $story_info['StoryGroup'];
			  $orders[] = $order;
			  $PLs[] = $PL;
		  }
		  $SG1 = min($SGs);
		  $SG2 = max($SGs);
		  if($SG1 == $SG2) {
			  $SG = $SG1;
			  $SO = min($orders);
			  $merged_para = "";
			  foreach($PLs as $PL) {
				  $para_row = $Translator->GetParaByPL($PL,true);
				  if($para_row === false) continue;
				  $merged_para .= $para_row['ParaText'];
				  $DB->DeactivatePL($PL);
			  }
			  $merged_para_row = $Translator->AddParagraph($merged_para,$sourceLangID,$_POST['box_id'],$_SESSION['userID'],PARA_USER,$artwork_row['brandID'],$artwork_row['subjectID'],$SG,$SO);
			  if($merged_para_row !== false) {
				  $merged_PL = $merged_para_row['PL'];
				  $DB->MarkPLType($merged_PL,TYPE_MERGE);
				  $DB->ResetStoryOrder($SG);
			  }
		  }
		}

		// edit img
		if(!empty($_FILES['img_file']['name']) || !empty($_POST['img_content'])) {
			if(!empty($_FILES['img_file']['name'])) {
				$img_file = time()."_".preg_replace('/[^\da-z_\(\).]+/i', '_', trim(basename($_FILES['img_file']['name'])));
				$content = IMG_LIBRARY_DIR.$img_file;
				@move_uploaded_file($_FILES['img_file']['tmp_name'], $content);
			} else {
				$content = $_POST['img_content'];
			}
			$IM = new ImageManager();
			$IM->ReplaceImage($_SESSION['userID'],$sourceLangID,$content,$artworkID,$_POST['box_id']);
		}
		// clean up other changes made may make this optional
		$DB->CleanupBox($_POST['box_id']);
		$DB->AddChangedItem($artworkID,$_POST['box_id']);
		$DB->LastUpdateArtwork($artworkID);
		$redirect = "index.php?layout=$layout&id=$artworkID&page=$page";
		if(!empty($_POST['auto_refresh'])) $redirect .= "&do=refresh";
		header("Location: $redirect");
		exit;
	}
	
	if($_POST['form']=="restore") {
		$DB->RestoreBoxChange($_POST['box_id']);
		$DB->RestoreBoxMoves($artworkID,$_POST['box_id']);
		$IM = new ImageManager();
		$IM->RestoreImage($artworkID,$_POST['box_id']);
		$Translator = new Translator();
		$PLs = $DB->GetPLsByBox($_POST['box_id']);
		foreach($PLs as $PL) {
			$DB->RestoreStoryOrder($PL);
			$source_para_row = $Translator->GetParaByPL($PL,false);
			if($source_para_row === false) continue;
			$source_para_id = $source_para_row['ParaID'];
			$source_para = $source_para_row['ParaText'];
			$amended_para_row = $Translator->GetAmendedPara($PL);
			if($amended_para_row === false) {
				$Translator->AmendPara($PL,$source_para_id,$_SESSION['userID']);
			} else {
				$amended_para_id = $amended_para_row['ParaID'];
				if($amended_para_id != $source_para_id) {
					$Translator->AmendPara($PL,$source_para_id,$_SESSION['userID']);
				}
			}
		}
		$DB->AddChangedItem($artworkID,$_POST['box_id']);
		$DB->LastUpdateArtwork($artworkID);
		$redirect = "index.php?layout=$layout&id=$artworkID&page=$page";
		if(!empty($_POST['auto_refresh'])) $redirect .= "&do=refresh";
		header("Location: $redirect");
		exit;
	}
}

if(isset($_GET['do'])) {
	if($_GET['do']=="refresh") {
		$refresh_page = empty($_GET['all']) ? $page : 0;
		require_once(CLASSES."services.php");
		$Service = new EngineService($artworkID);
		if(!$Service->IsServerRunning(10)) server_busy();
		$rebuild = $Service->RebuildFile($artworkID,0,$refresh_page,ROOT.PREVIEW_DIR,"JPG");
		if($rebuild === false) error_creating_file();
		$Service->CheckOverflow($artworkID);
		$DB->RebuildPageThumbnail(PREVIEW_DIR,$artworkID,$refresh_page);
		header("Location: index.php?layout=$layout&id=$artworkID&page=$page");
		exit;
	}
}

if (isset($_POST['update'])) {
	if($_POST['update']=="exForm") {
		// check credit
		$credits_ask = $DB->get_credit_config($_SESSION['packageID'],$_POST['service_tID']);
		if($credits_ask > $credits_available) no_credit_available();
		$transaction = $DB->get_service_process_transaction($_POST['service_tID']);
		if($transaction === false) error_creating_file();
		
		require_once('download.php');
		$file = GetExportFile($artworkID,0,$_POST['service_tID']);
		if($file === false) error_creating_file();

		// send file as attachment to recipients
		if(!empty($_POST['emails'])) {
			$DB->send_exported_file($_SESSION['username'],$_POST['emails'],$_POST['brief'],ROOT.TMP_DIR.$file);
		}

		$DB->log_credit_transaction($_SESSION['companyID'],$_SESSION['userID'],$campaignID,$artworkID,0,$transaction['notes'],$credits_ask);
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
		$import_id = $ProcessEngine->import($artworkID,0,$_FILES['ImportFile']['tmp_name'],$option,$CS);
		if($import_id === false){
			$filename =(!empty($_FILES['ImportFile']['name']))?$_FILES['ImportFile']['name']:"[Empty]";
			$DB->LogSystemEvent($_SESSION['userID'],"failed to import '$filename'",$campaignID,$artworkID,0);
			header("Location: index.php?layout=system&id=11");
			exit;
		}
		if($Service->IsServerRunning(10)) $Service->RebuildFile($artworkID,0,0,ROOT.PREVIEW_DIR,"JPG");
		$DB->log_credit_transaction($_SESSION['companyID'],$_SESSION['userID'],$campaignID,$artworkID,0,$transaction['notes'],$credits_ask);
		$DB->LastUpdateArtwork($artworkID);
		header("Location: index.php?layout=task_import&id=$import_id");
		exit;
	}

	if ($_POST['update']=="saveasForm") {
		require_once('download.php');
		$service_process_id = $DB->get_service_process_id($artwork_row['artworkType'],SERVICE_DOWNLOAD,TYPE_PREWORK);
		if($service_process_id === false) error_creating_file();
		$File = GetDownloadFile($artworkID,0,$service_process_id,0,false);
		if($File === false) error_creating_file();
		//upload file
		$File = ROOT.TMP_DIR.basename($File);
		$FileName = time()."_".RestrictName(BareFilename(basename($File)));
		$query = sprintf("SELECT ext FROM service_engines WHERE id = %d LIMIT 1", $artwork_row['artworkType']);
		$result = mysql_query($query, $conn) or die(mysql_error());
		$row = mysql_fetch_assoc($result);
		$FileName .= ".".$row['ext'];
		require_once(CLASSES."services.php");
		$Service = new EngineService($artwork_row['artworkType'],true);
		if(!$Service->IsServerRunning(10)) server_busy();
		$Storage = $Service->GetStorage();
		$DestFile = $Storage.$FileName;
		$moveFile = rename($File, $DestFile);
		if($moveFile === false) error_creating_file();
		$ArtworkName = RestrictName($_POST['ArtworkName']);
		$extra = array(
			"campaignID" => $_POST['CampaignID'],
			"artworkName" => $ArtworkName,
			"subjectID" => $artwork_row['subjectID'],
			"artworkType" => $artwork_row['artworkType'],
			"parse_type" => $artwork_row['parse_type'],
			"uploaderID" => $_SESSION['userID'],
			"parent" => 0,
			"version" => $_POST['Version'],
			"live" => 1,
			"default_sub_font_id" => $artwork_row['default_sub_font_id'],
			"default_img_dir" => $artwork_row['default_img_dir']
		);

		// check credit
		$service_process_id = $DB->get_service_process_id($artwork_row['artworkType'],SERVICE_UPLOAD,TYPE_ORIGINAL);
		if($service_process_id === false) error_creating_file();
		$credits_ask = $DB->get_credit_config($_SESSION['packageID'],$service_process_id);
		if($credits_ask > $credits_available) no_credit_available();

		if(!$Service->isValidFile($FileName)) error_creating_file();
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
		if(!empty($_POST['clear'])) {
			$DB->ClearPrework($artworkID);
			$rebuild = $Service->RebuildFile($artworkID,0,0,ROOT.PREVIEW_DIR,"JPG");
			$Service->CheckOverflow($artworkID);
			$DB->RebuildPageThumbnail(PREVIEW_DIR,$artworkID,0);
		}

		$DB->log_credit_transaction($_SESSION['companyID'],$_SESSION['userID'],$_POST['CampaignID'],$aID,0,'Save as',$DB->get_credit_config($_SESSION['packageID'],$service_process_id));
		$DB->LastUpdateCampaign($_POST['CampaignID']);
		header("Location: index.php?layout=campaign&id={$_POST['CampaignID']}");
		exit;
	}
}
?>