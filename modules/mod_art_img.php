<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("system","login");
require_once(MODULES.'mod_authorise.php');

$artwork_id = isset($_GET['artwork_id']) ? (int)$_GET['artwork_id'] : 0;
$img_link_id = isset($_GET['img_link_id']) ? (int)$_GET['img_link_id'] : 0;

$query = sprintf("SELECT img_links.box_id,
				boxes.Left, boxes.Right, boxes.Right-boxes.Left AS Width,
				boxes.Top, boxes.Bottom, boxes.Bottom-boxes.Top AS Height,
				pages.PreviewFile, pages.PageScale
				FROM img_links
				LEFT JOIN boxes ON img_links.box_id = boxes.uID
				LEFT JOIN pages ON boxes.PageID = pages.uID
				WHERE pages.ArtworkID = %d
				AND img_links.id = %d
				LIMIT 1",
				$artwork_id,
				$img_link_id);
$result = mysql_query($query, $conn) or die(mysql_error());
if(!mysql_num_rows($result)) die("Image Not Available");
$row = mysql_fetch_assoc($result);
$box_id = $row['box_id'];
$path = $DB->RebuildBoxPreview($artwork_id, $box_id);
$IM = new ImageManager();
$content = $IM->GetImageContent($artwork_id,$box_id);
?>
<form
	action="index.php?layout=artwork&id=<?php echo $artwork_id; ?>"
	name="imgForm"
	method="POST"
	enctype="multipart/form-data"
	onsubmit="hidediv('helper');Popup('loadingme','waiting');"
>
	<div><b><?php echo $lang->display('Link'); ?></b></div>
	<div class="thumbnailBox"><div class="pic"><img style="max-height:100px;max-width:100px;" src="<?php echo $path.'?'.time(); ?>" /></div></div>
	<div class="clear"></div>
	<div><b><?php echo $lang->display('Please Select'); ?>:</b></div>
	<?php BuildImgOption($content); ?>
	<div>
		<input
			id="refresh"
			name="refresh"
			type="checkbox"
			value="1"
		/>
		<?php echo $lang->display('Refresh Previews'); ?>
	</div>
	<div align="center">
		<input
			type="button"
			class="btnDo"
			onmousemove="this.className='btnOn'"
			onmouseout="this.className='btnDo'"
			title="<?php echo $lang->display('Save'); ?>"
			value="<?php echo $lang->display('Save'); ?>"
			onclick="SubmitForm('imgForm','save');"
		/>
		<input
			type="button"
			class="btnOff"
			onmousemove="this.className='btnOn'"
			onmouseout="this.className='btnOff'"
			title="<?php echo $lang->display('Restore'); ?>"
			value="<?php echo $lang->display('Restore'); ?>"
			onclick="SubmitForm('imgForm','restore');"
		/>
	</div>
	<input type="hidden" name="box_id" value="<?php echo $box_id; ?>">
	<input type="hidden" name="form" id="form">
</form>