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
					<div class="ico"><?php echo '<img src="'.IMG_PATH.'header/ico_artwork.png">'; ?></div>
					<div class="txt"><?php echo $lang->display('Artwork Manager'); ?></div>
				</div>
				<div class="options">
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
					<?php if($status == STATUS_ACTIVE) { ?>
					<!-- Trash -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Trash'); ?>">
						<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) SubmitForm('listform','trash');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_trash.png">'; ?></div>
							<div><?php echo $lang->display('Trash'); ?></div>
						</a>
					</div>
					<?php } ?>
					<?php if($status == STATUS_TRASHED) { ?>
					<!-- Restore -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Restore'); ?>">
						<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) SubmitForm('listform','restore');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_restore.png">'; ?></div>
							<div><?php echo $lang->display('Restore'); ?></div>
						</a>
					</div>
					<!-- Delete -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Delete'); ?>">
						<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) { if(confirm('<?php echo $lang->display('Are you sure you want to delete the selected?'); ?>')) SubmitForm('listform','delete'); }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_delete.png">'; ?></div>
							<div><?php echo $lang->display('Delete'); ?></div>
						</a>
					</div>
					<?php } ?>
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
								name="artwork_status"
								id="artwork_status"
								onchange="SubmitForm('listform','');"
								title="<?php echo $lang->display('Select Status'); ?>"
							>
							<?php BuildArtStatusList($status); ?>
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
							<th width="8%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','preview','<?php echo $pre; ?>');">
									<?php echo $lang->display('Preview'); ?>
								</a>
							</th>
							<th width="22%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','name','<?php echo $pre; ?>');">
									<?php echo $lang->display('Artwork Title'); ?>
								</a>
							</th>
							<th width="6%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','version','<?php echo $pre; ?>');">
									<?php echo $lang->display('Version'); ?>
								</a>
							</th>
							<th width="16%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','campaign','<?php echo $pre; ?>');">
									<?php echo $lang->display('Campaign'); ?>
								</a>
							</th>
							<th width="8%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','subject','<?php echo $pre; ?>');">
									<?php echo $lang->display('Subject'); ?>
								</a>
							</th>
							<th width="4%" align="center">
								<a href="javascript:void(0);" onclick="SetOrder('listform','pageno','<?php echo $pre; ?>');">
									<?php echo $lang->display('Pages'); ?>
								</a>
							</th>
							<th width="4%" align="center">
								<a href="javascript:void(0);" onclick="SetOrder('listform','words','<?php echo $pre; ?>');">
									<?php echo $lang->display('Words'); ?>
								</a>
							</th>
							<th width="10%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','uploader','<?php echo $pre; ?>');">
									<?php echo $lang->display('Uploaded by'); ?>
								</a>
							</th>
							<th width="10%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','lastUpdate','<?php echo $pre; ?>');">
									<?php echo $lang->display('Last Update'); ?>
								</a>
							</th>
							<th width="4%" align="center">
								<a href="javascript:void(0);" onclick="SetOrder('listform','taskno','<?php echo $pre; ?>');">
									<?php echo $lang->display('Tasks'); ?>
								</a>
							</th>
							<th width="2%" align="center">
								<a href="javascript:void(0);" onclick="SetOrder('listform','id','<?php echo $pre; ?>');">ID</a>
							</th>
						</tr>
						<?php
							$query =  sprintf("SELECT artworks.artworkID AS id, artworks.artworkName AS name, artworks.version, artworks.time, artworks.lastUpdate,
												artworks.campaignID, artworks.pageCount AS pageno, artworks.wordCount AS words, artworks.uploaderID,
												campaigns.campaignName AS campaign,
												subjects.subjectTitle AS subject,
												service_engines.ext AS type,
												users.username AS uploader,
												pages.PreviewFile AS preview,
												COUNT(tasks.taskID) AS taskno
												FROM artworks
												LEFT JOIN campaigns ON artworks.campaignID = campaigns.campaignID
												LEFT JOIN users ON artworks.uploaderID = users.userID
												LEFT JOIN companies ON users.companyID = companies.companyID
												LEFT JOIN subjects ON artworks.subjectID = subjects.subjectID
												LEFT JOIN service_engines ON artworks.artworkType = service_engines.id
												LEFT JOIN pages ON (artworks.artworkID = pages.ArtworkID AND pages.Page = 1)
												LEFT JOIN tasks ON artworks.artworkID = tasks.artworkID
												WHERE companies.companyID = %d
												AND artworks.live = %d
												AND (artworks.artworkName LIKE '%s'
												OR campaigns.campaignName LIKE '%s'
												OR users.username LIKE '%s'
												OR subjects.subjectTitle LIKE '%s'
												OR service_engines.ext LIKE '%s')
												GROUP BY artworks.artworkID
												ORDER BY `%s` %s
												LIMIT %d
												OFFSET %d",
												$company_id,
												$status,
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
									echo '<div class="preview">';
									if(!empty($row['preview']) && file_exists(ROOT.PREVIEW_DIR.$row['preview'])) {
										$preview = PREVIEW_DIR.$row['preview'];
									} else {
										$preview = IMG_PATH.'img_missing.png';
									}
									echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout='.$layout.'&task=edit&id='.$row['id'].'\');" onmouseover="display(\'preview'.$row['id'].'\')" onmouseout="hidediv(\'preview'.$row['id'].'\')"><img src="'.$preview.'" /></a>';
									echo '</div>';
									echo '<div id="preview'.$row['id'].'" class="img" style="display:none;"><img src="'.$preview.'"></div>';
									echo '</td>';
									echo '<td><a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout='.$layout.'&task=edit&id='.$row['id'].'\');">'.$row['name'].'</a></td>';
									echo '<td>'.$row['version'].'</td>';
									echo '<td><a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=campaign&id='.$row['campaignID'].'\');">'.$row['campaign'].'</a></td>';
									echo '<td>'.$lang->display($row['subject']).'</td>';
									echo '<td align="center">'.$row['pageno'].'</td>';
									echo '<td align="center">'.$row['words'].'</td>';
									echo '<td>';
									echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=user&id='.$row['uploaderID'].'\');">'.$row['uploader'].'</a>';
									if(!empty($row['time'])) echo '<div class="grey">'.date(FORMAT_TIME,$row['time'])."</div>";
									echo '</td>';
									echo '<td>'.date(FORMAT_TIME,strtotime($row['lastUpdate'])).'</td>';
									echo '<td align="center">'.$row['taskno'].'</td>';
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