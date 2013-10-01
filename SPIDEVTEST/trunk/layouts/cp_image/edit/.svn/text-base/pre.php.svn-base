<?php
$access = array("system","cpanel");
require_once(MODULES.'mod_authorise.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$img_query = sprintf("SELECT images.*,
						users.username,
						COUNT(img_usage.id) AS imgusage
						FROM images
						LEFT JOIN users ON users.userID = images.user_id
						LEFT JOIN img_usage ON img_usage.img_id = images.id
						WHERE images.type_id = %d
						AND images.id = %d
						GROUP BY images.id
						LIMIT 1",
						IMG_LIBRARY,
						$id);
$img_result = mysql_query($img_query, $conn) or die(mysql_error());
$img_found = mysql_num_rows($img_result);
if($img_found) {
	$img_row = mysql_fetch_assoc($img_result);
} else {
	access_denied();
}

if(!empty($_POST['form'])) {
	
	if($_POST['form']=="close") {
		header("Location: index.php?layout=$layout");
	}
	
	if($_POST["form"]=="save" || $_POST["form"]=="apply") {
		if(!empty($_FILES['replacement']['name'])) {
			$img_file = time()."_".preg_replace('/[^\da-z_\(\).]+/i', '_', trim(basename($_FILES['replacement']['name'])));
			$content = IMG_LIBRARY_DIR.$img_file;
			@move_uploaded_file($_FILES['replacement']['tmp_name'], $content);
		} else {
			$content = "";
		}
		$IM = new ImageManager();
		$IM->EditImage($id, $_SESSION['userID'], $_POST['lang_id'], $content, $_POST['brand_id'], $_POST['subject_id']);
		
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