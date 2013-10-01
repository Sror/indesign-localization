<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("campaigns","edit");
require_once(MODULES.'mod_authorise.php');

$ref = isset($_GET['ref']) ? (strpos($_GET['ref'],',') ? substr($_GET['ref'],0,strpos($_GET['ref'],',')) : $_GET['ref']) : 0;
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : "";

$artwork_count = $DB->count_campaign_artworks($ref);
$query = sprintf("SELECT campaigns.*, users.userID, users.companyID
				FROM campaigns
				LEFT JOIN users ON campaigns.ownerID = users.userID
				WHERE campaigns.campaignID = %d
				LIMIT 1",
				$ref);
$result = mysql_query($query, $conn) or die(mysql_error());
if(!mysql_num_rows($result)) access_denied();
$row = mysql_fetch_assoc($result);
?>
<?php if($artwork_count) BuildTipMsg($lang->display('Language option is disabled because some artworks have been uploaded.')); ?>
<form
	action="index.php?layout=campaign&id=<?php echo $ref; ?>"
	name="edit_camp_form"
	method="POST"
	enctype="multipart/form-data"
	onsubmit="hidediv('helper');Popup('loadingme','waiting');"
>
	<table width="100%" border="0" cellspacing="0" cellpadding="5">
		<tr>
			<td width="30%" class="highlight">* <?php echo $lang->display('Campaign Title'); ?></td>
			<td width="70%">
				<input
					type="text"
					class="input"
					onfocus="this.className='inputOn'"
					onblur="this.className='input'"
					name="campaign_name"
					id="campaign_name"
					value="<?php echo $row['campaignName']; ?>"
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
					<?php BuildBrandList($row['brandID']); ?>
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
					<?php if($artwork_count) echo 'disabled="disabled"'; ?>
				>
					<?php BuildLangList($row['sourceLanguageID']); ?>
				</select>
			</td>
		</tr>
		<tr>
			<td class="highlight">* <?php echo $lang->display('Status'); ?></td>
			<td>
				<select
					class="input"
					onfocus="this.className='inputOn'"
					onblur="this.className='input'"
					name="status"
					id="status"
				>
					<?php BuildCampStatusList($row['campaignStatus']); ?>
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
								<?php BuildCampaignACL($row['companyID'],$row['userID'],$ref); ?>
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
									<?php
										require_once(CLASSES.'Font_Substitution.php');
										BuildFontSubList(Font_Substitution::get_default_font($row['campaignID'],'campaign')); ?>
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
									value="<?php echo $row['default_img_dir']; ?>"
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
									value="<?php echo $row['ref']; ?>"
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
					title="<?php echo $lang->display('Edit Campaign Details'); ?>"
					value="<?php echo $lang->display('Edit Campaign Details'); ?>"
					onclick="validateForm('campaign_name','Campaign Title','R','brand_id','Brand','R','lang_id','Source language','R','status','Status','R');return document.returnValue;"
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
	<input name="redirect" type="hidden" value="<?php echo $redirect; ?>">
	<input name="update" type="hidden" value="edit_camp_form">
</form>