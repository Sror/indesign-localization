<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("artworks","edit");
require_once(MODULES.'mod_authorise.php');

$id = isset($_GET['id']) ? $_GET['id'] : 0;
$query = sprintf("SELECT artworkName
				FROM artworks
				WHERE artworkID = %d
				LIMIT 1",
				$id);
$result = mysql_query($query, $conn) or die(mysql_error());
if(!mysql_num_rows($result)) access_denied();
$row = mysql_fetch_assoc($result);
?>
<form
	action="index.php?layout=amend&id=<?php echo $id; ?>"
	name="saveasForm"
	method="POST"
	enctype="multipart/form-data"
	onsubmit="hidediv('helper');Popup('loadingme','waiting');"
>
<table width="100%" border="0" cellspacing="0" cellpadding="5">
	<tr>
		<td class="highlight" width="30%">* <?php echo $lang->display('Add to Existing Campaign'); ?>:</td>
		<td width="70%">
			<select
				class="input"
				onfocus="this.className='inputOn'"
				onblur="this.className='input'"
				name="CampaignID"
				id="CampaignID"
			>
			<?php BuildActiveCampaignList($_SESSION['companyID'],$_SESSION['userID'],$id); ?>
			</select>
		</td>
	</tr>
	<tr>
		<td class="highlight">* <?php echo $lang->display('Artwork Title'); ?>:</td>
		<td>
			<input
				type="text"
				class="input"
				onfocus="this.className='inputOn'"
				onblur="this.className='input'"
				name="ArtworkName"
				id="ArtworkName"
				value="<?php echo $row['artworkName']; ?>"
			/>
		</td>
	</tr>
	<tr>
		<td class="highlight">* <?php echo $lang->display('Version'); ?>:</td>
		<td>
			<input
				type="text"
				class="input"
				onfocus="this.className='inputOn'"
				onblur="this.className='input'"
				name="Version"
				id="Version"
				size="10"
				maxlength="20"
			/>
		</td>
	</tr>
	<tr>
		<td class="highlight"></td>
		<td>
			<input
				type="checkbox"
				class="input"
				onfocus="this.className='inputOn'"
				onblur="this.className='input'"
				name="clear"
				id="clear"
				value="1"
			/>
			<?php echo $lang->display('Clear all the comments and amendments afterwards'); ?>
		</td>
	</tr>
	<tr>
		<td class="highlight"></td>
		<td>
			<input
				type="submit"
				class="btnDo"
				onmousemove="this.className='btnOn'"
				onmouseout="this.className='btnDo'"
				title="<?php echo $lang->display('Save'); ?>"
				value="<?php echo $lang->display('Save'); ?>"
				onclick="validateForm('CampaignID','Campaign','R','ArtworkName','Artwork name','R','Version','Version','R');return document.returnValue;"
			/>
		</td>
	</tr>
</table>
<input name="update" type="hidden" value="saveasForm">
</form>