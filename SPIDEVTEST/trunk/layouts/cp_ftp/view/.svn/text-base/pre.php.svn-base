<?php
$access = array("system","cpanel");
require_once(MODULES.'mod_authorise.php');
require_once(CLASSES.'FTP_Sync.php');

$ftp_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$ftp_query = sprintf("SELECT ftps.*,
						companies.systemName as system_name
						FROM ftps
						LEFT JOIN companies ON ftps.company_id = companies.companyID
						WHERE ftps.id = %d
						LIMIT 1",
						$ftp_id);
$ftp_result = mysql_query($ftp_query, $conn) or die(mysql_error());
if(!mysql_num_rows($ftp_result)) access_denied();
$ftp_row = mysql_fetch_assoc($ftp_result);

$ftp_sync = new FTP_Sync($ftp_row['ftp_host'],$ftp_row['ftp_username'],$ftp_row['ftp_password']);
$local_path_to_ftp = ROOT.FTP_DIR.$ftp_row['system_name'];

if(empty($_SESSION['ftp_type'])) {
	$_SESSION['ftp_type'] = $ftp_sync->get_ftp_type();
}
//rebuild cache for client ftp if needed
$local_ftp_dir = $ftp_sync->format_ftp_dir('/');
if(!$ftp_sync->is_local_ftp_cache_usable($_SESSION['companyID'],$local_ftp_dir)) {
	$ftp_sync->rebuild_local_ftp_cache($_SESSION['companyID'],$local_ftp_dir,$ftp_sync->local_list_dir_contents($local_path_to_ftp.$local_ftp_dir));
}
//rebuild cache for remote ftp if needed
$remote_ftp_dir = $ftp_sync->format_ftp_dir($ftp_row['ftp_dir']);
if(!$ftp_sync->is_remote_ftp_cache_usable($ftp_id,$remote_ftp_dir)) {
	$ftp_sync->rebuild_remote_ftp_cache($ftp_id,$remote_ftp_dir,$ftp_sync->ftp_list_dir_contents($remote_ftp_dir));
}
if($_POST['ftp']=="local") {
	switch($_POST['form']) {
		case "new":
			header("Location: index.php?layout=$layout&task=new");
			break;
		case "download":
			$zip = basename($ftp_sync->download_local_ftp_items($_POST['id']));
			header("Location: download.php?File=$zip&SaveAs=$zip&temp");
			break;
		case "rename":
			$id = $_POST['id'][0];
			header("Location: index.php?layout=$layout&task=edit&id=$id");
			break;
	}
	exit();
}

if($_POST['ftp']=="remote") {
	switch($_POST['form']) {
		case "new":
			header("Location: index.php?layout=$layout&task=new");
			break;
		case "download":
			$zip = basename($ftp_sync->download_remote_ftp_items($_POST['id']));
			header("Location: download.php?File=$zip&SaveAs=$zip&temp");
			break;
		case "rename":
			$id = $_POST['id'][0];
			header("Location: index.php?layout=$layout&task=edit&id=$id");
			break;
	}
	exit();
}
?>