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
					<div class="ico"><?php echo '<img src="'.IMG_PATH.'header/ico_company.png">'; ?></div>
					<div class="txt"><?php echo $lang->display('Edit').": ".$com_row['companyName']; ?></div>
				</div>
				<div class="options">
					<!-- Save -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Save'); ?>">
						<a href="javascript:void(0);" onclick="validateForm('packageID','Service Package','R','siteURL','Site URL','R','systemName','System Name','R','systemVersion','Version','R','templateName','Template','R','email','Email','isEmail','format_date','Date Format','R','format_time','Time Format','R'); if(document.returnValue) { SubmitForm('editform','save'); }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_save.png">'; ?></div>
							<div><?php echo $lang->display('Save'); ?></div>
						</a>
					</div>
					<!-- Apply -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Apply'); ?>">
						<a href="javascript:void(0);" onclick="validateForm('packageID','Service Package','R','siteURL','Site URL','R','systemName','System Name','R','systemVersion','Version','R','templateName','Template','R','email','Email','isEmail','format_date','Date Format','R','format_time','Time Format','R'); if(document.returnValue) { SubmitForm('editform','apply'); }">
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
							<legend><?php echo $lang->display('Company Setup'); ?></legend>
							<table width="100%" cellpadding="3" cellspacing="0" border="0">
								<tr>
									<th width="40%">* <?php echo $lang->display('Company Name'); ?></th>
									<td width="60%"><b><?php echo $com_row['companyName']; ?></b></td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Parent Company'); ?></th>
									<td>
										<select
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="parentID"
											id="parentID"
										>
										<?php BuildParentCompanyList($id,$com_row['parentCompanyID']); ?>
										</select>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Company Logo'); ?></th>
									<td>
										<input
											type="file"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="companyLogo"
											id="companyLogo"
										/>
										<?php BuildImgTypeList(); ?>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Agency'); ?></th>
									<td>
										<?php
											echo '<input type="checkbox" id="agency" name="agency" value="1"';
											if($com_row['agency']==1) echo ' checked="checked"';
											echo '>';
										?>
									</td>
								</tr>
								<tr>
									<th>* <?php echo $lang->display('Service Package'); ?></th>
									<td>
										<select
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="packageID"
											id="packageID"
										>
										<?php BuildSPList($com_row['packageID']); ?>
										</select>
									</td>
								</tr>
								<tr>
									<th>* <?php echo $lang->display('Site URL'); ?></th>
									<td>
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="siteURL"
											id="siteURL"
											value="<?php echo $com_row['siteURL']; ?>"
										/>
									</td>
								</tr>
								<tr>
									<th>* <?php echo $lang->display('System Name'); ?></th>
									<td>
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="systemName"
											id="systemName"
											value="<?php echo $com_row['system']; ?>"
										/>
									</td>
								</tr>
								<tr>
									<th>* <?php echo $lang->display('Version'); ?></th>
									<td>
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="systemVersion"
											id="systemVersion"
											value="<?php echo $com_row['systemVersion']; ?>"
										/>
									</td>
								</tr>
								<tr>
									<th>* <?php echo $lang->display('Template'); ?></th>
									<td>
										<select
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="templateName"
											id="templateName"
										>
										<?php BuildTemplateList($com_row['templateName']); ?>
										</select>
									</td>
								</tr>
								<tr>
									<th>* <?php echo $lang->display('Currency'); ?></th>
									<td>
										<select
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="currency"
											id="currency"
										>
										<?php BuildCurrencyList($com_row['currency']); ?>
										</select>
									</td>
								</tr>
								<tr>
									<th>* <?php echo $lang->display('Date Format'); ?></th>
									<td>
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="format_date"
											id="format_date"
											value="<?php echo $com_row['format_date']; ?>"
										/>
									</td>
								</tr>
								<tr>
									<th>* <?php echo $lang->display('Time Format'); ?></th>
									<td>
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="format_time"
											id="format_time"
											value="<?php echo $com_row['format_time']; ?>"
										/>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Default Substitute Font'); ?>:</td>
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
												BuildFontSubList(Font_Substitution::get_default_font($id)); ?>
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
											onclick="display('local_ftp');ResetDiv('local_ftp');DoAjax('companyID=<?php echo $com_row['companyID']; ?>&dir='+this.value,'local_ftp','modules/mod_ftp_dir.php');"
											readonly="readonly"
											value="<?php echo $com_row['default_img_dir']; ?>"
										/>
										<a href="javascript:void(0);" onclick="setValue('default_img_dir','');hidediv('local_ftp');"><?php echo $lang->display('Reset'); ?></a>
										<div id="local_ftp">
											<!-- Local ftp dir will appear here. -->
										</div>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Cover Logo'); ?></th>
									<td>
										<input
											type="file"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="logoFile"
											id="logoFile"
										/>
										<?php BuildImgTypeList(); ?>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Corner Logo'); ?></th>
									<td>
										<input
											type="file"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="smallLogoFile"
											id="smallLogoFile"
										/>
										<?php BuildImgTypeList(); ?>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('System Name'); ?> / FTP</th>
									<td><?php echo $com_row['ftp'];?></td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Credits'); ?></th>
									<td><?php echo $com_row['credits'];?></td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Users'); ?></th>
									<td><?php echo $com_row['userno'];?></td>
								</tr>
							</table>
						</fieldset>
					</div>
				</div>
				<div class="rightwrap">
					<div class="fieldset">
						<fieldset>
							<legend><?php echo $lang->display('Contacts'); ?></legend>
							<table width="100%" cellpadding="3" cellspacing="0" border="0">
								<tr>
									<th width="40%"><?php echo $lang->display('First Contact'); ?></th>
									<td width="60%">
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="contact"
											id="contact"
											value="<?php echo $com_row['firstContact']; ?>"
										/>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Address Line 1'); ?></th>
									<td>
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="address1"
											id="address1"
											value="<?php echo $com_row['addressLine1']; ?>"
										/>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Address Line 2'); ?></th>
									<td>
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="address2"
											id="address2"
											value="<?php echo $com_row['addressLine2']; ?>"
										/>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Address Line 3'); ?></th>
									<td>
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="address3"
											id="address3"
											value="<?php echo $com_row['addressLine3']; ?>"
										/>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Town'); ?></th>
									<td>
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="town"
											id="town"
											value="<?php echo $com_row['town']; ?>"
										/>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('County'); ?></th>
									<td>
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="county"
											id="county"
											value="<?php echo $com_row['county']; ?>"
										/>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Postcode'); ?></th>
									<td>
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="postcode"
											id="postcode"
											value="<?php echo $com_row['postcode']; ?>"
										/>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Country'); ?></th>
									<td>
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="country"
											id="country"
											value="<?php echo $com_row['country']; ?>"
										/>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Telephone'); ?></th>
									<td>
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="telephone"
											id="telephone"
											value="<?php echo $com_row['companyTelephone']; ?>"
										/>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Fax'); ?></th>
									<td>
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="fax"
											id="fax"
											value="<?php echo $com_row['companyFax']; ?>"
										/>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Email'); ?></th>
									<td>
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="email"
											id="email"
											value="<?php echo $com_row['companyEmail']; ?>"
										/>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Website'); ?></th>
									<td>
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="web"
											id="web"
											value="<?php echo $com_row['companyWeb']; ?>"
										/>
									</td>
								</tr>
							</table>
						</fieldset>
					</div>
					<div class="fieldset">
						<fieldset>
							<legend>ACL</legend>
							<?php BuildCompanyACL($com_row['companyID'],$issuperadmin); ?>
						</fieldset>
					</div>
				</div>
				<div class="clear"></div>
				<input type="hidden" name="now_companyLogo" id="now_companyLogo" value="<?php echo $com_row['companyLogo']; ?>">
				<input type="hidden" name="now_coverLogo" id="now_coverLogo" value="<?php echo $com_row['logoFile']; ?>">
				<input type="hidden" name="now_cornerLogo" id="now_cornerLogo" value="<?php echo $com_row['smallLogoFile']; ?>">
				<input type="hidden" name="form" id="form">
				</form>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="javascripts/ajax.js"></script>