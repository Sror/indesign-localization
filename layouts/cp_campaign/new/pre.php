<?php
$access = array("system","cpanel");
require_once(MODULES.'mod_authorise.php');

if(!empty($_POST['form'])) {
	
	if($_POST['form']=="cancel") {
		header("Location: index.php?layout=$layout");
	}
	
	if($_POST["form"]=="save" || $_POST["form"]=="apply") {
		$name = RestrictName($_POST['name']);
		$update = sprintf("INSERT INTO campaigns
		  					(campaignName, brandID, ownerID, sourceLanguageID, ref, lastEdit, campaignStatus, default_sub_font_id, default_img_dir)
							VALUES ('%s', %d, %d, %d, '%s', NOW(), %d, %d, '%s')",
							mysql_real_escape_string($name),
							$_POST['brandID'],
							$_SESSION['userID'],
							$_POST['langID'],
							mysql_real_escape_string($_POST['ref']),
							STATUS_ACTIVE,
							$_POST['default_sub_font_id'],
							mysql_real_escape_string($_POST['default_img_dir']));
		$result = mysql_query($update, $conn) or die(mysql_error());
		$newID = mysql_insert_id($conn);
		$DB->update_campaign_acl($newID, $_POST['acl']);
		$DB->LogSystemEvent($_SESSION['userID'],"created a new campaign: $name",$newID);
		
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