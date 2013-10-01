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
					<div class="txt"><?php echo $lang->display('Edit').": ".$art_row['artworkName']; ?></div>
				</div>
				<div class="options">
					<!-- Save -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Save'); ?>">
						<a href="javascript:void(0);" onclick="validateForm('name','Artwork Title','R','campaignID','Campaign Title','R','version','Version','R'); if(document.returnValue) { SubmitForm('editform','save'); }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_save.png">'; ?></div>
							<div><?php echo $lang->display('Save'); ?></div>
						</a>
					</div>
					<!-- Apply -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Apply'); ?>">
						<a href="javascript:void(0);" onclick="validateForm('name','Artwork Title','R','campaignID','Campaign Title','R','version','Version','R'); if(document.returnValue) { SubmitForm('editform','apply'); }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_apply.png">'; ?></div>
							<div><?php echo $lang->display('Apply'); ?></div>
						</a>
					</div>
					<!-- Close -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Close'); ?>">
						<a href="javascript:void(0);" onclick="SubmitForm('editform','close');">
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
					id="editform"
					name="editform"
					action="index.php?layout=<?php echo $layout; ?>&task=<?php echo $task; ?>&id=<?php echo $id; ?>"
					method="POST"
					enctype="multipart/form-data"
				>
				<div class="leftwrap">
					<div class="fieldset">
						<fieldset>
							<legend><?php echo $lang->display('Artwork Details'); ?></legend>
							<table width="100%" cellpadding="3" cellspacing="0" border="0">
								<tr>
									<th>* <?php echo $lang->display('Campaign'); ?></th>
									<td>
										<select
											class="input"
											name="campaignID"
											id="campaignID"
										>
										<?php
											$sub = $issuperadmin ? "" : sprintf("WHERE users.companyID = %d",$_SESSION['companyID']);
											$query = sprintf("SELECT campaigns.campaignID, campaigns.campaignName
																FROM campaigns
																LEFT JOIN users ON campaigns.ownerID = users.userID
																%s",
																mysql_real_escape_string($sub));
											$result = mysql_query($query, $conn) or die(mysql_error());
											while($row = mysql_fetch_assoc($result)) {
												echo '<option value="'.$row['campaignID'].'"';
												if($row['campaignID']==$art_row['campaignID']) echo ' selected="selected"';
												echo '>'.$row['campaignName'].'</option>';
											}
										?>
										</select>
									</td>
								</tr>
								<tr>
									<th>* <?php echo $lang->display('Artwork Title'); ?></th>
									<td>
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="name"
											id="name"
											value="<?php echo $art_row['artworkName']; ?>"
										>
									</td>
								</tr>
								<tr>
									<th>* <?php echo $lang->display('Version'); ?></th>
									<td>
										<select
											class="input"
											name="version"
											id="version"
										>
										<?php
											$parentID = ($art_row['parent']==0) ? $art_row['artworkID'] : $art_row['parent'];
											$query = sprintf("SELECT artworkID, version
																FROM artworks
																WHERE parent = %d
																OR artworkID = %d",
																$parentID,
																$parentID);
											$result = mysql_query($query, $conn) or die(mysql_error());
											while($row = mysql_fetch_assoc($result)) {
												echo '<option value="'.$row['artworkID'].'"';
												if($row['artworkID']==$art_row['artworkID']) echo 'selected="selected"';
												echo '>'.$row['version'].'</option>';
											}
										?>
										</select>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Subject'); ?></th>
									<td>
										<select
											class="input"
											name="subjectID"
											id="subjectID"
										>
										<?php BuildSubjectList($art_row['subjectID']); ?>
										</select>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Default Substitute Font'); ?>:</th>
									<td>
										<select
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="default_sub_font_id"
											id="default_sub_font_id"
										>
										<?php BuildFontSubList($art_row['default_sub_font_id']); ?>
										</select>
									</td>
								</tr>
								<tr>
									<th valign="top"><?php echo $lang->display('Default Image Folder'); ?>:</th>
									<td>
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="default_img_dir"
											id="default_img_dir"
											onclick="display('local_ftp');ResetDiv('local_ftp');DoAjax('companyID=<?php echo $art_row['companyID']; ?>','local_ftp','modules/mod_ftp_dir.php');"
											readonly="readonly"
											value="<?php echo $art_row['default_img_dir']; ?>"
										/>
										<a href="javascript:void(0);" onclick="setValue('default_img_dir','/');hidediv('local_ftp');"><?php echo $lang->display('Reset'); ?></a>
										<div id="local_ftp">
											<!-- Local ftp dir will appear here. -->
										</div>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('File Type'); ?></th>
									<td><?php echo $art_row['service_name'].' ('.$art_row['service_ext'].')'; ?></td>
								</tr>
								<tr>
									<th><?php echo $lang->display('File Name'); ?></th>
									<td><?php echo $art_row['fileName']; ?></td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Uploaded by'); ?></th>
									<td>
										<?php
											echo '<a href="index.php?layout=user&id='.$art_row['uploaderID'].'">'.$art_row['username'].'</a>';
											if(!empty($art_row['time'])) echo ' <span class="grey">'.date(FORMAT_TIME,$art_row['time'])."</span>";
										?>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Last Update'); ?></th>
									<td><?php echo date(FORMAT_TIME,strtotime($art_row['lastUpdate'])); ?></td>
								</tr>
							</table>
						</fieldset>
					</div>
				</div>
				<div class="rightwrap">
					<div class="fieldset">
						<fieldset>
							<legend><?php echo $lang->display('Other Information'); ?></legend>
							<table width="100%" cellpadding="3" cellspacing="0" border="0">
								<tr>
									<th><?php echo $lang->display('Width'); ?></th>
									<td><?php echo $art_row['width'].' (px)'; ?></td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Height'); ?></th>
									<td><?php echo $art_row['height'].' (px)'; ?></td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Pages'); ?></th>
									<td><?php echo $art_row['pageCount']; ?></td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Word Count'); ?></th>
									<td><?php echo $art_row['wordCount']; ?></td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Cost'); ?></th>
									<td><?php echo CURRENCY_SYMBOL." ".number_format($art_row['cost'],2); ?></td>
								</tr>
							</table>
						</fieldset>
					</div>
				</div>
				<div class="clear"></div>
				<input type="hidden" name="form" id="form">
				</form>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="javascripts/ajax.js"></script>