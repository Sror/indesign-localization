<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("campaigns","new");
require_once(MODULES.'mod_authorise.php');
?>
<form
	action="index.php?layout=campaigns"
	name="new_camp_form"
	method="POST"
	enctype="multipart/form-data" 
	onsubmit="hidediv('helper');Popup('loadingme','waiting');"
>
	<table width="100%" border="0" cellspacing="0" cellpadding="5">
		<tr>
			<td class="highlight" width="30%">* <?php echo $lang->display('Campaign Title'); ?></td>
			<td width="70%">
				<input
					type="text"
					class="input"
					onfocus="this.className='inputOn'"
					onblur="this.className='input'"
					name="campaign_name"
					id="campaign_name"
				/>
			</td>
		</tr>
		<tr>
			<td class="highlight">* <?php echo $lang->display('Brand Name'); ?></td>
			<td>
				<select
					class="input"
					onfocus="this.className='inputOn'"
					onblur="this.className='input'"
					name="brand_id"
					id="brand_id"
				>
					<?php BuildBrandList(); ?>
				</select>
			</td>
		</tr>
		<tr>
			<td class="highlight">* <?php echo $lang->display('Source Language'); ?></td>
			<td>
				<select
					class="input"
					onfocus="this.className='inputOn'"
					onblur="this.className='input'"
					name="lang_id"
					id="lang_id"
				>
					<?php BuildLangList($_SESSION['userDefaultLangID']); ?>
				</select>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<div class="arrrgt" id="advanced" onclick="ChangeArrow('advanced');showandhide('advancedoptions');">
					<?php echo $lang->display('Advanced Options'); ?>
				</div>
				<div id="advancedoptions" class="greyBar" style="display:none;">
					<table width="100%" border="0" cellspacing="0" cellpadding="5">
						<tr>
							<td class="highlight" width="30%" valign="top"><?php echo $lang->display('User Access Control Level'); ?></td>
							<td width="70%">
								<?php BuildCampaignACL($_SESSION['companyID'],$_SESSION['userID']); ?>
							</td>
						</tr>
						<tr>
							<td class="highlight"><?php echo $lang->display('Default Substitute Font'); ?></td>
							<td>
								<select
									class="input"
									onfocus="this.className='inputOn'"
									onblur="this.className='input'"
									name="default_sub_font_id"
									id="default_sub_font_id"
								>
									<?php BuildFontSubList(); ?>
								</select>
							</td>
						</tr>
						<tr>
							<td class="highlight" valign="top"><?php echo $lang->display('Default Image Folder'); ?></td>
							<td>
								<input
									type="text"
									class="input"
									onfocus="this.className='inputOn'"
									onblur="this.className='input'"
									name="default_img_dir"
									id="default_img_dir"
									onclick="display('local_ftp');ResetDiv('local_ftp');DoAjax('companyID=<?php echo $_SESSION['companyID']; ?>&dir='+this.value,'local_ftp','modules/mod_ftp_dir.php');"
									readonly="readonly"
								/>
								<a href="javascript:void(0);" onclick="setValue('default_img_dir','');hidediv('local_ftp');"><?php echo $lang->display('Reset'); ?></a>
								<div id="local_ftp">
									<!-- Local ftp dir will appear here. -->
								</div>
							</td>
						</tr>
						<tr>
							<td class="highlight"><?php echo $lang->display('Reference'); ?></td>
							<td>
								<input
									type="text"
									class="input"
									onfocus="this.className='inputOn'"
									onblur="this.className='input'"
									name="ref"
									id="ref"
								/>
							</td>
						</tr>
					</table>
				</div>
			</td>
		</tr>
		<tr>
			<td class="highlight"></td>
			<td>
				<input
					type="submit"
					class="btnDo"
					onmousemove="this.className='btnOn'"
					onmouseout="this.className='btnDo'"
					title="<?php echo $lang->display('Create New Campaign'); ?>"
					value="<?php echo $lang->display('Create New Campaign'); ?>"
					onclick="validateForm('campaign_name','Campaign Title','R','brand_id','Brand','R','lang_id','Source language','R'); return document.returnValue;"
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
	<input name="update" type="hidden" value="new_camp_form">
</form>