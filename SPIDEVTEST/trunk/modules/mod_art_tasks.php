<?php

// Inactive Tasks Alert
$task_query = sprintf("SELECT taskID FROM tasks
						WHERE artworkID = %d AND taskStatus IN (5,7)",
						$artworkID);
$task_result = mysql_query($task_query, $conn) or die(mysql_error());
if(mysql_num_rows($task_result)) BuildTipMsg($lang->display('Do you know that you still have inactive tasks?'));
?>
<!-- Toolbar -->
<div class="toolbar" id="art_tasks">
	<div class="title">
		<div class="ico">
			<?php echo '<img src="'.IMG_PATH.'header/ico_task.png">'; ?>
		</div>
		<div class="txt">
			<?php echo $lang->display('Task Manager'); ?>
			<div class="intro"><?php echo $lang->display('Task Home Intro'); ?></div>
		</div>
	</div>
	<div class="options">
	<?php if($artwork_row['campaignStatus'] == STATUS_ACTIVE) { ?>
		<!-- Task Merge -->
		<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Merge'); ?>">
			<a href="javascript:void(0);" onclick="if(CheckSelected('taskform','id')) { SubmitForm('taskform','merge'); }">
				<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_merge.png">'; ?></div>
				<div><?php echo $lang->display('Merge'); ?></div>
			</a>
		</div>
		<?php if ($acl->acl_check("tasks","new",$_SESSION['companyID'],$_SESSION['userID'])) { ?>
		<!-- New -->
		<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('New'); ?>">
			<a href="javascript:void(0);" onclick="goToURL('parent','index.php?layout=artwork&id=<?php echo $artworkID; ?>&task=step1');">
				<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_new.png">'; ?></div>
				<div><?php echo $lang->display('New'); ?></div>
			</a>
		</div>
		<?php } ?>
		<?php if ($acl->acl_check("tasks","manage",$_SESSION['companyID'],$_SESSION['userID'])) { ?>
		<!-- Start -->
		<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Start'); ?>">
			<a href="javascript:void(0);" onclick="if(CheckSelected('taskform','id')) { SubmitForm('taskform','start'); }">
				<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_start.png">'; ?></div>
				<div><?php echo $lang->display('Start'); ?></div>
			</a>
		</div>
		<!-- Pause -->
		<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Pause'); ?>">
			<a href="javascript:void(0);" onclick="if(CheckSelected('taskform','id')) { SubmitForm('taskform','pause'); }">
				<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_pause.png">'; ?></div>
				<div><?php echo $lang->display('Pause'); ?></div>
			</a>
		</div>
		<?php } ?>
		<?php if ($acl->acl_check("tasks","delete",$_SESSION['companyID'],$_SESSION['userID'])) { ?>
		<!-- Delete -->
		<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Delete'); ?>">
			<a href="javascript:void(0);" onclick="if(CheckSelected('taskform','id')) { if(confirm('<?php echo $lang->display('Are you sure you want to delete this task?'); ?>')) { SubmitForm('taskform','delete'); } }">
				<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_delete.png">'; ?></div>
				<div><?php echo $lang->display('Delete'); ?></div>
			</a>
		</div>
		<?php } ?>
	<?php } ?>
	</div>
	<div class="clear"></div>
</div>
<form
	id="taskform"
	name="taskform"
	action="index.php?layout=artwork&id=<?php echo $artworkID; ?>"
	method="POST"
	enctype="multipart/form-data"
