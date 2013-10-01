<?php
BuildHelperDiv($artwork_row['artworkName'].' - '.$lang->display('Amend Artwork'));
if(!$is_guest) {
	$navStatus = array("campaigns");
	require_once(MODULES.'mod_header.php');
}
$breadcrumbs = "";
if(!$is_guest) $breadcrumbs .= '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=campaigns\');">'.$lang->display('Campaigns').'</a>'.BREADCRUMBS_ARROW.'<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=campaign&id='.$artwork_row['campaignID'].'\');">'.DisplayString($artwork_row['campaignName']).'</a>'.BREADCRUMBS_ARROW.'<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=artwork&id='.$artworkID.'\');">'.DisplayString($artwork_row['artworkName']).'</a>'.BREADCRUMBS_ARROW;
$breadcrumbs .= $lang->display('Amend Artwork');
BuildPageIntro($breadcrumbs);

// This code could go anyway as long as it gets included on /layout/manage/default.php
// return a query string from all of the _GET variables with changes from $mods applied
function GET_string($mods=array()){
	$q = '?';
	foreach(array_merge($_GET,$mods) as $k=>$v)
		$q .= urlencode($k).'='.urlencode($v).'&';
	return rtrim($q,'&');
}

?>
<div id="wrapperWhite">
	<div class="controlScroll">
		<div class="controlselectScroll">
			<!-- Toolbar -->
			<div class="toolbar">
				<div class="title">
					<div class="ico">
						<?php echo '<img src="'.DEFAULT_ICON_AMEND.'" />'; ?>
					</div>
					<div class="txt">
						<?php echo $lang->display('Amend Artwork'); ?>
						<div class="intro"><?php echo $lang->display('Please click on your artwork preview to customise settings.'); ?></div>
					</div>
				</div>
				<div class="options">
					<?php if($acl->acl_check("taskworkflow","export",$_SESSION['companyID'],$_SESSION['userID'])) { ?>
					<!-- Export -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Export'); ?>">
						<a href="javascript:void(0);" onclick="Popup('helper','blur');DoAjax('id=<?php echo $artworkID; ?>','window','modules/mod_amend_export.php');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_export.png">'; ?></div>
							<div><?php echo $lang->display('Export'); ?></div>
						</a>
					</div>
					<?php } ?>
					<?php if($acl->acl_check("taskworkflow","import",$_SESSION['companyID'],$_SESSION['userID'])) { ?>
					<!-- Import -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Import'); ?>">
						<a href="javascript:void(0);" onclick="Popup('helper','blur');DoAjax('id=<?php echo $artworkID; ?>','window','modules/mod_amend_import.php');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_import.png">'; ?></div>
							<div><?php echo $lang->display('Import'); ?></div>
						</a>
					</div>
					<?php } ?>
					<!-- Refresh -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Refresh'); ?>">
						<a href="javascript:void(0);" onclick="SubmitForm('mainform','refresh');process_start('<?php echo $artwork_row['fileName']; ?>');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_refresh.png">'; ?></div>
							<div><?php echo $lang->display('Refresh'); ?></div>
						</a>
					</div>
					<!-- Refresh All -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Refresh All'); ?>">
						<a href="javascript:void(0);" onclick="if(confirm('<?php echo $lang->display('Refreshing all pages would take longer to process. Are you sure you want to continue?'); ?>')) {SubmitForm('mainform','refreshall');process_start('<?php echo $artwork_row['fileName']; ?>');}">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_refresh_all.png">'; ?></div>
							<div><?php echo $lang->display('Refresh All'); ?></div>
						</a>
					</div>
					<?php if(!$is_guest) { ?>
						<!-- Download -->
						<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Download'); ?>">
							<a href="javascript:void(0);" onclick="Popup('helper','blur');DoAjax('id=<?php echo $artworkID; ?>','window','modules/mod_art_download.php');">
								<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_download.png">'; ?></div>
								<div><?php echo $lang->display('Download'); ?></div>
							</a>
						</div>
						<?php if($acl->acl_check("artworks","edit",$_SESSION['companyID'],$_SESSION['userID'])) { ?>
						<!-- Save as -->
						<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Save as'); ?>">
							<a href="javascript:void(0);" onclick="Popup('helper','blur');DoAjax('id=<?php echo $artworkID; ?>','window','modules/mod_art_saveas.php');">
								<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_saveas.png">'; ?></div>
								<div><?php echo $lang->display('Save as'); ?></div>
							</a>
						</div>
						<?php } ?>
						<!-- Options -->
						<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Options'); ?>">
							<a href="javascript:void(0);" onclick="Popup('helper','blur');DoAjax('artwork_id=<?php echo $artworkID; ?>','window','modules/mod_options.php');">
								<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_options.png">'; ?></div>
								<div><?php echo $lang->display('Options'); ?></div>
							</a>
						</div>
						<!-- Fonts -->
						<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Fonts'); ?>">
							<a href="javascript:void(0);" onclick="window.location='/index.php?layout=cp_font_sub&artworkID=<?php echo $artworkID; ?>&show=Used&back=<?php echo urlencode('/index.php?layout=amend&id='.$artworkID); ?>';">
								<div class="ico"><?php echo '<img src="'.IMG_PATH.'header/ico_font_sub.png">'; ?></div>
								<div><?php echo $lang->display('Fonts'); ?></div>
							</a>
						</div>
					<?php } ?>
					<!-- Close -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Close'); ?>">
						<?php if($is_guest){?>
						<a href="index.php">
						<?php }else{ ?>
						<a href="javascript:void(0);" onclick="SubmitForm('mainform','close');">
						<?php } ?>
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_close.png">'; ?></div>
							<div><?php echo $lang->display('Close'); ?></div>
						</a>
					</div>
					
				</div>
				<div class="clear"></div>
			</div>
			<?php
				if($is_guest) BuildTipMsg($lang->display('Welcome Message 1')." ".SYSTEM_NAME.", ".$guest_name.".");
				$all_overflows = $DB->check_box_overflow($artworkID);
				if($all_overflows) {
					$on_page = $DB->check_page_box_overflow($artworkID,$page);
					// Display links to other pages that have overflows if current page is the only one with them
					if($on_page != $all_overflows){
						$message = "There are ${all_overflows} text overflows in the current document";
						$query = sprintf("
							SELECT boxes.PageID
							FROM box_overflows
							LEFT JOIN boxes ON box_overflows.box_id = boxes.uID
							LEFT JOIN pages ON boxes.PageID = pages.uID
							WHERE box_overflows.artwork_id = %d
							AND pages.ArtworkID = %d",
							$artworkID,
							$artworkID,
							$page_no);
						$result = mysql_query($query) or die(mysql_error());
						while($row=mysql_fetch_row($result)){
							$pageNo = $row[0];
							if($pageNo==$page)continue;
							$get = GET_string(array('page'=>$pageNo));
							$count = $DB->check_page_box_overflow($artworkID,$pageNo);
							$message .= "<br/><a href=\"/index.php${get}\">${count} overflows on page ${pageNo}</a>";
						}
						BuildTipMsg($message);
					}
					// Let user know how many overflows
					if($on_page){
						BuildTipMsg("There are ${on_page} text overflows on this page");
					}
				}
			?>
			<div class="mainwrap">
				<form
					id="mainform"
					name="mainform"
					action="index.php?layout=amend&id=<?php echo $artworkID; ?>&page=<?php echo $page; ?>"
					method="POST"
					enctype="multipart/form-data"
				>
					<div class="pageBar">
						<?php
							$ret = BuildZoomIcons($artworkID,$page);
							$resize = $ret['resize'];
							$PageScale = $ret['PageScale'];
							$scale = $ret['scale'];
							$toggle = $ret['toggle'];
						?>
						<div class="right">
							<div class="filter">
								<?php BuildPageJumper($artworkID,$page); ?>
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
					<input type="hidden" name="form" id="form" />
				</form>
				<script language="javascript">
					var preload = new Image(<?php echo $img_width.','.$img_height; ?>);
					preload.src='<?php echo $thumbnail; ?>';
				</script>
				<?php BuildPageCols($artworkID,$page); ?>
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
								$query_box = sprintf("SELECT boxes.uID, boxes.Type, boxes.Left, boxes.Right, boxes.Top, boxes.Bottom, boxes.Angle,
													artwork_layers.visible, artwork_layers.locked, artwork_layers.colour,
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
									//get updated geometry info
									$geo = $DB->GetBoxMoves($artworkID,$boxID);
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
									switch($type) {
										case "TEXT":
											$query_edit = sprintf("SELECT paraedit.time, users.username
																FROM paraedit
																LEFT JOIN paralinks ON paraedit.pl_id = paralinks.uID
																LEFT JOIN users ON paraedit.user_id = users.userID
																WHERE paralinks.BoxID = %d
																ORDER BY paraedit.time DESC
																LIMIT 1",
																$boxID);
										break;
										case "PICT":
											$query_edit = sprintf("SELECT UNIX_TIMESTAMP(images.time) AS time, users.username
																FROM img_usage
																LEFT JOIN images ON img_usage.img_id = images.id
																LEFT JOIN users ON images.user_id = users.userID
																WHERE img_usage.artwork_id = %d
																AND img_usage.box_id = %d
																AND img_usage.task_id = 0
																ORDER BY images.time DESC
																LIMIT 1",
																$artworkID,
																$boxID);
										break;
									}
									$result_edit = mysql_query($query_edit, $conn) or die(mysql_error());
									if(mysql_num_rows($result_edit)) {
										$row_edit = mysql_fetch_assoc($result_edit);
										$title = $lang->display('Last Update').": ".$row_edit['username']." ".date(FORMAT_TIME,$row_edit['time']);
										if($toggle) {
											$style = get_line_style($colour,DEFAULT_DASHED_LINE_COLOUR,DEFAULT_DASHED_LINE_PIXELS);
											@imagesetstyle($im,$style);
											$border_colour = IMG_COLOR_STYLED;
											$icon = @imagecreatefrompng(ROOT.DEFAULT_ICON_AMEND);
											@imagecopyresized($im,$icon,$points[0],$points[1],0,0,DEFAULT_ICON_WIDTH,DEFAULT_ICON_HEIGHT,48,48);
											@imagedestroy($icon);
										}
									}

									$query_comment = sprintf("SELECT UNIX_TIMESTAMP(comments.time) AS time, users.username
															FROM comments
															LEFT JOIN users ON comments.user_id = users.userID
															WHERE comments.artwork_id = %d
															AND comments.box_id = %d
															AND comments.task_id = 0",
															$artworkID,
															$boxID);
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
										if($DB->check_box_overflow($artworkID,$boxID)) {
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
										} else {
											echo "Popup('helper','blur');DoAjax('artworkID=$artworkID&page=$page&boxID=$boxID','window','modules/mod_art_amend.php');\" title=\"$title\"";
										}
										echo " />";
									}
								}
								echo "</map>";
								if($toggle) {
									$thumbnail = PREVIEW_DIR.EDITS_DIR.basename($thumbnail);
									@imagejpeg($im,ROOT.$thumbnail);
									@imagedestroy($im);
								}
								echo "<img style=\"width:{$resize}px;\" src=\"$thumbnail?".filemtime(ROOT.$thumbnail)."\" usemap=\"#boxMap\" border=\"0\" />";
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
<script type="text/javascript" src="javascripts/dom_drag.js"></script>
<script language="javascript">Drag.init(document.getElementById('handle'),document.getElementById('root'));</script>
<?php
if(!empty($_SESSION['show_pages'])) {
	$apend = 'SetClassName(\'pageTool\',\'pageToolOn\');display(\'pageColL\');ResetDiv(\'pageColL\');DoAjax(\'layout='.$layout.'&artworkID='.$artworkID.'&page='.$page.'&taskID=0&istemplate=0&show_pages=1\',\'pageColL\',\'modules/mod_page_previews.php\');';
} else {
	$apend = "";
}
file_put_contents(ROOT."layouts/$layout/apend.js",$apend);
?>