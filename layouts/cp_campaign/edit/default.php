<?php
$navStatus = array("cpanel");
require_once(MODULES.'mod_header.php');
BuildPageIntro($lang->display('Control Panel'),$lang->display('CP Intro'));
?>
<div id="wrapperWhite">
	<div class="controlScroll">
		<div class="controlselectScroll">
			<!-- Toolbar -->
			<div class="toolbar">
				<div class="title">
					<div class="ico"><?php echo '<img src="'.IMG_PATH.'header/ico_campaign.png">'; ?></div>
					<div class="txt"><?php echo $lang->display('Edit').": ".$camp_row['campaignName']; ?></div>
				</div>
				<div class="options">
					<!-- Save -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Save'); ?>">
						<a href="javascript:void(0);" onclick="validateForm('name','Campaign Title','R','brandID','Brand Name','R','langID','Source Language','R'); if(document.returnValue) { SubmitForm('editform','save'); }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_save.png">'; ?></div>
							<div><?php echo $lang->display('Save'); ?></div>
						</a>
					</div>
					<!-- Apply -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Apply'); ?>">
						<a href="javascript:void(0);" onclick="validateForm('name','Campaign Title','R','brandID','Brand Name','R','langID','Source Language','R'); if(document.returnValue) { SubmitForm('editform','apply'); }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_apply.png">'; ?></div>
							<div><?php echo $lang->display('Apply'); ?></div>
						</a>
					</div>
					<!-- Close -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Close'); ?>">
						<a href="javascript:void(0);" onclick="SubmitForm('editform','close');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_close.png">'; ?></div>
							<div><?php echo $lang->display('Close'); ?></div>
						</a>
					</div>
				</div>
				<div class="clear"></div>
			</div>
			<!-- Mainwrap -->
			<div class="mainwrap">
				<form
					id="editform"
					name="editform"
					action="index.php?layout=<?php echo $layout; ?>&task=<?php echo $task; ?>&id=<?php echo $id; ?>"
					method="POST"
					enctype="multipart/form-data"
				>
				<div class="leftwrap">
					<div class="fieldset">
						<fieldset>
							<legend><?php echo $lang->display('Campaign Setup'); ?></legend>
							<table width="100%" cellpadding="3" cellspacing="0" border="0">
								<tr>
									<th>* <?php echo $lang->display('Campaign Title'); ?></th>
									<td>
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="name"
											id="name"
											value="<?php echo $camp_row['campaignName']; ?>"
										>
									</td>
								</tr>
								<tr>
									<th>* <?php echo $lang->display('Brand Name'); ?></th>
									<td>
										<select
											class="input"
											name="brandID"
											id="brandID"
										>
										<?php BuildBrandList($camp_row['brandID'],$camp_row['companyID']); ?>
										</select>
									</td>
								</tr>
								<tr>
									<th>* <?php echo $lang->display('Source Language'); ?></th>
									<td>
										<select
											class="input"
											name="langID"
											id="langID"
										>
										<?php BuildLangList($camp_row['sourceLanguageID']); ?>
										</select>
									</td>
								</tr>
								<tr>
									<th>* <?php echo $lang->display('Status'); ?></th>
									<td>
										<select
											class="input"
											name="status"
											id="status"
										>
										<?php BuildCampStatusList($camp_row['campaignStatus']); ?>
										</select>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Default Substitute Font'); ?>:</th>
									<td>
										<select
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="default_sub_font_id"
											id="default_sub_font_id"
										>
										<?php BuildFontSubList($camp_row['default_sub_font_id']); ?>
										</select>
									</td>
								</tr>
								<tr>
									<th valign="top"><?php echo $lang->display('Default Image Folder'); ?>:</th>
									<td>
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="default_img_dir"
											id="default_img_dir"
											onclick="display('local_ftp');ResetDiv('local_ftp');DoAjax('companyID=<?php echo $camp_row['companyID']; ?>','local_ftp','modules/mod_ftp_dir.php');"
											readonly="readonly"
											value="<?php echo $camp_row['default_img_dir']; ?>"
										/>
										<a href="javascript:void(0);" onclick="setValue('default_img_dir','/');hidediv('local_ftp');"><?php echo $lang->display('Reset'); ?></a>
										<div id="local_ftp">
											<!-- Local ftp dir will appear here. -->
										</div>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Reference'); ?></th>
									<td>
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="ref"
											id="ref"
											value="<?php echo $camp_row['ref']; ?>"
										>
									</td>
								</tr>
							</table>
						</fieldset>
					</div>
					<div class="fieldset">
						<fieldset>
							<legend><?php echo $lang->display('Other Information'); ?></legend>
							<table width="100%" cellpadding="3" cellspacing="0" border="0">
								<tr>
									<th><?php echo $lang->display('Company'); ?></th>
									<td><?php echo '<a href="index.php?layout=company&id='.$camp_row['companyID'].'">'.$camp_row['company'].'</a>'; ?></td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Owner'); ?></th>
									<td><?php echo '<a href="index.php?layout=user&id='.$camp_row['ownerID'].'">'.$camp_row['owner'].'</a>'; ?></td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Artworks'); ?></th>
									<td><?php echo $camp_row['artworkno']; ?></td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Last Update'); ?></th>
									<td><?php echo date(FORMAT_TIME,strtotime($camp_row['lastupdate'])); ?></td>
								</tr>
							</table>
						</fieldset>
					</div>
				</div>
				<div class="rightwrap">
					<div class="fieldset">
						<fieldset>
							<legend><?php echo $lang->display('User Access Control Level'); ?></legend>
							<?php BuildCampaignACL($camp_row['companyID'],$camp_row['userID']); ?>
						</fieldset>
					</div>
				</div>
				<div class="clear"></div>
				<input type="hidden" name="form" id="form">
				</form>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="javascripts/ajax.js"></script>