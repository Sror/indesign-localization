<?php
$access = array("system","superadmin");
require_once(MODULES.'mod_authorise.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$sub_query = sprintf("SELECT subjects.*,
						COUNT(userspecs.userID) AS userno
						FROM subjects
						LEFT JOIN userspecs ON subjects.subjectID = userspecs.subjectID
						WHERE subjects.subjectID = %d
						GROUP BY subjects.subjectID
						LIMIT 1",
						$id);
$sub_result = mysql_query($sub_query, $conn) or die(mysql_error());
$sub_found = mysql_num_rows($sub_result);
if($sub_found) {
	$sub_row = mysql_fetch_assoc($sub_result);
} else {
	access_denied();
}

if(!empty($_POST['form'])) {
	if($_POST['form']=="close") {
		header("Location: index.php?layout=$layout");
	}
	if($_POST["form"]=="save" || $_POST["form"]=="apply") {
		$update = sprintf("UPDATE subjects SET
							streamID = %d,
		  					subjectTitle = '%s'
		  					WHERE subjectID = %d",
							$_POST['streamID'],
							mysql_real_escape_string($_POST['subject']),
							$id);
		$result = mysql_query($update, $conn) or die(mysql_error());
		$DB->LogSystemEvent($_SESSION['userID'],"edited subject: {$_POST['subject']}");
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