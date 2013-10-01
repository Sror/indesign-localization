<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("profile","changePass");
require_once(MODULES.'mod_authorise.php');

$id = !empty($_GET['id']) ? (int)$_GET['id'] : 0;
$query = sprintf("SELECT password
				FROM users
				WHERE userID = %d
				LIMIT 1",
				$id);
$result = mysql_query($query, $conn) or die(mysql_error());
if(!mysql_num_rows($result)) access_denied();
$row = mysql_fetch_assoc($result);
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
					<th width="30%">* <?php echo $lang->display('Current Password'); ?></th>
					<td width="70%">
						<input
							type="password"
							class="input"
							onfocus="this.className='inputOn'"
							onblur="this.className='input';compareFields('now_pwd','user_pwd','!=','<?php echo $lang->display('Incorrect password.'); ?>');return document.returnValue;"
							id="now_pwd"
							name="now_pwd"
							maxlength="20"
						/>
					</td>
				</tr>
				<tr>
					<th>* <?php echo $lang->display('New Password'); ?></th>
					<td>
						<input
							type="password"
							class="input"
							onfocus="this.className='inputOn'"
							onblur="this.className='input'"
							id="new_pwd"
							name="new_pwd"
							maxlength="20"
						/>
					</td>
				</tr>
				<tr>
					<th>* <?php echo $lang->display('Confirm Password'); ?></th>
					<td>
						<input
							type="password"
							class="input"
							onfocus="this.className='inputOn'"
							onblur="this.className='input';compareFields('new_pwd','con_pwd','!=','<?php echo $lang->display('New passwords do NOT match.'); ?>');return document.returnValue;"
							id="con_pwd"
							name="con_pwd"
							maxlength="20"
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
							onclick="validateForm('now_pwd','Current password','R','new_pwd','New password','R','con_pwd','Comfirm password','R');if(document.returnValue) SubmitForm('edit_form','password');"
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
		<input name="user_pwd" type="hidden" value="<?php echo $row['password']; ?>">
		<input name="form" type="hidden">
	</form>
</div>