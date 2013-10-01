<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("system","login");
require_once(MODULES.'mod_authorise.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$query = sprintf("SELECT artworkName, artworkType
				FROM artworks
				WHERE artworkID = %d
				LIMIT 1",
				$id);
$result = mysql_query($query, $conn) or die(mysql_error());
if(!mysql_num_rows($result)) access_denied();
$row = mysql_fetch_assoc($result);
$artwork_name = $row['artworkName'];
$artwork_type = $row['artworkType'];

$IM = new ImageManager();
$default_img_dir = $IM->get_default_img_dir($artworkID);
$query = sprintf("SELECT img_links.id AS img_link_id, images.content
				FROM img_links
				LEFT JOIN images ON img_links.img_id = images.id
				LEFT JOIN boxes ON img_links.box_id = boxes.uID
				LEFT JOIN pages ON boxes.PageID = pages.uID
				LEFT JOIN artworks ON pages.ArtworkID = artworks.artworkID
				WHERE artworks.artworkID = %d
				ORDER BY img_links.img_id ASC",
				$id);
$result = mysql_query($query, $conn) or die(mysql_error());
$count = 0;
while($row = mysql_fetch_assoc($result)) {
	if(!$IM->CheckImageStatus($id,$row['img_link_id'])) $count++;
}
if($count) {
	echo '<div id="autolookup">';
	BuildTipMsg($lang->display('Missing').': '.$lang->display('Images').' ('.$count.') <a href="javascript:void(0);" onclick="DoAjax(\'id='.$id.'\',\'autolookup\',\'modules/mod_art_img_lookup.php\');"><img src="'.IMG_PATH.'ico_lookup.png" /> '.$lang->display('Auto Lookup').'</a>');
	echo '</div>';
}

echo '<form action="index.php?layout=artwork&id='.$id.'" name="dlForm" method="POST" enctype="multipart/form-data" onsubmit="validateForm(\'service_tID\',\'File Type\',\'R\');if(document.returnValue) display(\'reminder\'); else return false;">';
#echo '<form action="index.php?layout=artwork&id='.$id.'" name="dlForm" method="POST" enctype="multipart/form-data" target="_blank" onsubmit="validateForm(\'service_tID\',\'File Type\',\'R\');if(document.returnValue) fadeOut(\'helper\'); else return false;">';
echo '<table width="100%" cellspacing="0" cellpadding="3" border="0">';
echo '<tr>';
echo '<td width="30%" class="highlight">'.$lang->display('Download Artwork').':</td>';
echo '<td width="70%">'.$artwork_name.'</td>';
echo '</tr>';
echo '<tr>';
echo '<td class="highlight">* '.$lang->display('File Type').':</td>';
echo '<td>';
echo '<select
		class="input"
		onfocus="this.className=\'inputOn\'"
		onblur="this.className=\'input\'"
		name="service_tID"
		id="service_tID"
		onchange="ResetDiv(\'OptionsDiv\');DoAjax(\'service_tID=\'+document.getElementById(\'service_tID\').value,\'OptionsDiv\',\'modules/mod_pdf_options.php\');">';
BuildDownloadList($_SESSION['packageID'],$artwork_type,SERVICE_DOWNLOAD,array(TYPE_ORIGINAL,TYPE_PREWORK),$acl->acl_check("taskworkflow","download",$_SESSION['companyID'],$_SESSION['userID']));
echo '</select> ';
echo '</td>';
echo '</tr>';
echo '<tr>';
echo '<td class="highlight">'.$lang->display('Options').':</td>';
echo '<td>';
echo '<div id="OptionsDiv">';
echo '<select
		class="input"
		onfocus="this.className=\'inputOn\'"
		onblur="this.className=\'input\'"
		name="PDFOption"
		id="PDFOption">';
echo '<option value="0">-</option>';
echo '</select> ';
echo '</div>';
echo '</td>';
echo '</tr>';
echo '<tr>';
echo '<td></td>';
echo '<td>';
echo '<input
		type="submit"
		class="btnDo"
		onmousemove="this.className=\'btnOn\'"
		onmouseout="this.className=\'btnDo\'"
		value="'.$lang->display('Download').'"
		title="'.$lang->display('Download').'">';
echo '<input type="hidden" name="update" value="dlForm">';
echo '<div id="reminder" style="display:none;">'.$lang->display('Processing your request. Please wait').'...</div>';
echo '</td>';
echo '</tr>';
echo '</table>';
echo '</form>';