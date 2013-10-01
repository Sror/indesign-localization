<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("artworks","trash");
require_once(MODULES.'mod_authorise.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$query = sprintf("SELECT artworks.campaignID, artworks.artworkName,
				campaigns.campaignName
				FROM artworks
				LEFT JOIN campaigns ON artworks.campaignID = campaigns.campaignID
				WHERE artworks.artworkID = %d
				LIMIT 1",
				$id);
$result = mysql_query($query, $conn) or die(mysql_error());
if(!mysql_num_rows($result)) access_denied();
$row = mysql_fetch_assoc($result);
?>
<form
	action="index.php?layout=campaign&id=<?php echo $row['campaignID']; ?>"
	name="trash_art_form"
	method="POST"
	enctype="multipart/form-data"
	onsubmit="hidediv('helper');Popup('loadingme','waiting');"
>
	<table width="100%" border="0" cellspacing="0" cellpadding="5">
		<tr>
			<td class="highlight"><?php echo $lang->display('Are you sure you want to trash this artwork?'); ?></td>
		</tr>
		<tr>
			<td><?php echo $lang->display('Campaigns').' <img src="'.IMG_PATH.'arrow_subject.png" /> '.$row['campaignName'].' <img src="'.IMG_PATH.'arrow_subject.png" /> '.$row['artworkName']; ?></td>
		</tr>
		<tr>
			<td>
				<input
					type="submit"
					class="btnDo"
					onmousemove="this.className='btnOn'"
					onmouseout="this.className='btnDo'"
					title="<?php echo $lang->display('Trash'); ?>"
					value="<?php echo $lang->display('Trash'); ?>"
				/>
				<input
					type="button"
					class="btnOff"
					onmousemove="this.className='btnOn'"
					onmouseout="this.className='btnOff'"
					title="<?php echo $lang->display('Cancel'); ?>"
					value="<?php echo $lang->display('Cancel'); ?>"
					onclick="ResetDiv('window');hidediv('helper');"
				/>
			</td>
		</tr>
	</table>
	<input name="id[]" type="hidden" value="<?php echo $id; ?>">
	<input name="form" type="hidden" value="trash">
</form>