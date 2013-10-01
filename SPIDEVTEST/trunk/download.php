<?php
ob_start();
require_once(dirname(__FILE__).'/config.php');
@ob_end_clean();
if(isset($_GET['File']) && isset($_GET['SaveAs'])) {
	$FILE = basename($_GET['File']);
	$FILE = urldecode($FILE);
	$FILE = isset($_GET['attachment']) ? REPOSITORY_DIR.$FILE : (isset($_GET['temp']) ? ROOT.TMP_DIR.$FILE : UPLOAD_DIR.$FILE);
	$download = ForceDownload($FILE, $_GET['SaveAs'],false);
	if($download === false)	 error_creating_file();
	if(isset($_GET['bin'])) @unlink($FILE);
}

function GetExportFile($ArtworkID, $TaskID, $service_tID, $lines=0) {
	require_once(CLASSES."services.php");
	$process = new ProcessService($service_tID);
	$ProcessEngine = $process->getProcessEngine();
	return $ProcessEngine->export($ArtworkID,$TaskID,$lines);
}

function GetDownloadFile($ArtworkID, $TaskID, $service_tID, $record_id=0, $packed=true, $PDFOption=null) {
	//$record_id is optional for template based feature
	require_once(CLASSES."services.php");
	$process = new ProcessService($service_tID);
	$ProcessEngine = $process->getProcessEngine();
	
	//JobOptions
	require_once(MODULES.'mod_filesystem.php');
	$JobOptions = "/".preg_replace('%:?\\\%', '/', JOBOPTIONS_DIR);
	$addoptions = list_joboptions();
	foreach($addoptions as $Name=>$File) {
		$ProcessEngine->AddPDFOption($JobOptions.urlencode($File),$Name);
	}
	$ProcessEngine->setPDFOption($PDFOption);
	$File = $ProcessEngine->DownloadFile($ArtworkID,$TaskID,$record_id,$packed);
	return $File;
}