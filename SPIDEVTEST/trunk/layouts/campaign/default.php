<?php
BuildHelperDiv($camp_row['campaignName']);
$navStatus = array("campaigns");
require_once(MODULES.'mod_header.php');
BuildPageIntro('<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=campaigns\');">'.$lang->display('Campaigns').'</a>'.BREADCRUMBS_ARROW.$camp_row['campaignName']);
?>
<div id="wrapperWhite">
	<div class="controlScroll">
		<div class="controlselectScroll">
			<!-- Toolbar -->
			<div class="toolbar">
				<div class="title">
					<div class="ico">
						<?php echo '<img src="'.IMG_PATH.'header/ico_campaign.png">'; ?>
					</div>
					<div class="txt">
						<?php echo $lang->display('Campaign Artworks'); ?>
						<div class="intro"><?php echo $lang->display('Campaign Artworks Intro'); ?></div>
					</div>
				</div>
				<div class="options">
					<?php if($status==STATUS_ACTIVE) { ?>
						<?php if($camp_row['campaignStatus']==STATUS_ACTIVE && $acl->acl_check("artworks","new",$_SESSION['companyID'],$_SESSION['userID'])) { ?>
						<!-- Upload -->
						<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Upload'); ?>">
							<a href="javascript:void(0);" onclick="Popup('helper','blur');DoAjax('id=<?php echo $id; ?>','window','modules/mod_art_new.php');">
								<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_upload.png">'; ?></div>
								<div><?php echo $lang->display('Upload'); ?></div>
							</a>
						</div>
						<?php } ?>
						<!-- Fonts -->
						<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Fonts'); ?>">
							<a href="javascript:void(0);" onclick="window.location='/index.php?layout=cp_font_sub&campaignID=<?php echo $id; ?>&show=Used';">
								<div class="ico"><?php echo '<img src="'.IMG_PATH.'header/ico_font_sub.png">'; ?></div>
								<div><?php echo $lang->display('Fonts'); ?></div>
							</a>
						</div>
						<?php if($acl->acl_check("artworks","trash",$_SESSION['companyID'],$_SESSION['userID'])) { ?>
						<!-- Trash -->
						<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Trash'); ?>">
							<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) { if(confirm('<?php echo $lang->display('Are you sure you want to trash the selected?'); ?>')) SubmitForm('listform','trash'); }">
								<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_trash.png">'; ?></div>
								<div><?php echo $lang->display('Trash'); ?></div>
							</a>
						</div>
						<?php } ?>
					<?php } else if($status == STATUS_TRASHED) { ?>
						<?php if($acl->acl_check("artworks","restore",$_SESSION['companyID'],$_SESSION['userID'])) { ?>
						<!-- Restore -->
						<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Restore'); ?>">
							<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) { if(confirm('<?php echo $lang->display('Are you sure you want to restore the selected?'); ?>')) SubmitForm('listform','restore'); }">
								<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_restore.png">'; ?></div>
								<div><?php echo $lang->display('Restore'); ?></div>
							</a>
						</div>
						<?php } if($acl->acl_check("artworks","delete",$_SESSION['companyID'],$_SESSION['userID'])) { ?>
						<!-- Delete -->
						<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Delete'); ?>">
							<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) { if(confirm('<?php echo $lang->display('Are you sure you want to delete the selected?'); ?>')) SubmitForm('listform','delete'); }">
								<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_delete.png">'; ?></div>
								<div><?php echo $lang->display('Delete'); ?></div>
							</a>
						</div>
						<?php } ?>
					<?php } ?>
					<!-- Auto Lookup -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Auto Lookup'); ?>">
						<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) SubmitForm('listform','autolookup');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_scan.png">'; ?></div>
							<div><?php echo $lang->display('Auto Lookup'); ?></div>
						</a>
					</div>
					<!-- Information -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Information'); ?>">
						<a href="javascript:void(0);" onclick="Popup('helper','blur');DoAjax('id=<?php echo $id; ?>','window','modules/mod_camp_info.php');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_info.png">'; ?></div>
							<div><?php echo $lang->display('Information'); ?></div>
						</a>
					</div>
					<?php if ($acl->acl_check("campaigns","cache",$_SESSION['companyID'],$_SESSION['userID'])) { ?>
					<!-- Options -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Options'); ?>">
						<a href="javascript:void(0);" onclick="Popup('helper','blur');DoAjax('campaign_id=<?php echo $id; ?>','window','modules/mod_camp_options.php');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_options.png">'; ?></div>
							<div><?php echo $lang->display('Options'); ?></div>
						</a>
					</div>
					<?php } ?>
				</div>
				<div class="clear"></div>
			</div>
			<?php BuildTipMsg('<a href="javascript:void(0);" onclick="Popup(\'helper\',\'blur\');DoAjax(\'id='.$id.'\',\'window\',\'modules/mod_art_new.php\');">'.$lang->display('Upload Artworks').'</a>'); ?>
			<!-- Mainwrap -->
			<div class="mainwrap">
				<form
					id="listform"
					name="listform"
					action="index.php?layout=<?php echo $layout; ?>&id=<?php echo $id; ?>"
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
							<?php if($view == "thumbnails") { ?>
								<input
									type="checkbox"
									class="checkbox"
									name="checkall"
									id="checkall"
									onclick="jQueryCheckAll('listform',this.id,'.thumbnailBox .off');"
									title="<?php echo $lang->display('Select All'); ?>"
								/>
								<?php echo $lang->display('Select All'); ?>
								<span class="span"></span>
							<?php } ?>
							<select
								class="input"
								onfocus="this.className='inputOn'"
								onblur="this.className='input'"
								name="artwork_status"
								id="artwork_status"
								onchange="SubmitForm('listform','');"
								title="<?php echo $lang->display('Select Status'); ?>"
							>
							<?php BuildArtStatusList($status); ?>
							</select>
							<select
								class="input"
								onfocus="this.className='inputOn'"
								onblur="this.className='input'"
								name="filter_view"
								id="filter_view"
								onchange="SubmitForm('listform','');"
								title="<?php echo $lang->display('Select View'); ?>"
							>
							<?php BuildViewList($view); ?>
							</select>
							<select
								class="input"
								onfocus="this.className='inputOn'"
								onblur="this.className='input'"
								name="filter_type"
								id="filter_type"
								onchange="SubmitForm('listform','');"
								title="<?php echo $lang->display('Select File Type'); ?>"
							>
							<?php BuildFileTypeList($_SESSION['packageID'],SERVICE_UPLOAD,TYPE_ORIGINAL,$type,$lang->display('Select File Type')); ?>
							</select>
							<select
								class="input"
								onfocus="this.className='inputOn'"
								onblur="this.className='input'"
								name="filter_subject"
								id="filter_subject"
								onchange="SubmitForm('listform','');"
								title="<?php echo $lang->display('Select Subject'); ?>"
							>
							<?php BuildSubjectList($subject); ?>
							</select>
						</div>
					</div>
					<div class="clear"></div>
				</div>
				<div class="list">
					<div <?php if($view == "thumbnails") echo 'class="thumbnailBoxMargin"'; ?>>
					<?php if($view == "list") { ?>
						<table id="listview" width="100%" cellpadding="5" cellspacing="0" border="0">
							<tr>
								<th width="2%" align="center">#</th>
								<th width="2%" align="center">
									<input type="checkbox" class="checkbox" name="checkall" id="checkall" onclick="jQueryCheckAll('listform',this.id,'#listview tr');">
								</th>
								<th width="8%" align="center"><?php echo $lang->display('Action'); ?></th>
								<th width="22%">
									<a href="javascript:void(0);" onclick="SetOrder('listform','artworkName','<?php echo $pre; ?>');">
										<?php echo $lang->display('Artwork Title'); ?>
									</a>
								</th>
								<th width="5%">
									<a href="javascript:void(0);" onclick="SetOrder('listform','version','<?php echo $pre; ?>');">
										<?php echo $lang->display('Version'); ?>
									</a>
								</th>
								<th width="10%">
									<a href="javascript:void(0);" onclick="SetOrder('listform','name','<?php echo $pre; ?>');">
										<?php echo $lang->display('File Type'); ?>
									</a>
								</th>
								<th width="12%">
									<a href="javascript:void(0);" onclick="SetOrder('listform','forename','<?php echo $pre; ?>');">
										<?php echo $lang->display('Owner'); ?>
									</a>
								</th>
								<th width="4%" align="center">
									<a href="javascript:void(0);" onclick="SetOrder('listform','pageCount','<?php echo $pre; ?>');">
										<?php echo $lang->display('Pages'); ?>
									</a>
								</th>
								<th width="8%" align="center">
									<a href="javascript:void(0);" onclick="SetOrder('listform','wordCount','<?php echo $pre; ?>');">
										<?php echo $lang->display('Word Count'); ?>
									</a>
								</th>
								<?php if ($acl->acl_check("system","cost",$_SESSION['companyID'],$_SESSION['userID'])) { ?>
								<th width="5%" align="right">
									<a href="javascript:void(0);" onclick="SetOrder('listform','cost','<?php echo $pre; ?>');">
										<?php echo $lang->display('Cost'); ?>
									</a>
								<?php } ?>
								</th>
								<th width="8%">
									<a href="javascript:void(0);" onclick="SetOrder('listform','lastUpdate','<?php echo $pre; ?>');">
										<?php echo $lang->display('Last Update'); ?>
									</a>
								</th>
								<th width="12%">
									<a href="javascript:void(0);" onclick="return false;">
										<?php echo $lang->display('Progress'); ?>
									</a>
								</th>
								<th width="2%" align="center">
									<a href="javascript:void(0);" onclick="SetOrder('listform','artworkID','<?php echo $pre; ?>');">ID</a>
								</th>
							</tr>
					<?php } ?>
					<?php
						$query = sprintf("SELECT artworks.*,
										subjects.subjectTitle,
										users.forename, users.surname,
										companies.companyName,
										service_engines.name,
										service_engines.ext,
										pages.PreviewFile
										FROM artworks
										LEFT JOIN pages ON (artworks.artworkID = pages.ArtworkID AND pages.Page = 1)
										LEFT JOIN users ON artworks.uploaderID = users.userID
										LEFT JOIN companies ON users.companyID = companies.companyID
										LEFT JOIN subjects ON artworks.subjectID = subjects.subjectID
										LEFT JOIN service_engines ON artworks.artworkType = service_engines.id
										WHERE artworks.campaignID = %d
										AND artworks.live = %d
										%s
										AND (artworks.artworkName LIKE '%s'
										OR companies.companyName LIKE '%s'
										OR subjects.subjectTitle LIKE '%s'
										OR users.username LIKE '%s'
										OR users.forename LIKE '%s'
										OR users.surname LIKE '%s'
										OR service_engines.name LIKE '%s'
										OR service_engines.ext LIKE '%s')
										ORDER BY `%s` %s
										LIMIT %d
										OFFSET %d",
										$id,
										$status,
										mysql_real_escape_string($sub),
										"%".mysql_real_escape_string($keyword)."%",
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
						$counter = $offset + 1;
						while($row = mysql_fetch_assoc($result)) {
							//check missing fonts
							$font_query = sprintf("SELECT artwork_fonts.id
												FROM artwork_fonts
												LEFT JOIN fonts ON artwork_fonts.font_id = fonts.id
												WHERE artwork_fonts.artwork_id = %d
												AND fonts.installed = 0",
												$row['artworkID']);
							$font_result = mysql_query($font_query, $conn) or die(mysql_error());
							$font_found = mysql_num_rows($font_result);
							//check missing images
							$img_query = sprintf("SELECT img_links.id AS img_link_id
												FROM img_links
												LEFT JOIN images ON img_links.img_id = images.id
												LEFT JOIN boxes ON img_links.box_id = boxes.uID
												LEFT JOIN pages ON boxes.PageID = pages.uID
												LEFT JOIN artworks ON pages.ArtworkID = artworks.artworkID
												WHERE artworks.artworkID = %d
												AND (images.hash = '' OR images.hash IS NULL)",
												$row['artworkID']);
							$img_result = mysql_query($img_query, $conn) or die(mysql_error());
							$img_found = 0;
							$IM = new ImageManager();
							while($img_row = mysql_fetch_assoc($img_result)) {
								if(!$IM->CheckImageStatus($row['artworkID'], $img_row['img_link_id'])) $img_found++;
							}
							$missing = $font_found + $img_found;
							
							if($view == "thumbnails") {
								//start of thumbnailBox
								echo '<div class="thumbnailBox" title="'.$row['artworkName'].'">';
								echo '<div class="off" onmouseover="display(\'options_'.$row['artworkID'].'\');" onmouseout="hidediv(\'options_'.$row['artworkID'].'\');">';
								//start of picture
								echo '<div class="pic">';
								echo '<div class="thumbnail">';
								echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=artwork&id='.$row['artworkID'].'\');">';
								if((!empty($row['PreviewFile'])) && (file_exists(ROOT.PREVIEW_DIR.$row['PreviewFile']))) {
									$preview = PREVIEW_DIR.THUMBNAILS_DIR.$row['PreviewFile'];
									if(!file_exists(ROOT.$preview)) {
										$DB->RebuildPageThumbnail(PREVIEW_DIR,$row['artworkID'],1);
									}
								} else {
									$preview = IMG_PATH.'img_missing.png';
								}
								echo '<img src="'.$preview.'?'.filemtime(ROOT.$preview).'" />';
								echo '</a>';
								echo '</div>';
								echo '<div class="options" id="options_'.$row['artworkID'].'" style="display:none;">';
								echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=artwork&id='.$row['artworkID'].'\');" title="'.$lang->display('View').'"><img src="'.IMG_PATH.'toolbar/ico_view.png" /></a>';
								if($status==STATUS_ACTIVE) {
									if ($acl->acl_check("artworks","edit",$_SESSION['companyID'],$_SESSION['userID'])) {
										echo '<span class="span"></span><a href="javascript:void(0);" onclick="Popup(\'helper\',\'blur\');DoAjax(\'ref='.$row['artworkID'].'&redirect=campaign\',\'window\',\'modules/mod_art_edit.php\');" title="'.$lang->display('Edit').'"><img src="'.IMG_PATH.'toolbar/ico_edit.png" /></a>';
									}
									if($acl->acl_check("artworks","trash",$_SESSION['companyID'],$_SESSION['userID'])) {
										echo '<span class="span"></span><a href="javascript:void(0);" onclick="if(confirm(\''.$lang->display('Are you sure you want to trash the selected?').'\')) {CheckTheBoxOnly(\'id['.$row['artworkID'].']\',\'id\');SubmitForm(\'listform\',\'trash\');}" title="'.$lang->display('Trash').'"><img src="'.IMG_PATH.'toolbar/ico_trash.png" /></a>';
									}
								}
								if($status==STATUS_TRASHED) {
									if($acl->acl_check("artworks","restore",$_SESSION['companyID'],$_SESSION['userID'])) {
										echo '<span class="span"></span><a href="javascript:void(0);" onclick="if(confirm(\''.$lang->display('Are you sure you want to restore the selected?').'\')) {CheckTheBoxOnly(\'id['.$row['artworkID'].']\',\'id\');SubmitForm(\'listform\',\'restore\');}" title="'.$lang->display('Restore').'"><img src="'.IMG_PATH.'toolbar/ico_restore.png" /></a>';
									}
									if($acl->acl_check("artworks","delete",$_SESSION['companyID'],$_SESSION['userID'])) {
										echo '<span class="span"></span><a href="javascript:void(0);" onclick="if(confirm(\''.$lang->display('Are you sure you want to delete the selected?').'\')) {CheckTheBoxOnly(\'id['.$row['artworkID'].']\',\'id\');SubmitForm(\'listform\',\'delete\');}" title="'.$lang->display('Delete').'"><img src="'.IMG_PATH.'toolbar/ico_delete.png" /></a>';
									}
								}
								echo '</div>';
								echo '</div>';
								//end of picture
								//start of txt
								echo '<div class="txt">';
								echo '<div class="title">';
								echo '<div class="right"><input type="checkbox" class="checkbox" name="id['.$row['artworkID'].']" id="id['.$row['artworkID'].']" value="'.$row['artworkID'].'" /></div>';
								echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=artwork&id='.$row['artworkID'].'\');">';
								echo DisplayString($row['artworkName']);
								echo '</a>';
								if($missing) {
									echo ' <img src="'.IMG_PATH.'ico_error.png" title="'.$lang->display('Missing').':';
									if($font_found) echo ' '.$lang->display('Fonts').' ('.$font_found.')';
									if($img_found) echo ' '.$lang->display('Images').' ('.$img_found.')';
									echo '"/>';
								}
								echo '</div>';
								echo '<div class="version">'.$lang->display('Version').' '.$row['version'].'</div>';
								echo '<div><span class="subject">'.$lang->display('File Type').':</span> '.$row['name'].' ('.$row['ext'].')</div>';
								echo '<div><span class="subject">'.$lang->display('Owner').':</span> <a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=user&id='.$row['uploaderID'].'\');">'.$row['forename'].' '.$row['surname'].'</a></div>';
								echo '<div class="grey">'.$row['companyName'].'</div>';
								echo '<div><span class="subject">'.$lang->display('Pages').':</span> '.$row['pageCount'].'</div>';
								echo '<div><span class="subject">'.$lang->display('Word Count').':</span> '.$row['wordCount'].'</div>';
								if ($acl->acl_check("system","cost",$_SESSION['companyID'],$_SESSION['userID'])) {
									echo '<div><span class="subject">'.$lang->display('Cost').':</span> '.CURRENCY_SYMBOL.number_format($row['cost'],2).'</div>';
								}
								echo '<div><span class="subject">'.$lang->display('Last Update').':</span> '.date(FORMAT_DATE,strtotime($row['lastUpdate'])).'</div>';
								echo '<div><span class="subject">'.$lang->display('Subject').':</span> ';
								echo !empty($row['subjectID']) ? $lang->display($row['subjectTitle']) : '<span class="grey">'.$lang->display('N/S').'</span>';
								echo '</div>';
								BuildArtworkProgressBar($row['artworkID']);
								echo '</div>';
								//end of txt
								echo '<div class="clear"></div>';
								echo '</div>';
								echo '</div>';
								//end of thumbnailBox
							}
							
							if($view == "list") {
								$style = $counter%2==0 ? 'even' : 'odd';
								echo '<tr class="'.$style.'" title="'.$row['artworkName'].'">';
								echo '<td align="center">'.$counter.'</td>';
								echo '<td align="center"><input type="checkbox" class="checkbox" name="id['.$row['artworkID'].']" id="id['.$row['artworkID'].']" value="'.$row['artworkID'].'"></td>';
								echo '<td align="center">';
								echo '<div class="ico">';
								echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=artwork&id='.$row['artworkID'].'\');" title="'.$lang->display('View').'"><img src="'.IMG_PATH.'toolbar/ico_view.png" /></a>';
								if($status==STATUS_ACTIVE) {
									if ($acl->acl_check("artworks","edit",$_SESSION['companyID'],$_SESSION['userID'])) {
										echo '<span class="span"></span><a href="javascript:void(0);" onclick="Popup(\'helper\',\'blur\');DoAjax(\'ref='.$row['artworkID'].'&redirect=campaign\',\'window\',\'modules/mod_art_edit.php\');" title="'.$lang->display('Edit').'"><img src="'.IMG_PATH.'toolbar/ico_edit.png" /></a>';
									}
									if($acl->acl_check("artworks","trash",$_SESSION['companyID'],$_SESSION['userID'])) {
										echo '<span class="span"></span><a href="javascript:void(0);" onclick="if(confirm(\''.$lang->display('Are you sure you want to trash the selected?').'\')) {CheckTheBoxOnly(\'id['.$row['artworkID'].']\',\'id\');SubmitForm(\'listform\',\'trash\');}" title="'.$lang->display('Trash').'"><img src="'.IMG_PATH.'toolbar/ico_trash.png" /></a>';
									}
								}
								if($status==STATUS_TRASHED) {
									if($acl->acl_check("artworks","restore",$_SESSION['companyID'],$_SESSION['userID'])) {
										echo '<span class="span"></span><a href="javascript:void(0);" onclick="if(confirm(\''.$lang->display('Are you sure you want to restore the selected?').'\')) {CheckTheBoxOnly(\'id['.$row['artworkID'].']\',\'id\');SubmitForm(\'listform\',\'restore\');}" title="'.$lang->display('Restore').'"><img src="'.IMG_PATH.'toolbar/ico_restore.png" /></a>';
									}
									if($acl->acl_check("artworks","delete",$_SESSION['companyID'],$_SESSION['userID'])) {
										echo '<span class="span"></span><a href="javascript:void(0);" onclick="if(confirm(\''.$lang->display('Are you sure you want to delete the selected?').'\')) {CheckTheBoxOnly(\'id['.$row['artworkID'].']\',\'id\');SubmitForm(\'listform\',\'delete\');}" title="'.$lang->display('Delete').'"><img src="'.IMG_PATH.'toolbar/ico_delete.png" /></a>';
									}
								}
								echo '</div>';
								echo '</td>';
								echo '<td>';
								echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=artwork&id='.$row['artworkID'].'\');">'.DisplayString($row['artworkName']).'</a>';
								if($missing) {
									echo ' <img src="'.IMG_PATH.'ico_error.png" title="'.$lang->display('Missing').':';
									if($font_found) echo ' '.$lang->display('Fonts').' ('.$font_found.')';
									if($img_found) echo ' '.$lang->display('Images').' ('.$img_found.')';
									echo '"/>';
								}
								if(!empty($row['subjectID'])) echo '<div class="grey">'.$lang->display($row['subjectTitle']).'</div>';
								echo '</td>';
								echo '<td>'.$row['version'].'</td>';
								echo '<td>'.$row['name'].' ('.$row['ext'].')</td>';
								echo '<td>';
								echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=user&id='.$row['uploaderID'].'\');">'.$row['forename'].' '.$row['surname'].'</a>';
								echo '<div class="grey">'.$row['companyName'].'</div>';
								echo '</td>';
								echo '<td align="center">'.$row['pageCount'].'</td>';
								echo '<td align="center">'.$row['wordCount'].'</td>';
								if ($acl->acl_check("system","cost",$_SESSION['companyID'],$_SESSION['userID'])) {
									echo '<td align="right">'.CURRENCY_SYMBOL.number_format($row['cost'],2).'</td>';
								}
								echo '<td>'.date(FORMAT_DATE,strtotime($row['lastUpdate'])).'</td>';
								echo '<td>';
								BuildArtworkProgressBar($row['artworkID']);
								echo '</td>';
								echo '<td align="center">'.$row['artworkID'].'</td>';
								echo '</tr>';
								$counter++;
							}
						}
						if($view == "list") echo '</table>';
					?>
					<div class="clear"></div>
					</div>
				</div>
				<?php require_once(MODULES.'mod_list_nav.php'); ?>
				<input type="hidden" name="by" id="by" value="<?php echo $by; ?>">
				<input type="hidden" name="order" id="order" value="<?php echo $order; ?>">
				<input type="hidden" name="form" id="form">
				</form>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="javascripts/ajax.js"></script>
<script type="text/javascript" src="javascripts/datepicker.js"></script>
<script type="text/javascript" src="javascripts/dom_drag.js"></script>
<script language="javascript">Drag.init(document.getElementById('handle'),document.getElementById('root'));</script>