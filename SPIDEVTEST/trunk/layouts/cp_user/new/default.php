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
					<div class="ico"><?php echo '<img src="'.IMG_PATH.'header/ico_user.png">'; ?></div>
					<div class="txt"><?php echo $lang->display('Create New User'); ?></div>
				</div>
				<div class="options">
					<!-- Save -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Save'); ?>">
						<a href="javascript:void(0);" onclick="validateForm('companyID','Company','R','username','Username','R','ugID','User Group','R','pwd','Password','R','vpwd','Password verification','R','forename','Forename','R','surname','Surname','R','email','Email','RisEmail'); if(document.returnValue) { validatePwd('pwd','vpwd'); if(document.returnValue) { SubmitForm('newform','save'); } }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_save.png">'; ?></div>
							<div><?php echo $lang->display('Save'); ?></div>
						</a>
					</div>
					<!-- Apply -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Apply'); ?>">
						<a href="javascript:void(0);" onclick="validateForm('companyID','Company','R','username','Username','R','ugID','User Group','R','pwd','Password','R','vpwd','Password verification','R','forename','Forename','R','surname','Surname','R','email','Email','RisEmail'); if(document.returnValue) { validatePwd('pwd','vpwd'); if(document.returnValue) { SubmitForm('newform','apply'); } }">
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
							<legend><?php echo $lang->display('Account Setup'); ?></legend>
							<table width="100%" cellpadding="3" cellspacing="0" border="0">
								<tr>
									<th width="40%">* <?php echo $lang->display('Company'); ?></th>
									<td width="60%">
										<select
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="companyID"
											id="companyID"
										>
										<?php BuildCompanyList($_SESSION['companyID'],$issuperadmin); ?>
										</select>
									</td>
								</tr>
								<tr>
									<th>* <?php echo $lang->display('Username'); ?></th>
									<td>
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="username"
											maxlength="50"
										>
									</td>
								</tr>
								<tr>
									<th>* <?php echo $lang->display('User Group'); ?></th>
									<td>
										<select
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="ugID"
											id="ugID"
										>
										<?php BuildUserGroupList(0,$issuperadmin); ?>
										</select>
									</td>
								</tr>
								<tr>
									<th>* <?php echo $lang->display('Password'); ?></th>
									<td>
										<input
											type="password"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="pwd"
											id="pwd"
										>
									</td>
								</tr>
								<tr>
									<th>* <?php echo $lang->display('Verify Password'); ?></th>
									<td>
										<input
											type="password"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="vpwd"
											id="vpwd"
										>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Active'); ?></th>
									<td>
										<input type="checkbox" id="active" name="active" value="1" checked="checked">
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Agent'); ?></th>
									<td>
										<input type="checkbox" id="agent" name="agent" value="1">
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Global'); ?></th>
									<td>
										<input type="checkbox" id="global" name="global" value="0">
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Send Email Notifications'); ?></th>
									<td>
										<input
											id="emailOption"
											name="emailOption"
											type="checkbox"
											value="1"
											checked="checked"
										/>
									</td>
								</tr>
							</table>
						</fieldset>
					</div>
				</div>
				<div class="rightwrap">
					<div class="fieldset">
						<fieldset>
							<legend><?php echo $lang->display('Personal Details'); ?></legend>
							<table width="100%" cellpadding="3" cellspacing="0" border="0">
								<tr>
									<th width="40%">* <?php echo $lang->display('Language Setup'); ?></th>
									<td width="60%">
										<select
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="langID"
											id="langID"
										>
										<?php BuildLangOptions(DEFAULT_LANGUAGE); ?>
										</select>
									</td>
								</tr>
								<tr>
									<th>* <?php echo $lang->display('Default Language'); ?></th>
									<td>
										<select
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="defaultLangID"
											id="defaultLangID"
										>
										<?php BuildDefaultLangOptions(DEFAULT_LANGUAGE); ?>
										</select>
									</td>
								</tr>
								<tr>
									<th>* <?php echo $lang->display('Forename'); ?></th>
									<td>
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="forename"
											id="forename"
										>
									</td>
								</tr>
								<tr>
									<th>* <?php echo $lang->display('Surname'); ?></th>
									<td>
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="surname"
											id="surname"
										>
									</td>
								</tr>
								<tr>
									<th>* <?php echo $lang->display('Email'); ?></th>
									<td>
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="email"
											id="email"
										>
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
										>
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
										>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Mobile'); ?></th>
									<td>
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="mobile"
											id="mobile"
										>
									</td>
								</tr>
							</table>
						</fieldset>
					</div>
					<div class="fieldset">
						<fieldset>
							<legend><?php echo $lang->display('Credit Setup'); ?></legend>
							<?php BuildCreditAllowance(); ?>
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