<?php
$access = array("system","cpanel");
require_once(MODULES.'mod_authorise.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$task_query = sprintf("SELECT tasks.*,
						artworks.artworkID, artworks.artworkName AS artwork,
						artworks.campaignID, campaigns.campaignName AS campaign,
						U1.forename AS tforename, U1.surname AS tsurname,
						U2.forename AS mforename, U2.surname AS msurname,
						L1.languageName AS sourceLang, L1.flag AS sourceFlag,
						L2.languageName AS targetLang, L2.flag AS targetFlag
						FROM tasks
						LEFT JOIN artworks ON tasks.artworkID = artworks.artworkID
						LEFT JOIN campaigns ON artworks.campaignID = campaigns.campaignID
						LEFT JOIN users U1 ON tasks.translatorID = U1.userID
						LEFT JOIN users U2 ON tasks.creatorID = U2.userID
						LEFT JOIN languages L1 ON campaigns.sourceLanguageID = L1.languageID
						LEFT JOIN languages L2 ON tasks.desiredLanguageID = L2.languageID
						WHERE tasks.taskID = %d
						LIMIT 1",
						$id);
$task_result = mysql_query($task_query, $conn) or die(mysql_error());
$task_found = mysql_num_rows($task_result);
if($task_found) {
	$task_row = mysql_fetch_assoc($task_result);
} else {
	access_denied();
}

if(!empty($_POST['form'])) {
	
	if($_POST['form']=="close") {
		header("Location: index.php?layout=$layout");
	}
	
	if($_POST["form"]=="save" || $_POST["form"]=="apply") {
		$name = RestrictName($_POST['name']);
		$update = sprintf("UPDATE tasks SET
		  					deadline = '%s',
		  					taskStatus = %d,
		  					brief = '%s',
		  					trial = %d,
		  					lastUpdate = NOW()
		  					WHERE taskID = %d",
							mysql_real_escape_string($_POST['deadline']),
							$_POST['taskstatus'],
							mysql_real_escape_string($_POST['brief']),
							$_POST['trial'],
							$id);
		$result = mysql_query($update, $conn) or die(mysql_error());
		$DB->LogSystemEvent($_SESSION['userID'],"edited task",0,0,$id);
		$Translator = new Translator();
		$Translator->CheckProgress($id);
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