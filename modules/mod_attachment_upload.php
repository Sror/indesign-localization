<?php
require_once(dirname(__FILE__).'/../config.php');

$attachment = "";
$uploaded = false;
if(!empty($_FILES['attachment']['name'])) {
	$attachment = time()."_".$_FILES['attachment']['name'];
	$uploaded = @move_uploaded_file($_FILES['attachment']['tmp_name'], REPOSITORY_DIR.$attachment);
}
echo '<div style="font-family:Arial,Helvetica,sans-serif;font-size:11px;color:#333333;margin:0px;padding:0px;">';
echo $uploaded ? $_FILES['attachment']['name'] : $lang->display('Error Uploading File') ;
echo '</div>';
echo '<input type="hidden" id="attach" name="attach" value="'.$attachment.'">';
?>
<script language="javascript" type="text/javascript">
	top.document.getElementById('attachment').innerHTML = '<input type="file" class="input" onfocus="this.className=\'inputOn\'" onblur="this.className=\'input\'" id="attachment" name="attachment" size="8" /><input type="submit" class="btnDo" onmousemove="this.className=\'btnOn\'" onmouseout="this.className=\'btnDo\'" title="<?php echo $lang->display('Upload'); ?>" value="<?php echo $lang->display('Upload'); ?>" />';
	top.document.getElementById('upload_loading').style.display = 'none';
	top.document.getElementById('file_frame').style.display = 'block';
</script>