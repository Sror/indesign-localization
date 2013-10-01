<?php
$access = array("system","cpanel");
require_once(MODULES.'mod_authorise.php');
require_once(CLASSES.'FTP_Sync.php');

$system_name = $DB->get_system_name($_SESSION['companyID']);
if($system_name === false) access_denied();
$dir = !empty($_GET['dir']) ? urldecode($_GET['dir']) : "/";
/*
 * Local FTP
 */
$ftp_local = new FTP_Local();
$local_path_to_ftp = ROOT.FTP_DIR.$system_name;
//rebuild cache for client ftp if needed
$local_ftp_dir = $ftp_local->format_ftp_dir($dir);
if(!$ftp_local->is_local_ftp_cache_usable($_SESSION['companyID'],$local_ftp_dir)) {
	$ftp_local->rebuild_local_ftp_cache($_SESSION['companyID'],$local_ftp_dir,$ftp_local->local_list_dir_contents($local_path_to_ftp.$local_ftp_dir));
}

/*
 * Remote FTP
 */
if(!empty($_GET['id'])) {
	$ftp_id = (int)$_GET['id'];
	$_SESSION['ftp_id'] = $ftp_id;
} else {
	if(!empty($_SESSION['ftp_id'])) {
		$ftp_id = $_SESSION['ftp_id'];
	} else {
		$ftp_id = 0;
	}
}
if(!empty($ftp_id)) {
	$ftp_query = sprintf("SELECT *
						FROM ftps
						WHERE id = %d
						LIMIT 1",
						$ftp_id);
	$ftp_result = mysql_query($ftp_query, $conn) or die(mysql_error());
	if(!mysql_num_rows($ftp_result)) access_denied();
	$ftp_row = mysql_fetch_assoc($ftp_result);
	try {
        $ftp_sync = new FTP_Sync($ftp_row['ftp_host'],$ftp_row['ftp_username'],$ftp_row['ftp_password']);
    } catch(Exception $e) {
		$_SESSION['ftp_id'] = null;
		unset($_SESSION['ftp_id']);
		header("Location: index.php?layout=$layout&error=1");
		exit();
	}

    if($ftp_sync === false) {
        $_SESSION['ftp_id'] = null;
		unset($_SESSION['ftp_id']);
		header("Location: index.php?layout=$layout");
		exit();
    }
	if(empty($_SESSION['ftp_type'])) {
		$_SESSION['ftp_type'] = $ftp_sync->get_ftp_type();
	}
	//rebuild cache for remote ftp if needed
	$remote_ftp_dir = $ftp_sync->format_ftp_dir($ftp_row['ftp_dir']);
	if(!$ftp_sync->is_remote_ftp_cache_usable($ftp_id,$remote_ftp_dir)) {
		$ftp_sync->rebuild_remote_ftp_cache($ftp_id,$remote_ftp_dir,$ftp_sync->ftp_list_dir_contents($remote_ftp_dir));
	}
}

if($_POST['ftp']=="local") {
	if($_POST['form'] == "upload") {
		foreach($_FILES['upload_file']['name'] as $k=>$name) {
			if(empty($name)) continue;
			@move_uploaded_file($_FILES['upload_file']['tmp_name'][$k], $local_path_to_ftp.$_POST['dir'].$name);
		}
		$ftp_local->rebuild_local_ftp_cache($_SESSION['companyID'],$_POST['dir'],$ftp_local->local_list_dir_contents($local_path_to_ftp.$_POST['dir']));
		header("Location: index.php?layout=$layout&dir=".urlencode($_POST['dir']));
		exit();
	}
	if($_POST['form'] == "download") {
		$zip = basename($ftp_local->download_local_ftp_items($_POST['id']));
		header("Location: download.php?File=$zip&SaveAs=$zip&temp");
		exit();
	}
}

if($_POST['ftp']=="remote") {
	if($_POST['form'] == "download") {
		$zip = basename($ftp_sync->download_remote_ftp_items($_POST['id']));
		header("Location: download.php?File=$zip&SaveAs=$zip&temp");
		exit();
	}
	if($_POST['form'] == "disconnect") {
		$_SESSION['ftp_id'] = null;
		unset($_SESSION['ftp_id']);
		header("Location: index.php?layout=$layout");
		exit();
	}
}