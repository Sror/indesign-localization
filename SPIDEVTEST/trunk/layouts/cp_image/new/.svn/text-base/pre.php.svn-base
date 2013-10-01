<?php
$access = array("system","cpanel");
require_once(MODULES.'mod_authorise.php');

if(!empty($_POST['form'])) {
	
	if($_POST['form']=="cancel") {
		header("Location: index.php?layout=$layout");
	}
	
	if($_POST["form"]=="save" || $_POST["form"]=="apply") {
		if(!empty($_FILES['img_content']['name'])) {
			$img_file = time()."_".preg_replace('/[^\da-z_\(\).]+/i', '_', trim(basename($_FILES['img_content']['name'])));
			$content = IMG_LIBRARY_DIR.$img_file;
			@move_uploaded_file($_FILES['img_content']['tmp_name'], $content);
			$IM = new ImageManager();
			$id = $IM->UploadImage($_SESSION['userID'], $_POST['lang_id'], $content, $_POST['brand_id'], $_POST['subject_id']);
		}
		
		switch($_POST["form"]) {
			case "apply":	header("Location: index.php?layout=$layout&task=edit&id=$id");
							break;
			case "save":	header("Location: index.php?layout=$layout");
							break;
			default:		header("Location: index.php?layout=$layout");
		}
	}
	exit();
}
?>