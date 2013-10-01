<?php
$navStatus = array("campaigns");
require_once(MODULES.'mod_header.php');
BuildPageIntro('<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=campaigns\');">'.$lang->display('Campaigns').'</a>'.BREADCRUMBS_ARROW.'<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=campaign&id='.$campaignID.'\');">'.DisplayString($artwork_row['campaignName']).'</a>'.BREADCRUMBS_ARROW.'<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=artwork&id='.$artworkID.'\');">'.DisplayString($artwork_row['artworkName']).'</a>'.BREADCRUMBS_ARROW.$lang->display('Customise'));
?>
<div id="wrapperWhite">
	<div>
		<div class="artworkPanel">
			<div class="controlScroll">
				<div class="artworkBoxes">
					<!-- Toolbar -->
					<div class="toolbar">
						<div class="title">
							<div class="ico">
								<?php echo '<img src="'.IMG_PATH.'header/ico_customise.png">'; ?>
							</div>
							<div class="txt">
								<?php echo $lang->display('Customise'); ?>
								<div class="intro"><?php echo $lang->display('Artbox Manager'); ?></div>
							</div>
						</div>
						<div class="options">
							<!-- Save -->
							<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Save'); ?>">
								<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) SubmitForm('listform','save');">
									<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_save.png">'; ?></div>
									<div><?php echo $lang->display('Save'); ?></div>
								</a>
							</div>
							<!-- Apply -->
							<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Apply'); ?>">
								<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) SubmitForm('listform','apply');">
									<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_apply.png">'; ?></div>
									<div><?php echo $lang->display('Apply'); ?></div>
								</a>
							</div>
							<!-- Refresh -->
							<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Refresh'); ?>">
								<a href="javascript:void(0);" onclick="SubmitForm('listform','refresh');process_start('<?php echo $artwork_row['fileName']; ?>');">
									<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_refresh.png">'; ?></div>
									<div><?php echo $lang->display('Refresh'); ?></div>
								</a>
							</div>
							<!-- Restore -->
							<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Restore'); ?>">
								<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) SubmitForm('listform','restore');">
									<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_restore.png">'; ?></div>
									<div><?php echo $lang->display('Restore'); ?></div>
								</a>
							</div>
							<!-- Close -->
							<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Close'); ?>">
								<a href="javascript:void(0);" onclick="SubmitForm('listform','close');">
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
							id="listform"
							name="listform"
							action="index.php?layout=artbox&id=<?php echo $artworkID; ?>"
							method="POST"
							enctype="multipart/form-data"
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
										name="filter_layer"
										id="filter_layer"
										onchange="SubmitForm('listform','');"
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
										onchange="SubmitForm('listform','');"
										title="<?php echo $lang->display('Select Box Type'); ?>"
									>
									<?php BuildBoxTypeList($box_type); ?>
									</select>
								</div>
							</div>
							<div class="clear"></div>
						</div>
						<div class="list">
							<table width="100%" cellpadding="5" cellspacing="0" border="0">
								<tr>
									<th width="2%" align="center">#</th>
									<th width="2%" align="center">
										<input type="checkbox" class="checkbox" name="checkall" id="checkall" onclick="GroupCheckbox(this,'id');">
									</th>
									<th>
										<a href="javascript:void(0);" onclick="SetOrder('listform','PreviewFile','<?php echo $pre; ?>');"><?php echo $lang->display('Preview'); ?></a>
									</th>
									<th align="center">
										<a href="javascript:void(0);" onclick="SetOrder('listform','Type','<?php echo $pre; ?>');"><?php echo $lang->display('Type'); ?></a>
									</th>
									<th align="center">
										<a href="javascript:void(0);" onclick="SetOrder('listform','Page','<?php echo $pre; ?>');"><?php echo $lang->display('Page'); ?></a>
									</th>
									<th colspan="2" align="center">
										<a href="javascript:void(0);" onclick="SetOrder('listform','LayerName','<?php echo $pre; ?>');"><?php echo $lang->display('Layer'); ?></a>
									</th>
									<th align="center">
										<a href="javascript:void(0);" onclick="SetOrder('listform','order','<?php echo $pre; ?>');"><?php echo $lang->display('Order'); ?></a>
									</th>
									<th align="center">
										<a href="javascript:void(0);" onclick="SetOrder('listform','Left','<?php echo $pre; ?>');"><?php echo $lang->display('Left'); ?></a>
									</th>
									<th align="center">
										<a href="javascript:void(0);" onclick="SetOrder('listform','Top','<?php echo $pre; ?>');"><?php echo $lang->display('Top'); ?></a>
									</th>
									<th align="center">
										<a href="javascript:void(0);" onclick="SetOrder('listform','Width','<?php echo $pre; ?>');"><?php echo $lang->display('Width'); ?></a>
									</th>
									<th align="center">
										<a href="javascript:void(0);" onclick="SetOrder('listform','Height','<?php echo $pre; ?>');"><?php echo $lang->display('Height'); ?></a>
									</th>
									<th align="center">
										<a href="javascript:void(0);" onclick="SetOrder('listform','Angle','<?php echo $pre; ?>');"><?php echo $lang->display('Angle'); ?></a>
									</th>
									<th align="center" title="<?php echo $lang->display('Heading'); ?>">
										<a href="javascript:void(0);" onclick="SetOrder('listform','heading','<?php echo $pre; ?>');"><?php echo $lang->display('Heading'); ?></a>
										<br /><input type="checkbox" class="checkbox" name="headingall" id="headingall" onclick="GroupCheckbox(this,'heading');ForceGroupCheckbox(this,'id');" />
									</th>
									<th align="center" title="<?php echo $lang->display('Grouped'); ?>">
										<a href="javascript:void(0);" onclick="SetOrder('listform','Grouped','<?php echo $pre; ?>');"><?php echo $lang->display('Grouped'); ?></a>
										<br /><input type="checkbox" class="checkbox" name="groupall" id="groupall" onclick="GroupCheckbox(this,'group');ForceGroupCheckbox(this,'id');" disabled="disabled" />
									</th>
									<th align="center" title="<?php echo $lang->display('Lock up Content'); ?>">
										<a href="javascript:void(0);" onclick="SetOrder('listform','lock','<?php echo $pre; ?>');"><?php echo $lang->display('Lock'); ?></a>
										<br /><input type="checkbox" class="checkbox" name="lockall" id="lockall" onclick="GroupCheckbox(this,'lock');ForceGroupCheckbox(this,'id');" />
									</th>
									<th align="center" title="<?php echo $lang->display('Reduce Text Size to Fit Box'); ?>">
										<a href="javascript:void(0);" onclick="SetOrder('listform','resize','<?php echo $pre; ?>');"><?php echo $lang->display('Resize'); ?></a>
										<br /><input type="checkbox" class="checkbox" name="resizeall" id="resizeall" onclick="GroupCheckbox(this,'resize');ForceGroupCheckbox(this,'id');" />
									</th>
									<th align="center" title="<?php echo $lang->display('Allow Dynamic Content'); ?>">
										<a href="javascript:void(0);" onclick="SetOrder('listform','dynamic','<?php echo $pre; ?>');"><?php echo $lang->display('Dynamic'); ?></a>
										<br /><input type="checkbox" class="checkbox" name="dynamicall" id="dynamicall" onclick="GroupCheckbox(this,'dynamic');ForceGroupCheckbox(this,'id');" />
									</th>
									<th width="2%" align="center">
										<a href="javascript:void(0);" onclick="SetOrder('listform','id','<?php echo $pre; ?>');">ID</a>
									</th>
								</tr>
								<?php
									$query = sprintf("SELECT boxes.uID AS id, boxes.LinkedBoxID, boxes.Type,
													boxes.Left, boxes.Right, boxes.Right-boxes.Left AS Width,
													boxes.Top, boxes.Bottom, boxes.Bottom-boxes.Top AS Height, boxes.Angle, boxes.Grouped,
													boxes.order, boxes.dynamic, boxes.heading,
													pages.Page, pages.PageRef, pages.PreviewFile, pages.PageScale,
													box_properties.lock, box_properties.resize,
													box_overflows.overflow,
													artwork_layers.name AS LayerName, artwork_layers.colour AS LayerColour, artwork_layers.locked AS LayerLocked, artwork_layers.visible AS LayerVisible
													FROM boxes
													LEFT JOIN artwork_layers ON boxes.LayerID = artwork_layers.id
													LEFT JOIN pages ON boxes.PageID = pages.uID
													LEFT JOIN artworks ON pages.ArtworkID = artworks.artworkID
													LEFT JOIN box_properties ON (box_properties.box_id = boxes.uID AND box_properties.task_id = 0)
													LEFT JOIN box_overflows ON (box_overflows.box_id = boxes.uID AND box_overflows.task_id = 0)
													WHERE artworks.artworkID = %d
													%s
													AND boxes.uID LIKE '%s'
													ORDER BY pages.Page ASC, pages.PageRef ASC, `%s` %s
													LIMIT %d
													OFFSET %d",
													$artworkID,
													$sub,
													"%".mysql_real_escape_string($keyword)."%",
													mysql_real_escape_string($by),
													mysql_real_escape_string($order),
													$limit,
													$offset);
									$result = mysql_query($query, $conn) or die(mysql_error());
									if(mysql_num_rows($result)) {
										$counter = 1;
										while($row = mysql_fetch_assoc($result)) {
											$boxID = $row['id'];
											//get default geometry info
											$left = $row['Left'];
											$right = $row['Right'];
											$top = $row['Top'];
											$bottom = $row['Bottom'];
											$angle = $row['Angle'];
											$PageScale = $row['PageScale'];
											//get updated geometry info
											$geo = $DB->GetBoxMoves($artworkID,$boxID);
											if($geo) {
												$left = $geo['left'];
												$right = $geo['right'];
												$top = $geo['top'];
												$bottom = $geo['bottom'];
												$angle = $geo['angle'];
											}
											
											//build textbox previews
											if(!empty($boxID)) {
												$path = PREVIEW_DIR.TEXTBOXES_DIR.BareFilename($row['PreviewFile'])."-".$boxID.".jpg";
												if(!file_exists(ROOT.$path)) {
													$path = $DB->RebuildBoxPreview($artworkID,$boxID);
												}
											}
											$style = $counter%2==0 ? 'even' : 'odd';
											echo '<tr class="'.$style.'" onmouseover="this.className=\'hover\'" onmouseout="this.className=\''.$style.'\'">';
											echo '<td align="center">'.$counter.'</td>';
											echo '<td align="center"><input type="checkbox" class="checkbox" name="id['.$boxID.']" id="id['.$boxID.']" value="'.$boxID.'"></td>';
											echo '<td>';
											if(file_exists(ROOT.$path)) echo '<div class="preview" onmouseover="display(\'tb'.$boxID.'\')" onmouseout="hidediv(\'tb'.$boxID.'\')"><img src="'.$path.'?'.filemtime(ROOT.$path).'"></div><div id="tb'.$boxID.'" class="img" style="display:none;"><img src="'.$path.'?'.filemtime(ROOT.$path).'" /></div>';
											echo '</td>';
											echo '<td align="center">'.$row['Type'].'</td>';
											echo '<td align="center">'.$row['Page'].' ('.$row['PageRef'].')</td>';
											echo '<td align="center" title="'.$row['LayerName'].'"><div style="width:10px;height:10px;background-color:#'.$row['LayerColour'].';"></div></td>';
											echo '<td align="center">';
											if($row['LayerVisible']) echo '<div><img src="'.IMG_PATH.'ico_visible.png" title="'.$lang->display('Visible').'" /></div>';
											if($row['LayerLocked']) echo '<div><img src="'.IMG_PATH.'ico_locked.png" title="'.$lang->display('Locked').'" /></div>';
											echo '</td>';
											echo '<td align="center">';
											if($row['Type']=="TEXT") {
												echo '<input type="text" class="input" onfocus="this.className=\'inputOn\';CheckTheBox(\'id['.$boxID.']\');" onblur="this.className=\'input\'" name="orderno['.$boxID.']" id="orderno['.$boxID.']" value="'.$row['order'].'" size="1" maxlength="4"/>';
											}
											echo '</td>';
											echo '<td align="center"><input type="text" class="input" onfocus="this.className=\'inputOn\';CheckTheBox(\'id['.$boxID.']\');" onblur="this.className=\'input\'" name="left['.$boxID.']" id="left['.$boxID.']" value="'.$left.'" size="1" maxlength="4"';
											if($row['Grouped']) echo 'disabled';
											echo '/></td>';
											echo '<td align="center"><input type="text" class="input" onfocus="this.className=\'inputOn\';CheckTheBox(\'id['.$boxID.']\');" onblur="this.className=\'input\'" name="top['.$boxID.']" id="top['.$boxID.']" value="'.$top.'" size="1" maxlength="4"';
											if($row['Grouped']) echo 'disabled';
											echo '/></td>';
											echo '<td align="center"><input type="text" class="input" onfocus="this.className=\'inputOn\';CheckTheBox(\'id['.$boxID.']\');" onblur="this.className=\'input\'" name="width['.$boxID.']" id="width['.$boxID.']" value="'.($right-$left).'" size="1" maxlength="4"';
											if($row['Grouped']) echo 'disabled';
											echo '/></td>';
											echo '<td align="center"><input type="text" class="input" onfocus="this.className=\'inputOn\';CheckTheBox(\'id['.$boxID.']\');" onblur="this.className=\'input\'" name="height['.$boxID.']" id="height['.$boxID.']" value="'.($bottom-$top).'" size="1" maxlength="4"';
											if($row['Grouped']) echo 'disabled';
											echo '/></td>';
											echo '<td><input type="text" class="input" onfocus="this.className=\'inputOn\';CheckTheBox(\'id['.$boxID.']\');" onblur="this.className=\'input\'" name="angle['.$boxID.']" id="angle['.$boxID.']" value="'.$angle.'" size="1" maxlength="4"';
											if($row['Grouped']) echo 'disabled';
											echo '/></td>';
											echo '<td align="center"><input type="checkbox" class="checkbox" name="heading['.$boxID.']" id="heading['.$boxID.']" value="1"';
											if($row['heading']) echo 'checked="checked"';
											if($row['Grouped']) echo 'onclick="return false;"';
											echo ' onfocus="CheckTheBox(\'id['.$boxID.']\')"/></td>';
											echo '<td align="center"><input type="checkbox" class="checkbox" name="group['.$boxID.']" id="group['.$boxID.']" value="1"';
											if($row['Grouped']) echo 'checked="checked"';
											echo ' onfocus="CheckTheBox(\'id['.$boxID.']\')" disabled="disabled"/></td>';
											echo '<td align="center"><input type="checkbox" class="checkbox" name="lock['.$boxID.']" id="lock['.$boxID.']" value="1"';
											if($row['lock']) echo 'checked="checked"';
											if($row['Grouped']) echo 'onclick="return false;"';
											echo ' onfocus="CheckTheBox(\'id['.$boxID.']\')"/></td>';
											echo '<td align="center"><input type="checkbox" class="checkbox" name="resize['.$boxID.']" id="resize['.$boxID.']" value="1"';
											if($row['resize']) echo 'checked="checked"';
											echo ' onfocus="CheckTheBox(\'id['.$boxID.']\')"/>';
											if($row['overflow']) {
												echo ' <img src="'.IMG_PATH.'ico_error.png" title="'.$lang->display('Text Overflow').'">';
											}
											echo '</td>';
											if($row['Type']=="TEXT") {
												echo '<td align="center"><input type="checkbox" class="checkbox" name="dynamic['.$boxID.']" id="dynamic['.$boxID.']" value="1"';
												if($row['dynamic']) echo 'checked="checked"';
												echo ' onfocus="CheckTheBox(\'id['.$boxID.']\')"/></td>';
											} else {
												echo '<td></td>';	
											}
											echo '<td align="center">'.$boxID;
											if(!empty($row['LinkedBoxID'])) echo ' <img src="'.IMG_PATH.'ico_link.png" /> '.$row['LinkedBoxID'];
											echo '</td>';
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
		<div id="rightPanel">
			<?php BuildPageViewer($artworkID); ?>
		</div>
		<div class="clear"></div>
	</div>
</div>
<script type="text/javascript" src="javascripts/ajax.js"></script>
<script type="text/javascript" src="javascripts/datepicker.js"></script>
<script type="text/javascript" src="javascripts/lightbox/prototype.js"></script>
<script type="text/javascript" src="javascripts/lightbox/scriptaculous.js?load=effects,builder"></script>
<script type="text/javascript" src="javascripts/lightbox/lightbox.js"></script>