<?php
BuildHelperDiv($lang->display('Create New Tasks').' - '.$lang->display('Step 2 of 3'));
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
						<div class="intro"><?php echo $lang->display('Task Home Intro'); ?></div>
					</div>
				</div>
				<div class="options">
					<!-- Back -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Back'); ?>">
						<a href="javascript:void(0);" onclick="goToURL('parent','index.php?layout=artwork&id=<?php echo $artworkID; ?>&task=step1');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_prev_on.png">'; ?></div>
							<div><?php echo $lang->display('Back'); ?></div>
						</a>
					</div>
					<!-- Next -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Next'); ?>">
						<a href="javascript:void(0);" onclick="<?php if($_SESSION['task_type']=="agent") echo "if(jQueryCheckLanguageSelection('languageoptions')) { validateForm('agencyID','Agency','R','agentID','Agent','R','deadline','Deadline','R'); if(document.returnValue) SubmitForm('newform','next'); } else return false;"; else if(empty($_SESSION['tasks'])) echo "alert('Please add at least one language.');"; else echo "SubmitForm('newform','next');"; ?>">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_next_on.png">'; ?></div>
							<div><?php echo $lang->display('Next'); ?></div>
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
					action="index.php?layout=artwork&id=<?php echo $artworkID; ?>&task=step2"
					method="POST"
					enctype="multipart/form-data"
				>
					<div class="artworkdetailPanel">
						<div class="handle">
							<div class="left"><?php echo $lang->display('New Task Step 2 Title'); ?></div>
							<div class="right"><?php BuildStepIndicator(2); ?></div>
							<div class="clear"></div>
						</div>
						<div class="window">
							<?php if($_SESSION['task_type'] == "default") { ?>
							<div id="tasklist"><?php require_once(MODULES.'mod_new_task_details.php'); ?></div>
							<hr />
							<div id="add_new_lang_btn">
								<?php echo '<img src="'.IMG_PATH.'ico_addnew.png">'; ?>
								<input
									type="button"
									class="btnDo"
									onmousemove="this.className='btnOn'"
									onmouseout="this.className='btnDo'"
									title="<?php echo $lang->display('Add New Language'); ?>"
									value="<?php echo $lang->display('Add New Language'); ?>"
									onclick="openandclose('add_new_lang_btn');openandclose('add_new_lang_div');"
								/>
							</div>
							<fieldset id="add_new_lang_div" style="display:none;">
								<legend><?php echo $lang->display('Add New Language'); ?></legend>
								<table width="100%" cellspacing="0" cellpadding="3" border="0">
									<tr>
										<td width="30%" class="highlight"><?php echo $lang->display('Use Story Group'); ?>:</td>
										<td width="70%">
                                                                                    <select
                                                                                            class="input"
                                                                                            onfocus="this.className='inputOn'"
                                                                                            onblur="this.className='input'"
                                                                                            name="storyGroup"
                                                                                            id="storyGroup"
                                                                                            title="<?php echo $lang->display('Select Story Group'); ?>"
                                                                                    >
                                                                                    <?php BuildStoryGroupList($artworkID,0); ?>
                                                                                    </select>
										</td>
									</tr>
									<tr>
										<td width="30%" class="highlight">Task Type</td>
										<td width="70%">
                                                                                    <select
                                                                                            class="input"
                                                                                            onfocus="this.className='inputOn'"
                                                                                            onblur="this.className='input'"
                                                                                            onchange="if (this.selectedIndex == 0) { document.getElementById('paraID_container').style.display = 'none'; } else { document.getElementById('paraID_container').style.display = 'inline'; } "
                                                                                            name="taskType"
                                                                                            id="taskType"
                                                                                            title="<?php echo $lang->display('Task Type'); ?>"
                                                                                    >
                                                                                    <?php echo $ttq_html; ?>
                                                                                    </select>
                                                                                    
                                                                                    <span id="paraID_container" style="display: none;">
                                                                                    	<br />
                     																	<select
                                                                                            class="input"
                                                                                            onfocus="this.className='inputOn'"
                                                                                            onblur="this.className='input'"
                                                                                            name="paraID"
                                                                                            id="paraID"
                                                                                        
                                                                                            title="Choose a document paragraph"
                                                                                    	>
                                                                                    	<?php echo $mt_html; ?>
                                                                                    
                                                                                    </select>
                                                                                    </span>
										</td>
									</tr>
									<tr>
										<td width="30%" class="highlight">* <?php echo $lang->display('Desired Language'); ?>:</td>
										<td width="70%">
											<select
												class="input"
												onfocus="this.className='inputOn'"
												onblur="this.className='input'"
												name="desiredLanguageID"
												id="desiredLanguageID"
												onChange="ResetDiv('mappedTranslators');DoAjax('source_lang_id=<?php echo $artwork_row['sourceLanguageID']; ?>&target_lang_id='+this.value+'&subject_id=<?php echo $artwork_row['subjectID']; ?>&word_count=<?php echo $artwork_row['wordCount']; ?>','mappedTranslators','modules/mod_new_task_tender.php');"
											>
											<?php BuildTargetLangList($artwork_row['sourceLanguageID']); ?>
											</select>
										</td>
									</tr>
									<tr>
										<td colspan="2">
											<div id="mappedTranslators"></div>
										</td>
									</tr>
									<tr>
										<td class="highlight">* <?php echo $lang->display('Deadline')." (".$lang->display('Sign-off').")"; ?>:</td>
										<td>
											<input
												type="text"
												class="input"
												onfocus="this.className='inputOn'"
												onblur="this.className='input'"
												name="deadline"
												id="deadline"
												onclick="displayDatePicker('deadline')"
												readonly="readonly"
												value="<?php echo $task_deadline; ?>"
											/>
											<a href="javascript:void(0);" onclick="displayDatePicker('deadline');"><img src="<?php echo IMG_PATH; ?>ico_calendar.gif"></a>
										</td>
									</tr>
									<tr>
										<td class="highlight" valign="top"><?php echo $lang->display('Notes'); ?>:</td>
										<td>
											<textarea
												class="input"
												onfocus="this.className='inputOn'"
												onblur="this.className='input'"
												name="notes"
												id="notes"
												rows="3"
												cols="80"
											></textarea>
										</td>
									</tr>
									<tr>
										<td></td>
										<td>
											<input
												type="button"
												class="btnDo"
												onmousemove="this.className='btnOn'"
												onmouseout="this.className='btnDo'"
												title="<?php echo $lang->display('Save to List'); ?>"
												value="<?php echo $lang->display('Save to List'); ?>"
												onclick="validateForm('desiredLanguageID','Desired language','R','deadline','Deadline','R'); if(document.returnValue) SubmitForm('newform','save');"
											/>
											<input
												type="button"
												class="btnOff"
												onmousemove="this.className='btnOn'"
												onmouseout="this.className='btnOff'"
												title="<?php echo $lang->display('Cancel'); ?>"
												value="<?php echo $lang->display('Cancel'); ?>"
												onclick="openandclose('add_new_lang_btn');openandclose('add_new_lang_div');"
											/>
										</td>
									</tr>
								</table>
							</fieldset>
							<?php } ?>
							<?php if($_SESSION['task_type'] == "agent") { ?>
							<table width="100%" cellspacing="0" cellpadding="3" border="0">
								<tr>
									<td width="30%" class="highlight" valign="top">* <?php echo $lang->display('Desired Language'); ?>:</td>
									<td width="70%"><div id="languageoptions"><?php BuildTargetLangOption($artwork_row['sourceLanguageID'], $task_langs, 4); ?></div></td>
								</tr>
								<tr>
									<td class="highlight">* <?php echo $lang->display('Agency'); ?>:</td>
									<td>
										<select
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="agencyID"
											id="agencyID"
											onchange="ResetDiv('mappedAgents');DoAjax('agency_id='+this.value,'mappedAgents','modules/mod_task_agent.php')"
										>
										<?php BuildAgencyList($_SESSION['companyID'], $agencyID); ?>
										</select>
									</td>
								</tr>
								<tr>
									<td class="highlight">* <?php echo $lang->display('Agent'); ?>:</td>
									<td>
										<div id="mappedAgents">
											<?php
												$agency_id = $agencyID;
												$agent_id = $agentID;
												require(MODULES.'mod_task_agent.php');
											?>
										</div>
									</td>
								</tr>
								<tr>
									<td class="highlight">* <?php echo $lang->display('Deadline')." (".$lang->display('Sign-off').")"; ?>:</td>
									<td>
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="deadline"
											id="deadline"
											onclick="displayDatePicker('deadline')"
											readonly="readonly"
											value="<?php echo $task_deadline; ?>"
										/>
										<a href="javascript:void(0);" onclick="displayDatePicker('deadline');"><img src="<?php echo IMG_PATH; ?>ico_calendar.gif"></a>
									</td>
								</tr>
							</table>
							<?php } ?>
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