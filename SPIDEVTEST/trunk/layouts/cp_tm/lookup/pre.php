<?php
$access = array("system","cpanel");
require_once(MODULES.'mod_authorise.php');

if(!empty($_POST['form'])) {
	
	if($_POST['form']=="new") {
		header("Location: index.php?layout=$layout&task=new&fromLangID={$_POST['fromLangID']}&toLangID={$_POST['toLangID']}&source=".urlencode($_POST['source']));
	}
	
	if($_POST['form']=="close") {
		header("Location: index.php?layout=$layout");
	}
	exit();
}
?>