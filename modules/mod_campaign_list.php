<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("artworks","new");
require_once(MODULES.'mod_authorise.php');
?>
<table width="100%" border="0" cellspacing="0" cellpadding="3">
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
			<?php BuildActiveCampaignList($_SESSION['companyID'],$_SESSION['userID']); ?>
			</select>
		</td>
	</tr>
	<tr>
		<td></td>
		<td>
			<input
				type="button"
				class="btnDo"
				onmousemove="this.className='btnOn'"
				onmouseout="this.className='btnDo'"
				title="<?php echo $lang->display('Next'); ?>"
				value="<?php echo $lang->display('Next'); ?>"
				onclick="validateForm('CampaignID','Campaign','R');if(document.returnValue) DoAjax('id='+document.getElementById('CampaignID').value,'window','modules/mod_art_new.php');"
			/>
			<input
				type="reset"
				class="btnOff"
				onmousemove="this.className='btnOn'"
				onmouseout="this.className='btnOff'"
				title="<?php echo $lang->display('New Campaign'); ?>"
				value="<?php echo $lang->display('New Campaign'); ?>"
				onclick="DoAjax('','window','modules/mod_camp_new.php');"
			/>
		</td>
	</tr>
</table>