<?php
$access = array("system","cpanel");
require_once(MODULES.'mod_authorise.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_query = sprintf("SELECT * FROM users
						WHERE userID = %d
						LIMIT 1",
						$id);
$user_result = mysql_query($user_query, $conn) or die(mysql_error());
$user_found = mysql_num_rows($user_result);
if($user_found) {
	$user_row = mysql_fetch_assoc($user_result);
} else {
	access_denied();
}

if(!empty($_POST['form'])) {
	
	if($_POST['form']=="close") {
		header("Location: index.php?layout=$layout");
	}
	
	if( ($_POST["form"]=="save" || $_POST["form"]=="apply") && ($id!=ADMIN_USERID || $_SESSION['userID']==ADMIN_USERID) ) {
		$acl_api = new acl_api($acl_options);
		$DB->edit_user_account($acl_api,$id,$user_row['username'],$_POST['pwd'],$_POST['companyID'],$_POST['ugID'],$_POST['active'],$_POST['agent'],$_POST['global'],$_POST['allowance']);
		if($_SESSION['userID'] == $id) {
			$_SESSION['lang'] = $DB->get_lang_by_id($_POST['langID']);
			$_SESSION['userDefaultLangID'] = $_POST['defaultLangID'];
		}
		$DB->edit_user_profile($id,$_POST['forename'],$_POST['surname'],$_POST['email'],$_POST['telephone'],$_POST['fax'],$_POST['mobile'],$_POST['langID'],$_POST['defaultLangID']);
		$DB->edit_user_lang($id,$_POST['lang'],$_POST['pro'],$_POST['delete'],$_POST['new_lang'],$_POST['new_pro']);
		$DB->edit_user_rate($id,$_POST['source_lang'],$_POST['target_lang'],$_POST['currency'],$_POST['rate'],$_POST['preference'],$_POST['delete'],$_POST['new_source_lang'],$_POST['new_target_lang'],$_POST['new_currency'],$_POST['new_rate'],$_POST['new_preference']);
		$DB->edit_user_spec($id,$_POST['subjectID']);
		$DB->edit_user_acl($acl_api,$id,(int)$_POST['companyID'],$_POST['aco'],$_POST["resetACL"]);
		//email option
		if($_POST["emailOption"]) {
			$body = "******PRIVATE AND CONFIDENTIAL******";
			$body .= "\n\n\nDear ".$_POST['forename']." ".$_POST['surname'].",";
			$body .= "\n\nYour login details at ".SYSTEM_NAME." are:";
			$body .= "\n\nUsername: ".$user_row['username'];
			$body .= "\nPassword: ".$_POST['pwd'];
			$body .= "\n\nTo access and update your own profile, please go to:";
			$body .= "\n\n".SITE_URL."index.php?layout=profile";
			$body .= "\n\nKind Regards,";
			$body .= "\n\n".COMPANY_NAME;
			$name = $_POST['forename']." ".$_POST['surname'];
			$address = $_POST['email'];
			$subject = SYSTEM_NAME.": User Login Details";
			require_once(CLASSES.'Mailer.php');
			$mailer = new Mailer();
			$mailer->send_mail($name,$address,$subject,$body);
		}
		$DB->LogSystemEvent($_SESSION['userID'], "edited user: {$user_row['username']}");
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