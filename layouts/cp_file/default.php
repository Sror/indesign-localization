<?php
BuildHelperDiv($lang->display('File Manager'));
$navStatus = array("cpanel");
require_once(MODULES.'mod_header.php');
BuildPageIntro($lang->display('Control Panel'),$lang->display('CP Intro'));
?>

<div id="wrapperWhite">
	<table width="100%" cellpadding="0" cellspacing="0" border="0">
		<tr>
			<td width="47%" valign="top">
				<div class="controlScroll">
					<div class="controlselectScroll">
						<!-- Toolbar -->
						<div class="toolbar">
							<div class="title">
								<div class="ico"><?php echo '<img src="'.IMG_PATH.'header/ico_folder.png">'; ?></div>
								<div class="txt"><?php echo $lang->display('System'); ?></div>
							</div>
							<div class="options">
								<!-- Refresh -->
								<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Refresh'); ?>">
									<a href="javascript:void(0);" onclick="hidediv('local_cache_info');display('local_cache_loader');DoAjax('id=<?php echo $ftp_id; ?>&dir='+document.local_form.path.value+'&do=refresh','local_ftp','modules/mod_ftp_local.php');">
										<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_refresh.png">'; ?></div>
										<div><?php echo $lang->display('Refresh'); ?></div>
									</a>
								</div>
								<!-- Upload -->
								<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Upload'); ?>">
									<a href="javascript:void(0);" onclick="display('helper');DoAjax('id=<?php echo $ftp_id; ?>&dir='+document.local_form.path.value+'&location=local','window','modules/mod_ftp_upload.php');">
										<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_upload.png">'; ?></div>
										<div><?php echo $lang->display('Upload'); ?></div>
									</a>
								</div>
								<!-- Download -->
								<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Download'); ?>">
									<a href="javascript:void(0);" onclick="if(CheckSelected('local_form','id')) { hidediv('local_cache_info');display('local_cache_loader');SubmitForm('local_form','download');hidediv('loadingme'); }">
										<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_download.png">'; ?></div>
										<div><?php echo $lang->display('Download'); ?></div>
									</a>
								</div>
								<!-- Rename -->
								<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Rename'); ?>">
									<a href="javascript:void(0);" onclick="if(CheckSelected('local_form','id')) { display('helper'); DoAjax('id=<?php echo $ftp_id; ?>&ref='+GetCheckedValues('local_form')+'&location=local','window','modules/mod_ftp_rename.php'); }">
										<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_edit.png">'; ?></div>
										<div><?php echo $lang->display('Rename'); ?></div>
									</a>
								</div>
								<!-- Delete -->
								<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Delete'); ?>">
									<a href="javascript:void(0);" onclick="if(CheckSelected('local_form','id')) { if(confirm('<?php echo $lang->display('Are you sure you want to delete the selected?'); ?>')) { hidediv('local_cache_info');display('local_cache_loader');DoAjax('id=<?php echo $ftp_id; ?>&dir='+document.local_form.path.value+'&do=delete&ref='+GetCheckedValues('local_form'),'local_ftp','modules/mod_ftp_local.php'); } }">
										<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_delete.png">'; ?></div>
										<div><?php echo $lang->display('Delete'); ?></div>
									</a>
								</div>
								<!-- MkDir -->
								<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Make Directory'); ?>">
									<a href="javascript:void(0);" onclick="display('helper');DoAjax('id=<?php echo $ftp_id; ?>&dir='+document.local_form.path.value+'&location=local','window','modules/mod_ftp_mkdir.php');">
										<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_mkdir.png">'; ?></div>
										<div>MkDir</div>
									</a>
								</div>
								<!-- Extract -->
								<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Extract'); ?>">
									<a href="javascript:void(0);" onclick="if(CheckSelected('local_form','id')) { if(confirm('<?php echo $lang->display('Are you sure you want to extract the selected?'); ?>')) { hidediv('local_cache_info');display('local_cache_loader');DoAjax('id=<?php echo $ftp_id; ?>&dir='+document.local_form.path.value+'&do=extract&ref='+GetCheckedValues('local_form'),'local_ftp','modules/mod_ftp_local.php'); } }">
										<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_extract.png">'; ?></div>
										<div><?php echo $lang->display('Extract'); ?></div>
									</a>
								</div>
							</div>
							<div class="clear"></div>
						</div>
						
						<!-- Mainwrap -->
						<div class="mainwrap" id="local_ftp">
							<div class="breadcrumbs">
								<?php
									echo '<div class="left"><img src="'.IMG_PATH.'ico_fopen.png"> '.$local_ftp_dir.'</div>';
									echo '<div class="right" id="local_cache_info" style="display:block;"><a href="javascript:void(0);" onclick="hidediv(\'local_cache_info\'); display(\'local_cache_loader\'); DoAjax(\'id='.$ftp_id.'&dir='.$local_ftp_dir.'&do=refresh\',\'local_ftp\',\'modules/mod_ftp_local.php\');"><img src="'.IMG_PATH.'ico_swap.png" title="'.$lang->display('Refresh').'" /></a> '.$lang->display('Cached').': '.date("d/m/Y H:i",strtotime($ftp_local->get_local_ftp_cache_time($_SESSION['companyID'],$local_ftp_dir))).'</div>';
									echo '<div class="right" id="local_cache_loader" style="display:none;"><img src="'.IMG_PATH.'zoomloader.gif"></div>';
									echo '<div class="clear"></div>';
								?>
							</div>
							<div class="ftpPanel">
								<form
									id="local_form"
									name="local_form"
									action="index.php?layout=<?php echo $layout; ?>&task=<?php echo $task; ?>&id=<?php echo $ftp_id; ?>"
									method="POST"
									enctype="multipart/form-data"
								>
									<div class="list">
										<table width="100%" cellpadding="5" cellspacing="0" border="0">
											<tr>
												<th width="2%">
													<input type="checkbox" class="checkbox" name="checkall" id="checkall" onclick="GroupCheckbox(this,'id')">
												</th>
												<th>
													<a href="javascript:void(0);" onclick="hidediv('local_cache_info'); display('local_cache_loader'); DoAjax('id=<?php echo $ftp_id; ?>&dir=<?php echo $ftp_local->local_get_current_dir(); ?>&sort=name&order=DESC','local_ftp','modules/mod_ftp_local.php');">
														<?php echo $lang->display('Name'); ?>
													</a>
												</th>
												<th align="right">
													<a href="javascript:void(0);" onclick="hidediv('local_cache_info'); display('local_cache_loader'); DoAjax('id=<?php echo $ftp_id; ?>&dir=<?php echo $ftp_local->local_get_current_dir(); ?>&sort=size&order=DESC','local_ftp','modules/mod_ftp_local.php');">
														<?php echo $lang->display('Size'); ?>
													</a>
												</th>
												<th width="4%">
													<a href="javascript:void(0);" onclick="hidediv('local_cache_info'); display('local_cache_loader'); DoAjax('id=<?php echo $ftp_id; ?>&dir=<?php echo $ftp_local->local_get_current_dir(); ?>&sort=type&order=DESC','local_ftp','modules/mod_ftp_local.php');">
														<?php echo $lang->display('Type'); ?>
													</a>
												</th>
												<th>
													<a href="javascript:void(0);" onclick="hidediv('local_cache_info'); display('local_cache_loader'); DoAjax('id=<?php echo $ftp_id; ?>&dir=<?php echo $ftp_local->local_get_current_dir(); ?>&sort=date&order=DESC','local_ftp','modules/mod_ftp_local.php');">
														<?php echo $lang->display('Date Modified'); ?>
													</a>
												</th>
												<th width="4%">
													<a href="javascript:void(0);" onclick="hidediv('local_cache_info'); display('local_cache_loader'); DoAjax('id=<?php echo $ftp_id; ?>&dir=<?php echo $ftp_local->local_get_current_dir(); ?>&sort=chmod&order=DESC','local_ftp','modules/mod_ftp_local.php');">
														<?php echo $lang->display('Mode'); ?>
													</a>
												</th>
											</tr>
											<?php
												echo '<tr class="odd" onmouseover="this.className=\'hover\'" onmouseout="this.className=\'odd\'">';
												echo '<td></td>';
												echo '<td colspan="5"><a href="javascript:void(0);" onclick="hidediv(\'local_cache_info\'); display(\'local_cache_loader\'); DoAjax(\'id='.$ftp_id.'&dir='.$ftp_local->local_go_parent_dir().'\',\'local_ftp\',\'modules/mod_ftp_local.php\');"><img src="'.IMG_PATH.'ico_up.png" title="'.$lang->display('Up').'" /></a></td>';
												echo '</tr>';
												$query =  sprintf("SELECT `ftp_cache_local`.*
																	FROM `ftp_cache_local`
																	LEFT JOIN `ftp_cache_local_dir` ON `ftp_cache_local`.`dir_id` = `ftp_cache_local_dir`.`id`
																	WHERE `ftp_cache_local_dir`.`company_id` = %d
																	AND `ftp_cache_local_dir`.`dir` = '%s'
																	ORDER BY
																	`ftp_cache_local`.`type` ASC,
																	`ftp_cache_local`.`name` ASC",
																	$_SESSION['companyID'],
																	mysql_real_escape_string($local_ftp_dir));
												$result = mysql_query($query, $conn) or die(mysql_error());
												$found = mysql_num_rows($result);
												if($found) {
													$counter = 1;
													while($row = mysql_fetch_assoc($result)) {
														$valid = ValidateImage($local_path_to_ftp.$local_ftp_dir.$row['name']);
														$preview = FTP_DIR.$system_name.$local_ftp_dir.$row['name'];
														echo '<tr class="';
														if($counter%2==0) echo 'odd'; else echo 'even';
														echo '" onmouseover="this.className=\'hover\';display(\'preview'.$row['id'].'\');" onmouseout="this.className=\'';
														if($counter%2==0) echo 'odd'; else echo 'even';
														echo '\';hidediv(\'preview'.$row['id'].'\');"';
														if($row['type']=="dir") {
															echo 'ondblclick="hidediv(\'local_cache_info\'); display(\'local_cache_loader\'); DoAjax(\'id='.$ftp_id.'&dir='.$local_ftp_dir.$row['name'].'\',\'local_ftp\',\'modules/mod_ftp_local.php\');"';
														}
														echo '>';
														echo '<td><input type="checkbox" class="checkbox" name="id[]" id="id[]" value="'.$row['id'].'"></td>';
														echo '<td>';
														if($row['type']=="dir") {
															echo '<img src="'.IMG_PATH.'ico_folder.png"> ';
														}
														echo $row['name'];
														if($valid) echo '<div id="preview'.$row['id'].'" class="img" style="display:none;"><img src="'.$preview.'"></div>';
														echo '</td>';
														echo '<td align="right">'.convert_byte($row['size']).'</td>';
														echo '<td>'.$row['type'].'</td>';
														echo '<td>'.date(FORMAT_TIME,strtotime($row['date'])).'</td>';
														echo '<td>'.$row['chmod'].'</td>';
														echo '</tr>';
														$counter++;
													}
												}
											?>
										</table>
									</div>
									<input type="hidden" name="form" id="form">
									<input type="hidden" name="ftp" id="ftp" value="local">
									<input type="hidden" name="path" id="path" value="<?php echo $local_ftp_dir; ?>">
								</form>
							</div>
						</div>
					</div>
				</div>
			</td>
			<td width="6%" valign="middle">
				<?php if(!empty($ftp_id)) { ?>
				<div class="toolbar">
					<div class="options">
						<!-- Sync from FTP -->
						<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Sync'); ?>">
							<a href="javascript:void(0);" onclick="if(CheckSelected('remote_form','id')) { if(confirm('<?php echo $lang->display('Are you sure you want to sync the selected?'); ?>')) { hidediv('local_cache_info'); display('local_cache_loader'); DoAjax('id=<?php echo $ftp_id; ?>&dir='+document.local_form.path.value+'&do=sync&ref='+GetCheckedValues('remote_form'),'local_ftp','modules/mod_ftp_local.php'); } }">
								<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_prev_on.png">'; ?></div>
								<div><?php echo $lang->display('Sync'); ?></div>
							</a>
						</div>
						<!-- Sync to FTP -->
						<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Sync'); ?>">
							<a href="javascript:void(0);" onclick="if(CheckSelected('local_form','id')) { if(confirm('<?php echo $lang->display('Are you sure you want to sync the selected?'); ?>')) { hidediv('remote_cache_info'); display('remote_cache_loader'); DoAjax('id=<?php echo $ftp_id; ?>&dir='+document.remote_form.path.value+'&do=sync&ref='+GetCheckedValues('local_form'),'remote_ftp','modules/mod_ftp_remote.php'); } }">
								<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_next_on.png">'; ?></div>
								<div><?php echo $lang->display('Sync'); ?></div>
							</a>
						</div>
						<!-- FTP Type -->
						<div id="ftp_type" class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Mode'); ?>">
							<a href="javascript:DoAjax('type=
											<?php
												switch($_SESSION['ftp_type']) {
													case FTP_BINARY:
														echo FTP_ASCII;
														break;
													case FTP_ASCII:
														echo FTP_BINARY;
														break;
												}
											?>
											','ftp_type','modules/mod_ftp_type.php');">
								<div class="ico">
								<?php
									echo '<img src="'.IMG_PATH.'toolbar/ico_';
									switch($_SESSION['ftp_type']) {
										case FTP_BINARY:
											echo 'binary';
											break;
										case FTP_ASCII:
											echo 'ascii';
											break;
									}
									echo '.png" />';
								?>
								</div>
								<div>
								<?php
									switch($_SESSION['ftp_type']) {
										case FTP_BINARY:
											echo 'BINARY';
											break;
										case FTP_ASCII:
											echo 'ASCII';
											break;
									}
								?>
								</div>
							</a>
						</div>
					</div>
					<div class="clear"></div>
				</div>
				<?php } ?>
			</td>
			<td width="47%" valign="top">
				<div class="controlScroll">
					<div id="remote_connection" class="controlselectScroll">
						<?php if(!empty($ftp_id)) { ?>
						<!-- Toolbar -->
						<div class="toolbar">
							<div class="title">
								<div class="ico"><?php echo '<img src="'.IMG_PATH.'header/ico_folder.png">'; ?></div>
								<div class="txt"><?php echo $ftp_row['ftp_host']; ?></div>
							</div>
							<div class="options">
								<!-- Refresh -->
								<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Refresh'); ?>">
									<a href="javascript:void(0);" onclick="hidediv('remote_cache_info'); display('remote_cache_loader');DoAjax('id=<?php echo $ftp_id; ?>&dir='+document.remote_form.path.value+'&do=refresh','remote_ftp','modules/mod_ftp_remote.php');">
										<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_refresh.png">'; ?></div>
										<div><?php echo $lang->display('Refresh'); ?></div>
									</a>
								</div>
								<!-- Download -->
								<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Download'); ?>">
									<a href="javascript:void(0);" onclick="if(CheckSelected('remote_form','id')) { hidediv('remote_cache_info');display('remote_cache_loader');SubmitForm('remote_form','download');hidediv('loadingme'); }">
										<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_download.png">'; ?></div>
										<div><?php echo $lang->display('Download'); ?></div>
									</a>
								</div>
								<!-- Rename -->
								<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Rename'); ?>">
									<a href="javascript:void(0);" onclick="if(CheckSelected('remote_form','id')) { display('helper');DoAjax('id=<?php echo $ftp_id; ?>&ref='+GetCheckedValues('remote_form')+'&location=remote','window','modules/mod_ftp_rename.php'); }">
										<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_edit.png">'; ?></div>
										<div><?php echo $lang->display('Rename'); ?></div>
									</a>
								</div>
								<!-- Delete -->
								<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Delete'); ?>">
									<a href="javascript:void(0);" onclick="if(CheckSelected('remote_form','id')) { if(confirm('<?php echo $lang->display('Are you sure you want to delete the selected?'); ?>')) { hidediv('remote_cache_info');display('remote_cache_loader');DoAjax('id=<?php echo $ftp_id; ?>&dir='+document.remote_form.path.value+'&do=delete&ref='+GetCheckedValues('remote_form'),'remote_ftp','modules/mod_ftp_remote.php'); } }">
										<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_delete.png">'; ?></div>
										<div><?php echo $lang->display('Delete'); ?></div>
									</a>
								</div>
								<!-- MkDir -->
								<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Make Directory'); ?>">
									<a href="javascript:void(0);" onclick="display('helper');DoAjax('id=<?php echo $ftp_id; ?>&dir='+document.remote_form.path.value+'&location=remote','window','modules/mod_ftp_mkdir.php');">
										<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_mkdir.png">'; ?></div>
										<div>MkDir</div>
									</a>
								</div>
								<!-- Disconnect -->
								<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Disconnect'); ?>">
									<a href="javascript:void(0);" onclick="SubmitForm('remote_form','disconnect');">
										<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_close.png">'; ?></div>
										<div><?php echo $lang->display('Disconnect'); ?></div>
									</a>
								</div>
							</div>
							<div class="clear"></div>
						</div>
						<!-- Mainwrap -->
						<div class="mainwrap" id="remote_ftp">
							<div class="breadcrumbs">
								<?php
									echo '<div class="left"><img src="'.IMG_PATH.'ico_fopen.png"> '.$remote_ftp_dir.'</div>';
									echo '<div class="right" id="remote_cache_info" style="display:block;"><a href="javascript:void(0);" onclick="hidediv(\'remote_cache_info\'); display(\'remote_cache_loader\'); DoAjax(\'id='.$ftp_id.'&dir='.$remote_ftp_dir.'&do=refresh\',\'remote_ftp\',\'modules/mod_ftp_remote.php\');"><img src="'.IMG_PATH.'ico_swap.png" title="'.$lang->display('Refresh').'" /></a> '.$lang->display('Cached').': '.date(FORMAT_TIME,strtotime($ftp_sync->get_remote_ftp_cache_time($ftp_id,$remote_ftp_dir))).'</div>';
									echo '<div class="right" id="remote_cache_loader" style="display:none;"><img src="'.IMG_PATH.'zoomloader.gif"></div>';
									echo '<div class="clear"></div>';
								?>
							</div>
							<div class="ftpPanel">
								<form
									id="remote_form"
									name="remote_form"
									action="index.php?layout=<?php echo $layout; ?>&task=<?php echo $task; ?>&id=<?php echo $ftp_id; ?>"
									method="POST"
									enctype="multipart/form-data"
								>
									<div class="list">
										<table width="100%" cellpadding="5" cellspacing="0" border="0">
											<tr>
												<th width="2%">
													<input type="checkbox" class="checkbox" name="checkall" id="checkall" onclick="GroupCheckbox(this,'id')">
												</th>
												<th>
													<a href="javascript:void(0);" onclick="hidediv('remote_cache_info'); display('remote_cache_loader');DoAjax('id=<?php echo $ftp_id; ?>&dir=<?php echo $remote_ftp_dir; ?>&sort=name&order=DESC','remote_ftp','modules/mod_ftp_remote.php');">
														<?php echo $lang->display('Name'); ?>
													</a>
												</th>
												<th align="right">
													<a href="javascript:void(0);" onclick="hidediv('remote_cache_info'); display('remote_cache_loader');DoAjax('id=<?php echo $ftp_id; ?>&dir=<?php echo $remote_ftp_dir; ?>&sort=size&order=DESC','remote_ftp','modules/mod_ftp_remote.php');">
														<?php echo $lang->display('Size'); ?>
													</a>
												</th>
												<th width="4%">
													<a href="javascript:void(0);" onclick="hidediv('remote_cache_info'); display('remote_cache_loader');DoAjax('id=<?php echo $ftp_id; ?>&dir=<?php echo $remote_ftp_dir; ?>&sort=type&order=DESC','remote_ftp','modules/mod_ftp_remote.php');">
														<?php echo $lang->display('Type'); ?>
													</a>
												</th>
												<th>
													<a href="javascript:void(0);" onclick="hidediv('remote_cache_info'); display('remote_cache_loader');DoAjax('id=<?php echo $ftp_id; ?>&dir=<?php echo $remote_ftp_dir; ?>&sort=date&order=DESC','remote_ftp','modules/mod_ftp_remote.php');">
														<?php echo $lang->display('Date Modified'); ?>
													</a>
												</th>
												<th width="4%">
													<a href="javascript:void(0);" onclick="hidediv('remote_cache_info'); display('remote_cache_loader');DoAjax('id=<?php echo $ftp_id; ?>&dir=<?php echo $remote_ftp_dir; ?>&sort=chmod&order=DESC','remote_ftp','modules/mod_ftp_remote.php');">
														<?php echo $lang->display('Mode'); ?>
													</a>
												</th>
											</tr>
											<?php
												echo '<tr class="odd" onmouseover="this.className=\'hover\'" onmouseout="this.className=\'odd\'">';
												echo '<td></td>';
												echo '<td colspan="5"><a href="javascript:void(0);" onclick="hidediv(\'remote_cache_info\'); display(\'remote_cache_loader\'); DoAjax(\'id='.$ftp_id.'&dir='.$ftp_sync->ftp_go_parent_dir().'\',\'remote_ftp\',\'modules/mod_ftp_remote.php\');"><img src="'.IMG_PATH.'ico_up.png" title="'.$lang->display('Up').'" /></a></td>';
												echo '</tr>';
												$query =  sprintf("SELECT `ftp_cache_remote`.*
																	FROM `ftp_cache_remote`
																	LEFT JOIN `ftp_cache_remote_dir` ON `ftp_cache_remote`.`dir_id` = `ftp_cache_remote_dir`.`id`
																	WHERE `ftp_cache_remote_dir`.`ftp_id` = %d
																	AND `ftp_cache_remote_dir`.dir = '%s'
																	ORDER BY
																	`ftp_cache_remote`.`type` ASC,
																	`ftp_cache_remote`.`name` ASC",
																	$ftp_id,
																	mysql_real_escape_string($remote_ftp_dir));
												$result = mysql_query($query, $conn) or die(mysql_error());
												$found = mysql_num_rows($result);
												if($found) {
													$counter = 1;
													while($row = mysql_fetch_assoc($result)) {
														echo '<tr class="';
														if($counter%2==0) echo 'odd'; else echo 'even';
														echo '" onmouseover="this.className=\'hover\'" onmouseout="this.className=\'';
														if($counter%2==0) echo 'odd'; else echo 'even';
														echo '\'"';
														if($row['type']=="dir") {
															echo 'ondblclick="hidediv(\'remote_cache_info\'); display(\'remote_cache_loader\'); DoAjax(\'id='.$ftp_id.'&dir='.$remote_ftp_dir.$row['name'].'\',\'remote_ftp\',\'modules/mod_ftp_remote.php\');"';
														}
														echo '>';
														echo '<td><input type="checkbox" class="checkbox" name="id[]" id="id[]" value="'.$row['id'].'"></td>';
														echo '<td>';
														if($row['type']=="dir") {
															echo '<img src="'.IMG_PATH.'ico_folder.png"> ';
														}
														echo $row['name'];
														echo '</td>';
														echo '<td align="right">'.convert_byte($row['size']).'</td>';
														echo '<td>'.$row['type'].'</td>';
														echo '<td>'.date(FORMAT_TIME,strtotime($row['date'])).'</td>';
														echo '<td>'.$row['chmod'].'</td>';
														echo '</tr>';
														$counter++;
													}
												}
											?>
										</table>
									</div>
									<input type="hidden" name="form" id="form">
									<input type="hidden" name="ftp" id="ftp" value="remote">
									<input type="hidden" name="path" id="path" value="<?php echo $remote_ftp_dir; ?>">
								</form>
							</div>
						</div>
						<?php } else { ?>
						<div class="mainwrap">
							<form
								id="newform"
								name="newform"
								action="index.php?layout=<?php echo $layout; ?>&task=<?php echo $task; ?>"
								method="POST"
								enctype="multipart/form-data"
							>
								<div class="fieldset">
									<fieldset>
										<legend><?php echo $lang->display('Remote'); ?></legend>
										<table width="100%" cellpadding="3" cellspacing="0" border="0">
											<tr>
												<th>* <?php echo $lang->display('FTP Host'); ?></th>
												<td>
													<select
														class="input"
														name="ftp_id"
														id="ftp_id"
													>
													<?php BuildFTPHostList($_SESSION['companyID']); ?>
													</select>
													<span class="span"><a href="javascript:void(0);" onclick="goToURL('parent','index.php?layout=cp_file&task=manage');"><?php echo $lang->display('FTP Manager'); ?></a></span>
												</td>
											</tr>
											<tr>
												<th></th>
												<td>
													<input
														type="button"
														class="btnDo"
														onmousemove="this.className='btnOn'"
														onmouseout="this.className='btnDo'"
														value="<?php echo $lang->display('Connect'); ?>"
														onclick="goToURL('parent','index.php?layout=cp_file&id='+jQueryGetValue('ftp_id'));"
													>
													<?php if(!empty($_GET['error'])) BuildTipMsg($lang->display('Access Denied')); ?>
												</td>
											</tr>
										</table>
									</fieldset>
								</div>
								<input type="hidden" name="form" id="form">
							</form>
						</div>
						<?php } ?>
					</div>
				</div>
			</td>
		</tr>
	</table>
</div>
<script type="text/javascript" src="javascripts/ajax.js"></script>
<script type="text/javascript" src="javascripts/dom_drag.js"></script>
<script language="javascript">Drag.init(document.getElementById('handle'),document.getElementById('root'));</script>