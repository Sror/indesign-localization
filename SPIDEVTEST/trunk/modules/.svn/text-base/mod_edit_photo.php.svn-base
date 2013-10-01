<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("profile","changePass");
require_once(MODULES.'mod_authorise.php');

$id = !empty($_GET['id']) ? (int)$_GET['id'] : 0;
?>
<div class="mainwrap">
	<form
		action="index.php?layout=profile"
		name="edit_form"
		method="POST"
		enctype="multipart/form-data" 
	>
		<div class="fieldset">
			<table width="100%" border="0" cellspacing="0" cellpadding="3">
				<tr>
					<th width="30%"><?php echo $lang->display('Upload Photo'); ?></th>
					<td width="70%">
						<input
							type="file"
							class="input"
							onfocus="this.className='inputOn'"
							onblur="this.className='input'"
							id="photoFile"
							name="photoFile"
						/>
						<?php BuildImgTypeList(); ?>
					</td>
				</tr>
				<tr>
					<th><?php echo $lang->display('Remove Photo'); ?></th>
					<td>
						<input
							type="checkbox"
							class="checkbox"
							id="delete"
							name="delete"
							value="1"
							title="<?php echo $lang->display('Remove Photo'); ?>"
						/>
					</td>
				</tr>
				<tr>
					<th></th>
					<td>
						<input 
							type="button"
							class="btnDo"
							onmousemove="this.className='btnOn'"
							onmouseout="this.className='btnDo'"
							title="<?php echo $lang->display('Update'); ?>"
							value="<?php echo $lang->display('Update'); ?>"
							onclick="SubmitForm('edit_form','photo');"
						/>
					</td>
				</tr>
			</table>
		</div>
		<input name="form" type="hidden">
	</form>
</div>