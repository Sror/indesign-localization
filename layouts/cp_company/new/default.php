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
					<div class="txt"><?php echo $lang->display('Create New Company'); ?></div>
				</div>
				<div class="options">
					<!-- Save -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Save'); ?>">
						<a href="javascript:void(0);" onclick="validateForm('name','Company Name','R','packageID','Service Package','R','siteURL','Site URL','R','systemName','System Name','R','systemVersion','Version','R','templateName','Template','R','email','Email','isEmail','format_date','Date Format','R','format_time','Time Format','R'); if(document.returnValue) { SubmitForm('newform','save'); }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_save.png">'; ?></div>
							<div><?php echo $lang->display('Save'); ?></div>
						</a>
					</div>
					<!-- Apply -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Apply'); ?>">
						<a href="javascript:void(0);" onclick="validateForm('name','Company Name','R','packageID','Service Package','R','siteURL','Site URL','R','systemName','System Name','R','systemVersion','Version','R','templateName','Template','R','email','Email','isEmail','format_date','Date Format','R','format_time','Time Format','R'); if(document.returnValue) { SubmitForm('newform','apply'); }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_apply.png">'; ?></div>
							<div><?php echo $lang->display('Apply'); ?></div>
						</a>
					</div>
					<!-- Cancel -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Cancel'); ?>">
						<a href="javascript:void(0);" onclick="SubmitForm('newform','cancel');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_close.png">'; ?></div>
							<div><?php echo $lang->display('Cancel'); ?></div>
						</a>
					</div>
				</div>
				<div class="clear"></div>
			</div>
			<!-- Mainwrap -->
			<div class="mainwrap">
				<form
					id="newform"
					name="newform"
					action="index.php?layout=<?php echo $layout; ?>&task=<?php echo $task; ?>"
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
									<td width="60%">
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="name"
											id="name"
										/>
									</td>
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
										<?php BuildParentCompanyList(); ?>
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
											echo '<input type="checkbox" id="agency" name="agency" value="1">';
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
										<?php BuildSPList(); ?>
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
											value="localhost"
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
											value="<?php echo SYSTEM_NAME; ?>"
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
											value="<?php echo SYSTEM_VERSION; ?>"
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
										<?php BuildTemplateList(TEMPLATE_NAME); ?>
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
										<?php BuildCurrencyList(CURRENCY); ?>
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
											value="j M Y"
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
											value="j M Y, H:i"
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
											<?php BuildFontSubList(); ?>
										</select>
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
										/>
									</td>
								</tr>
							</table>
						</fieldset>
					</div>
					<div class="fieldset">
						<fieldset>
							<legend>ACL</legend>
							<?php BuildCompanyACL($_SESSION['companyID'],$issuperadmin); ?>
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