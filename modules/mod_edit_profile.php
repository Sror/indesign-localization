<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("profile","editProfile");
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
				<?php BuildProfileEditList($id); ?>
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
							onclick="validateForm('forename','Forename','R','surname','Surname','R','email','Email','RisEmail');if(document.returnValue) SubmitForm('edit_form','profile');"
						/>
						<input 
							type="reset"
							class="btnOff"
							onmousemove="this.className='btnOn'"
							onmouseout="this.className='btnOff'"
							title="<?php echo $lang->display('Reset'); ?>"
							value="<?php echo $lang->display('Reset'); ?>"
						/>
					</td>
				</tr>
			</table>
		</div>
		<input name="form" type="hidden">
	</form>
</div>