<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("artworks","new");
require_once(MODULES.'mod_authorise.php');

if(isset($_SESSION['TmpFile'])) {
	if(isset($_GET['id'])) {
		$id = (int)$_GET['id'];
		unset($_SESSION['TmpFile']['name'][$id]);
		unset($_SESSION['TmpFile']['tmp_name'][$id]);
		@unlink($_SESSION['TmpFile']['tmp_name'][$id]);
	}
	if(count($_SESSION['TmpFile']['name'])==0 && count($_SESSION['TmpFile']['tmp_name'])==0) {
		unset($_SESSION['TmpFile']);
	}
}

if(!isset($_SESSION['TmpFile'])) {
	BuildUploadOption($_SESSION['companyID']);
} else {
	if(isset($_GET['id'])) {
		$id = (int)$_GET['id'];
		unset($_SESSION['TmpFile']['name'][$id]);
		unset($_SESSION['TmpFile']['tmp_name'][$id]);
	}
	foreach($_SESSION['TmpFile']['name'] as $k=>$tmp) {
		echo '<div id="tmp'.$k.'"><a href="javascript:void(0);" onclick="ResetDiv(\'tmp'.$k.'\');DoAjax(\'id='.$k.'\',\'uploader\',\'modules/mod_art_uploader.php\');"><img src="'.IMG_PATH.'btn_s_delete.png" title="'.$lang->display('Delete').'" /></a> '.$tmp.'</div>';
	}
}
?>