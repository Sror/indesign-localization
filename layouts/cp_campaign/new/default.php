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
					<div class="ico"><?php echo '<img src="'.IMG_PATH.'header/ico_campaign.png">'; ?></div>
					<div class="txt"><?php echo $lang->display('Create New Campaign'); ?></div>
				</div>
				<div class="options">
					<!-- Save -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Save'); ?>">
						<a href="javascript:void(0);" onclick="validateForm('name','Campaign Title','R','brandID','Brand Name','R','langID','Source Language','R'); if(document.returnValue) { SubmitForm('newform','save'); }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_save.png">'; ?></div>
							<div><?php echo $lang->display('Save'); ?></div>
						</a>
					</div>
					<!-- Apply -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Apply'); ?>">
						<a href="javascript:void(0);" onclick="validateForm('name','Campaign Title','R','brandID','Brand Name','R','langID','Source Language','R'); if(document.returnValue) { SubmitForm('newform','apply'); }">
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
							<legend><?php echo $lang->display('Campaign Setup'); ?></legend>
							<table width="100%" cellpadding="3" cellspacing="0" border="0">
								<tr>
									<th>* <?php echo $lang->display('Campaign Title'); ?></th>
									<td>
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="name"
											id="name"
										>
									</td>
								</tr>
								<tr>
									<th>* <?php echo $lang->display('Brand Name'); ?></th>
									<td>
										<select
											class="input"
											name="brandID"
											id="brandID"
										>
										<?php BuildBrandList(); ?>
										</select>
									</td>
								</tr>
								<tr>
									<th>* <?php echo $lang->display('Source Language'); ?></th>
									<td>
										<select
											class="input"
											name="langID"
											id="langID"
										>
										<?php BuildLangList(); ?>
										</select>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Default Substitute Font'); ?>:</th>
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
									<th valign="top"><?php echo $lang->display('Default Image Folder'); ?>:</th>
									<td>
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="default_img_dir"
											id="default_img_dir"
											onclick="display('local_ftp');ResetDiv('local_ftp');DoAjax('companyID=<?php echo $_SESSION['companyID']; ?>','local_ftp','modules/mod_ftp_dir.php');"
											readonly="readonly"
											value="/"
										/>
										<a href="javascript:void(0);" onclick="setValue('default_img_dir','/');hidediv('local_ftp');"><?php echo $lang->display('Reset'); ?></a>
										<div id="local_ftp">
											<!-- Local ftp dir will appear here. -->
										</div>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Reference'); ?></th>
									<td>
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="ref"
											id="ref"
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
							<legend><?php echo $lang->display('User Access Control Level'); ?></legend>
							<?php BuildCampaignACL($_SESSION['companyID'],$_SESSION['userID']); ?>
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
<script type="text/javascript" src="javascripts/ajax.js"></script>