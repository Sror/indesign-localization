<?php
BuildHelperDiv($row_artwork['artworkName'].' - '.$lang->display('Template Manager'));
$navStatus = array("campaigns");
require_once(MODULES.'mod_header.php');
BuildPageIntro('<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=campaigns\');">'.$lang->display('Campaigns').'</a>'.BREADCRUMBS_ARROW.'<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=campaign&id='.$row_artwork['campaignID'].'\');">'.DisplayString($row_artwork['campaignName']).'</a>'.BREADCRUMBS_ARROW.'<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=artwork&id='.$artworkID.'\');">'.DisplayString($row_artwork['artworkName']).'</a>'.BREADCRUMBS_ARROW.$lang->display('Template'));
?>
<div id="wrapperWhite">
	<div class="controlScroll">
		<div class="controlselectScroll">
			<!-- Toolbar -->
			<div class="toolbar">
				<div class="title">
					<div class="ico">
						<?php echo '<img src="'.IMG_PATH.'header/ico_template.png">'; ?>
					</div>
					<div class="txt">
						<?php echo $lang->display('Template'); ?>
						<div class="intro"><?php echo $lang->display('Template Manager'); ?></div>
					</div>
				</div>
				<div class="options">
					<!-- Import -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Import'); ?>">
						<a href="javascript:void(0);" onclick="Popup('helper','blur');DoAjax('id=<?php echo $artworkID; ?>&page=<?php echo $page; ?>','window','modules/mod_art_tpl_import.php');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_import.png">'; ?></div>
							<div><?php echo $lang->display('Import'); ?></div>
						</a>
					</div>
					<!-- View -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('View'); ?>">
						<a href="javascript:void(0);" onclick="Popup('helper','blur');DoAjax('id=<?php echo $artworkID; ?>&page=<?php echo $page; ?>&type=<?php echo $row_artwork['artworkType']; ?>','window','modules/mod_art_tpl_data.php');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_view.png">'; ?></div>
							<div><?php echo $lang->display('View'); ?></div>
						</a>
					</div>
					<!-- Refresh -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Refresh'); ?>">
						<a href="javascript:void(0);" onclick="SubmitForm('mainform','refresh');process_start('<?php echo $row_artwork['fileName']; ?>');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_refresh.png">'; ?></div>
							<div><?php echo $lang->display('Refresh'); ?></div>
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
			<?php
				$query = sprintf("SELECT boxes.uID
								FROM boxes
								LEFT JOIN pages ON pages.uID = boxes.PageID
								WHERE pages.ArtworkID = %d
								AND boxes.dynamic = 1",
								$artworkID);
				$result = mysql_query($query, $conn) or die(mysql_error());
				if(!mysql_num_rows($result)) {
					BuildTipMsg($lang->display('No dynamic fields found.')." <img src=\"".IMG_PATH."arrow_gold_rgt.png\" /> <a href=\"index.php?layout=artbox&id=$artworkID\">".$lang->display('Artbox Manager')."</a>");
				}
				$query = sprintf("SELECT id
								FROM import_map_para
								WHERE artwork_id = %d",
								$artworkID);
				$result = mysql_query($query, $conn) or die(mysql_error());
				if(!mysql_num_rows($result)) {
					BuildTipMsg($lang->display('No mapped fields found.')." ".$lang->display('Please click on your artwork preview to customise settings.'));
				}
			?>
			<div class="mainwrap">
				<form
					id="mainform"
					name="mainform"
					action="index.php?layout=arttpl&id=<?php echo $artworkID; ?>&page=<?php echo $page; ?>"
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
				<script language="javascript">
					var preload = new Image(<?php echo $img_width.','.$img_height; ?>);
					preload.src='<?php echo $thumbnail; ?>';
				</script>
				<?php BuildPageCols($artworkID,$page,0,1); ?>
				<div class="artworkScroll">
					<span class="artwork">
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
									
									if($toggle) {
										@imagepolygon($im,$points,4,hexdec($colour));
										if($type=="PICT") {
											@imageline($im,$points[0],$points[1],$points[4],$points[5],hexdec($colour));
											@imageline($im,$points[2],$points[3],$points[6],$points[7],hexdec($colour));
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
											echo "Popup('helper','blur');DoAjax('id=$artworkID&page=$page&box=$boxID','window','modules/mod_art_tpl.php');\" title=\"{$lang->display('Map Imported Data')}\"";
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
					</span>
				</div>
				<input type="hidden" name="form" id="form" />
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
	$apend = 'SetClassName(\'pageTool\',\'pageToolOn\');display(\'pageColL\');ResetDiv(\'pageColL\');DoAjax(\'layout='.$layout.'&artworkID='.$artworkID.'&page='.$page.'&taskID=0&istemplate=1&show_pages=1\',\'pageColL\',\'modules/mod_page_previews.php\');';
} else {
	$apend = "";
}
file_put_contents(ROOT."layouts/$layout/apend.js",$apend);
?>