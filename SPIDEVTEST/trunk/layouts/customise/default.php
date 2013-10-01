<?php
BuildHelperDiv($row_task['artworkName'].' - '.$lang->display('Task Home').' - '.$lang->display('Customise'));
$navStatus = array("mytasks");
require_once(MODULES.'mod_header.php');
BuildPageIntro($lang->display('Customise'),$lang->display('Please click on your artwork preview to customise settings.'));
?>
<div id="wrapperWhite">
	<div class="controlScroll">
		<div class="controlselectScroll">
			<!-- Toolbar -->
			<div class="toolbar">
				<div class="title">
					<div class="ico">
						<?php echo '<img src="'.IMG_PATH.'header/ico_customise.png">'; ?>
					</div>
					<div class="txt">
						<?php echo $row_task['artworkName'].' :: <img src="images/flags/'.$row_task['flag'].'" title="'.$lang->display($row_task['languageName']).'" /> <img src="'.IMG_PATH.'flag_to.gif" title="'.$lang->display('to be translated to').'" /> <img src="images/flags/'.$row_desiredRs['flag'].'" title="'.$lang->display($row_desiredRs['languageName']).'" /> :: '.$lang->display('Customise'); ?>
					</div>
				</div>
				<div class="options">
					<!-- Download -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Download'); ?>">
						<a href="javascript:void(0);" onclick="Popup('helper','blur');DoAjax('id=<?php echo $taskID; ?>','window','modules/mod_task_download.php');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_download.png">'; ?></div>
							<div><?php echo $lang->display('Download'); ?></div>
						</a>
					</div>
					<!-- Upload -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Tweak'); ?>">
						<a href="javascript:void(0);" onclick="Popup('helper','blur');DoAjax('artworkID=<?php echo $artworkID; ?>&taskID=<?php echo $taskID; ?>','window','modules/mod_upload_tweaks.php');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_upload.png">'; ?></div>
							<div><?php echo $lang->display('Upload'); ?></div>
						</a>
					</div>
					<!-- Fonts -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Fonts'); ?>">
						<a href="javascript:void(0);" onclick="window.location='/index.php?layout=cp_font_sub&taskID=<?php echo $taskID; ?>&show=Used';">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'header/ico_font.png">'; ?></div>
							<div><?php echo $lang->display('Fonts'); ?></div>
						</a>
					</div>
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
			<div class="mainwrap">
				<form
					id="mainform"
					name="mainform"
					action="index.php?layout=customise&id=<?php echo $taskID; ?>&page=<?php echo $page; ?>"
					method="POST"
					enctype="multipart/form-data"
				>
				<div class="pageBar">
					<?php
						$ret = BuildZoomIcons($artworkID,$page,$taskID);
						$resize = $ret['resize'];
						$PageScale = $ret['PageScale'];
						$scale = $ret['scale'];
						$toggle = $ret['toggle'];
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
							<select
								class="input"
								onfocus="this.className='inputOn'"
								onblur="this.className='input'"
								name="filter_box"
								id="filter_box"
								onchange="SubmitForm('mainform','');"
								title="<?php echo $lang->display('Select Box Type'); ?>"
							>
							<?php BuildBoxTypeList($box_type); ?>
							</select>
						</div>
					</div>
					<div class="clear"></div>
				</div>
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
								$sub .= !empty($box_type) ? sprintf(" AND boxes.Type = '%s'",mysql_real_escape_string($box_type)) : "";
								$sub .= $DB->get_task_trial_status($taskID) ? " AND boxes.heading = 1" : "";
								$query_box = sprintf("SELECT boxes.uID, boxes.Type, boxes.Left, boxes.Right, boxes.Top, boxes.Bottom, boxes.Angle,
													artwork_layers.visible, artwork_layers.locked, artwork_layers.colour, box_properties.lock,
													(boxes.Right-boxes.Left) AS BoxWidth, (boxes.Bottom-boxes.Top) AS BoxHeight
													FROM boxes
													LEFT JOIN artwork_layers ON boxes.LayerID = artwork_layers.id
													LEFT JOIN pages ON pages.uID = boxes.PageID
													LEFT JOIN box_properties ON (box_properties.box_id = boxes.uID AND box_properties.task_id = 0)
													LEFT JOIN paralinks ON boxes.uID = paralinks.BoxID
													WHERE pages.ArtworkID = %d
													AND pages.Page IN (0,%d)
													%s
													GROUP BY boxes.uID
													ORDER BY
														(BoxWidth*BoxHeight) ASC,
														BoxWidth ASC,
														BoxHeight ASC",
													$artworkID,
													$page,
													$sub);
								$result_box = mysql_query($query_box, $conn) or die(mysql_error());
								while($row_box = mysql_fetch_assoc($result_box)) {
									$boxID = $row_box['uID'];
									$type = $row_box['Type'];
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
									$title = $lang->display('Amend');
									if($toggle) @imagepolygon($im,$points,4,$border_colour);
									if($type=="PICT") {
										$query_edit = sprintf("SELECT UNIX_TIMESTAMP(images.time) AS time, users.username
															FROM img_usage
															LEFT JOIN images ON img_usage.img_id = images.id
															LEFT JOIN users ON images.user_id = users.userID
															WHERE img_usage.artwork_id = %d
															AND img_usage.box_id = %d
															AND img_usage.task_id = %d
															ORDER BY images.time DESC
															LIMIT 1",
															$artworkID,
															$boxID,
															$taskID);
										$result_edit = mysql_query($query_edit, $conn) or die(mysql_error());
										if(mysql_num_rows($result_edit)) {
											$row_edit = mysql_fetch_assoc($result_edit);
											$title = $lang->display('Last Update').": ".$row_edit['username']." ".date(FORMAT_TIME,$row_edit['time']);
											if($toggle) {
												$style = get_line_style($colour,DEFAULT_DASHED_LINE_COLOUR,DEFAULT_DASHED_LINE_PIXELS);
												@imagesetstyle($im,$style);
												$border_colour = IMG_COLOR_STYLED;
											}
										}
										if($toggle) {
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
											echo "Popup('helper','blur');DoAjax('artworkID=$artworkID&taskID=$taskID&page=$page&boxID=$boxID','window','modules/mod_task_customise.php');\" title=\"$title\"";
										}
										echo "/>";
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
				<input type="hidden" name="form" id="form">
				</form>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="javascripts/ajax.js"></script>
<script type="text/javascript" src="javascripts/dom_drag.js"></script>
<script language="javascript">Drag.init(document.getElementById('handle'),document.getElementById('root'));</script>
<?php
if(!empty($_SESSION['show_pages'])) {
	$apend = 'SetClassName(\'pageTool\',\'pageToolOn\');display(\'pageColL\');ResetDiv(\'pageColL\');DoAjax(\'layout='.$layout.'&artworkID='.$artworkID.'&page='.$page.'&taskID='.$taskID.'&istemplate=0&show_pages=1\',\'pageColL\',\'modules/mod_page_previews.php\');';
} else {
	$apend = "";
}
file_put_contents(ROOT."layouts/$layout/apend.js",$apend);
?>