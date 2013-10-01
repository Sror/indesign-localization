<?php
$access = array("system","login");
require_once(MODULES.'mod_authorise.php');

$taskID = (isset($_GET['id'])) ? $_GET['id'] : 0;
$page = (isset($_GET['page'])) ? $_GET['page'] : 1;
$pl = (isset($_GET['pl'])) ? $_GET['pl'] : 0;

if(isset($_POST['task_search_type'])) $_SESSION['task_search_type'] = $_POST['task_search_type'];
$task_search_type = (isset($_POST['task_search_type'])) ? $_POST['task_search_type'] : (isset($_SESSION['task_search_type']) ? $_SESSION['task_search_type'] : TYPE_ORIGINAL);
if(isset($_POST['task_search_keyword'])) $_SESSION['task_search_keyword'] = $_POST['task_search_keyword'];
$task_search_keyword = (isset($_POST['task_search_keyword'])) ? $_POST['task_search_keyword'] : (isset($_SESSION['task_search_keyword']) ? $_SESSION['task_search_keyword'] : "");
	
$query_task = sprintf("SELECT *, tasks.artworkID AS aID, pages.uID AS pageID,
					artworks.subjectID,
					U1.userID AS cuid, U1.forename AS cforename, U1.surname AS csurname, U1.email AS cemail,
					U2.userID AS tuid, U2.forename AS tforename, U2.surname AS tsurname, U2.email AS temail,
					pages.PageScale
					FROM tasks
					LEFT JOIN artworks ON tasks.artworkID = artworks.artworkID
					LEFT JOIN subjects ON artworks.subjectID = subjects.subjectID
					LEFT JOIN pages ON (artworks.artworkID = pages.ArtworkID AND pages.Page = %d)
					LEFT JOIN campaigns ON artworks.campaignID = campaigns.campaignID
					LEFT JOIN brands ON campaigns.brandID = brands.brandID
					LEFT JOIN languages ON campaigns.sourceLanguageID = languages.languageID
					LEFT JOIN users U1 ON tasks.creatorID = U1.userID
					LEFT JOIN users U2 ON tasks.translatorID = U2.userID
					WHERE tasks.taskID = %d
					ORDER BY pages.Page ASC, pages.PageRef ASC
					LIMIT 1",
					$page,
					$taskID);
$result_task = mysql_query($query_task, $conn) or die(mysql_error());
if(!mysql_num_rows($result_task)) access_denied();
$row_task = mysql_fetch_assoc($result_task);
if($page<0 || $page>$row_task['pageCount'])  access_denied();
#if(!$DB->check_campaign_acl($row_task['campaignID'],$_SESSION['companyID'],$_SESSION['userID'])) access_denied();
#if(!$issuperadmin && !in_array($_SESSION['userID'],$DB->get_task_acl_user_ids($taskID))) access_denied();
if(!($acl->acl_check("artworks","viewtasks",$_SESSION['companyID'],$_SESSION['userID']) && $DB->check_campaign_acl($row_task['campaignID'],$_SESSION['companyID'],$_SESSION['userID'])) && !in_array($_SESSION['userID'],$DB->get_task_acl_user_ids($taskID))) access_denied();
$artworkID = $row_task['aID'];
$PreviewFile = $row_task['PreviewFile'];
$PageScale = $row_task['PageScale'];

