<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("system","login");
require_once(MODULES.'mod_authorise.php');

$artworkID = isset($_GET['artworkID']) ? (int)$_GET['artworkID'] : 0;
$taskID = isset($_GET['taskID']) ? (int)$_GET['taskID'] : 0;
if(empty($taskID)) {
	$redirect = "amend";
	$id = $artworkID;
} else {
	$redirect = "customise";
	$id = $taskID;
}
$query = sprintf("SELECT service_engines.id
				FROM artworks
				LEFT JOIN service_engines ON artworks.artworkType = service_engines.id
				WHERE artworks.artworkID = %d
				LIMIT 1",
				$artworkID);
$result = mysql_query($query,$conn) or die(mysql_error());
if(!mysql_num_rows($result)) die("Invalid Token");
$row = mysql_fetch_assoc($result);
echo '<form
		action="index.php?layout='.$redirect.'&id='.$id.'"
		name="upForm"
		method="POST"
		enctype="multipart/form-data"
		onsubmit="hidediv(\'helper\');Popup(\'loadingme\',\'waiting\');">';
echo '<table width="100%" cellspacing="0" cellpadding="3" border="0">';
echo '<tr>';
echo '<td width="30%" class="highlight">* '.$lang->display('Customise').':</td>';
echo '<td width="70%">';
echo '<select
		class="input"
		onfocus="this.className=\'inputOn\'"
		onblur="this.className=\'input\'"
		name="service_tID"
		id="service_tID">';
BuildTweakUploadType($_SESSION['packageID'],$row['id'],SERVICE_UPLOAD,TYPE_TWEAK);
echo '</select>';
echo '</td>';
echo '</tr>';
echo '<tr>';
echo '<td class="highlight" valign="top">* '.$lang->display('Select File').':</td>';
echo '<td>';
BuildUploadOption($_SESSION['companyID'],false);
echo '</td>';
echo '</tr>';
echo '<tr>';
echo '<td class="highlight"></td>';
echo '<td>';
echo '<input
		type="submit"
		class="btnDo"
		onmousemove="this.className=\'btnOn\'"
		onmouseout="this.className=\'btnDo\'"
		value="'.$lang->display('Upload').'"
		title="'.$lang->display('Upload').'" /> ';
echo '<input type="hidden" name="update" value="upForm" />';
echo '</td>';
echo '</tr>';
echo '</table>';
echo '</form>';
?>