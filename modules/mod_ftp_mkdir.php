<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("system","cpanel");
require_once(MODULES.'mod_authorise.php');

$ftp_id = isset($_GET['id']) ? $_GET['id'] : 0;
$dir = isset($_GET['dir']) ? $_GET['dir'] : "/";
$location = isset($_GET['location']) ? $_GET['location'] : "";
?>
<form
	name="mkdir_form"
	id="mkdir_form"
	action="javascript:void(0);"
	method="POST"
	enctype="multipart/form-data"
	onsubmit="validateForm('dir_name','Directory Name','R'); if(document.returnValue) { hidediv('helper'); hidediv('<?php echo $location; ?>_cache_info'); display('<?php echo $location; ?>_cache_loader'); DoAjax('id=<?php echo $ftp_id; ?>&dir=<?php echo $dir; ?>&do=mkdir&name='+document.mkdir_form.dir_name.value,'<?php echo $location; ?>_ftp','modules/mod_ftp_<?php echo $location; ?>.php'); ResetDiv('window'); }"
>
	<b><?php echo $lang->display('Make Directory'); ?>:</b>
	<input
		type="text"
		class="input"
		onfocus="this.className='inputOn'"
		onblur="this.className='input'"
		name="dir_name"
		id="dir_name"
	/>
	<input
		type="submit"
		class="btnDo"
		onmousemove="this.className='btnOn'"
		onmouseout="this.className='btnDo'"
		title="<?php echo $lang->display('Submit'); ?>"
		value="<?php echo $lang->display('Submit'); ?>"
	/>
	<input
		type="reset"
		class="btnOff"
		onmousemove="this.className='btnOn'"
		onmouseout="this.className='btnOff'"
		title="<?php echo $lang->display('Reset'); ?>"
		value="<?php echo $lang->display('Reset'); ?>"
	/>
</form>