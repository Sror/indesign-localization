<?php
BuildHelperDiv($row_task['artworkName'].' - '.$lang->display('Work Online'));
$navStatus = array("mytasks");
require_once(MODULES.'mod_header.php');
BuildPageIntro('<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=mytasks\');" title="'.$lang->display('My Tasks').'">'.$lang->display('My Tasks').'</a>'.BREADCRUMBS_ARROW.'<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=task&id='.$taskID.'\');">'.$lang->display('Task Home').'</a>'.BREADCRUMBS_ARROW.$lang->display('Work Online'));
?>
<div id="wrapperWhite">
	<div class="controlselectScroll">
		<!-- Toolbar -->
		<div class="toolbar">
			<div class="title">
				<div class="ico">
					<?php echo '<img src="'.IMG_PATH.'header/ico_online.png">'; ?>
				</div>
				<div class="txt">
					<?php echo $lang->display('Visual Translation'); ?>
					<div class="intro"><?php echo $lang->display('Work Online Intro'); ?></div>
				</div>
			</div>
			<div class="options">
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
				<?php if($row_task['taskStatus']==8 && $acl->acl_check("taskworkflow","approve",$_SESSION['companyID'],$_SESSION['userID'])) { ?>
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
				<!-- Close -->
				<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Close'); ?>">
					<a href="javascript:void(0);" onclick="SubmitForm('mainform','close');">
						<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_close.png">'; ?></div>
						<div><?php echo $lang->display('Close'); ?></div>
					</a>
				</div>
			</div>
			<div class="clear"></div>
		</div>
	</div>
	<form
		id="mainform"
		name="mainform"
		action="index.php?layout=<?php echo $layout; ?>&id=<?php echo $taskID; ?>&page=<?php echo $page; ?>&box=<?php echo $boxID; ?>&pl=<?php echo $pl; ?>"
		method="POST"
		enctype="multipart/form-data"
		onsubmit="return false;"
	>
	<div class="pageBar">
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
			</div>
		</div>
		<div class="clear"></div>
	</div>
	<input type="hidden" name="form" id="form">
	</form>
	<div class="titleBar" onclick="SlideDiv('translationPreview');swaparrow('translationPreviewArrow');">
		<div class="icon"><img src="<?php echo IMG_PATH; ?>ico_previewartwork.gif" title="<?php echo $lang->display('Translation Preview'); ?>" /></div>
		<div class="topic"><?php echo $lang->display('Translation Preview'); ?></div>
		<div class="arrow"><img id="translationPreviewArrow" src="images/ico_minus.png" /></div>
		<div class="clear"></div>
	</div>
	<div id="translationPreview" style="display:block;overflow:hidden;height:250px;">
		<div class="previewPanel" id="bothView" <?php if($view==1) echo 'style="display:none;"'; ?>>
			<div class="left">
				<div class="previewBox">
					<div class="title">
						<div class="languageIcon">
							<?php echo "<img src=\"images/flags/".$row_task['flag']."\" title=\"".$lang->display('Source Language')."\" />"; ?>
						</div>
						<div class="topic"><?php echo $lang->display('Textbox').": ".$lang->display($row_task['languageName']); ?></div>
					</div>
					<div class="body">
						<div class="pic">
							<?php
								if(!empty($boxID)) {
									$pre_box = $DB->RebuildBoxPreview($artworkID,$boxID);
									echo "<a href=\"$pre_box?".filemtime(ROOT.$pre_box)."\" class=\"jqzoom\"><img src=\"$pre_box?".filemtime(ROOT.$pre_box)."\" /></a>";
								}
							?>
						</div>
						<div class="clear"></div>
					</div>
				</div>
				<div class="previewBox">
					<div class="title">
						<div class="documentIcon">
							<?php echo "<img src=\"".IMG_PATH."ico_previewbox_document.gif\" title=\"".$lang->display('Source Artwork')."\" />"; ?>
						</div>
						<div class="topic"><?php echo $lang->display('Document').": ".$lang->display($row_task['languageName']); ?></div>
					</div>
					<div class="body">
						<div class="pic">
							<?php
								$pre_page = PREVIEW_DIR.$PreviewFile;
								if(file_exists(ROOT.$pre_page)) {
									echo "<a href=\"$pre_page?".filemtime(ROOT.$pre_page)."\" class=\"jqzoom\"><img src=\"$pre_page?".filemtime(ROOT.$pre_page)."\" /></a>";
								}
							?>
						</div>
						<div class="clear"></div>
					</div>
				</div>
			</div>
			<div class="right">
				<div class="previewBox">
					<div class="title">
						<div class="languageIcon">
							<?php echo "<img src=\"images/flags/".$row_desiredRs['flag']."\" title=\"".$lang->display('Desired Language')."\" />"; ?>
						</div>
						<div class="topic"><?php echo $lang->display('Textbox').": ".$lang->display($row_desiredRs['languageName']); ?></div>
					</div>
					<div class="body">
						<div class="pic">
							<?php
								if(!empty($boxID)) {
									$post_box = $DB->RebuildBoxPreview($artworkID,$boxID,$taskID);
									echo "<a href=\"$post_box?".filemtime(ROOT.$post_box)."\" class=\"jqzoom\"><img src=\"$post_box?".filemtime(ROOT.$post_box)."\" /></a>";
								}
							?>
						</div>
						<div class="clear"></div>
					</div>
				</div>
				<div class="previewBox">
					<div class="title">
						<div class="documentIcon">
							<?php echo "<img src=\"".IMG_PATH."ico_previewbox_document.gif\" title=\"".$lang->display('Translated Artwork')."\" />"; ?>
						</div>
						<div class="topic"><?php echo $lang->display('Document').": ".$lang->display($row_desiredRs['languageName']); ?></div>
					</div>
					<div class="body">
						<div class="pic">
						<?php
							$post_page = POSTVIEW_DIR.BareFilename($PreviewFile)."-".$taskID.".jpg";
							if(file_exists(ROOT.$post_page)) {
								echo "<a href=\"$post_page?".filemtime(ROOT.$post_page)."\" class=\"jqzoom\"><img src=\"$post_page?".filemtime(ROOT.$post_page)."\" /></a>";
							}
						?>
						</div>
						<div class="clear"></div>
					</div>
				</div>
			</div>
			<div class="clear"></div>
		</div>
		<div class="previewPanel" id="textboxView" <?php if($view==0) echo 'style="display:none;"'; ?>>
			<div class="left">
				<div class="previewBox2">
					<div class="title">
						<div class="languageIcon">
							<?php echo "<img src=\"images/flags/".$row_task['flag']."\" title=\"".$lang->display('Source Language')."\" />"; ?>
						</div>
						<div class="topic"><?php echo $lang->display('Textbox').": ".$lang->display($row_task['languageName']); ?></div>
					</div>
					<div class="body">
						<div class="pic">
							<?php
								if(!empty($boxID)) {
									echo "<img src=\"$pre_box?".filemtime(ROOT.$pre_box)."\" />";
								}
							?>
						</div>
						<div class="clear"></div>
					</div>
				</div>
			</div>
			<div class="right">
				<div class="previewBox2">
					<div class="title">
						<div class="languageIcon">
							<?php echo "<img src=\"images/flags/".$row_desiredRs['flag']."\" title=\"".$lang->display('Desired Language')."\" />"; ?>
						</div>
						<div class="topic"><?php echo $lang->display('Textbox').": ".$lang->display($row_desiredRs['languageName']); ?></div>
					</div>
					<div class="body">
						<div class="pic">
							<?php
								if(!empty($boxID)) {
									echo "<img src=\"$post_box?".filemtime(ROOT.$post_box)."\" />";
								}
							?>
						</div>
						<div class="clear"></div>
					</div>
				</div>
			</div>
			<div class="clear"></div>
		</div>
	</div>
	<div id="previewPanel_collapse">
		<div class="left">
			<div>
				<input name="optionSwitch" type="checkbox" onclick="goToURL('parent','<?php echo "index.php?layout=translate&id=$taskID&page=$page&box=$boxID&view=$viewo"; ?>');" <?php if($view==1) echo 'checked="checked"'; ?>>
				<?php echo $lang->display('View Textboxes Only'); ?>
			</div>
		</div>
		<div class="right">
			<?php
				if($lock) {
					echo "<img src=\"".IMG_PATH."ico_locked.png\" title=\"".$lang->display('Edit Locked')."\" />";
				} else {
					echo "<input name=\"mtSwitch\" type=\"checkbox\" onclick=\"goToURL('parent','index.php?layout=translate&id=$taskID&page=$page&box=$boxID&mt=$mto');\"";
					if($mt==1) {
						echo 'checked="checked"';
					}
					echo "> ".$lang->display('Load Machine Translation');
				}
			?>
		</div>
		<div class="clear"></div>
	</div>
	<div id="sentencesTable">
		<div class="titleBar" onclick="showandhide('sentencesScroll','sentencesScrollArrow')">
			<div class="icon"><img src="<?php echo IMG_PATH; ?>ico_sentences.gif" title="<?php echo $lang->display('Paragraphs in This Textbox'); ?>" /></div>
			<div class="topic"><?php echo $lang->display('Paragraphs in This Textbox'); ?></div>
			<div class="arrow"><img id="sentencesScrollArrow" src="images/ico_minus.png" /></div>
			<div class="clear"></div>
		</div>
		<div id="sentencesBox">
			<table cellpadding="3" cellspacing="0" border="0" width="100%">
				<tr>
					<th width="7%" class="indicator"><?php echo $lang->display('Order'); ?></th>
					<th width="86%" class="para"><?php echo $lang->display('Paragraph'); ?></th>
					<th width="7%" class="indicator"><?php echo $lang->display('Ignore'); ?></th>
				</tr>
				<?php
					//Get the box that contain the paragraphs (e.g. linked box)
					$TheBoxID = $DB->GetTheBox($boxID);
					$Translator = new Translator();
					if(!$lock && $mt==1) {
						//Pre Cache
						$paras = array();
						$result = $Translator->get_all_paras($artworkID,$taskID,$TheBoxID,$pl);
						while($row = mysql_fetch_assoc($result)){
							$PL = $row['PL'];
							$para_row = $Translator->GetParaByPL($PL);
							if($para_row === false) {
								$ParaText = $row['ParaText'];
							} else {
								$ParaText = $para_row['ParaText'];
							}
							$paras[] = $ParaText;
						}
						
						$MT = new Google();
						$trans = $MT->MassMT($paras,BareFilename($row_task['flag']),BareFilename($row_desiredRs['flag']));
						if(!empty($trans)) {
							$MT->InsertMTCache($paras,BareFilename($row_task['flag']),BareFilename($row_desiredRs['flag']),$trans);
						}
					}
					// list paragraphs
					$result_para = $Translator->get_all_paras($artworkID,$taskID,$TheBoxID,$pl);
					$found_para = mysql_num_rows($result_para);
					if($found_para) {
						while($row_para = mysql_fetch_assoc($result_para)) {
							$PL = $row_para['PL'];
							$SO = $DB->GetStoryOrder($PL,$taskID);
							$para_row = $Translator->GetParaByPL($PL);
							if($para_row === false) {
								$ParaText = $row_para['ParaText'];
								$PG = $row_para['ParaGroup'];
							} else {
								$ParaText = $para_row['ParaText'];
								$PG = $para_row['ParaGroup'];
							}
				?>
				<tr id="<?php echo "paraRow".$PL; ?>" class="off">
					<td align="center" valign="middle" width="5%" class="indicator">
						<div id="story_order_<?php echo $PL ?>">
							<select
								class="input"
								onfocus="this.className='inputOn'"
								onblur="this.className='input'"
								name="order[<?php echo $PL; ?>]"
								id="order[<?php echo $PL; ?>]"
								onchange="ResetDiv('story_order_<?php echo $PL ?>');DoAjax('pl=<?php echo $PL ?>&no=<?php echo $found_para ?>&task_id=<?php echo $taskID ?>&order='+this.value,'story_order_<?php echo $PL ?>','modules/mod_para_order.php');"
							>
							<?php BuildOrders($found_para,$SO); ?>
							</select>
						</div>
					</td>
					<td class="para">
						<!-- Start of Machine Translation in Balloon -->
						<?php
							if(!$lock && $mt==1) {
								echo "<div id=\"otext{$PL}\" name=\"otext$PL\" style=\"display:none;\"><a href=\"#otext$PL\" onclick=\"setMT($taskID,$PL,'otext$PL','paragraphBox$PL');\">".$MT->GetMT($ParaText,BareFilename($row_task['flag']),BareFilename($row_desiredRs['flag']))."</a></div>";
								$balloon = " onmouseover=\"javascript:var balloon = new Balloon; balloon.showTooltip(event,'load:otext{$PL}',1);\"";
							} else {
								$balloon = "";
							}
						?>
						<!-- End of Machine Translation in Balloon -->
						<div>
							<?php echo "<div$balloon>".html_display_para($ParaText)."</div>"; ?>
							<textarea
								class="input"
								id="<?php echo "paragraphBox$PL"; ?>"
								name="<?php echo "paragraphBox$PL"; ?>"
								onkeydown="return catchTab(this,event);"
								onfocus="this.className='inputOn';<?php echo "tBox='paragraphBox$PL';onandoff('paragraphBox$PL','paraRow$PL','statusChecker$PL');ListTM($taskID,$PL,'TMDiv');moveTMbox('paraRow$PL');doResize('paragraphBox$PL',120);"; ?>"
								onchange="<?php echo "SaveTranslation($taskID,$PL,this.value,".PARA_USER.");"; ?>"
								onblur="this.className='input';<?php echo "onandoff('paragraphBox$PL','paraRow$PL','statusChecker$PL');"; ?>"
								rows="1"
								<?php if($lock) echo 'disabled="disabled"'; ?>
							><?php
								$para_trans = $Translator->GetTransPara($taskID,$PL);
								if(!empty($para_trans)) {
									echo "\n$para_trans";
								}
							?></textarea>
						</div>
					</td>
					<td align="center" valign="middle" class="indicator">
						<div id="ignore<?php echo $PL; ?>">
							<input type="checkbox" id="ignore_check_<?php echo $PL; ?>" name="ignore_check_<?php echo $PL; ?>" value="1" <?php if($Translator->CheckParaIgnore($PL,$taskID)) { echo ' checked="checked"';$ignore=0; } else { $ignore=1; } ?> onclick="ResetDiv('<?php echo "ignore$PL"; ?>');DoAjax('id=<?php echo $taskID; ?>&pl=<?php echo $PL; ?>&ignore=<?php echo $ignore; ?>','ignore<?php echo $PL; ?>','modules/mod_para_ignore.php');" />
						</div>
					</td>
				</tr>
				<?php
						}
					} else {
						echo "<tr><td colspan=\"3\" align=\"center\"><div class=\"alert\">".$lang->display('No paragraph in this textbox.')."</div></td></tr>";
					}
				?>
			</table>
		</div>
		<div class="btnBar">
			<?php
				echo '<span class="btn">';
				$query_previousboxRs = sprintf("SELECT boxes.uID AS BoxID, pages.Page
												FROM boxes
												LEFT JOIN pages ON boxes.PageID = pages.uID
												LEFT JOIN box_properties ON ( box_properties.box_id = boxes.uID AND box_properties.task_id IN (0,%d) )
												WHERE pages.ArtworkID = %d
												AND boxes.order < %d
												AND (box_properties.lock IS NULL OR box_properties.lock = 0)
												$trial
												ORDER BY boxes.order DESC
												LIMIT 1",
												$taskID,
												$artworkID,
												$order);
				$previousboxRs = mysql_query($query_previousboxRs, $conn) or die(mysql_error());
				$totalRows_previousboxRs = mysql_num_rows($previousboxRs);
				if ($totalRows_previousboxRs) {
					$row_previousboxRs = mysql_fetch_assoc($previousboxRs);
					echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=translate&id='.$taskID.'&page='.$row_previousboxRs['Page'].'&box='.$row_previousboxRs['BoxID'].'\');"><img src="'.IMG_PATH.'toolbar/ico_prev_on.png" title="'.$lang->display('Previous').'"></a>';
				} else {
					echo '<img src="'.IMG_PATH.'toolbar/ico_prev_off.png">';
				}
				echo '</span>';
				echo '<span class="btn">';
				echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=translate&id='.$taskID.'&page='.$page.'&box='.$boxID.'&do=refresh\');"><img src="'.IMG_PATH.'toolbar/ico_refresh.png" title="'.$lang->display('Refresh').'"></a>';
				echo '</span>';
				echo '<span class="btn">';
				$query_nextboxRs = sprintf("SELECT boxes.uID AS BoxID, pages.Page
											FROM boxes
											LEFT JOIN pages ON boxes.PageID = pages.uID
											LEFT JOIN box_properties ON ( box_properties.box_id = boxes.uID AND box_properties.task_id IN (0,%d) )
											WHERE pages.ArtworkID = %d
											AND boxes.order > %d
											AND (box_properties.lock IS NULL OR box_properties.lock = 0)
											$trial
											ORDER BY boxes.order ASC
											LIMIT 1",
											$taskID,
											$artworkID,
											$order);
				$nextboxRs = mysql_query($query_nextboxRs, $conn) or die(mysql_error());
				$totalRows_nextboxRs = mysql_num_rows($nextboxRs);
				if ($totalRows_nextboxRs) {
					$row_nextboxRs = mysql_fetch_assoc($nextboxRs);
					echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=translate&id='.$taskID.'&page='.$row_nextboxRs['Page'].'&box='.$row_nextboxRs['BoxID'].'\');"><img src="'.IMG_PATH.'toolbar/ico_next_on.png" title="'.$lang->display('Next').'"></a>';
				} else {
					echo '<img src="'.IMG_PATH.'toolbar/ico_next_off.png">';
				}
				echo '</span>';
			?>
		</div>
	</div>
	<div id="tabTable">
		<div class="title">
			<div>
				<ul id="maintab" class="shadetabs">
					<li id="TMList" class="selected" onclick="activatetab('TMList','TMDiv','CList','CDiv','SEList','SEDiv')" title="<?php echo $lang->display('Translation Memory'); ?>">
						<img src="<?php echo IMG_PATH; ?>ico_translation_memory.gif" />
						<?php echo $lang->display('Translation Memory'); ?>
					</li>
					<li id="SEList" onclick="activatetab('SEList','SEDiv','TMList','TMDiv','CList','CDiv')" title="<?php echo $lang->display('Style Editor'); ?>">
						<img src="<?php echo IMG_PATH; ?>ico_style_editor.gif" />
						<?php
							echo $lang->display('Style Editor');
							if($overflow) {
								echo ' <img src="'.IMG_PATH.'ico_error.png" title="'.$lang->display('Text Overflow').'">';
							}
						?>
					</li>
					<li id="CList" onclick="activatetab('CList','CDiv','TMList','TMDiv','SEList','SEDiv')" title="<?php echo $lang->display('Comments'); ?>">
						<img src="<?php echo IMG_PATH; ?>ico_notes.png" />
						<?php
							echo $lang->display('Comments');
							$query_comment = sprintf("SELECT comments.*,
													users.username
													FROM comments
													LEFT JOIN users ON comments.user_id = users.userID
													WHERE comments.task_id = %d
													AND comments.box_id = %d
													ORDER BY comments.time DESC",
													$taskID,
													$boxID);
							$result_comment = mysql_query($query_comment, $conn) or die(mysql_error());
							$found_comment = mysql_num_rows($result_comment);
							if($found_comment) {
								echo " [$found_comment]";
							}
						?>
					</li>
				</ul>
			</div>
		</div>
		<div class="clear"></div>
		<div id="tabScroll">
			<div class="tabcontentstyle">
				<div id="TMDiv">
					<div style="padding:10px;"><?php echo $lang->display('Please click in the textbox to retrieve translation memory.'); ?></div>
					<!-- Translation memory will appear here. -->
				</div>
				<div id="SEDiv" class="tabcontent">
					<div class="contentdiv">
						<?php if($acl->acl_check("artworks","edit",$_SESSION['companyID'],$_SESSION['userID'])) { ?>
							<div>
								<form
									action="<?php echo $current_layout; ?>"
									name="styleForm"
									method="post"
									enctype="multipart/form-data"
								>
									<table width="100%" cellspacing="0" cellpadding="3" border="0">
										<tr>
											<td><?php echo $lang->display('Left'); ?></td>
											<td>
												<input
													type="text"
													class="input"
													onfocus="this.className='inputOn'"
													onblur="this.className='input'"
													name="left"
													id="left"
													value="<?php echo $left; ?>"
													size="2"
													maxlength="4"
												/>
											</td>
											<td><?php echo $lang->display('Width'); ?></td>
											<td>
												<input
													type="text"
													class="input"
													onfocus="this.className='inputOn'"
													onblur="this.className='input'"
													name="boxwidth"
													id="boxwidth"
													value="<?php echo $right-$left; ?>"
													size="2"
													maxlength="4"
												/>
											</td>
										</tr>
										<tr>
											<td><?php echo $lang->display('Top'); ?></td>
											<td>
												<input
													type="text"
													class="input"
													onfocus="this.className='inputOn'"
													onblur="this.className='input'"
													name="top"
													id="top"
													value="<?php echo $top; ?>"
													size="2"
													maxlength="4"
												/>
											</td>
											
											<td><?php echo $lang->display('Height'); ?></td>
											<td>
												<input
													type="text"
													class="input"
													onfocus="this.className='inputOn'"
													onblur="this.className='input'"
													name="boxheight"
													id="boxheight"
													value="<?php echo $bottom-$top; ?>"
													size="2"
													maxlength="4"
												/>
											</td>
										</tr>
										<tr>
											<td><?php echo $lang->display('Resize'); ?></td>
											<td>
												<input
													type="checkbox"
													class="checkbox"
													name="resize"
													id="resize"
													value="1"
													<?php if($resize) echo 'checked="checked"'; ?>
												/>
												<?php if($overflow) echo ' <img src="'.IMG_PATH.'ico_error.png" title="'.$lang->display('Text Overflow').'">'; ?>
											</td>
											<td><?php echo $lang->display('Angle'); ?></td>
											<td>
												<input
													type="text"
													class="input"
													onfocus="this.className='inputOn'"
													onblur="this.className='input'"
													name="angle"
													id="angle"
													value="<?php echo $angle; ?>"
													size="2"
													maxlength="4"
												/>
											</td>
										</tr>
										<tr>
											<td><?php echo $lang->display('Lock'); ?></td>
											<td>
												<input
													type="checkbox"
													class="checkbox"
													name="lock"
													id="lock"
													value="1"
													<?php if($lock) echo 'checked="checked"'; ?>
												/>
											</td>
											<td class="highlight"><?php echo $lang->display('Reference'); ?>:</td>
											<td><?php echo $box_uid; ?></td>
										</tr>
										<tr>
											<td colspan="4" align="center">
												<input
													type="button"
													class="btnDo"
													onmousemove="this.className='btnOn'"
													onmouseout="this.className='btnDo'"
													title="<?php echo $lang->display('Save'); ?>"
													value="<?php echo $lang->display('Save'); ?>"
													onclick="validateForm('left','Left','RisNum','right','Right','RisNum','top','Top','RisNum','bottom','Bottom','RisNum'); if(document.returnValue) { SubmitForm('styleForm','save'); }"
												/>
												<input
													type="button"
													class="btnOff"
													onmousemove="this.className='btnOn'"
													onmouseout="this.className='btnOff'"
													title="<?php echo $lang->display('Restore'); ?>"
													value="<?php echo $lang->display('Restore'); ?>"
													onclick="SubmitForm('styleForm','restore');"
												/>
											</td>
										</tr>
									</table>
									<input type="hidden" name="form" id="form">
								</form>
							</div>
						<?php } else echo $lang->display('N/A'); ?>
					</div>
				</div>
				<div id="CDiv" class="tabcontent">
					<div class="contentdiv">
						<div id="commentList">
						<?php
							while($row_comment = mysql_fetch_assoc($result_comment)) {
								echo '<div class="bgOption" onmouseover="this.className=\'hover\'" onmouseout="this.className=\'bgOption\'" style="padding:5px;">';
								if($acl->acl_check("taskworkflow","deletecomments",$_SESSION['companyID'],$_SESSION['userID'])) {
									echo "<a href=\"index.php?layout=translate&id=$taskID&page=$page&box=$boxID&do=rmcomment&ref=".$row_comment['id']."\"><img src=\"".IMG_PATH."btn_s_delete.png\" title=\"".$lang->display('Delete')."\"></a> ";
								} else {
									echo "<img src=\"".IMG_PATH."ico_comment.png\" /> ";
								}
								echo "<a href=\"index.php?layout=user&id=".$row_comment['user_id']."\">".$row_comment['username']."</a> <span class=\"grey\">".date(FORMAT_TIME,strtotime($row_comment['time']))."</span>";
								echo "<div>".nl2br($row_comment['comment'])."</div>";
								if(!empty($row_comment['attachment'])) {
									echo "<div>";
									echo "<img src=\"".IMG_PATH."ico_attachment.png\" title=\"{$lang->display('Attachment')}\"> <a href=\"index.php?layout=$layout&id=$taskID&page=$page&do=attachment&ref={$row_comment['attachment']}\">{$row_comment['attachment']}</a>";
									if(ValidateMedia(REPOSITORY_DIR.$row_comment['attachment'])) {
										echo " <a href=\"javascript:void(0);\" onclick=\"Popup('helper','blur');DoAjax('ref={$row_comment['attachment']}','window','modules/mod_media_play.php');\"><img src=\"".IMG_PATH."arrow_right.png\" title=\"{$lang->display('Play')}\" />{$lang->display('Play')}</a>";
									}
									echo "</div>";
								}
								echo "</div>";
							}
						?>
						</div>
						<div>
							<form action="<?php echo $current_layout; ?>" name="commentForm" method="post" enctype="multipart/form-data" onsubmit="validateForm('comment','Comment','R'); if(document.returnValue) { Popup('loadingme','waiting'); }">
							<table width="100%" cellspacing="0" cellpadding="3" border="0">
								<tr>
									<td>
										<div id="textInput">
											<textarea
												class="input"
												onfocus="this.className='inputOn';doResize('comment',60);"
												onblur="this.className='input'"
												id="comment"
												name="comment"
												rows="1"
											></textarea>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div><a href="javascript:void(0);" onclick="openandclose('attachment')"><?php echo $lang->display('Attachment'); ?>?</a></div>
										<div id="attachment" style="display:none;">
											<input
												type="file"
												class="input"
												onfocus="this.className='inputOn'"
												onblur="this.className='input'"
												id="attachment"
												name="attachment"
											/>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<input
											type="submit"
											class="btnDo"
											onmousemove="this.className='btnOn'"
											onmouseout="this.className='btnDo'"
											title="<?php echo $lang->display('Add a Comment'); ?>"
											value="<?php echo $lang->display('Add a Comment'); ?>"
										/>
										<input
											type="button"
											class="btnOff"
											onmousemove="this.className='btnOn'"
											onmouseout="this.className='btnOff'"
											title="<?php echo $lang->display('Cancel'); ?>"
											value="<?php echo $lang->display('Cancel'); ?>"
											onclick="hidediv('addComment');display('commentList');"
										/>
									</td>
								</tr>
							</table>
							<input type="hidden" name="update" value="comment">
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="clear"></div>
</div>
<script type="text/javascript" src="javascripts/tm.js"></script>
<script type="text/javascript" src="javascripts/encoder.js"></script>
<script type="text/javascript" src="javascripts/ajax.js"></script>
<script type="text/javascript" src="javascripts/dom_drag.js"></script>
<script language="javascript">Drag.init(document.getElementById('handle'),document.getElementById('root'));</script>
<script type="text/javascript" src="javascripts/balloon/balloon.js"></script>
<script type="text/javascript" src="javascripts/balloon/balloon.config.js"></script>
<script type="text/javascript" src="javascripts/balloon/yahoo-dom-event.js"></script>
<script type="text/javascript" src="javascripts/jquery/jquery-1.2.6.js"></script>
<script type="text/javascript" src="javascripts/jquery/jquery.jqzoom1.0.1.js"></script>
<script type="text/javascript">
	var options =
	{
		zoomWidth: 300,
		zoomHeight: 300,
		title: false,
		zoomType: 'standard',
		showPreload: true,
		preloadText: 'Loading Zoom...',
		preloadPosition: 'center'
	}
	$(function() {
		$(".jqzoom").jqzoom(options);
	});
</script>