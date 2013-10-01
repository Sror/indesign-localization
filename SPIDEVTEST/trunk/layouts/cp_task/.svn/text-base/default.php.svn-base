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
					<div class="ico"><?php echo '<img src="'.IMG_PATH.'header/ico_task.png">'; ?></div>
					<div class="txt"><?php echo $lang->display('Task Manager'); ?></div>
				</div>
				<div class="options">
					<!-- Start -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Start'); ?>">
						<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) { SubmitForm('listform','start'); }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_start.png">'; ?></div>
							<div><?php echo $lang->display('Start'); ?></div>
						</a>
					</div>
					<!-- Pause -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Pause'); ?>">
						<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) { SubmitForm('listform','pause'); }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_pause.png">'; ?></div>
							<div><?php echo $lang->display('Pause'); ?></div>
						</a>
					</div>
					<!-- Sign Off -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Sign Off'); ?>">
						<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) { if(confirm('<?php echo $lang->display('Are you sure you want to sign off this task?'); ?>')) SubmitForm('listform','signoff'); }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_save.png">'; ?></div>
							<div><?php echo $lang->display('Sign Off'); ?></div>
						</a>
					</div>
					<!-- View -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('View'); ?>">
						<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) { SubmitForm('listform','view'); }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_view.png">'; ?></div>
							<div><?php echo $lang->display('View'); ?></div>
						</a>
					</div>
					<!-- Edit -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Edit'); ?>">
						<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) { SubmitForm('listform','edit'); }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_edit.png">'; ?></div>
							<div><?php echo $lang->display('Edit'); ?></div>
						</a>
					</div>
					<!-- Delete -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Delete'); ?>">
						<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) { if(confirm('<?php echo $lang->display('Are you sure you want to delete the selected?'); ?>')) { SubmitForm('listform','delete'); } }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_delete.png">'; ?></div>
							<div><?php echo $lang->display('Delete'); ?></div>
						</a>
					</div>
				</div>
				<div class="clear"></div>
			</div>
			<!-- Mainwrap -->
			<div class="mainwrap">
				<form
					id="listform"
					name="listform"
					action="index.php?layout=<?php echo $layout; ?>"
					method="POST"
					enctype="multipart/form-data"
					onsubmit="Popup('loadingme','waiting');"
				>
				<div class="option">
					<div class="left">
						<?php require_once(MODULES.'mod_list_search.php'); ?>
					</div>
					<div class="right">
						<div class="filter">
							<select
								class="input"
								onfocus="this.className='inputOn'"
								onblur="this.className='input'"
								name="filter_company"
								id="filter_company"
								onchange="SubmitForm('listform','');"
								title="<?php echo $lang->display('Please Select Company'); ?>"
							>
								<?php BuildCompanyList($company_id,$issuperadmin); ?>
							</select>
							<select
								class="input"
								onfocus="this.className='inputOn'"
								onblur="this.className='input'"
								name="task_status"
								id="task_status"
								onchange="SubmitForm('listform','');"
								title="<?php echo $lang->display('Select Status'); ?>"
							>
								<option value="0">- <?php echo $lang->display('Select Status'); ?> -</option>
								<?php BuildTaskStatusList($task_status); ?>
							</select>
						</div>
					</div>
					<div class="clear"></div>
				</div>
				<div class="list">
					<table id="listview" width="100%" cellpadding="5" cellspacing="0" border="0">
						<tr>
							<th width="2%" align="center">#</th>
							<th width="2%" align="center">
								<input type="checkbox" class="checkbox" name="checkall" id="checkall" onclick="jQueryCheckAll('listform',this.id,'#listview tr');">
							</th>
							<th width="10%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','sourceLang','<?php echo $pre; ?>');">
									<?php echo $lang->display('Task Description'); ?>
								</a>
							</th>
							<th width="18%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','artwork','<?php echo $pre; ?>');">
									<?php echo $lang->display('Artwork'); ?>
								</a>
							</th>
							<th width="16%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','campaign','<?php echo $pre; ?>');">
									<?php echo $lang->display('Campaign'); ?>
								</a>
							</th>
							<th width="10%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','translator','<?php echo $pre; ?>');">
									<?php echo $lang->display('Translator'); ?>
								</a>
							</th>
							<th width="8%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','deadline','<?php echo $pre; ?>');">
									<?php echo $lang->display('Deadline'); ?>
								</a>
							</th>
							<th width="10%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','manager','<?php echo $pre; ?>');">
									<?php echo $lang->display('Manager'); ?>
								</a>
							</th>
							<th width="12%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','missingWords','<?php echo $pre; ?>');">
									<?php echo $lang->display('Progress'); ?>
								</a>
							</th>
							<th width="10%" colspan="2">
								<a href="javascript:void(0);" onclick="SetOrder('listform','taskstatus','<?php echo $pre; ?>');">
									<?php echo $lang->display('Task Status'); ?>
								</a>
							</th>
							<th width="2%" align="center">
								<a href="javascript:void(0);" onclick="SetOrder('listform','id','<?php echo $pre; ?>');">ID</a>
							</th>
						</tr>
						<?php
							$query =  sprintf("SELECT tasks.taskID AS id, tasks.translatorID, tasks.creatorID, tasks.deadline, tasks.tdeadline,
												tasks.userWords, tasks.tmWords, tasks.missingWords, tasks.taskStatus, tasks.trial,
												artworks.artworkID, artworks.artworkName AS artwork,
												artworks.campaignID, campaigns.campaignName AS campaign,
												U2.username AS translator,
												U3.username AS manager,
												L1.languageName AS sourceLang, L1.flag AS sourceFlag,
												L2.languageName AS targetLang, L2.flag AS targetFlag,
												status.statusInfo AS taskstatus
												FROM tasks
												LEFT JOIN artworks ON tasks.artworkID = artworks.artworkID
												LEFT JOIN campaigns ON artworks.campaignID = campaigns.campaignID
												LEFT JOIN users U1 ON campaigns.ownerID = U1.userID
												LEFT JOIN companies ON U1.companyID = companies.companyID
												LEFT JOIN users U2 ON tasks.translatorID = U2.userID
												LEFT JOIN users U3 ON tasks.creatorID = U3.userID
												LEFT JOIN languages L1 ON campaigns.sourceLanguageID = L1.languageID
												LEFT JOIN languages L2 ON tasks.desiredLanguageID = L2.languageID
												LEFT JOIN status ON tasks.taskStatus = status.statusID
												WHERE companies.companyID = %d
												%s
												AND (artworks.artworkName LIKE '%s'
												OR campaigns.campaignName LIKE '%s'
												OR U2.username LIKE '%s'
												OR U3.username LIKE '%s'
												OR L1.languageName LIKE '%s'
												OR L2.languageName LIKE '%s'
												OR status.statusInfo LIKE '%s')
												ORDER BY `%s` %s
												LIMIT %d
												OFFSET %d",
												$company_id,
												mysql_real_escape_string($sub),
												"%".mysql_real_escape_string($keyword)."%",
												"%".mysql_real_escape_string($keyword)."%",
												"%".mysql_real_escape_string($keyword)."%",
												"%".mysql_real_escape_string($keyword)."%",
												"%".mysql_real_escape_string($keyword)."%",
												"%".mysql_real_escape_string($keyword)."%",
												"%".mysql_real_escape_string($keyword)."%",
												mysql_real_escape_string($by),
												mysql_real_escape_string($order),
												$limit,
												$offset);
							$result = mysql_query($query, $conn) or die(mysql_error());
							$found = mysql_num_rows($result);
							if($found) {
								$counter = $offset+1;
								while($row = mysql_fetch_assoc($result)) {
									$style = $counter%2==0 ? 'even' : 'odd';
									echo '<tr class="'.$style.'">';
									echo '<td align="center">'.$counter.'</td>';
									echo '<td align="center"><input type="checkbox" class="checkbox" name="id[]" id="id[]" value="'.$row['id'].'"></td>';
									echo '<td>';
									echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout='.$layout.'&task=edit&id='.$row['id'].'\');"><img src="images/flags/'.$row['sourceFlag'].'" title="'.$lang->display($row['sourceLang']).'"> <img src="'.IMG_PATH.'flag_to.gif" title="'.$lang->display('to be translated to').'"> <img src="images/flags/'.$row['targetFlag'].'" title="'.$lang->display($row['targetLang']).'"></a>';
									if(!empty($row['trial'])) echo '<span class="span"></span><img src="'.IMG_PATH.'ico_error.png" title="'.$lang->display('This is a trial run that only deals with headings.').'" />';
									echo '</td>';
									echo '<td><a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=artwork&id='.$row['artworkID'].'\');">'.$row['artwork'].'</a></td>';
									echo '<td><a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=campaign&id='.$row['campaignID'].'\');">'.$row['campaign'].'</a></td>';
									echo '<td>';
									echo '<div><a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=user&id='.$row['translatorID'].'\');">'.$row['translator'].'</a></div>';
									if(!empty($row['tdeadline'])) echo '<div class="grey">'.date(FORMAT_DATE,strtotime($row['tdeadline'])).'</div>';
									echo '</td>';
									echo '<td>'.date(FORMAT_DATE,strtotime($row['deadline'])).'</td>';
									echo '<td><a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=user&id='.$row['creatorID'].'\');">'.$row['manager'].'</a></td>';
									echo '<td>';
									BuildTaskProgressBar($row['id']);
									echo '</td>';
									echo '<td align="center">';
									BuildTaskStatusIcon($row['taskStatus']);
									echo '</td>';
									echo '<td>'.$row['taskstatus'].'</td>';
									echo '<td align="center">'.$row['id'].'</td>';
									echo '</tr>';
									$counter++;
								}
							}
						?>
						<input type="hidden" name="by" id="by" value="<?php echo $by; ?>">
						<input type="hidden" name="order" id="order" value="<?php echo $order; ?>">
					</table>
				</div>
				<?php require_once(MODULES.'mod_list_nav.php'); ?>
				<input type="hidden" name="form" id="form">
				</form>
			</div>
		</div>
	</div>
</div>