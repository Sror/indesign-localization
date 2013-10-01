<?php
BuildHelperDiv($row_task['artworkName'].' - '.$lang->display('Task Home'));
$navStatus = array("mytasks");
require_once(MODULES.'mod_header.php');
BuildPageIntro('<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=mytasks\');" title="'.$lang->display('My Tasks').'">'.$lang->display('My Tasks').'</a>'.BREADCRUMBS_ARROW.$row_task['artworkName'],'<img src="images/flags/'.$row_task['flag'].'" title="'.$lang->display($row_task['languageName']).'" /> <img src="'.IMG_PATH.'flag_to.gif" title="'.$lang->display('to be translated to').'" /> <img src="images/flags/'.$row_desiredRs['flag'].'" title="'.$lang->display($row_desiredRs['languageName']).'" />');
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
						<?php echo $lang->display('Task Home'); ?>
						<div class="intro"><?php echo $lang->display('Task Home Intro'); ?></div>
					</div>
				</div>
				<div class="options">
					<?php if($row_task['taskStatus'] < 10) { ?>
						<!-- Work Online -->
						<?php
							$trial = $DB->get_task_trial_status($taskID) ? " AND boxes.heading = 1" : "";
							$query_nextbox = sprintf("SELECT paralinks.BoxID, pages.Page
													FROM paralinks
													LEFT JOIN boxes ON boxes.uID = paralinks.BoxID
													LEFT JOIN pages ON boxes.PageID = pages.uID
													LEFT JOIN box_properties ON ( box_properties.box_id = boxes.uID AND box_properties.task_id IN (0,%d) )
													WHERE pages.ArtworkID = %d
													AND (box_properties.lock IS NULL OR box_properties.lock = 0)
													$trial
													GROUP BY paralinks.BoxID
													ORDER BY boxes.order ASC
													LIMIT 1",
													$taskID,
													$artworkID);
							$result_nextbox = mysql_query($query_nextbox, $conn) or die(mysql_error());
							if(mysql_num_rows($result_nextbox)) {
								$row_nextbox = mysql_fetch_assoc($result_nextbox);
								echo '<div
										class="optionOff"
										onmouseover="this.className=\'optionOn\'"
										onmouseout="this.className=\'optionOff\'"
										title="'.$lang->display('Work Online').'">';
								echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=translate&id='.$taskID.'&page='.$row_nextbox['Page'].'\');">';
								echo '<div class="ico"><img src="'.IMG_PATH.'header/ico_online.png" /></div>';
								echo '<div>'.$lang->display('Work Online').'</div>';
								echo '</a>';
								echo '</div>';
							}
						?>
						<?php if($acl->acl_check("taskworkflow","export",$_SESSION['companyID'],$_SESSION['userID'])) { ?>
						<!-- Export -->
						<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Export'); ?>">
							<a href="javascript:void(0);" onclick="Popup('helper','blur');DoAjax('id=<?php echo $taskID; ?>','window','modules/mod_task_terms.php');">
								<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_export.png">'; ?></div>
								<div><?php echo $lang->display('Export'); ?></div>
							</a>
						</div>
						<?php } ?>
						<?php if($acl->acl_check("taskworkflow","import",$_SESSION['companyID'],$_SESSION['userID'])) { ?>
						<!-- Import -->
						<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Import'); ?>">
							<a href="javascript:void(0);" onclick="Popup('helper','blur');DoAjax('id=<?php echo $taskID; ?>','window','modules/mod_task_import.php');">
								<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_import.png">'; ?></div>
								<div><?php echo $lang->display('Import'); ?></div>
							</a>
						</div>
						<?php } ?>
						<?php if($acl->acl_check("taskworkflow","customise",$_SESSION['companyID'],$_SESSION['userID'])) { ?>
						<!-- Customise -->
						<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Customise'); ?>">
							<a href="javascript:void(0);" onclick="goToURL('parent','index.php?layout=customise&id=<?php echo $taskID; ?>&page=<?php echo $page; ?>');">
								<div class="ico"><?php echo '<img src="'.IMG_PATH.'header/ico_customise.png">'; ?></div>
								<div><?php echo $lang->display('Customise'); ?></div>
							</a>
						</div>
						<?php } ?>
						<?php if($acl->acl_check("tasks","edit",$_SESSION['companyID'],$_SESSION['userID'])) { ?>
						<!-- Assign -->
						<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Assign'); ?>">
							<a href="javascript:void(0);" onclick="Popup('helper','blur');DoAjax('ref=<?php echo $taskID; ?>','window','modules/mod_task_edit.php');">
								<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_guest.png">'; ?></div>
								<div><?php echo $lang->display('Assign'); ?></div>
							</a>
						</div>
						<?php } ?>
					<?php } ?>
					<!-- Download -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Download'); ?>">
						<a href="javascript:void(0);" onclick="Popup('helper','blur');DoAjax('id=<?php echo $taskID; ?>','window','modules/mod_task_download.php');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_download.png">'; ?></div>
							<div><?php echo $lang->display('Download'); ?></div>
						</a>
					</div>
					<?php if($acl->acl_check("taskworkflow","tasklog",$_SESSION['companyID'],$_SESSION['userID'])) { ?>
					<!-- Log -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Log'); ?>">
						<a href="javascript:void(0);" onclick="Popup('helper','blur');DoAjax('id=<?php echo $taskID; ?>','window','modules/mod_task_log.php');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'header/ico_log.png">'; ?></div>
							<div><?php echo $lang->display('Log'); ?></div>
						</a>
					</div>
					<?php } ?>
					<!-- Refresh -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Refresh'); ?>">
						<a href="javascript:void(0);" onclick="SubmitForm('mainform','refresh');process_start('<?php echo $row_task['fileName']; ?>');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_refresh.png">'; ?></div>
							<div><?php echo $lang->display('Refresh'); ?></div>
						</a>
					</div>
					<!-- Refresh All -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Refresh All'); ?>">
						<a href="javascript:void(0);" onclick="if(confirm('<?php echo $lang->display('Refreshing all pages would take longer to process. Are you sure you want to continue?'); ?>')) {SubmitForm('mainform','refreshall');process_start('<?php echo $row_task['fileName']; ?>');}">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_refresh_all.png">'; ?></div>
							<div><?php echo $lang->display('Refresh All'); ?></div>
						</a>
					</div>
					<!-- Version Control -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Version Control'); ?>">
						<a href="javascript:void(0);" onclick="Popup('helper','blur');DoAjax('id=<?php echo $taskID; ?>','window','modules/mod_task_version.php');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_info.png">'; ?></div>
							<div><?php echo $lang->display('Version Control'); ?></div>
						</a>
					</div>
					<!-- Info -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Information'); ?>">
						<a href="javascript:void(0);" onclick="Popup('helper','blur');DoAjax('id=<?php echo $taskID; ?>','window','modules/mod_task_info.php');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_info.png">'; ?></div>
							<div><?php echo $lang->display('Information'); ?></div>
						</a>
					</div>
					<!-- Fonts -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Fonts'); ?>">
						<a href="javascript:void(0);" onclick="window.location='/index.php?layout=cp_font_sub&taskID=<?php echo $taskID; ?>&show=Used';">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'header/ico_font_sub.png">'; ?></div>
							<div><?php echo $lang->display('Fonts'); ?></div>
						</a>
					</div>
					<!-- Options -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Options'); ?>">
						<a href="javascript:void(0);" onclick="Popup('helper','blur');DoAjax('artwork_id=<?php echo $artworkID; ?>&task_id=<?php echo $taskID; ?>','window','modules/mod_options.php');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_options.png">'; ?></div>
							<div><?php echo $lang->display('Options'); ?></div>
						</a>
					</div>
					
					<?php if($row_task['taskStatus']==6 && $acl->acl_check("taskworkflow","submit",$_SESSION['companyID'],$_SESSION['userID'])) { ?>
					<!-- Submit -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Submit'); ?>">
						<a href="javascript:void(0);" onclick="if(confirm('<?php echo $lang->display('Are you sure you want to submit this task for approval?'); ?>')) goToURL('parent','index.php?layout=task&id=<?php echo $taskID; ?>&do=submit');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_submit.png">'; ?></div>
							<div><?php echo $lang->display('Submit'); ?></div>
						</a>
					</div>
					<?php } ?>
					<?php if($acl->acl_check("taskworkflow","approve",$_SESSION['companyID'],$_SESSION['userID']) && $row_task['taskStatus']==8) { ?>
					<!-- Revert -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Revert'); ?>">
						<a href="javascript:void(0);" onclick="if(confirm('<?php echo $lang->display('Are you sure you want to revert this task for translation?'); ?>')) goToURL('parent','index.php?layout=task&id=<?php echo $taskID; ?>&do=revert');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_restore.png">'; ?></div>
							<div><?php echo $lang->display('Revert'); ?></div>
						</a>
					</div>
					<!-- Approve -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Approve'); ?>">
						<a href="javascript:void(0);" onclick="if(confirm('<?php echo $lang->display('Are you sure you want to approve this task?'); ?>')) goToURL('parent','index.php?layout=task&id=<?php echo $taskID; ?>&do=approve');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_apply.png">'; ?></div>
							<div><?php echo $lang->display('Approve'); ?></div>
						</a>
					</div>
					<?php } ?>
					<?php if($row_task['taskStatus']==9 && $acl->acl_check("taskworkflow","signoff",$_SESSION['companyID'],$_SESSION['userID'])) { ?>
					<!-- Sign Off -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Sign Off'); ?>">
						<a href="javascript:void(0);" onclick="if(confirm('<?php echo $lang->display('Are you sure you want to sign off this task?'); ?>')) goToURL('parent','index.php?layout=task&id=<?php echo $taskID; ?>&do=signoff');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_save.png">'; ?></div>
							<div><?php echo $lang->display('Sign Off'); ?></div>
						</a>
					</div>
					<?php } ?>
				</div>
				<div class="clear"></div>
			</div>
			<?php
		
				if($row_task['trial']) BuildTipMsg($lang->display('This is a trial run that only deals with headings.'));
				if($alert && $acl->acl_check("tasks","edit",$_SESSION['companyID'],$_SESSION['userID'])) {
					BuildTipMsg($lang->display('Agency Task Tip').' <img src="'.IMG_PATH.'arrow_gold_rgt.png" /> <a href="javascript:void(0);" onclick="Popup(\'helper\',\'blur\');DoAjax(\'ref='.$taskID.'\',\'window\',\'modules/mod_task_edit.php\');">'.$lang->display('Assign Translators/Proofreaders').'</a>');
				}
				$all_overflows = $DB->check_box_overflow($artworkID,0,$taskID);
				if($all_overflows) {
					BuildTipMsg($lang->display('Text Overflow').': '.$lang->display('Found').' <b>'.$DB->check_page_box_overflow($artworkID,$page,$taskID).'</b> / '.$all_overflows);
				}
			?>
			<div class="mainwrap">
				<form
					id="mainform"
					name="mainform"
					action="index.php?layout=<?php echo $layout; ?>&id=<?php echo $taskID; ?>&page=<?php echo $page; ?>"
					method="POST"
					enctype="multipart/form-data"
					onsubmit="return false;"
				>
				<div class="pageBar">
					<div class="left">
						<?php
							$ret = BuildZoomIcons($artworkID,$page,$taskID);
							$resize = $ret['resize'];
							$PageScale = $ret['PageScale'];
							$scale = $ret['scale'];
							$toggle = $ret['toggle'];
						?>
					</div>
					<?php
						if($acl->acl_check("taskworkflow","search",$_SESSION['companyID'],$_SESSION['userID'])) {
							echo '<div class="left">';
							require_once(MODULES.'mod_task_search.php');
							echo '</div>';
						}
						BuildAdvancedTaskProgressBar($taskID,$pl);
					?>
					<div class="right">
						<div class="filter">
							<?php BuildPageJumper($artworkID,$page,$taskID); ?>
							<select
								class="input"
								onfocus="this.className='inputOn'"
								onblur="this.className='input'"
								name="filter_page"
								id="filter_page"
								onchange="SubmitForm('mainform','');"
								title="<?php echo $lang->display('Select Page'); ?>"
							>
							<?php BuildPageList($artworkID,$page,$page_id); ?>
							</select>
							<select
								class="input"
								onfocus="this.className='inputOn'"
								onblur="this.className='input'"
								name="filter_layer"
								id="filter_layer"
								onchange="SubmitForm('mainform','');"
								title="<?php echo $lang->display('Select Layer'); ?>"
							>
							<?php BuildLayerList($artworkID,$layer_id); ?>
							</select>
						</div>
					</div>
					<div class="clear"></div>
				</div>
				<input type="hidden" name="form" id="form">
				</form>
				<script language="javascript">
					var preload = new Image(<?php echo $img_width.','.$img_height; ?>);
					preload.src='<?php echo $thumbnail; ?>';
				</script>
				<?php BuildPageCols($artworkID,$page,$taskID); ?>
				<div class="artworkScroll">
					<div class="artwork">
						<?php
                                                        
							if(!empty($previewFile) && file_exists(ROOT.$thumbnail)) {
								//cretae edit icons for PICT boxes
								if($toggle) $im = @imagecreatefromjpeg(ROOT.$thumbnail);
								echo "<map id=\"boxMap\" name=\"boxMap\">";
								$sub = !empty($page_id) ? sprintf(" AND pages.uID = %d",$page_id) : sprintf(" AND pages.uID IN (%s)",mysql_real_escape_string($DB->GetAllPages($artworkID,$page)));
								$sub .= !empty($layer_id) ? sprintf(" AND boxes.LayerID = %d",$layer_id) : "";
								$sub .= $trial;
								$query_box = sprintf("SELECT boxes.uID, boxes.Left, boxes.Right, boxes.Top, boxes.Bottom, boxes.Angle,
													artwork_layers.visible, artwork_layers.locked, artwork_layers.colour, box_properties.lock,
													(boxes.Right-boxes.Left) AS BoxWidth, (boxes.Bottom-boxes.Top) AS BoxHeight
													FROM boxes
													LEFT JOIN artwork_layers ON boxes.LayerID = artwork_layers.id
													LEFT JOIN pages ON pages.uID = boxes.PageID
													LEFT JOIN box_properties ON (box_properties.box_id = boxes.uID AND box_properties.task_id IN (0,%d))
													LEFT JOIN paralinks ON boxes.uID = paralinks.BoxID
													WHERE pages.ArtworkID = %d
													AND pages.Page IN (0,%d)
													AND boxes.Type = 'TEXT'
													%s
													GROUP BY boxes.uID
													ORDER BY
														(BoxWidth*BoxHeight) ASC,
														BoxWidth ASC,
														BoxHeight ASC",
													$taskID,
													$artworkID,
													$page,
													$sub);
								$result_box = mysql_query($query_box, $conn) or die(mysql_error());
								while($row_box = mysql_fetch_assoc($result_box)) {
									$boxID = $row_box['uID'];
									$TheBoxID = $DB->GetTheBox($boxID);
									$left = $row_box['Left'];
									$right = $row_box['Right'];
									$top = $row_box['Top'];
									$bottom = $row_box['Bottom'];
									$angle = $row_box['Angle'];
									$visible = $row_box['visible'];
									$locked = $row_box['locked'];
									$colour = $row_box['colour'];
									$lock = $row_box['lock'];
									//get updated geometry info
									$geo = $DB->GetBoxMoves($artworkID,$boxID,$taskID);
									if($geo) {
										$left = $geo['left'];
										$right = $geo['right'];
										$top = $geo['top'];
										$bottom = $geo['bottom'];
										$angle = $geo['angle'];
									}
									//build box colour and shapes
									$points = get_points($left*$PageScale,$top*$PageScale,$right*$PageScale,$bottom*$PageScale,$angle);
									if(empty($colour)) $colour = DEFAULT_LAYER_COLOUR;
									$border_colour = hexdec($colour);
									$title = $lang->display('Work Online');
									$query_comment = sprintf("SELECT UNIX_TIMESTAMP(comments.time) AS time, users.username
															FROM comments
															LEFT JOIN users ON comments.user_id = users.userID
															WHERE comments.artwork_id = %d
															AND comments.box_id = %d
															AND comments.task_id = %d",
															$artworkID,
															$boxID,
															$taskID);
									$result_comment = mysql_query($query_comment, $conn) or die(mysql_error());
									if(mysql_num_rows($result_comment)) {
										$row_comment = mysql_fetch_assoc($result_comment);
										$title = $lang->display('Comments').": ".$row_comment['username']." ".date(FORMAT_TIME,$row_comment['time']);
										if($toggle) {
											$style = get_line_style($colour,DEFAULT_DASHED_LINE_COLOUR,DEFAULT_DASHED_LINE_PIXELS);
											@imagesetstyle($im,$style);
											$border_colour = IMG_COLOR_STYLED;
											$icon = @imagecreatefrompng(ROOT.DEFAULT_ICON_COMMENT);
											@imagecopyresized($im,$icon,$points[0]+DEFAULT_ICON_WIDTH,$points[1],0,0,DEFAULT_ICON_WIDTH,DEFAULT_ICON_HEIGHT,48,48);
											@imagedestroy($icon);
										}
									}

									if($toggle) {
										if($DB->check_box_overflow($artworkID,$boxID,$taskID)) {
											$icon = @imagecreatefrompng(ROOT.DEFAULT_ICON_MORE);
											@imagecopyresized($im,$icon,$points[4]-DEFAULT_ICON_WIDTH,$points[5]-DEFAULT_ICON_HEIGHT,0,0,DEFAULT_ICON_WIDTH,DEFAULT_ICON_HEIGHT,48,48);
											@imagedestroy($icon);
										}
										@imagepolygon($im,$points,4,$border_colour);
										if($type=="PICT") {
											@imageline($im,$points[0],$points[1],$points[4],$points[5],$border_colour);
											@imageline($im,$points[2],$points[3],$points[6],$points[7],$border_colour);
										}
									}

									//map coords
									$coords = array();
									foreach($points as $point) {
										$coords[] = $point * $scale;
									}
									$coords = implode(",",$coords);
									if($visible) {
										echo "<area shape=\"poly\" coords=\"$coords\" href=\"javascript:void(0);\" onclick=\"";
										if($locked) {
											echo "alert('{$lang->display('Layer Alert')}');\"";
										} else if($lock) {
											echo "alert('{$lang->display('Box Alert')}');\"";
										} else {
											#if($row_task['taskStatus']>7) {
												$query_proof = sprintf("SELECT paraedit.time, users.username
																		FROM paraedit
																		LEFT JOIN users ON paraedit.user_id = users.userID
																		LEFT JOIN paralinks ON paraedit.pl_id = paralinks.uID
																		WHERE paraedit.task_id = %d
																		AND paralinks.BoxID = %d
																		ORDER BY paraedit.time DESC
																		LIMIT 1",
																		$taskID,
																		$boxID);
												$result_proof = mysql_query($query_proof,$conn) or die($query_proof.mysql_error());
												if(mysql_num_rows($result_proof)) {
													$row_proof = mysql_fetch_assoc($result_proof);
													$title="{$lang->display('Last Update')}: {$row_proof['username']} ".date(FORMAT_TIME,$row_proof['time']);
													if($toggle) {
														$style = get_line_style($colour,DEFAULT_DASHED_LINE_COLOUR,DEFAULT_DASHED_LINE_PIXELS);
														@imagesetstyle($im, $style);
														@imagepolygon($im,$points,4,IMG_COLOR_STYLED);
														$icon = @imagecreatefrompng(ROOT.DEFAULT_ICON_AMEND);
														@imagecopyresized($im,$icon,$points[0],$points[1],0,0,DEFAULT_ICON_WIDTH,DEFAULT_ICON_HEIGHT,48,48);
														@imagedestroy($icon);
													}
												}
												if($row_task['taskStatus']<10) {
													echo "Popup('helper','blur');DoAjax('taskID=$taskID&page=$page&boxID=$TheBoxID','window','modules/mod_proofread.php');\"";
												} else {
													echo "alert('{$lang->display('Signed off Translation Task')}');\"";
													$title = $lang->display('Signed off Translation Task');
												}
											#} else {
												#echo "goToURL('parent','index.php?layout=translate&id=$taskID&page=$page&box=$TheBoxID');\"";
											#}
											echo " title=\"$title\"";
										}
										echo " />";
									}
								}
								echo "</map>";
								if($toggle) {
									$thumbnail = POSTVIEW_DIR.EDITS_DIR.basename($thumbnail);
									@imagejpeg($im,ROOT.$thumbnail);
									@imagedestroy($im);
								}
								echo "<img style=\"width:{$resize}px;\" src=\"".$thumbnail."?".filemtime(ROOT.$thumbnail)."\" usemap=\"#boxMap\" border=\"0\" />";
                                                                
                                                                
							} else {
								echo "<img src=\"".IMG_PATH."img_missing.png\" />";
							}
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="javascripts/ajax.js"></script>
<script type="text/javascript" src="javascripts/datepicker.js"></script>
<script type="text/javascript" src="javascripts/dom_drag.js"></script>
<script language="javascript">Drag.init(document.getElementById('handle'),document.getElementById('root'));</script>
<script type="text/javascript" src="javascripts/datepicker.js"></script>
<script type="text/javascript" src="javascripts/tm.js"></script>
<?php
if(!empty($_SESSION['show_pages'])) {
	$apend = 'SetClassName(\'pageTool\',\'pageToolOn\');display(\'pageColL\');ResetDiv(\'pageColL\');DoAjax(\'layout='.$layout.'&artworkID='.$artworkID.'&page='.$page.'&taskID='.$taskID.'&istemplate=0&show_pages=1\',\'pageColL\',\'modules/mod_page_previews.php\');';
} else {
	$apend = "";
}
file_put_contents(ROOT."layouts/$layout/apend.js",$apend);
?>