<?php
$access = array("system","cpanel");
require_once(MODULES.'mod_authorise.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$art_query = sprintf("SELECT artworks.*,
						users.username, users.companyID,
						service_engines.name AS service_name, service_engines.ext AS service_ext,
						COUNT(tasks.taskID) AS taskno
						FROM artworks
						LEFT JOIN users ON artworks.uploaderID = users.userID
						LEFT JOIN service_engines ON artworks.artworkType = service_engines.id
						LEFT JOIN tasks ON artworks.artworkID = tasks.artworkID
						WHERE artworks.artworkID = %d
						GROUP BY artworks.artworkID
						LIMIT 1",
						$id);
$art_result = mysql_query($art_query, $conn) or die(mysql_error());
$art_found = mysql_num_rows($art_result);
if($art_found) {
	$art_row = mysql_fetch_assoc($art_result);
} else {
	access_denied();
}

if(!empty($_POST['form'])) {
	
	if($_POST['form']=="close") {
		header("Location: index.php?layout=$layout");
	}
	
	if($_POST["form"]=="save" || $_POST["form"]=="apply") {
		$name = RestrictName($_POST['name']);
		$update = sprintf("UPDATE artworks SET
		  					artworkName = '%s',
		  					campaignID = %d,
		  					subjectID = %d,
		  					lastUpdate = NOW(),
		  					default_sub_font_id = %d,
		  					default_img_dir = '%s'
		  					WHERE artworkID = %d",
							mysql_real_escape_string($name),
							$_POST['campaignID'],
							$_POST['subjectID'],
							$_POST['default_sub_font_id'],
							mysql_real_escape_string($_POST['default_img_dir']),
							$id);
		$result = mysql_query($update, $conn) or die(mysql_error());
		$update = sprintf("UPDATE campaigns SET
							lastEdit = NOW()
							WHERE campaignID = %d",
							$_POST['campaignID']);
		$result = mysql_query($update, $conn) or die(mysql_error());
		
		if($art_row['artworkID']!=$_POST['version']) {
			$update = sprintf("UPDATE artworks SET
								`live` = 0
								WHERE artworkID = %d",
								$art_row['artworkID']);
			$result = mysql_query($update, $conn) or die(mysql_error());
			$update = sprintf("UPDATE artworks SET
								`live` = 1
								WHERE artworkID = %d",
								$_POST['version']);
			$result = mysql_query($update, $conn) or die(mysql_error());
			$update = sprintf("UPDATE tasks SET
								artworkID = %d
								WHERE artworkID = %d",
								$_POST['version'],
								$art_row['artworkID']);
			$result = mysql_query($update, $conn) or die(mysql_error());
		}
		
		$DB->LogSystemEvent($_SESSION['userID'],"edited artwork",0,$id);
		
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