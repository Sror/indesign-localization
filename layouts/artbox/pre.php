<?php
$access = array("artworks","edit");
require_once(MODULES.'mod_authorise.php');

$artworkID = isset($_GET['id'])?$_GET['id']:0;
$artwork_query = sprintf("SELECT pages.PreviewFile,
						artworks.campaignID, artworks.artworkName, artworks.fileName,
						campaigns.campaignName
						FROM pages
						LEFT JOIN artworks ON pages.ArtworkID = artworks.artworkID
						LEFT JOIN campaigns ON artworks.campaignID = campaigns.campaignID
						WHERE pages.ArtworkID = %d
						AND pages.Page = 1
						LIMIT 1",
						$artworkID);
$artwork_result = mysql_query($artwork_query, $conn) or die(mysql_error());
if(!mysql_num_rows($artwork_result)) access_denied();
$artwork_row = mysql_fetch_assoc($artwork_result);
$campaignID = $artwork_row['campaignID'];
if(!$DB->check_campaign_acl($campaignID,$_SESSION['companyID'],$_SESSION['userID'])) access_denied();

if(!empty($_POST['form'])) {
	if($_POST['form']=="close") {
		header("Location: index.php?layout=artwork&id=$artworkID");
		exit;
	} else {
		if($_POST['form']=="apply" || $_POST['form']=="save") {
			foreach($_POST['id'] as $id) {
				$DB->SaveBoxConfigs($id,(int)$_POST['orderno'][$id],$_POST['dynamic'][$id],$_POST['heading'][$id]);
				$DB->SaveBoxProperties($artworkID,$id,0,$_POST['lock'][$id],$_POST['resize'][$id]);
				$DB->SaveBoxMoves($artworkID,$id,0,(int)$_POST['left'][$id],(int)$_POST['left'][$id]+(int)$_POST['width'][$id],(int)$_POST['top'][$id],(int)$_POST['top'][$id]+(int)$_POST['height'][$id],(int)$_POST['angle'][$id]);
			}
		}
		
		if($_POST['form']=="restore") {
			foreach($_POST['id'] as $id) {
				$DB->RestoreBoxMoves($artworkID,$id);
			}
		}

		if($_POST['form']=="refresh") {
			//rebuild page previews
			require_once(CLASSES."services.php");
			$Service = new EngineService($artworkID);
			$IsServerRunning = $Service->IsServerRunning(10);
			if(!$IsServerRunning) server_busy();
			$rebuild = $Service->RebuildFile($artworkID,0,0,ROOT.PREVIEW_DIR,"JPG");
			if($rebuild === false) error_creating_file();
			$DB->RebuildBoxPreview($artworkID);
			$Service->CheckOverflow($artworkID);
		}
		
		if($_POST['form']=="save") {
			header("Location: index.php?layout=artwork&id=$artworkID");
			exit;
		} else {
			header("Location: index.php?layout=artbox&id=$artworkID");
			exit;
		}
	}
}

$by = isset($_POST['by'])?$_POST['by']:"order";
$order = isset($_POST['order'])?$_POST['order']:"ASC";
$keyword = isset($_POST['keyword']) ? $_POST['keyword'] : "";
$pre = ($order=="ASC")?"DESC":"ASC";

if(isset($_POST['filter_layer'])) $_SESSION['filter_layer'] = $_POST['filter_layer'];
$layer_id = (isset($_POST['filter_layer'])) ? $_POST['filter_layer'] : (isset($_SESSION['filter_layer']) ? $_SESSION['filter_layer'] : 0);
$layer_id = $DB->reset_layer($artworkID,$layer_id);
if(isset($_POST['filter_box'])) $_SESSION['filter_box'] = $_POST['filter_box'];
$box_type = (isset($_POST['filter_box'])) ? $_POST['filter_box'] : (isset($_SESSION['filter_box']) ? $_SESSION['filter_box'] : 'TEXT');

$sub = "";
$sub .= !empty($layer_id) ? " AND boxes.LayerID = $layer_id" : "";
$sub .= !empty($box_type) ? sprintf(" AND boxes.Type = '%s'",mysql_real_escape_string($box_type)) : "";
$query = sprintf("SELECT boxes.uID
					FROM boxes
					LEFT JOIN pages ON boxes.PageID = pages.uID
					LEFT JOIN artworks ON pages.ArtworkID = artworks.artworkID
					WHERE artworks.artworkID = %d
					%s
					AND boxes.uID LIKE '%s'",
					$artworkID,
					$sub,
					"%".mysql_real_escape_string($keyword)."%");
$result = mysql_query($query, $conn) or die(mysql_error());
$total = mysql_num_rows($result);

$limit = isset($_POST['limit'])?(int)$_POST['limit']:RPP;
$pages = (ceil($total/$limit)==0)?1:ceil($total/$limit);
$page = isset($_POST['page'])?(int)$_POST['page']:1;
$offset = $limit*($page-1);
?>