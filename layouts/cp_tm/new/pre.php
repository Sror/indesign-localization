<?php
$access = array("system","cpanel");
require_once(MODULES.'mod_authorise.php');

if(!empty($_POST['form'])) {
	
	if($_POST['form']=="cancel") {
		header("Location: index.php?layout=$layout");
	}
	
	if($_POST["form"]=="save" || $_POST["form"]=="apply") {
		
		$sourcePara = $_POST['sourcePara'];
		$targetPara = $_POST['targetPara'];
		$sourceLangID = (int)$_POST['sourceLangID'];
		$targetLangID = (int)$_POST['targetLangID'];
		$typeID = (int)$_POST['typeID'];
		$brandID = (int)$_POST['brandID'];
		$subjectID = (int)$_POST['subjectID'];
		$Trans = new Translator();
		$para_row = $Trans->AddParagraph($sourcePara,$sourceLangID,0,$_SESSION['userID'],$typeID,$brandID,$subjectID);
		$PG = $para_row['PG'];
		$Trans->AddTranslated($targetPara,$targetLangID,$PG,$sourceLangID,0,$_SESSION['userID'],$typeID,$brandID,$subjectID);
		$DB->LogSystemEvent($_SESSION['userID'],"created new translation memory Type [$typeID]: $sourcePara as: $targetPara");
		
		switch($_POST["form"]) {
			case "apply":	header("Location: index.php?layout=$layout&task=new");
							break;
			case "save":	header("Location: index.php?layout=$layout");
							break;
			default:		header("Location: index.php?layout=$layout");
		}
	}
	exit();
}
?>