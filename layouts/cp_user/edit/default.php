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
					<div class="txt"><?php echo $lang->display('Edit').": ".$user_row['username']; ?></div>
				</div>
				<div class="options">
					<!-- Save -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Save'); ?>">
						<a href="javascript:void(0);" onclick="validateForm('companyID','Company','R','ugID','User Group','R','pwd','Password','R','vpwd','Password verification','R','forename','Forename','R','surname','Surname','R','email','Email','RisEmail'); if(document.returnValue) { validatePwd('pwd','vpwd'); if(document.returnValue) { SubmitForm('editform','save'); } }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_save.png">'; ?></div>
							<div><?php echo $lang->display('Save'); ?></div>
						</a>
					</div>
					<!-- Apply -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Apply'); ?>">
						<a href="javascript:void(0);" onclick="validateForm('companyID','Company','R','ugID','User Group','R','pwd','Password','R','vpwd','Password verification','R','forename','Forename','R','surname','Surname','R','email','Email','RisEmail'); if(document.returnValue) { validatePwd('pwd','vpwd'); if(document.returnValue) { SubmitForm('editform','apply'); } }">
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
										<?php BuildCompanyList($user_row['companyID'],$issuperadmin); ?>
										</select>
									</td>
								</tr>
								<tr>
									<th>* <?php echo $lang->display('Username'); ?></th>
									<td><b><?php echo $user_row['username']; ?></b></td>
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
											onchange="CheckTheBox('resetACL');"
										>
										<?php BuildUserGroupList($user_row['userGroupID'],$issuperadmin); ?>
										</select>
										<span class="span">
											<input
												id="resetACL"
												name="resetACL"
												type="checkbox"
												value="1"
											/> <?php echo $lang->display('Reset'); ?> ACL
										</span>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Password'); ?></th>
									<td>
										<input
											type="password"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="pwd"
											id="pwd"
											value="<?php echo $user_row['password']; ?>"
										/>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Verify Password'); ?></th>
									<td>
										<input
											type="password"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input';validatePwd('pwd','vpwd');return document.returnValue;"
											name="vpwd"
											id="vpwd"
											value="<?php echo $user_row['password']; ?>"
										/>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Active'); ?></th>
									<td>
										<?php
											echo '<input type="checkbox" id="active" name="active" value="1"';
											if($user_row['active']==1) echo ' checked="checked"';
											echo ' />';
										?>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Agent'); ?></th>
									<td>
										<?php
											echo '<input type="checkbox" id="agent" name="agent" value="1"';
											if($user_row['agent']==1) echo ' checked="checked"';
											echo ' />';
										?>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Global'); ?></th>
									<td>
										<?php
											echo '<input type="checkbox" id="global" name="global" value="0"';
											if($user_row['vtID']==0) echo ' checked="checked"';
											echo ' />';
										?>
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
										/>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Last Login'); ?></th>
									<td><?php echo date(FORMAT_TIME,strtotime($user_row['lastLogin'])); ?></td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Last IP'); ?></th>
									<td><?php echo $user_row['lastIP'];?></td>
								</tr>
							</table>
						</fieldset>
					</div>
					<div class="fieldset">
						<fieldset>
							<legend><?php echo $lang->display('Language Capability'); ?></legend>
							<table width="100%" cellpadding="3" cellspacing="0" border="0">
								<?php BuildLangEditList($id); ?>
							</table>
						</fieldset>
					</div>
					<div class="fieldset">
						<fieldset>
							<legend><?php echo $lang->display('Specialisations'); ?></legend>
							<table width="100%" cellpadding="3" cellspacing="0" border="0">
								<?php BuildSpecEditList($id); ?>
							</table>
						</fieldset>
					</div>
				</div>
				<div class="rightwrap">
					<div class="fieldset">
						<fieldset>
							<legend><?php echo $lang->display('Personal Details'); ?></legend>
							<table width="100%" cellpadding="3" cellspacing="0" border="0">
								<?php BuildProfileEditList($id); ?>
							</table>
						</fieldset>
					</div>
					<div class="fieldset">
						<fieldset>
							<legend><?php echo $lang->display('Credit Setup'); ?></legend>
							<?php BuildCreditAllowance($id); ?>
						</fieldset>
					</div>
					<div class="fieldset">
						<fieldset>
							<legend><?php echo $lang->display('Minimum Rate Per Word'); ?></legend>
							<table width="100%" cellpadding="3" cellspacing="0" border="0">
								<?php BuildRateEditList($id); ?>
							</table>
						</fieldset>
					</div>
					<div class="fieldset">
						<fieldset>
							<legend>ACL</legend>
							<table width="100%" cellpadding="3" cellspacing="0" border="0">
								<?php BuildACLEditList($acl,$id,$user_row['companyID'],$issuperadmin); ?>
							</table>
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