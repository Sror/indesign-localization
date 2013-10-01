<?php
$access = array("system","superadmin");
require_once(MODULES.'mod_authorise.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$lang_query = sprintf("SELECT languages.*,
						COUNT(campaigns.campaignID) AS campaignno
						FROM languages
						LEFT JOIN campaigns ON languages.languageID = campaigns.sourceLanguageID
						WHERE languages.languageID = %d
						GROUP BY languages.languageID
						LIMIT 1",
						$id);
$lang_result = mysql_query($lang_query, $conn) or die(mysql_error());
$lang_found = mysql_num_rows($lang_result);
if($lang_found) {
	$lang_row = mysql_fetch_assoc($lang_result);
} else {
	access_denied();
}

if(!empty($_POST['form'])) {
	
	if($_POST['form']=="close") {
		header("Location: index.php?layout=$layout");
	}
	
	if($_POST["form"]=="save" || $_POST["form"]=="apply") {
		
		$update = sprintf("UPDATE languages SET
		  					languageName = '%s',
		  					flag = '%s'
		  					WHERE languageID = %d",
							$_POST['name'],
							$_POST['flag'],
							$id);
		$result = mysql_query($update, $conn) or die(mysql_error());
		$DB->LogSystemEvent($_SESSION['userID'],"edited language: {$_POST['name']}");
		
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