$query_desiredRs = sprintf("SELECT * FROM languages
							WHERE languageID = %d", $row_task['desiredLanguageID']);
$desiredRs = mysql_query($query_desiredRs, $conn) or die(mysql_error());
$row_desiredRs = mysql_fetch_assoc($desiredRs);

$trial = $DB->get_task_trial_status($taskID) ? " AND boxes.heading = 1" : "";
$query_fboxRs = sprintf("SELECT paralinks.BoxID
						FROM paralinks
						LEFT JOIN boxes ON boxes.uID = paralinks.BoxID
						LEFT JOIN pages ON boxes.PageID = pages.uID
						LEFT JOIN box_properties ON ( box_properties.box_id = boxes.uID AND box_properties.task_id IN (0,%d) )
						WHERE pages.ArtworkID = %d
						AND pages.Page = %d
						AND (box_properties.lock IS NULL OR box_properties.lock = 0)
						$trial
						GROUP BY paralinks.BoxID
						ORDER BY boxes.order ASC
						LIMIT 1",
						$taskID,
						$artworkID,
						$page);
$fboxRs = mysql_query($query_fboxRs, $conn) or die(mysql_error());
if(mysql_num_rows($fboxRs)) {
	$row_fboxRs = mysql_fetch_assoc($fboxRs);
	$boxID = $row_fboxRs['BoxID'];
} else {
	$boxID = 0;
}
//set boxID if passed
$boxID = (isset($_GET['box'])) ? $_GET['box'] : $boxID;
$view = (isset($_GET['view'])) ? $_GET['view'] : ((isset($_SESSION['view'])) ? $_SESSION['view'] : 0);
$_SESSION['view'] = $view;
$viewo = ($view==0) ? 1 : 0;
$mt = (isset($_GET['mt'])) ? $_GET['mt'] : ((isset($_SESSION['mt'])) ? $_SESSION['mt'] : 0);
$_SESSION['mt'] = $mt;
$mto = ($mt==0) ? 1 : 0;

if (isset($_POST['update']) && ($_POST['update']=="comment")) {
	if(!empty($_FILES['attachment']['name'])) {
		$attachment = time()."_".preg_replace('/[^\da-z_\(\).]+/i', '_', trim(basename($_FILES['attachment']['name'])));
		@move_uploaded_file($_FILES['attachment']['tmp_name'], REPOSITORY_DIR.$attachment);
	} else {
		$attachment = "";
	}
	$DB->AddComment($artworkID, $page, $_SESSION['userID'], $_POST['comment'], $attachment, $boxID, $taskID);
	header("Location: index.php?layout=$layout&id=$taskID&page=$page&box=$boxID&pl=$pl");
	exit;
}

if (isset($_POST['form'])) {
	if($_POST['form']=="close") {
		header("Location: index.php?layout=task&id=$taskID");
		exit;
	}

	if($_POST['form']=="refresh") {
		header("Location: index.php?layout=$layout&id=$taskID&page=$page&box=$boxID&do=refresh");
		exit;
	}

	if($_POST['form']=="refreshall") {
		header("Location: index.php?layout=$layout&id=$taskID&page=$page&box=$boxID&do=refresh&all=1");
		exit;
	}

	if($_POST['form']=="save") {
		$DB->SaveBoxProperties($artworkID,$boxID,$taskID,$_POST['lock'],$_POST['resize']);
		$DB->SaveBoxMoves($artworkID,$boxID,$taskID,(int)$_POST['left'],(int)$_POST['left']+(int)$_POST['boxwidth'],(int)$_POST['top'],(int)$_POST['top']+(int)$_POST['boxheight'],(int)$_POST['angle']);
		header("Location: index.php?layout=$layout&id=$taskID&page=$page&box=$boxID&do=refresh");
		exit;
	}

	if($_POST['form']=="restore") {
		$DB->RestoreBoxMoves($artworkID,$boxID,$taskID);
		header("Location: index.php?layout=$layout&id=$taskID&page=$page&box=$boxID&do=refresh");
		exit;
	}

	if($_POST['form']=="reset") {
		$task_search_type = TYPE_ORIGINAL;
		$_SESSION['task_search_type'] = TYPE_ORIGINAL;
		$task_search_keyword = "";
		$_SESSION['task_search_keyword'] = "";
	}
}

if (isset($_GET['do'])) {
	if($_GET['do']=="refresh") {
		require_once(CLASSES."translator.php");
		$Translator = new Translator();
		$Translator->CheckProgress($taskID);
		require_once(CLASSES."services.php");
		$Service = new EngineService($artworkID);
		$IsServerRunning = $Service->IsServerRunning(10);
		if(!$IsServerRunning) server_busy();
		$refresh_page = empty($_GET['all']) ? $page : 0;
		$rebuild = $Service->RebuildFile($artworkID,$taskID,$refresh_page,ROOT.POSTVIEW_DIR,"JPG",0);
		if($rebuild === false) error_creating_file('RebuildFile Faild');
		$Service->CheckOverflow($artworkID,$taskID);
		$DB->RebuildPageThumbnail(POSTVIEW_DIR,$artworkID,$refresh_page,$taskID);
		header("Location: index.php?layout=$layout&id=$taskID&page=$page&box=$boxID&pl=$pl");
		exit;
	}

	if ($_GET['do']=="rmcomment" && !empty($_GET['ref'])) {
		$DB->RemoveComment((int)$_GET['ref']);
		header("Location: index.php?layout=$layout&id=$taskID&page=$page&box=$boxID&pl=$pl");
		exit;
	}

	if ($_GET['do']=="attachment" && !empty($_GET['ref'])) {
		ForceDownload(REPOSITORY_DIR.$_GET['ref'], ROOT.TMP_DIR.$_GET['ref']);
		exit;
	}

}

$query_box = sprintf("SELECT boxes.Left, boxes.Right, boxes.Top, boxes.Bottom, boxes.Angle, boxes.order, boxes.BoxUID,
					box_properties.lock, box_properties.resize,
					box_overflows.overflow
					FROM boxes
					LEFT JOIN box_properties ON (box_properties.box_id = boxes.uID AND box_properties.task_id = %d)
					LEFT JOIN box_overflows ON (box_overflows.box_id = boxes.uID AND box_overflows.task_id = %d)
					WHERE boxes.uID = %d
					LIMIT 1",
					$taskID,
					$taskID,
					$boxID);
$result_box = mysql_query($query_box, $conn) or die(mysql_error());
if(mysql_num_rows($result_box)) {
	$row_box = mysql_fetch_assoc($result_box);
	$left = $row_box['Left'];
	$right = $row_box['Right'];
	$top = $row_box['Top'];
	$bottom = $row_box['Bottom'];
	$angle = $row_box['Angle'];
	$order = $row_box['order'];
	$box_uid = $row_box['BoxUID'];
	$lock = $row_box['lock'];
	$resize = $row_box['resize'];
	$overflow = $row_box['overflow'];
	//get updated geometry info
	$geo = $DB->GetBoxMoves($artworkID,$boxID,$taskID);
	if($geo) {
		$left = $geo['left'];
		$right = $geo['right'];
		$top = $geo['top'];
		$bottom = $geo['bottom'];
		$angle = $geo['angle'];
	}
} else {
	$order = 1;
}