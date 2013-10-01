<?php
if (isset($_GET['redirect'])) {
	$_SESSION['PrevUrl'] = $_GET['redirect'];
}
if (isset($_POST['username']) && isset($_POST['password'])) {
	$query = sprintf("SELECT users.userID, users.username, users.password,
					users.userGroupID, users.companyID, users.defaultLangID, 
					companies.packageID,
					language_options.acronym
					FROM users
					LEFT JOIN companies ON companies.companyID = users.companyID
					LEFT JOIN language_options ON users.langID = language_options.id
					WHERE username='%s'
					AND password='%s'
					AND active=1
					LIMIT 1",
					mysql_real_escape_string($_POST['username']), mysql_real_escape_string($_POST['password'])); 
	$result = mysql_query($query, $conn) or die(mysql_error());
	if(mysql_num_rows($result)) {
		$row = mysql_fetch_assoc($result);
		$_SESSION['userID'] = $row['userID'];
		$_SESSION['username'] = $row['username'];
		$_SESSION['ugID'] = $row['userGroupID'];
		$_SESSION['companyID'] = $row['companyID'];
		$_SESSION['packageID'] = $row['packageID'];
		$_SESSION['userDefaultLangID'] = $row['defaultLangID'];
		$_SESSION['lang'] = $row['acronym'];
		#setcookie("companyID", $row['companyID'], time()+60*60*24*7);
		unset($_SESSION['token']);
		header("Location: index.php?layout=system&id=1");
	} else {
		$DB->LogSystemEvent(null,"attempted to login with {$_POST['username']} from {$_SERVER['REMOTE_ADDR']}");
		header("Location: index.php?layout=system&id=5");
	}
	exit();
}

if(isset($_GET['do']) && ($_GET['do']=="logout")) {
	$DB->LogSystemEvent($_SESSION['userID'],"logged out");
	$update = sprintf("UPDATE users SET lastActive = null WHERE userID = %d", $_SESSION['userID']);
	$result = mysql_query($update,$conn) or die(mysql_error());
	
	unset($_SESSION['username']);
	unset($_SESSION['userID']);
	unset($_SESSION['ugID']);
	unset($_SESSION['companyID']);
	unset($_SESSION['packageID']);
	unset($_SESSION['PrevUrl']);
	unset($_SESSION['view']);
	unset($_SESSION['ftp_type']);
	unset($_SESSION['campaign_status']);
	unset($_SESSION['artwork_status']);
	unset($_SESSION['task_status']);
	unset($_SESSION['filter_view']);
	unset($_SESSION['filter_type']);
	unset($_SESSION['filter_lang']);
	unset($_SESSION['filter_brand']);
	unset($_SESSION['filter_import']);
	unset($_SESSION['filter_company']);
	unset($_SESSION['filter_engine']);
	unset($_SESSION['filter_page']);
	unset($_SESSION['filter_layer']);
	unset($_SESSION['filter_box']);
	unset($_SESSION['filter_folder']);
	unset($_SESSION['filter_tm_type']);
	unset($_SESSION['filter_tm_lang']);
	unset($_SESSION['TmpFile']);
	unset($_SESSION['zoom']);
	unset($_SESSION['task_search_type']);
	unset($_SESSION['task_search_keyword']);
	unset($_SESSION['tm_check_option']);
	unset($_SESSION['wc_check_option']);
	unset($_SESSION['ignore_check_option']);
	unset($_SESSION['toggle']);
	unset($_SESSION['show_pages']);
	unset($_SESSION['userDefaultLangID']);
	session_destroy();
	header("Location: index.php?layout=system&id=2");
	exit;
}
?>