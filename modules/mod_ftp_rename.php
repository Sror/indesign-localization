<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("system","cpanel");
require_once(MODULES.'mod_authorise.php');

$ftp_id = isset($_GET['id']) ? $_GET['id'] : 0;
$ref = isset($_GET['ref']) ? substr($_GET['ref'],0,strpos($_GET['ref'],',')) : 0;
$location = isset($_GET['location']) ? $_GET['location'] : "";

$query = sprintf("SELECT `ftp_cache_%s`.id, `ftp_cache_%s`.name,
				`ftp_cache_%s_dir`.`dir`
				FROM `ftp_cache_%s`
				LEFT JOIN `ftp_cache_%s_dir` ON `ftp_cache_%s_dir`.`id` = `ftp_cache_%s`.`dir_id`
				WHERE `ftp_cache_%s`.id = %d
				LIMIT 1",
				mysql_real_escape_string($location),
				mysql_real_escape_string($location),
				mysql_real_escape_string($location),
				mysql_real_escape_string($location),
				mysql_real_escape_string($location),
				mysql_real_escape_string($location),
				mysql_real_escape_string($location),
				mysql_real_escape_string($location),
				$ref);
$result = mysql_query($query,$conn) or die(mysql_error());
$row = mysql_fetch_assoc($result);
?>
<form
	name="rename_form"
	id="rename_form"
	action="javascript:void(0);"
	method="POST"
	enctype="multipart/form-data"
	onsubmit="validateForm('item_name','Name','R'); if(document.returnValue) { hidediv('helper'); hidediv('<?php echo $location; ?>_cache_info'); display('<?php echo $location; ?>_cache_loader'); DoAjax('id=<?php echo $ftp_id; ?>&dir=<?php echo $row['dir']; ?>&do=rename&ref=<?php echo $row['id']; ?>&name='+document.rename_form.item_name.value,'<?php echo $location; ?>_ftp','modules/mod_ftp_<?php echo $location; ?>.php'); ResetDiv('window'); }"
>
	<b><?php echo $lang->display('Rename'); ?>:</b>
	<input
		type="text"
		class="input"
		onfocus="this.className='inputOn'"
		onblur="this.className='input'"
		name="item_name"
		id="item_name"
		value="<?php echo $row['name']; ?>"
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