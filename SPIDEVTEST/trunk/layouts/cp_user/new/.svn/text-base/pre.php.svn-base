<?php
$access = array("system","cpanel");
require_once(MODULES.'mod_authorise.php');

if(!empty($_POST['form'])) {
	
	if($_POST['form']=="cancel") {
		header("Location: index.php?layout=$layout");
		exit();
	}
	
	if($_POST["form"]=="save" || $_POST["form"]=="apply") {
		$query = sprintf("SELECT userID
							FROM users
							WHERE username='%s'
							LIMIT 1",
							mysql_real_escape_string($_POST['username']));
		$result = mysql_query($query, $conn) or die(mysql_error());
		if (mysql_num_rows($result)) {
		  header("Location: index.php?layout=system&id=10");
		  exit;
		} else {
			$vtID = isset($_POST['global']) ? $_POST['global'] : $_POST['companyID'];
			$agent = empty($_POST['agent']) ? 0 : 1;
			$active = empty($_POST['active']) ? 0 : $_POST['active'];
			$update = sprintf("INSERT INTO users
		  					(username, password, companyID, userGroupID, forename, surname, telephone, fax, mobile, email, vtID, agent, active, langID, defaultLangID, allowance)
							VALUES ('%s', '%s', %d, %d, '%s', '%s', '%s', '%s', '%s', '%s', %d, %d, %d, %d, %d, %d)",
							mysql_real_escape_string($_POST['username']),
							mysql_real_escape_string($_POST['pwd']),
							$_POST['companyID'],
							$_POST['ugID'],
							mysql_real_escape_string($_POST['forename']),
							mysql_real_escape_string($_POST['surname']),
							mysql_real_escape_string($_POST['telephone']),
							mysql_real_escape_string($_POST['fax']),
							mysql_real_escape_string($_POST['mobile']),
							mysql_real_escape_string($_POST['email']),
							$vtID,
							$agent,
							$active,
							$_POST['langID'],
							$_POST['defaultLangID'],
							$_POST['allowance']);
			$result = mysql_query($update, $conn) or die(mysql_error());
			$newID = mysql_insert_id($conn);
			$acl_api = new acl_api($acl_options);
			//ACL: add user to `aro` table
			$acl_api->add_object($_POST['companyID'], mysql_real_escape_string($_POST['username']), $newID, $newID, 0, 'ARO');
			//ACL: add user to `groups_aro_map` table
			$acl_api->add_group_object($_POST['ugID'], $_POST['companyID'], $newID, 'ARO');
			//email option
			if($_POST["emailOption"]) {
				$body = "******PRIVATE AND CONFIDENTIAL******";
				$body .= "\n\n\nDear ".$_POST['forename']." ".$_POST['surname'].",";
				$body .= "\n\nYou have been registered at ".SYSTEM_NAME.". Your login details are:";
				$body .= "\n\nUsername: ".$_POST['username'];
				$body .= "\nPassword: ".$_POST['pwd'];
				$body .= "\n\nTo access and update your own profile, please go to:";
				$body .= "\n\n".SITE_URL."index.php?layout=profile";
				$body .= "\n\nKind Regards,";
				$body .= "\n\n".COMPANY_NAME;
				$name = $_POST['forename']." ".$_POST['surname'];
				$address = $_POST['email'];
				$subject = SYSTEM_NAME.": New User Notification";
				require_once(CLASSES.'Mailer.php');
				$mailer = new Mailer();
				$mailer->send_mail($name,$address,$subject,$body);
			}
			$DB->LogSystemEvent($_SESSION['userID'],"created a new user: {$_POST['username']}");
		}
		switch($_POST["form"]) {
			case "apply":	header("Location: index.php?layout=$layout&task=edit&id=$newID");
							break;
			case "save":	header("Location: index.php?layout=$layout");
							break;
			default:		header("Location: index.php?layout=$layout");
		}
		exit();
	}
	
}
?>