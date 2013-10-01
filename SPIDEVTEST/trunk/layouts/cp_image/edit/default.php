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
					<div class="ico"><?php echo '<img src="'.IMG_PATH.'header/ico_image.png">'; ?></div>
					<div class="txt"><?php echo $lang->display('Edit'); ?></div>
				</div>
				<div class="options">
					<!-- Save -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Save'); ?>">
						<a href="javascript:void(0);" onclick="validateForm('name','Artwork Title','R','campaignID','Campaign Title','R','version','Version','R'); if(document.returnValue) { SubmitForm('editform','save'); }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_save.png">'; ?></div>
							<div><?php echo $lang->display('Save'); ?></div>
						</a>
					</div>
					<!-- Apply -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Apply'); ?>">
						<a href="javascript:void(0);" onclick="validateForm('name','Artwork Title','R','campaignID','Campaign Title','R','version','Version','R'); if(document.returnValue) { SubmitForm('editform','apply'); }">
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
							<legend><?php echo $lang->display('Images'); ?></legend>
							<table width="100%" cellpadding="3" cellspacing="0" border="0">
								<tr>
									<td colspan="2">
										<?php
											$img_content = $img_row['content'];
											$tmp_file = ROOT.TMP_DIR.basename($img_content);
											if(!file_exists($tmp_file)) {
												copy($img_content, $tmp_file);
											}
											if(!empty($img_content) && file_exists($tmp_file)) {
												$preview = TMP_DIR.basename($img_content);
											} else {
												$preview = IMG_PATH.'img_missing.png';
											}
											echo '<div class="preview"><img class="preview" src="'.$preview.'" /></div>';
										?>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Replacement'); ?></th>
									<td>
										<input
											type="file"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="replacement"
											id="replacement"
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
							<legend><?php echo $lang->display('Other Information'); ?></legend>
							<table width="100%" cellpadding="3" cellspacing="0" border="0">
								<tr>
									<th><?php echo $lang->display('Uploaded by'); ?></th>
									<td><?php echo $img_row['username']; ?></td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Last Update'); ?></th>
									<td><?php echo date(FORMAT_TIME,strtotime($img_row['time'])); ?></td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Source Language'); ?></th>
									<td>
										<select
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="lang_id"
											id="lang_id"
										>
										<?php BuildLangList($img_row['lang_id']); ?>
										</select>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Subject'); ?></th>
									<td>
										<select
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="subject_id"
											id="subject_id"
										>
										<?php BuildSubjectList($img_row['subject_id']); ?>
										</select>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Brand Name'); ?></th>
									<td>
										<select
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="brand_id"
											id="brand_id"
										>
										<?php BuildBrandList($img_row['brand_id']); ?>
										</select>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Reference'); ?></th>
									<td><?php echo $img_row['hash']; ?></td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Usage'); ?></th>
									<td><?php echo $img_row['imgusage']; ?></td>
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