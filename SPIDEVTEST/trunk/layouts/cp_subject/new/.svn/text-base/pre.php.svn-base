<?php
$access = array("system","cpanel");
require_once(MODULES.'mod_authorise.php');

if(!empty($_POST['form'])) {
	
	if($_POST['form']=="cancel") {
		header("Location: index.php?layout=$layout");
	}
	
	if($_POST["form"]=="save" || $_POST["form"]=="apply") {
		
		$query = sprintf("SELECT subjectID
							FROM subjects
							WHERE subjectTitle='%s'
							AND streamID = %d
							LIMIT 1",
							mysql_real_escape_string($_POST['subject']),
							$_POST['streamID']);
		$result = mysql_query($query, $conn) or die(mysql_error());
		$found = mysql_num_rows($result);
		
		if ($found) {
		  header("Location: index.php?layout=system&id=10");
		  exit;
		} else {
			$update = sprintf("INSERT INTO subjects
			  					(streamID, subjectTitle)
								VALUES (%d, '%s')",
								$_POST['streamID'],
								mysql_real_escape_string($_POST['subject']));
			$result = mysql_query($update, $conn) or die(mysql_error());
			$newID = mysql_insert_id($conn);
			$DB->LogSystemEvent($_SESSION['userID'],"created a new subject: {$_POST['subject']}");
		}
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