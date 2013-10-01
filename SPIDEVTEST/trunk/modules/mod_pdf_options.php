<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("system","login");
require_once(MODULES.'mod_authorise.php');

echo '<select
		class="input"
		onfocus="this.className=\'inputOn\'"
		onblur="this.className=\'input\'"
		name="PDFOption"
		id="PDFOption">';
$service_tID = isset($_GET['service_tID']) ? (int)$_GET['service_tID'] : 0;
$query = sprintf("SELECT id
				FROM service_transaction_process
				WHERE id = %d
				AND class LIKE '%s'
				AND type_id > 0
				LIMIT 1",
				$service_tID,
				"%PDF");
$result = mysql_query($query, $conn) or die(mysql_error());
if(mysql_num_rows($result)==0) {
	echo '<option value="0">-</option>';
} else {
	require_once(CLASSES."services.php");
	$process = new ProcessService($service_tID);
	$ProcessEngine = $process->getProcessEngine();
	$options = $ProcessEngine->GetPDFOptions($PDFOption);
	
	//JobOptions
	require_once(MODULES.'mod_filesystem.php');
	$JobOptions = "/".preg_replace('%:?\\\%', '/', JOBOPTIONS_DIR);
	$addoptions = list_joboptions();
	foreach($addoptions as $Name=>$File) {
		$ProcessEngine->AddPDFOption($JobOptions.urlencode($File),$Name);
	}
	$options = $ProcessEngine->GetPDFOptions($PDFOption);

	if($options === false) {
		echo '<option value="0">-</option>';
	} else {
		foreach($options as $k=>$option) {
			echo '<option value="'.$k.'">'.$option.'</option>';
		}
	}

}
echo '</select> ';