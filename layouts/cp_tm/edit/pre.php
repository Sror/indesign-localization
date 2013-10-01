<?php
$access = array("system","cpanel");
require_once(MODULES.'mod_authorise.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$tm_query = sprintf("SELECT paragraphs.*,
					users.username
					FROM paragraphs
					LEFT JOIN users ON paragraphs.user_id = users.userID
					WHERE paragraphs.uID = %d
					LIMIT 1",
					$id);
$tm_result = mysql_query($tm_query, $conn) or die(mysql_error());
$tm_found = mysql_num_rows($tm_result);
if($tm_found) {
	$tm_row = mysql_fetch_assoc($tm_result);
} else {
	access_denied();
}

if(!empty($_POST['form'])) {
	
	if($_POST["form"]=="save" || $_POST["form"]=="apply") {
		
		$update = sprintf("UPDATE paragraphs SET
		  					LangID = %d,
		  					ParaText = '%s',
		  					Words = %d,
		  					type_id = %d,
		  					brand_id = %d,
		  					subject_id = %d,
		  					notes = '%s',
		  					rating = %d
		  					WHERE uID = %d",
							$_POST['langID'],
							mysql_real_escape_string($_POST['paratext']),
							$_POST['words'],
							$_POST['type_id'],
							$_POST['brand_id'],
							$_POST['subject_id'],
							mysql_real_escape_string($_POST['notes']),
							$_POST['rating'],
							$id);
		$result = mysql_query($update, $conn) or die(mysql_error());
		$DB->LogSystemEvent($_SESSION['userID'],"edited paragraph: {$_POST['paratext']}");
		
		switch($_POST["form"]) {
			case "apply":	header("Location: index.php?layout=$layout&task=$task&id=$id");
							break;
			case "save":	header("Location: index.php?layout=$layout");
							break;
			default:		header("Location: index.php?layout=$layout");
		}
	}
	
	if($_POST['form']=="lookup") {
		header("Location: index.php?layout=$layout&task=lookup&langID={$_POST['langID']}&para=".urlencode($_POST['paratext']));
	}
	
	if($_POST['form']=="close") {
		header("Location: index.php?layout=$layout");
	}
	exit();
}
?>