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
					<div class="txt"><?php echo $lang->display('Edit')." FTP"; ?></div>
				</div>
				<div class="options">
					<!-- Save -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Save'); ?>">
						<a href="javascript:void(0);" onclick="validateForm('company_id','Company','R','ftp_host','FTP Host','R','ftp_username','FTP Username','R','ftp_password','FTP Password','R','ftp_port','FTP Port','RisNum','ftp_timeout','FTP Timeout','RisNum','ftp_dir','FTP Directory','R'); if(document.returnValue) { SubmitForm('editform','save'); }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_save.png">'; ?></div>
							<div><?php echo $lang->display('Save'); ?></div>
						</a>
					</div>
					<!-- Apply -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Apply'); ?>">
						<a href="javascript:void(0);" onclick="validateForm('company_id','Company','R','ftp_host','FTP Host','R','ftp_username','FTP Username','R','ftp_password','FTP Password','R','ftp_port','FTP Port','RisNum','ftp_timeout','FTP Timeout','RisNum','ftp_dir','FTP Directory','R'); if(document.returnValue) { SubmitForm('editform','apply'); }">
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
										<?php BuildCompanyList($ftp_row['company_id'],$issuperadmin); ?>
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
											value="<?php echo $ftp_row['ftp_host']; ?>"
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
											value="<?php echo $ftp_row['ftp_username']; ?>"
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
											value="<?php echo $ftp_row['ftp_password']; ?>"
										>
									</td>
								</tr>
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
											value="<?php echo $ftp_row['ftp_port']; ?>"
										>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Passive Mode'); ?></th>
									<td>
										<?php
											echo '<input type="checkbox" id="ftp_pasv" name="ftp_pasv" value="1"';
											if($ftp_row['ftp_pasv']==1) echo ' checked="checked"';
											echo '>';
										?>
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
											value="<?php echo $ftp_row['ftp_timeout']; ?>"
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
											value="<?php echo $ftp_row['ftp_dir']; ?>"
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
									<th><?php echo $lang->display('FTP Memo'); ?></th>
									<td>
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="ftp_memo"
											id="ftp_memo"
											value="<?php echo $ftp_row['ftp_memo']; ?>"
										>
									</td>
								</tr>
                                <tr>
									<th><?php echo $lang->display('Public'); ?></th>
									<td>
										<?php
											echo '<input type="checkbox" id="public" name="public" value="1"';
											if($ftp_row['public']==1) echo ' checked="checked"';
											echo '>';
										?>
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