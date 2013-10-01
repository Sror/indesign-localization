<?php
BuildHelperDiv($lang->display('Create New Tasks').' - '.$lang->display('Step 3 of 3'));
$navStatus = array("campaigns");
require_once(MODULES.'mod_header.php');
BuildPageIntro('<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=campaigns\');">'.$lang->display('Campaigns').'</a>'.BREADCRUMBS_ARROW.'<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=campaign&id='.$artwork_row['campaignID'].'\');">'.DisplayString($artwork_row['campaignName']).'</a>'.BREADCRUMBS_ARROW.'<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=artwork&id='.$artworkID.'\');">'.$artworkName.'</a>'.BREADCRUMBS_ARROW.$lang->display('Create New Tasks'));
?>
<div id="wrapperWhite">
	<div class="controlScroll">
		<div class="controlselectScroll">
			<!-- Toolbar -->
			<div class="toolbar">
				<div class="title">
					<div class="ico">
						<?php echo '<img src="'.IMG_PATH.'header/ico_task.png">'; ?>
					</div>
					<div class="txt">
						<?php echo $lang->display('Create New Task'); ?>
						<div class="intro"><?php echo $lang->display('Start your translation task, online or offline.'); ?></div>
					</div>
				</div>
				<div class="options">
					<!-- Back -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Back'); ?>">
						<a href="javascript:void(0);" onclick="goToURL('parent','index.php?layout=artwork&id=<?php echo $artworkID; ?>&task=step2');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_prev_on.png">'; ?></div>
							<div><?php echo $lang->display('Back'); ?></div>
						</a>
					</div>
					<!-- Save -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Confirm'); ?>">
						<a href="javascript:void(0);" onclick="SubmitForm('newform','save');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_apply.png">'; ?></div>
							<div><?php echo $lang->display('Confirm'); ?></div>
						</a>
					</div>
					<!-- Cancel -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Cancel'); ?>">
						<a href="javascript:void(0);" onclick="goToURL('parent','index.php?layout=artwork&id=<?php echo $artworkID; ?>');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_close.png">'; ?></div>
							<div><?php echo $lang->display('Cancel'); ?></div>
						</a>
					</div>
				</div>
				<div class="clear"></div>
			</div>
			<div class="mainwrap">
				<form
					id="newform"
					name="newform"
					action="index.php?layout=artwork&id=<?php echo $artworkID; ?>&task=step3"
					method="POST"
					enctype="multipart/form-data"
				>
					<div class="artworkdetailPanel">
						<div class="handle">
							<div class="left"><?php echo $lang->display('New Task Step 3 Title'); ?></div>
							<div class="right"><?php BuildStepIndicator(3); ?></div>
							<div class="clear"></div>
						</div>
						<div class="window">
							<table width="100%" cellspacing="0" cellpadding="3" border="0">
								<tr>
									<td class="highlight" valign="top"><?php echo $lang->display('Job Brief'); ?>:</td>
									<td>
										<textarea
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="brief"
											id="brief"
											rows="3"
											cols="80"
										></textarea>
									</td>
								</tr>
								<tr>
									<td class="highlight"><?php echo $lang->display('Attachment'); ?>:</td>
									<td>
										<input
											type="file"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="attachment"
											id="attachment"
										/>
									</td>
								</tr>
								<tr>
									<td class="highlight"></td>
									<td>
										<input
											type="checkbox"
											name="trial"
											id="trial"
											value="1"
										/>
										<?php echo $lang->display('This is a trial run that only deals with headings.'); ?>
                                        <?php if($_SESSION['task_type'] == "default") { ?>
										<br />
										<input
											id="startOption"
											name="startOption"
											type="checkbox"
											value="1"
										/>
										<?php echo $lang->display('No Task Start'); ?>
                                        <?php } ?>
									</td>
								</tr>
							</table>
						</div>
					</div>
					<input type="hidden" name="form" id="form">
				</form>
			</div>
		</div>
	</div>
</div>
<?php require_once(MODULES.'mod_footer.php'); ?>
<script type="text/javascript" src="javascripts/ajax.js"></script>
<script type="text/javascript" src="javascripts/datepicker.js"></script>
<script type="text/javascript" src="javascripts/jscolor/jscolor.js"></script>
<script type="text/javascript" src="javascripts/dom_drag.js"></script>
<script language="javascript">Drag.init(document.getElementById('handle'),document.getElementById('root'));</script>