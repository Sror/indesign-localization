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
					<div class="ico"><?php echo '<img src="'.IMG_PATH.'header/ico_subject.png">'; ?></div>
					<div class="txt"><?php echo $lang->display('Edit').": ".$lang->display($sub_row['subjectTitle']); ?></div>
				</div>
				<div class="options">
					<!-- Save -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Save'); ?>">
						<a href="javascript:void(0);" onclick="validateForm('streamID','Stream','R','subject','Subject','R'); if(document.returnValue) { SubmitForm('editform','save'); }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_save.png">'; ?></div>
							<div><?php echo $lang->display('Save'); ?></div>
						</a>
					</div>
					<!-- Apply -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Apply'); ?>">
						<a href="javascript:void(0);" onclick="validateForm('streamID','Stream','R','subject','Subject','R'); if(document.returnValue) { SubmitForm('editform','apply'); }">
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
				<div>
					<div class="fieldset">
						<fieldset>
							<legend><?php echo $lang->display('Subject Setup'); ?></legend>
							<table width="100%" cellpadding="3" cellspacing="0" border="0">
								<tr>
									<th>* <?php echo $lang->display('Stream'); ?></th>
									<td>
										<select
											class="input"
											name="streamID"
											id="streamID"
										>
										<?php
											echo '<option value="">'.$lang->display('Please Select').'...</option>';
											$query = sprintf("SELECT streamID, streamTitle
																FROM streams
																ORDER BY streamID ASC");
											$result = mysql_query($query, $conn) or die(mysql_error());
											while ($row = mysql_fetch_assoc($result)) {
												echo '<option value="'.$row['streamID'].'"';
												if($row['streamID']==$sub_row['streamID']) echo ' selected="selected"';
												echo '>'.$row['streamTitle'].'</option>';
											}
										?>
										</select>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Subject'); ?></th>
									<td>
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="subject"
											id="subject"
											value="<?php echo $sub_row['subjectTitle']; ?>"
										>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Users'); ?></th>
									<td><?php echo $sub_row['userno']; ?></td>
								</tr>
								<tr>
									<th><?php echo '<img src="'.IMG_PATH.'ico_error.png">'; ?></th>
									<td><b><?php echo $lang->display('Please contact system support to update language files accordingly.'); ?></b></td>
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