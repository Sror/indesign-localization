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
					<div class="txt"><?php echo $lang->display('Create New Translation Memory'); ?></div>
				</div>
				<div class="options">
					<!-- Save -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Save'); ?>">
						<a href="javascript:void(0);" onclick="validateForm('typeID','Type','R','sourceLangID','Source Language','R','sourcePara','Source Text','R','targetLangID','Desired Language','R','targetPara','Translation','R'); if(document.returnValue) { SubmitForm('newform','save'); }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_save.png">'; ?></div>
							<div><?php echo $lang->display('Save'); ?></div>
						</a>
					</div>
					<!-- Apply -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Apply'); ?>">
						<a href="javascript:void(0);" onclick="validateForm('typeID','Type','R','sourceLangID','Source Language','R','sourcePara','Source Text','R','targetLangID','Desired Language','R','targetPara','Translation','R'); if(document.returnValue) { SubmitForm('newform','apply'); }">
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
							<legend><?php echo $lang->display('Source Language'); ?></legend>
							<table width="100%" cellpadding="3" cellspacing="0" border="0">
								<tr>
									<td>
										<select
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="sourceLangID"
											id="sourceLangID"
											title="<?php echo $lang->display('Select Language'); ?>"
										>
										<?php
											$fromLangID = !empty($_GET['fromLangID']) ? $_GET['fromLangID'] : 0;
											BuildLangList($fromLangID);
										?>
										</select>
										<select
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="typeID"
											id="typeID"
											title="<?php echo $lang->display('Type'); ?>"
										>
										<?php BuildTMTypeList(PARA_USER); ?>
										</select>
										<select
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="brandID"
											id="brandID"
											title="<?php echo $lang->display('Select Brand'); ?>"
										>
										<?php BuildBrandList(); ?>
										</select>
										<select
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="subjectID"
											id="subjectID"
											title="<?php echo $lang->display('Select Subject'); ?>"
										>
										<?php BuildSubjectList(); ?>
										</select>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<textarea
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="sourcePara"
											id="sourcePara"
											rows="5"
											style="width:99%"
										><?php if(!empty($_GET['source'])) echo "\n".$_GET['source'];?></textarea>
									</td>
								</tr>
							</table>
						</fieldset>
					</div>
				</div>
				<div class="rightwrap">
					<div class="fieldset">
						<fieldset>
							<legend><?php echo $lang->display('Desired Language'); ?></legend>
							<table width="100%" cellpadding="3" cellspacing="0" border="0">
								<tr>
									<td>
										<select
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="targetLangID"
											id="targetLangID"
											title="<?php echo $lang->display('Select Language'); ?>"
										>
										<?php
											$toLangID = !empty($_GET['toLangID']) ? $_GET['toLangID'] : 0;
											BuildLangList($toLangID);
										?>
										</select>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<textarea
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="targetPara"
											id="targetPara"
											rows="5"
											style="width:99%"
										></textarea>
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