<?php
$access = array("system","login");
require_once(MODULES.'mod_authorise.php');

if(isset($_GET['do']) && ($_GET['do']=="rmphoto")) {
	$query = sprintf("SELECT photo FROM users WHERE userID = %d", $_SESSION['userID']);
	$result = mysql_query($query, $conn) or die(mysql_error());
	$row = mysql_fetch_assoc($result);
	@unlink(ROOT.PHOTO_PATH.$row['photo']);
	$update = sprintf("UPDATE users SET photo='default.jpg' WHERE userID = %d", $_SESSION['userID']);
	$result = mysql_query($update, $conn) or die(mysql_error());
	header("Location: index.php?layout=profile");
	exit();
}

if (isset($_POST['form'])) {
	
	if ($_POST['form'] == "photo") {
		if(!empty($_FILES['photoFile']['name']) || !empty($_POST['delete'])) {
			if(!empty($_POST['delete'])) {
				$fileName = "default.jpg";
				$note = "removed the photo";
			} else if(!empty($_FILES['photoFile']['name'])) {
				if(!ValidateImage($_FILES['photoFile']['tmp_name'])) {
					header("Location: index.php?layout=system&id=11");
					exit;
				}
				$fileName = $_SESSION['username']."_".$_FILES['photoFile']['name'];
				move_uploaded_file($_FILES['photoFile']['tmp_name'],ROOT.PHOTO_PATH.$fileName);
				$note = "uploaded a new photo";
			}
			$query = sprintf("UPDATE users
							SET photo = '%s'
							WHERE userID = %d",
							mysql_real_escape_string($fileName),
							$_SESSION['userID']);
			$result = mysql_query($query, $conn) or die(mysql_error());
			$DB->LogSystemEvent($_SESSION['userID'],$note);
		}
	}
	
	if ($_POST['form'] == "password") {
		if($_POST['user_pwd']==$_POST['now_pwd'] && !empty($_POST['new_pwd']) && !empty($_POST['con_pwd']) && $_POST['new_pwd']==$_POST['con_pwd']) {
			$query = sprintf("UPDATE users
							SET password = '%s'
							WHERE userID = %d",
							mysql_real_escape_string($_POST['new_pwd']),
							$_SESSION['userID']);
			$result = mysql_query($query, $conn) or die(mysql_error());
			$DB->LogSystemEvent($_SESSION['userID'],"changed password");
		}
	}
	
	if ($_POST['form'] == "profile") {
		$DB->edit_user_profile($_SESSION['userID'],$_POST['forename'],$_POST['surname'],$_POST['email'],$_POST['telephone'],$_POST['fax'],$_POST['mobile'],$_POST['langID'],$_POST['defaultLangID']);
		$_SESSION['userDefaultLangID']=$_POST['defaultLangID'];
		$DB->LogSystemEvent($_SESSION['userID'],"updated profile");
	}
	
	if ($_POST['form'] == "lang") {
		$DB->edit_user_lang($_SESSION['userID'],$_POST['lang'],$_POST['pro'],$_POST['delete'],$_POST['new_lang'],$_POST['new_pro']);
		$DB->LogSystemEvent($_SESSION['userID'],"updated language capability");
	}
	
	if ($_POST['form'] == "rate") {
		$DB->edit_user_rate($_SESSION['userID'],$_POST['source_lang'],$_POST['target_lang'],$_POST['currency'],$_POST['rate'],$_POST['preference'],$_POST['delete'],$_POST['new_source_lang'],$_POST['new_target_lang'],$_POST['new_currency'],$_POST['new_rate'],$_POST['new_preference']);
		$DB->LogSystemEvent($_SESSION['userID'],"updated rates");
	}
	
	if ($_POST['form'] == "spec") {
		$DB->edit_user_spec($_SESSION['userID'],$_POST['subjectID']);
		$DB->LogSystemEvent($_SESSION['userID'],"updated specialisations");
	}
	
	header("Location: index.php?layout=profile");
	exit;
}
?>