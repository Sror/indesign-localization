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
					<div class="ico"><?php echo '<img src="'.IMG_PATH.'header/ico_translation_memory.png">'; ?></div>
					<div class="txt"><?php echo $lang->display('Edit').': '.$lang->display('Paragraph'); ?></div>
				</div>
				<div class="options">
					<!-- Save -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Save'); ?>">
						<a href="javascript:void(0);" onclick="validateForm('langID','Language','R','paratext','Paragraph','R','words','Word Count','RisNum','type_id','Type','R'); if(document.returnValue) { SubmitForm('editform','save'); }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_save.png">'; ?></div>
							<div><?php echo $lang->display('Save'); ?></div>
						</a>
					</div>
					<!-- Apply -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Apply'); ?>">
						<a href="javascript:void(0);" onclick="validateForm('langID','Language','R','paratext','Paragraph','R','words','Word Count','RisNum','type_id','Type','R'); if(document.returnValue) { SubmitForm('editform','apply'); }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_apply.png">'; ?></div>
							<div><?php echo $lang->display('Apply'); ?></div>
						</a>
					</div>
					<!-- Lookup -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Lookup'); ?>">
						<a href="javascript:void(0);" onclick="SubmitForm('editform','lookup');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_search.png">'; ?></div>
							<div><?php echo $lang->display('Lookup'); ?></div>
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
							<legend><?php echo $lang->display('Paragraph Setup'); ?></legend>
							<table width="100%" cellpadding="3" cellspacing="0" border="0">
								<tr>
									<th>* <?php echo $lang->display('Language'); ?></th>
									<td>
										<select
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="langID"
											id="langID"
										>
										<?php BuildLangList($tm_row['LangID']); ?>
										</select>
									</td>
								</tr>
								<tr>
									<th>* <?php echo $lang->display('Type'); ?></th>
									<td>
										<select
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="type_id"
											id="type_id"
										>
										<?php BuildTMTypeList($tm_row['type_id']); ?>
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
										<?php BuildBrandList($tm_row['brand_id']); ?>
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
										<?php BuildSubjectList($tm_row['subject_id']); ?>
										</select>
									</td>
								</tr>
								<tr>
									<th valign="top">* <?php echo $lang->display('Paragraph'); ?></th>
									<td>
										<textarea
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="paratext"
											id="paratext"
											rows="5"
											cols="40"
										><?php echo "\n".$tm_row['ParaText']; ?></textarea>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Word Count'); ?></th>
									<td>
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="words"
											id="words"
											size="2"
											value="<?php echo $tm_row['Words']; ?>"
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
									<th><?php echo $lang->display('Owner'); ?></th>
									<td><?php echo '<a href="index.php?layout=user&id='.$tm_row['user_id'].'">'.$tm_row['username'].'</a>'; ?></td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Timestamp'); ?></th>
									<td><?php echo date(FORMAT_TIME,strtotime($tm_row['timeRef'])); ?></td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Usage'); ?></th>
									<td>
										<?php
											$query = sprintf("SELECT uID
															FROM paraset
															WHERE ParaID = %d",
															$id);
											$result = mysql_query($query, $conn) or die(mysql_error());
											echo $lang->display('Translation Memory').' ['.mysql_num_rows($result).']';

										?>
									</td>
								</tr>
								<tr>
									<th></th>
									<td>
										<?php
											$query = sprintf("SELECT refID
															FROM paratrans
															WHERE transParaID = %d",
															$id);
											$result = mysql_query($query, $conn) or die(mysql_error());
											echo $lang->display('Translation').' ['.mysql_num_rows($result).']';
										?>
									</td>
								</tr>
								<tr>
									<th valign="top">* <?php echo $lang->display('Notes'); ?></th>
									<td>
										<textarea
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="notes"
											id="notes"
											rows="5"
											cols="40"
										><?php echo "\n".$tm_row['notes']; ?></textarea>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Rating'); ?></th>
									<td>
										<select
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="rating"
											id="rating"
										>
										<?php BuildRatingScale(5,$tm_row['rating']); ?>
										</select>
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