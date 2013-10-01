<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("system","cpanel");
require_once(MODULES.'mod_authorise.php');

if(!empty($_GET['id'])) {
	$ftp_id = (int)$_GET['id'];
	$_SESSION['ftp_id'] = $ftp_id;
} else {
	if(!empty($_SESSION['ftp_id'])) {
		$ftp_id = $_SESSION['ftp_id'];
	} else {
		$ftp_id = 0;
	}
}
$dir = isset($_GET['dir']) ? $_GET['dir'] : "/";
$location = isset($_GET['location']) ? $_GET['location'] : "";
?>
<form
	name="upload_form"
	id="upload_form"
	action="index.php?layout=cp_file"
	method="POST"
	enctype="multipart/form-data"
	onsubmit="validateForm('upload_file','File Name','R'); return document.returnValue;"
>
	<p><b><?php echo $lang->display('Upload File'); ?>:</b></p>
	<div id="filelist">
	<input
		type="file"
		class="input"
		onfocus="this.className='inputOn'"
		onblur="this.className='input'"
		name="upload_file[]"
		id="upload_file[]"
		size="30"
	/>
	</div>
	<div id="addmore">
		<a href="javascript:void(0);" onclick="insertRow('upload_file[]');">
			<img id="addNewArtwork" name="addNewArtwork" src="<?php echo IMG_PATH; ?>ico_createitems.gif" title="'.$lang->display('Add New Artwork').'" />
			<?php echo $lang->display('Add More'); ?>
		</a>
	</div>
	<p>
	<input
		type="submit"
		class="btnDo"
		onmousemove="this.className='btnOn'"
		onmouseout="this.className='btnDo'"
		title="<?php echo $lang->display('Submit'); ?>"
		value="<?php echo $lang->display('Submit'); ?>"
	/>
	</p>
	<input type="hidden" name="dir" value="<?php echo $dir; ?>">
	<input type="hidden" name="ftp" value="local">
	<input type="hidden" name="form" value="upload" />
</form>