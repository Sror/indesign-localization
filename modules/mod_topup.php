<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("system","superadmin");
require_once(MODULES.'mod_authorise.php');
?>
<div class="mainwrap">
	<form
		action="index.php?layout=cp_credit"
		name="topup_form"
		method="POST"
		enctype="multipart/form-data"
		onsubmit="hidediv('helper');Popup('loadingme','waiting');"
	>
		<div class="fieldset">
			<table width="100%" border="0" cellspacing="0" cellpadding="5">
				<tr>
					<th width="40%">* <?php echo $lang->display('Credits'); ?></th>
					<td width="60%">
						<select
							class="input"
							onfocus="this.className='inputOn'"
							onblur="this.className='input'"
							name="amount"
							id="amount"
						>
						<?php BuildCreditTopupList(1000); ?>
						</select>
					</td>
				</tr>
				<tr>
					<th></th>
					<td>
						<input
							type="submit"
							class="btnDo"
							onmousemove="this.className='btnOn'"
							onmouseout="this.className='btnDo'"
							title="<?php echo $lang->display('Top up'); ?>"
							value="<?php echo $lang->display('Top up'); ?>"
						/>
					</td>
				</tr>
			</table>
		</div>
		<input name="form" type="hidden" value="topup">
	</form>
</div>