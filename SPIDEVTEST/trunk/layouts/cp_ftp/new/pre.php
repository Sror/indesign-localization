<?php
$access = array("system","cpanel");
require_once(MODULES.'mod_authorise.php');

if(!empty($_POST['form'])) {
	
	if($_POST['form']=="cancel") {
		header("Location: index.php?layout=$layout");
	}
	
	if($_POST["form"]=="save" || $_POST["form"]=="apply") {
		
		$ftp_pasv = empty($_POST['ftp_pasv']) ? 0 : $_POST['ftp_pasv'];
		$public = empty($_POST['public']) ? 0 : $_POST['public'];
		
		$update = sprintf("INSERT INTO ftps
							(company_id, ftp_host, ftp_memo, ftp_username, ftp_password, ftp_port, ftp_pasv, ftp_timeout, ftp_dir, public)
							VALUES (%d, '%s', '%s', '%s', '%s', %d, %d, %d, '%s', %d)",
							$_POST['company_id'],
							mysql_real_escape_string($_POST['ftp_host']),
							mysql_real_escape_string($_POST['ftp_memo']),
							mysql_real_escape_string($_POST['ftp_username']),
							mysql_real_escape_string($_POST['ftp_password']),
							$_POST['ftp_port'],
							$ftp_pasv,
							$_POST['ftp_timeout'],
							mysql_real_escape_string($_POST['ftp_dir']),
                            $public);
		$result = mysql_query($update, $conn) or die(mysql_error());
		$newID = mysql_insert_id($conn);
		
		$DB->LogSystemEvent($_SESSION['userID'],"created a new ftp: {$_POST['ftp_host']}");
		
		switch($_POST["form"]) {
			case "apply":	header("Location: index.php?layout=$layout&task=edit&id=$newID");
							break;
			case "save":	header("Location: index.php?layout=$layout");
							break;
			default:		header("Location: index.php?layout=$layout");
		}
	}
	exit();
}
?>