>
<div class="mainwrap">
	<div class="artworkTable">
		<div class="languageTable">
			<table id="listview" cellspacing="0" cellpadding="0" border="0">
				<tr class="title">
					<th width="2%" title="#" align="center">
						<a href="javascript:void(0);">#</a>
					</th>
					<th width="2%" title="<?php echo $lang->display('Select All'); ?>">
						<input type="checkbox" class="checkbox" name="checkall" id="checkall" onclick="jQueryCheckAll('taskform',this.id,'#listview tr');">
					</th>
					<th width="2%" title="MT">
						<!-- TH for task type -->
					</th>
					<th width="8%" align="center"><?php echo $lang->display('Action'); ?></th>
					<th colspan="2" title="<?php echo $lang->display('Languages'); ?>">
						<a href="javascript:void(0);" onclick="SetOrder('taskform','languageName','<?php echo $preorder; ?>');">
							<span class="icon"><img src="<?php echo IMG_PATH; ?>ico_lang.png" /></span>
							<span class="heading"><?php echo $lang->display('Languages'); ?></span>
						</a>
					</th>
					<th title="<?php echo $lang->display('Translator'); ?>">
						<a href="javascript:void(0);" onclick="SetOrder('taskform','tforename','<?php echo $preorder; ?>');">
							<span class="icon"><img src="<?php echo IMG_PATH; ?>ico_user.png" /></span>
							<span class="heading"><?php echo $lang->display('Translator'); ?></span>
						</a>
					</th>
					<th title="<?php echo $lang->display('Proofreader'); ?>">
						<a href="javascript:void(0);" onclick="return false;">
							<span class="icon"><img src="<?php echo IMG_PATH; ?>ico_user.png" /></span>
							<span class="heading"><?php echo $lang->display('Proofreader'); ?></span>
						</a>
					</th>
					<th title="<?php echo $lang->display('Agent'); ?>">
						<a href="javascript:void(0);" onclick="SetOrder('taskform','aforename','<?php echo $preorder; ?>');">
							<span class="icon"><img src="<?php echo IMG_PATH; ?>ico_agent.png" /></span>
							<span class="heading"><?php echo $lang->display('Agent'); ?></span>
						</a>
					</th>
					<th title="<?php echo $lang->display('Deadline'); ?>">
						<a href="javascript:void(0);" onclick="SetOrder('taskform','deadline','<?php echo $preorder; ?>');">
							<span class="icon"><img src="<?php echo IMG_PATH; ?>ico_clock.png" /></span>
							<span class="heading"><?php echo $lang->display('Deadline'); ?></span>
						</a>
					</th>
					<th title="<?php echo $lang->display('Task Manager'); ?>">
						<a href="javascript:void(0);" onclick="SetOrder('taskform','cforename','<?php echo $preorder; ?>');">
							<span class="icon"><img src="<?php echo IMG_PATH; ?>ico_owner.png" /></span>
							<span class="heading"><?php echo $lang->display('Task Manager'); ?></span>
						</a>
					</th>
					<th title="<?php echo $lang->display('Progress'); ?>">
						<a href="javascript:void(0);" onclick="SetOrder('taskform','missingWords','<?php echo $preorder; ?>');">
							<span class="icon"><img src="<?php echo IMG_PATH; ?>ico_progress.png" /></span>
							<span class="heading"><?php echo $lang->display('Progress'); ?></span>
						</a>
					</th>
					<th colspan="2" title="<?php echo $lang->display('Task Status'); ?>">
						<a href="javascript:void(0);" onclick="SetOrder('taskform','statusInfo','<?php echo $preorder; ?>');">
							<span class="icon"><img src="<?php echo IMG_PATH; ?>ico_status.png" /></span>
							<span class="heading"><?php echo $lang->display('Task Status'); ?></span>
						</a>
					</th>
					<?php if ($acl->acl_check("system","cost",$_SESSION['companyID'],$_SESSION['userID'])) { ?>
					<th title="<?php echo $lang->display('Service'); ?>">
						<a href="javascript:void(0);" onclick="SetOrder('taskform','serviceCharge','<?php echo $preorder; ?>');">
							<span class="icon"><img src="<?php echo IMG_PATH; ?>ico_service.png" /></span>
							<span class="heading"><?php echo $lang->display('Service'); ?></span>
						</a>
					</th>
					<th title="<?php echo $lang->display('Cost'); ?>">
						<a href="javascript:void(0);" onclick="SetOrder('taskform','cost','<?php echo $preorder; ?>');">
							<span class="icon"><img src="<?php echo IMG_PATH; ?>ico_cost.png" /></span>
							<span class="heading"><?php echo $lang->display('Cost'); ?></span>
						</a>
					</th>
					<th title="<?php echo $lang->display('Total Cost'); ?>">
						<a href="javascript:void(0);" onclick="SetOrder('taskform','total_cost','<?php echo $preorder; ?>');">
							<span class="icon"><img src="<?php echo IMG_PATH; ?>ico_cost.png" /></span>
							<span class="heading"><?php echo $lang->display('Total Cost'); ?></span>
						</a>
					</th>
					<?php } ?>
					<th width="2%" title="ID" align="center">
						<a href="javascript:void(0);" onclick="SetOrder('taskform','taskID','<?php echo $preorder; ?>');">ID</a>
					</th>
				</tr>
				<?php
					$query_task = sprintf("SELECT tasks.taskID, tasks.taskTypeID, tasks.taskStatus, tasks.deadline, tasks.tdeadline, tasks.notes, tasks.brief, tasks.trial,
											tasks.currencyID, tasks.cost, tasks.serviceCurrencyID, tasks.serviceCharge, tasks.attachment,
											tasks.userWords, tasks.tmWords, tasks.missingWords,
											task_types.title AS taskTypeTitle,
											languages.flag, languages.languageName,
											U1.userID AS cuid, U1.forename AS cforename, U1.surname AS csurname,
											U2.userID AS tuid, U2.forename AS tforename, U2.surname AS tsurname,
											U3.userID AS auid, U3.forename AS aforename, U3.surname AS asurname,
											companies.companyName AS agency,
											status.statusInfo,
											C1.currencySymbol, C1.currencyAb,
											C2.currencySymbol AS serviceCurrencySymbol, C2.currencyAb AS serviceCurrencyAb
											FROM tasks
											LEFT JOIN task_types ON tasks.taskTypeID = task_types.id
											LEFT JOIN languages ON tasks.desiredLanguageID = languages.languageID
											LEFT JOIN users U1 ON tasks.creatorID = U1.userID
											LEFT JOIN users U2 ON tasks.translatorID = U2.userID
											LEFT JOIN users U3 ON tasks.agentID = U3.userID
											LEFT JOIN companies ON U3.companyID = companies.companyID
											LEFT JOIN status ON tasks.taskStatus = status.statusID
											LEFT JOIN currencies C1 ON tasks.currencyID = C1.currencyID
											LEFT JOIN currencies C2 ON tasks.serviceCurrencyID = C2.currencyID
											WHERE artworkID = %d
                                                                                        AND `tasks`.`desiredLanguageID` != 0 
                                                                                        AND `tasks`.`translatorID` IS NOT NULL
											ORDER BY %s %s",
											$artworkID,
											mysql_real_escape_string($by),
											mysql_real_escape_string($order));
											
					$result_task = mysql_query($query_task, $conn) or die(mysql_error());
					$counter = 0;
					$estimatedCost = 0;
					$estimatedService = 0;
					$artworkCost = 0;
					while($row_task = mysql_fetch_assoc($result_task)) {
						$counter++;
						$serviceCharge = number_format($row_task['serviceCharge'],2,".","");
						$cost = number_format($row_task['cost'],2,".","");
						#shall we count the inactive tasks?
						#if($row_task['taskStatus']>5 && $row_task['taskStatus']!=7) {
							// calculate service charge
							if($row_task['serviceCurrencyID']==CURRENCY) {
								$thisService = $serviceCharge;
							} else {
								$thisService = XeConvert($serviceCharge, $row_task['serviceCurrencyAb'], CURRENCY_AB);
							}
							$estimatedService = $estimatedService + $thisService;
							// calculate cost
							if($row_task['currencyID']==CURRENCY) {
								$thisCost = $cost;
							} else {
								$thisCost = number_format(XeConvert($cost, $row_task['currencyAb'], CURRENCY_AB),2,".","");
							}
							// prep for artwork costing
							if($thisService>0) {
								$artworkCost = $artworkCost + $thisService;
							} else {
								$artworkCost = $artworkCost + $thisCost;
							}
							$estimatedCost = $estimatedCost + $thisCost;
						#}
						$style = ($counter%2==0) ? 'even' : 'odd';
						echo '<tr class="'.$style.'" title="'.$lang->display('Notes').': '.$row_task['notes'].$lang->display('Job Brief').': '.$row_task['brief'].'"';
						echo '>';
						echo '<td align="center">'.$counter.'</td>';
						echo '<td><input
									type="checkbox"
									class="checkbox"
									name="id['.$row_task['taskID'].']"
									id="id['.$row_task['taskID'].']"
									value="'.$row_task['taskID'].'" /></td>';
						
						echo '<td align="center">'.$row_task['taskTypeTitle'].'</td>';
						echo '<td align="center">';
						echo '<div class="ico">';
						echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=task&id='.$row_task['taskID'].'\');" title="'.$lang->display('View').'"><img src="'.IMG_PATH.'toolbar/ico_view.png" /></a>';
						if ($acl->acl_check("tasks","edit",$_SESSION['companyID'],$_SESSION['userID'])) {
							echo '<span class="span"></span><a href="javascript:void(0);" onclick="Popup(\'helper\',\'blur\');DoAjax(\'ref='.$row_task['taskID'].'&target=artwork\',\'window\',\'modules/mod_task_edit.php\');" title="'.$lang->display('Edit').'"><img src="'.IMG_PATH.'toolbar/ico_edit.png" /></a>';
						}
						if($acl->acl_check("tasks","manage",$_SESSION['companyID'],$_SESSION['userID'])) {
							if($row_task['taskStatus']==7) {
								echo '<span class="span"></span><a href="javascript:void(0);" onclick="CheckTheBoxOnly(\'id['.$row_task['taskID'].']\',\'id\');SubmitForm(\'taskform\',\'start\');" title="'.$lang->display('Start').'"><img src="'.IMG_PATH.'toolbar/ico_start.png" /></a>';
							} else {
								echo '<span class="span"></span><a href="javascript:void(0);" onclick="CheckTheBoxOnly(\'id['.$row_task['taskID'].']\',\'id\');SubmitForm(\'taskform\',\'pause\');" title="'.$lang->display('Pause').'"><img src="'.IMG_PATH.'toolbar/ico_pause.png" /></a>';
							}
						}
						echo '</div>';
						echo '</td>';
						
						echo '<td align="center"><img src="images/flags/'.$row_task['flag'].'" title="'.$lang->display($row_task['languageName']).'" /></td>';
						echo '<td>';
						echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=task&id='.$row_task['taskID'].'\');">'.$lang->display($row_task['languageName']).'</a>';
						if(!empty($row_task['trial'])) echo ' <img src="'.IMG_PATH.'ico_error.png" title="'.$lang->display('This is a trial run that only deals with headings.').'" />';
						if(!empty($row_task['attachment'])) echo ' <a href="download.php?attachment&File='.$row_task['attachment'].'&SaveAs='.$row_task['attachment'].'" title="'.$lang->display('Attachment').'"><img src="'.IMG_PATH.'ico_attachment.png"></a>';
						echo '</td>';
						echo '<td>';
						if(!empty($row_task['tuid'])) {
							echo '<div><a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=user&id='.$row_task['tuid'].'\');">'.$row_task['tforename'].' '.$row_task['tsurname'].'</a></div>';
							if(!empty($row_task['tdeadline'])) echo '<div class="grey">'.date(FORMAT_DATE,strtotime($row_task['tdeadline'])).'</div>';
						} else {
							echo '<i>'.$lang->display('N/S').'</i>';
						}
						echo '</td>';
						echo '<td>';
						$query = sprintf("SELECT task_proofreaders.order, task_proofreaders.deadline, task_proofreaders.done,
										users.userID, users.forename, users.surname
										FROM task_proofreaders
										LEFT JOIN users ON task_proofreaders.user_id = users.userID
										WHERE task_proofreaders.task_id = %d
										ORDER BY task_proofreaders.order ASC, task_proofreaders.deadline ASC, users.forename ASC",
										$row_task['taskID']);
						$result = mysql_query($query, $conn) or die(mysql_error());
						if(mysql_num_rows($result)) {
							while($row = mysql_fetch_assoc($result)) {
								echo '<div>';
								echo $row['order'].'. <a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=user&id='.$row['userID'].'\');">'.$row['forename'].' '.$row['surname'].'</a>';
								if(!empty($row['done'])) echo ' <img src="'.IMG_PATH.'ico_enable.png" title="'.$lang->display('Done').'" />';
								echo '</div>';
								if(!empty($row['deadline'])) echo '<div class="grey">'.date(FORMAT_DATE,strtotime($row['deadline'])).'</div>';
							}
						} else {
							echo '<i>'.$lang->display('N/S').'</i>';
						}
						echo '</td>';
						echo '<td>';
						if(!empty($row_task['auid'])) {
							echo '<div><a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=user&id='.$row_task['auid'].'\');">'.$row_task['aforename'].' '.$row_task['asurname'].'</a></div>';
							echo '<div class="grey">'.$row_task['agency'].'</div>';
						} else {
							echo '<i>'.$lang->display('N/S').'</i>';
						}
						echo '</td>';
						echo '<td>'.date(FORMAT_DATE,strtotime($row_task['deadline'])).'</td>';
						echo '<td><a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=user&id='.$row_task['cuid'].'\');">'.$row_task['cforename'].' '.$row_task['csurname'].'</a></td>';
						echo '<td>';
						BuildTaskProgressBar($row_task['taskID']);
						echo '</td>';
						echo '<td align="center">';
						BuildTaskStatusIcon($row_task['taskStatus']);
						echo '</td>';
						echo '<td>'.$lang->display($row_task['statusInfo']).'</td>';
						if ($acl->acl_check("system","cost",$_SESSION['companyID'],$_SESSION['userID'])) {
							echo '<td align="right">';
							echo $row_task['serviceCurrencySymbol']." ".number_format($serviceCharge,2,".",",");
							echo '</td>';
							echo '<td align="right">';
							echo $row_task['currencySymbol']." ".number_format($cost,2,".",",");
							echo '</td>';
							echo '<td align="right">';
							echo $row_task['currencySymbol']." ".number_format($serviceCharge+$cost,2,".",",");
							echo '</td>';
						}
						echo '<td align="center">'.$row_task['taskID'].'</td>';
						echo '</tr>';
					}
					//save costing to artwork for thumbnail views
					$estimatedCost = number_format($estimatedCost,2,".","");
					$estimatedService = number_format($estimatedService,2,".","");
					$estimatedTotalCost = number_format($artworkCost+estimatedService,2,".","");
					$update = sprintf("UPDATE artworks SET cost = %f WHERE artworkID = %d", (float)$artworkCost, $artworkID);
					$result = mysql_query($update, $conn) or die(mysql_error());
					if($counter==0) {
						echo '<tr><td colspan="15" align="center"><i>'.$lang->display('No Task').'<i></td></tr>';
					} else {
						if ($acl->acl_check("system","cost",$_SESSION['companyID'],$_SESSION['userID'])) {
							$counter++;
							$style = ($counter%2==0) ? 'even' : 'odd';
							echo '<tr class="'.$style.'" onmouseover="this.className=\'hover\'" onmouseout="this.className=\''.$style.'\'">';
							echo '<td colspan="13">';
							echo '<div class="blue"><b>'.$lang->display('Total Estimated Cost').'</b></div>';
							echo '<div class="grey">* '.$lang->display('Live currency exchange rates provided by').' <a href="http://www.xe.com" target="_blank">xe.com</a>.</div>';
							echo '</td>';
							echo '<td align="right"><span class="blue"><b>'.CURRENCY_SYMBOL.' '.number_format($estimatedService,2,".",",").'</b></span></td>';
							echo '<td align="right"><span class="blue"><b>'.CURRENCY_SYMBOL.' '.number_format($estimatedCost,2,".",",").'</b></span></td>';
							echo '<td align="right"><span class="blue"><b>'.CURRENCY_SYMBOL.' '.number_format($estimatedService+$estimatedCost,2,".",",").'</b></span></td>';
							echo '<td align="center">*</td>';
							echo '</tr>';
						}
					}
				?>
			</table>
		</div>
	</div>
</div>
<input type="hidden" name="by" id="by" value="<?php echo $by; ?>">
<input type="hidden" name="order" id="order" value="<?php echo $order; ?>">
<input type="hidden" name="form" id="form">
</form>