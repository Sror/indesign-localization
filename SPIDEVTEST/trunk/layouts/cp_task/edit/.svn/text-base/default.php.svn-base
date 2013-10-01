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
					<div class="ico"><?php echo '<img src="'.IMG_PATH.'header/ico_artwork.png">'; ?></div>
					<div class="txt"><?php echo $lang->display('Edit').": ".$lang->display('Task Details'); ?></div>
				</div>
				<div class="options">
					<!-- Save -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Save'); ?>">
						<a href="javascript:void(0);" onclick="validateForm('deadline','Deadline','R','taskstatus','Task Status','R'); if(document.returnValue) { SubmitForm('editform','save'); }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_save.png">'; ?></div>
							<div><?php echo $lang->display('Save'); ?></div>
						</a>
					</div>
					<!-- Apply -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Apply'); ?>">
						<a href="javascript:void(0);" onclick="validateForm('deadline','Deadline','R','taskstatus','Task Status','R'); if(document.returnValue) { SubmitForm('editform','apply'); }">
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
							<legend><?php echo $lang->display('Task Details'); ?></legend>
							<table width="100%" cellpadding="3" cellspacing="0" border="0">
								<tr>
									<th><?php echo $lang->display('Campaign'); ?></th>
									<td><?php echo '<a href="index.php?layout=campaign&id='.$task_row['campaignID'].'">'.$task_row['campaign'].'</a>'; ?></td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Artwork'); ?></th>
									<td><?php echo '<a href="index.php?layout=artwork&id='.$task_row['artworkID'].'">'.$task_row['artwork'].'</a>'; ?></td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Source Language'); ?></th>
									<td>
										<?php
											echo '<img src="images/flags/'.$task_row['sourceFlag'].'" title="'.$lang->display($task_row['sourceLang']).'"> '.$lang->display($task_row['sourceLang']);
										?>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Desired Language'); ?></th>
									<td>
										<?php
											echo '<img src="images/flags/'.$task_row['targetFlag'].'" title="'.$lang->display($task_row['targetLang']).'"> '.$lang->display($task_row['targetLang']);
										?>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Translator'); ?></th>
									<td>
										<?php
											echo '<a href="index.php?layout=user&id='.$task_row['translatorID'].'">'.$task_row['tforename'].' '.$task_row['tsurname'].'</a>';
											if(!empty($task_row['tdeadline'])) echo ' <span class="grey">'.date(FORMAT_DATE,strtotime($task_row['tdeadline'])).'</span>';
										?>
									</td>
								</tr>
								<tr>
									<th valign="top"><?php echo $lang->display('Proofreader'); ?></th>
									<td>
										<?php
											$query = sprintf("SELECT task_proofreaders.order, task_proofreaders.deadline, task_proofreaders.done,
															users.userID, users.forename, users.surname
															FROM task_proofreaders
															LEFT JOIN users ON task_proofreaders.user_id = users.userID
															WHERE task_proofreaders.task_id = %d
															ORDER BY task_proofreaders.order ASC, users.forename ASC",
															$id);
											$result = mysql_query($query, $conn) or die(mysql_error());
											while($row = mysql_fetch_assoc($result)) {
												echo '<div>';
												echo '<a href="index.php?layout=user&id='.$row['userID'].'">'.$row['forename'].' '.$row['surname'].'</a>';
												if(!empty($row['deadline'])) echo ' <span class="grey">'.date(FORMAT_DATE,strtotime($row['deadline'])).'</span>';
												if(!empty($row['done'])) echo ' <img src="'.IMG_PATH.'ico_enable.png" title="'.$lang->display('Done').'" />';
												echo '</div>';
											}
										?>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Progress'); ?></th>
									<td>
										<?php
											echo '<div class="progress">';
											$totalWords = $task_row['userWords'] + $task_row['tmWords'] + $task_row['missingWords'];
											$totalDone = $task_row['userWords'] + $task_row['tmWords'];
											$uProgress = 0;
											$tmProgress = 0;
											$Progress = 0;
											if($totalWords>0) {
												$uProgress = round($task_row['userWords']/$totalWords*100);
												$tmProgress = round($task_row['tmWords']/$totalWords*100);
												$Progress = round($totalDone/$totalWords*100);
											}
											echo '<div>'.$lang->display('Word Count').': <b>'.$totalDone.'</b> / '.$totalWords.' ('.$task_row['missingWords'].')</div>';
											echo '<div class="progressBar">';
											echo '<div style="float:left"><img src="'.IMG_PATH.'prounit.png" width="'.$uProgress.'" height="10" title="'.$lang->display('User').': '.$task_row['userWords'].' ('.$uProgress.')"></div>';
											echo '<div style="float:left"><img src="'.IMG_PATH.'tmunit.png" width="'.$tmProgress.'" height="10" title="'.$lang->display('Translation Memory').': '.$task_row['tmWords'].' ('.$tmProgress.')"></div>';
											echo '</div>';
											echo '<div class="left"><b>'.$Progress.'</b>% '.$lang->display('Complete').'</div>';
											echo '</div>';
										?>
									</td>
								</tr>
								<tr>
									<th>* <?php echo $lang->display('Task Status'); ?></th>
									<td>
										<select
											class="input"
											name="taskstatus"
											id="taskstatus"
										>
										<?php BuildTaskStatusList($task_row['taskStatus']); ?>
										</select>
									</td>
								</tr>
								<tr>
									<th>* <?php echo $lang->display('Deadline'); ?></th>
									<td>
										<input
											type="text"
											class="input"
											id="deadline"
											name="deadline"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											onclick="displayDatePicker('deadline')"
											value="<?php echo $task_row['deadline']; ?>"
											readonly
										>
										<a
											href="javascript:void();"
											onclick="javascript:displayDatePicker('deadline');"
										><img src="<?php echo IMG_PATH; ?>ico_calendar.gif"></a>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Manager'); ?></th>
									<td><?php echo '<a href="index.php?layout=user&id='.$task_row['creatorID'].'">'.$task_row['mforename'].' '.$task_row['msurname'].'</a>'; ?></td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Last Update'); ?></th>
									<td><?php echo date(FORMAT_TIME,strtotime($task_row['lastUpdate'])); ?></td>
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
									<th valign="top"><?php echo $lang->display('Job Brief'); ?></th>
									<td>
										<textarea
											class="input"
											id="brief"
											name="brief"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											rows="15"
											cols="45"
										><?php echo "\n".$task_row['brief']; ?></textarea>
									</td>
								</tr>
								<tr>
									<th></th>
									<td>
										<input
											type="checkbox"
											name="trial"
											id="trial"
											value="1"
											<?php if($task_row['trial']) echo ' checked="checked"'; ?>
										/>
										<?php echo $lang->display('This is a trial run that only deals with headings.'); ?>
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
<script type="text/javascript" src="javascripts/datepicker.js"></script>