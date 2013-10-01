<?php
$navStatus = array("campaigns");
require_once(MODULES.'mod_header.php');
BuildPageIntro('<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=campaigns\');">'.$lang->display('Campaigns').'</a>'.BREADCRUMBS_ARROW.'<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=campaign&id='.$campaignID.'\');">'.DisplayString($artwork_row['campaignName']).'</a>'.BREADCRUMBS_ARROW.'<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=artwork&id='.$artworkID.'\');">'.DisplayString($artwork_row['artworkName']).'</a>'.BREADCRUMBS_ARROW.$lang->display('Story Groups'));
BuildHelperDiv('StoryManEdit');
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
								<?php echo '<img src="'.IMG_PATH.'header/ico_stories.png">'; ?>
							</div>
							<div class="txt">
								<?php echo $lang->display('Story Groups'); ?>
								<div class="intro"><?php echo $lang->display('Story Manager'); ?></div>
							</div>
						</div>
						<div class="options">
                                                        <!-- StoryEdit -->
                                                        <div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="Story Edit">
                                                            <a href="javascript:void(0);" onclick="Popup('helper','blur');DoAjax('id=<?php echo $artworkID; ?>','window','modules/mod_storyman_edit.php');">
                                                                <div class="ico"><img src="templates/default/images/toolbar/ico_stories.png"></div>
                                                                <div><?php echo $lang->display('Story Group'); ?></div>
                                                            </a>
                                                        </div> 
                                                        <!-- Save -->
							<!--div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Save'); ?>">
								<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) SubmitForm('listform','save');">
									<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_save.png">'; ?></div>
									<div><?php echo $lang->display('Save'); ?></div>
								</a>
							</div-->
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
							action="index.php?layout=artstories&id=<?php echo $artworkID; ?>"
							method="POST"
							enctype="multipart/form-data"
						>
						<div class="option">
							<!--div class="left">
								<?php require_once(MODULES.'mod_list_search.php'); ?>
							</div-->
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
										<a href="javascript:void(0);" onclick="SetOrder('listform','Page','<?php echo $pre; ?>');"><?php echo $lang->display('Page'); ?></a>
									</th>
									<th colspan="2" align="center">
										<a href="javascript:void(0);" onclick="SetOrder('listform','LayerName','<?php echo $pre; ?>');"><?php echo $lang->display('Layer'); ?></a>
									</th>
                                                                        
                                                                        <th align="center">
										<a href="javascript:void(0);" onclick="SetOrder('listform','Assign to StoryGroup','<?php echo $pre; ?>');"><?php echo $lang->display('Story Group'); ?></a>
									</th>
									
									<th width="2%" align="center">
										<a href="javascript:void(0);" onclick="SetOrder('listform','id','<?php echo $pre; ?>');">ID</a>
									</th>
								</tr>
								<?php
									$query = sprintf("SELECT boxes.uID AS id, boxes.LinkedBoxID, boxes.Type, boxes.StoryRef, story_files.id as storyfileid, artwork_story_group_items.artwork_story_groups_id as  StoryGroupID,
													pages.Page, pages.PageRef, pages.PreviewFile, pages.PageScale,
													artwork_layers.name AS LayerName, artwork_layers.colour AS LayerColour, artwork_layers.locked AS LayerLocked, artwork_layers.visible AS LayerVisible
													FROM boxes
													LEFT JOIN artwork_layers ON boxes.LayerID = artwork_layers.id
													LEFT JOIN pages ON boxes.PageID = pages.uID
													LEFT JOIN artworks ON pages.ArtworkID = artworks.artworkID
													LEFT JOIN box_properties ON (box_properties.box_id = boxes.uID AND box_properties.task_id = 0)
													LEFT JOIN box_overflows ON (box_overflows.box_id = boxes.uID AND box_overflows.task_id = 0)
                                                                                                        LEFT JOIN story_files ON story_files.story_ref = boxes.StoryRef AND story_files.artwork_id=artworks.artworkID
                                                                                                        LEFT JOIN artwork_story_group_items ON artwork_story_group_items.story_files_id=story_files.id
													WHERE artworks.artworkID = %d
													%s
													AND boxes.uID LIKE '%s'
													ORDER BY pages.Page ASC, pages.PageRef ASC, `%s` %s
                                                                                                        LIMIT %d
													OFFSET %d
                                                                                                        ",
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
                                                                                        echo '<td align="center">'.$row['Page'].' ('.$row['PageRef'].')</td>';
											echo '<td align="center" title="'.$row['LayerName'].'"><div style="width:10px;height:10px;background-color:#'.$row['LayerColour'].';"></div></td>';
											echo '<td align="center">';
											if($row['LayerVisible']) echo '<div><img src="'.IMG_PATH.'ico_visible.png" title="'.$lang->display('Visible').'" /></div>';
											if($row['LayerLocked']) echo '<div><img src="'.IMG_PATH.'ico_locked.png" title="'.$lang->display('Locked').'" /></div>';
											echo '</td>';
                                                                                        echo '<td align="center">';
                                                                                        ?>
                                                                                        <select
                                                                                                class="input"
                                                                                                onfocus="this.className='inputOn'"
                                                                                                onblur="this.className='input'"
                                                                                                name="storyGroup[<?php echo $row['storyfileid'];?>]"
                                                                                                id="storyGroup"
                                                                                                onchange="SubmitForm('listform','');"
                                                                                                title="<?php echo $lang->display('Select Story Group'); ?>"
                                                                                        >
                                                                                        <?php BuildStoryGroupList($artworkID,$row['StoryGroupID']); ?>
                                                                                        </select>
                                                                                        <?php
                                                                                        echo '</td>';
                                                                                        
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