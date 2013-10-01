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
					<div class="ico"><?php echo '<img src="'.IMG_PATH.'header/ico_ftp.png">'; ?></div>
					<div class="txt"><?php echo $lang->display('Create New FTP'); ?></div>
				</div>
				<div class="options">
					<!-- Save -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Save'); ?>">
						<a href="javascript:void(0);" onclick="validateForm('company_id','Company','R','ftp_host','FTP Host','R','ftp_username','FTP Username','R','ftp_password','FTP Password','R','ftp_port','FTP Port','RisNum','ftp_timeout','FTP Timeout','RisNum','ftp_dir','FTP Directory','R'); if(document.returnValue) { SubmitForm('newform','save'); }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_save.png">'; ?></div>
							<div><?php echo $lang->display('Save'); ?></div>
						</a>
					</div>
					<!-- Apply -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Apply'); ?>">
						<a href="javascript:void(0);" onclick="validateForm('company_id','Company','R','ftp_host','FTP Host','R','ftp_username','FTP Username','R','ftp_password','FTP Password','R','ftp_port','FTP Port','RisNum','ftp_timeout','FTP Timeout','RisNum','ftp_dir','FTP Directory','R'); if(document.returnValue) { SubmitForm('newform','apply'); }">
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
							<legend>FTP</legend>
							<table width="100%" cellpadding="3" cellspacing="0" border="0">
								<tr>
									<th>* <?php echo $lang->display('Company Name'); ?></th>
									<td>
										<select
											class="input"
											name="company_id"
											id="company_id"
										>
										<?php BuildCompanyList($_SESSION['companyID'],$issuperadmin); ?>
										</select>
									</td>
								</tr>
								<tr>
									<th>* <?php echo $lang->display('FTP Host'); ?></th>
									<td>
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="ftp_host"
											id="ftp_host"
										>
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
											name="ftp_username"
											id="ftp_username"
										>
									</td>
								</tr>
								<tr>
									<th>* <?php echo $lang->display('Password'); ?></th>
									<td>
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="ftp_password"
											id="ftp_password"
										>
									</td>
								</tr>
							</table>
						</fieldset>
					</div>
				</div>
				<div class="rightwrap">
					<div class="fieldset">
						<fieldset>
							<legend><?php echo $lang->display('Other Information'); ?></legend>
							<table width="100%" cellpadding="3" cellspacing="0" border="0">
								<tr>
									<th>* <?php echo $lang->display('FTP Port'); ?></th>
									<td>
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="ftp_port"
											id="ftp_port"
											value="21"
										>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Passive Mode'); ?></th>
									<td>
										<input
											type="checkbox"
											id="ftp_pasv"
											name="ftp_pasv"
											value="1"
											checked="checked"
										>
									</td>
								</tr>
								<tr>
									<th>* <?php echo $lang->display('Timeout Limit'); ?></th>
									<td>
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="ftp_timeout"
											id="ftp_timeout"
											value="90"
										>
									</td>
								</tr>
								<tr>
									<th>* <?php echo $lang->display('Default Directory'); ?></th>
									<td>
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="ftp_dir"
											id="ftp_dir"
											value="/"
										>
									</td>
								</tr>
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