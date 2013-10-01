<?php
$access = array("artworks","edit");
require_once(MODULES.'mod_authorise.php');
require_once(CLASSES.'CSVImporter.php');

$artworkID = isset($_GET['id']) ? $_GET['id'] : 0;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
if(isset($_POST['filter_page'])) $_SESSION['filter_page'] = $_POST['filter_page'];
$page_id = (isset($_POST['filter_page'])) ? $_POST['filter_page'] : (isset($_SESSION['filter_page']) ? $_SESSION['filter_page'] : 0);
$page_id = $DB->reset_page($artworkID,$page_id);
if(isset($_POST['filter_layer'])) $_SESSION['filter_layer'] = $_POST['filter_layer'];
$layer_id = (isset($_POST['filter_layer'])) ? $_POST['filter_layer'] : (isset($_SESSION['filter_layer']) ? $_SESSION['filter_layer'] : 0);
$layer_id = $DB->reset_layer($artworkID,$layer_id);
if(isset($_POST['filter_box'])) $_SESSION['filter_box'] = $_POST['filter_box'];
$box_type = (isset($_POST['filter_box'])) ? $_POST['filter_box'] : (isset($_SESSION['filter_box']) ? $_SESSION['filter_box'] : 'TEXT');

$query_artwork = sprintf("SELECT *, artworks.version AS version,
							service_engines.name AS serviceName, service_engines.ext AS serviceExt,
							pages.uID AS pageID, pages.PageScale
							FROM artworks
							LEFT JOIN service_engines ON artworks.artworkType = service_engines.id
							LEFT JOIN subjects ON artworks.subjectID = subjects.subjectID
							LEFT JOIN pages ON (artworks.artworkID = pages.ArtworkID AND pages.Page = %d)
							LEFT JOIN campaigns ON artworks.campaignID = campaigns.campaignID
							LEFT JOIN brands ON campaigns.brandID = brands.brandID
							LEFT JOIN languages ON campaigns.sourceLanguageID = languages.languageID
							WHERE artworks.artworkID = %d",
							$page,
							$artworkID);
$result_artwork = mysql_query($query_artwork, $conn) or die(mysql_error());
if(!mysql_num_rows($result_artwork)) access_denied();
$row_artwork = mysql_fetch_assoc($result_artwork);
$campaignID = $row_artwork['campaignID'];
$artworkName = $row_artwork['artworkName'];
$pages = $row_artwork['pageCount'];
if($page<0 || $page>$pages)  access_denied();
if(!$DB->check_campaign_acl($campaignID,$_SESSION['companyID'],$_SESSION['userID'])) access_denied();
$previewFile = $row_artwork['PreviewFile'];
$thumbnail = POSTVIEW_DIR.$previewFile;
//check if preview has been updated
if(!file_exists(ROOT.$thumbnail)) {
	@copy(ROOT.PREVIEW_DIR.$previewFile,ROOT.$thumbnail);
}
list($img_width,$img_height) = @getimagesize(ROOT.$thumbnail);

if(!empty($_POST['form'])) {
	if($_POST['form'] == "close") {
		header("Location: index.php?layout=artwork&id=$artworkID");
		exit;
	}

	if($_POST['form'] == "refresh") {
		@copy(ROOT.PREVIEW_DIR.$previewFile,ROOT.$thumbnail);
		$DB->RebuildPageThumbnail(POSTVIEW_DIR,$artworkID,$page);
		header("Location: index.php?layout=$layout&id=$artworkID&page=$page");
		exit;
	}
	
	if($_POST['form'] == "map") {
		foreach($_POST['import_map_id'] as $k=>$v) {
			$query = sprintf("SELECT import_map_id
							FROM import_map_para
							WHERE pl_id = %d
							AND artwork_id = %d",
							$k,
							$artworkID);
			$result = mysql_query($query, $conn) or die(mysql_error());
			if(mysql_num_rows($result)) {
				if(empty($v)) {
					$query = sprintf("DELETE FROM import_map_para
									WHERE pl_id = %d
									AND artwork_id = %d",
									$k,
									$artworkID);
					$result = mysql_query($query, $conn) or die(mysql_error());
				} else {
					$query = sprintf("UPDATE import_map_para SET
									import_map_id = %d
									WHERE pl_id = %d
									AND artwork_id = %d",
									$v,
									$k,
									$artworkID);
					$result = mysql_query($query, $conn) or die(mysql_error());
				}
			} else {
				if(!empty($v)) {
					$query = sprintf("INSERT INTO import_map_para SET
										import_map_id = %d,
										pl_id = %d,
										artwork_id = %d",
										$v,
										$k,
										$artworkID);
					$result = mysql_query($query, $conn) or die(mysql_error());
				}
			}
		}
		header("Location: index.php?layout=$layout&id=$artworkID&page=$page");
		exit;
	}
	
	if($_POST['form'] == "import") {
		$query = sprintf("INSERT INTO imports SET
						name = '%s',
						time = NOW(),
						user_id = %d",
						mysql_real_escape_string($_POST['name']),
						$_SESSION['userID']);
		$result = mysql_query($query, $conn) or die(mysql_error());
		$id = mysql_insert_id($conn);
		$importer = new CSVImporter($_FILES['csvfile']['tmp_name']);
		$importer->import_csv($id);
		$DB->LogSystemEvent($_SESSION['userID'],"imported CSV file to template-based function [$id]",0,$artworkID);
		header("Location: index.php?layout=$layout&id=$artworkID&page=$page");
		exit;
	}
	
	if($_POST['form']=="preview") {
		require_once(CLASSES."services.php");
		$Service = new EngineService($artworkID);
		if(!$Service->IsServerRunning(10)) server_busy();
		$id = $_POST['id'][0];
		$Service->RebuildFileTemp($artworkID,$id,0,ROOT.POSTVIEW_DIR,"JPG");
		header("Location: index.php?layout=$layout&id=$artworkID&page=$page");
		exit;
	}
	
	if($_POST['form']=="download") {
		// check credit
		$credits_ask = $DB->get_credit_config($_SESSION['packageID'],$_POST['service_tID']);
		if($credits_ask > $credits_available) no_credit_available();
		$transaction = $DB->get_service_process_transaction($_POST['service_tID']);
		if($transaction === false) error_creating_file();

		require_once('download.php');
		$zip = new ZipArchive();
		$filename = ROOT.TMP_DIR.time().".zip";
		if ($zip->open($filename,ZIPARCHIVE::CREATE)===TRUE) {
			$files = array();
			foreach($_POST['id'] as $id) {
				$PDFOption = (!empty($_POST['PDFOption']))?$_POST['PDFOption']:null;
				$x = GetDownloadFile($artworkID,0,$_POST['service_tID'],$id,true,$PDFOption);
				$File = basename($x);
				$files[] = $x;
				$zip->addFile(ROOT.TMP_DIR.$File,$File);
			}
			$zip->close();
			foreach($files as $File) {
				@unlink($File);
			}
		}
		$DB->log_credit_transaction($_SESSION['companyID'],$_SESSION['userID'],$campaignID,$artworkID,0,$transaction['notes'],$credits_ask);
		$link = basename($filename);
		header("Location: download.php?File=$link&SaveAs=$link&temp&bin");
		exit;
	}
}