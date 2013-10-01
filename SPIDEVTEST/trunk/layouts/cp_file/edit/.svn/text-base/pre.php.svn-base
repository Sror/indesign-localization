<?php
$access = array("system","cpanel");
require_once(MODULES.'mod_authorise.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$ftp_query = sprintf("SELECT ftps.*
						FROM ftps
						LEFT JOIN companies ON ftps.company_id = companies.companyID
						WHERE ftps.id = %d
						LIMIT 1",
						$id);
$ftp_result = mysql_query($ftp_query, $conn) or die(mysql_error());
$ftp_found = mysql_num_rows($ftp_result);
if($ftp_found) {
	$ftp_row = mysql_fetch_assoc($ftp_result);
} else {
	access_denied();
}

if(!empty($_POST['form'])) {
	
	if($_POST['form']=="close") {
		header("Location: index.php?layout=$layout&task=manage");
	}
	
	if( $_POST["form"]=="save" || $_POST["form"]=="apply" ) {
		
		$agency = empty($_POST['agency']) ? 0 : $_POST['agency'];
		$update = sprintf("UPDATE ftps SET
		  					company_id = %d,
		  					ftp_host = '%s',
		  					ftp_username = '%s',
		  					ftp_password = '%s',
		  					ftp_port = %d,
		  					ftp_pasv = %d,
		  					ftp_timeout = %d,
		  					ftp_dir = '%s'
		  					WHERE id = %d",
							$_POST['company_id'],
							mysql_real_escape_string($_POST['ftp_host']),
							mysql_real_escape_string($_POST['ftp_username']),
							mysql_real_escape_string($_POST['ftp_password']),
							$_POST['ftp_port'],
							$_POST['ftp_pasv'],
							$_POST['ftp_timeout'],
							mysql_real_escape_string($_POST['ftp_dir']),
							$id);
		$result = mysql_query($update, $conn) or die(mysql_error());
		$DB->LogSystemEvent($_SESSION['userID'],"edited ftp: {$_POST['ftp_host']}");
		
		switch($_POST["form"]) {
			case "apply":	header("Location: index.php?layout=$layout&task=$task&id=$id");
							break;
			case "save":	header("Location: index.php?layout=$layout");
							break;
			default:		header("Location: index.php?layout=$layout");
		}
	}
	exit();
}
?>