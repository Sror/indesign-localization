<?php
$access = array("system","cpanel");
require_once(MODULES.'mod_authorise.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$camp_query = sprintf("SELECT campaigns.campaignName, campaigns.brandID, campaigns.sourceLanguageID, campaigns.ref,
						campaigns.campaignStatus, campaigns.ownerID, campaigns.lastEdit AS lastupdate, campaigns.default_sub_font_id, campaigns.default_img_dir,
						COUNT(artworks.artworkID) AS artworkno,
						users.userID, users.username AS owner, users.companyID,
						companies.companyName AS company
						FROM campaigns
						LEFT JOIN artworks ON campaigns.campaignID = artworks.campaignID
						LEFT JOIN users ON campaigns.ownerID = users.userID
						LEFT JOIN companies ON users.companyID = companies.companyID
						WHERE campaigns.campaignID = %d
						GROUP BY campaigns.campaignID
						LIMIT 1",
						$id);
$camp_result = mysql_query($camp_query, $conn) or die(mysql_error());
if(!mysql_num_rows($camp_result)) access_denied();
$camp_row = mysql_fetch_assoc($camp_result);

if(!empty($_POST['form'])) {
	
	if($_POST['form']=="close") {
		header("Location: index.php?layout=$layout");
	}
	
	if($_POST["form"]=="save" || $_POST["form"]=="apply") {
		$name = RestrictName($_POST['name']);
		$update = sprintf("UPDATE campaigns SET
		  					campaignName = '%s',
		  					brandID = %d,
		  					sourceLanguageID = %d,
		  					ref = '%s',
		  					lastEdit = NOW(),
		  					campaignStatus = %d,
		  					default_sub_font_id = %d,
		  					default_img_dir = '%s'
		  					WHERE campaignID = %d",
							mysql_real_escape_string($name),
							$_POST['brandID'],
							$_POST['langID'],
							mysql_real_escape_string($_POST['ref']),
							$_POST['status'],
							$_POST['default_sub_font_id'],
							mysql_real_escape_string($_POST['default_img_dir']),
							$id);
		$result = mysql_query($update, $conn) or die(mysql_error());
		$DB->update_campaign_acl($id, $_POST['acl']);
		$DB->LogSystemEvent($_SESSION['userID'],"edited campaign: $name",$id);
		
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