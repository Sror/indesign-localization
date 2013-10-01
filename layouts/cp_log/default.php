<?php
BuildHelperDiv($lang->display('System Log Manager').' - '.$lang->display('Error log'));
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
					<div class="ico"><?php echo '<img src="'.IMG_PATH.'header/ico_log.png">'; ?></div>
					<div class="txt"><?php echo $lang->display('System Log Manager'); ?></div>
				</div>
				<div class="options">
					<!-- Export -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Export'); ?>">
						<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) {SubmitForm('listform','export');hidediv('loadingme');}">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_export.png">'; ?></div>
							<div><?php echo $lang->display('Export'); ?></div>
						</a>
					</div>
					<?php if($isadmin) { ?>
					<!-- Error Log -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Error Log'); ?>">
						<a href="javascript:void(0);" onclick="Popup('helper','blur');DoAjax('','window','modules/mod_error_log.php');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_error.png">'; ?></div>
							<div><?php echo $lang->display('Error Log'); ?></div>
						</a>
					</div>
					<!-- Delete -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Delete'); ?>">
						<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) { if(confirm('<?php echo $lang->display('Are you sure you want to delete the selected?'); ?>')) { SubmitForm('listform','delete'); } }">
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
								<a href="javascript:void(0);" onclick="SetOrder('listform','log_time','<?php echo $pre; ?>');">
									<?php echo $lang->display('Timestamp'); ?>
								</a>
							</th>
							<th width="10%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','username','<?php echo $pre; ?>');">
									<?php echo $lang->display('User'); ?>
								</a>
							</th>
							<th width="10%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','campaignName','<?php echo $pre; ?>');">
									<?php echo $lang->display('Campaign'); ?>
								</a>
							</th>
							<th width="22%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','artworkName','<?php echo $pre; ?>');">
									<?php echo $lang->display('Artwork'); ?>
								</a>
							</th>
							<th width="8%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','source_lang','<?php echo $pre; ?>');">
									<?php echo $lang->display('Task Details'); ?>
								</a>
							</th>
							<th width="36%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','action','<?php echo $pre; ?>');">
									<?php echo $lang->display('Action'); ?>
								</a>
							</th>
							<th width="2%" align="center">
								<a href="javascript:void(0);" onclick="SetOrder('listform','id','<?php echo $pre; ?>');">ID</a>
							</th>
						</tr>
						<?php
							$query =  sprintf("SELECT systemlog.logID AS id, systemlog.action, UNIX_TIMESTAMP(systemlog.time) AS log_time,
												systemlog.campaignID, systemlog.artworkID, systemlog.taskID,
												users.userID, users.username, users.forename, users.surname,
												campaigns.campaignName,
												artworks.artworkName,
												L1.languageName AS source_lang, L1.flag AS source_flag,
												L2.languageName AS target_lang, L2.flag AS target_flag
												FROM systemlog
												LEFT JOIN users ON systemlog.userID = users.userID
												LEFT JOIN campaigns ON systemlog.campaignID = campaigns.campaignID
												LEFT JOIN artworks ON systemlog.artworkID = artworks.artworkID
												LEFT JOIN tasks ON systemlog.taskID = tasks.taskID
												LEFT JOIN languages L1 ON campaigns.sourceLanguageID = L1.languageID
												LEFT JOIN languages L2 ON tasks.desiredLanguageID = L2.languageID
												WHERE users.companyID = %d
												AND (users.username LIKE '%s'
												OR users.forename LIKE '%s'
												OR users.surname LIKE '%s'
												OR systemlog.action LIKE '%s')
												ORDER BY `%s` %s
												LIMIT %d
												OFFSET %d",
												$company_id,
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
									echo '<td>'.date(FORMAT_TIME,$row['log_time']).'</td>';
									echo '<td>';
									echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=user&id='.$row['userID'].'\');">'.$row['username'].'</a>';
									echo '<div class="grey">'.$row['forename'].' '.$row['surname'].'</div>';
									echo '</td>';
									echo '<td><a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=campaign&id='.$row['campaignID'].'\');">'.$row['campaignName'].'</a></td>';
									echo '<td><a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=artwork&id='.$row['artworkID'].'\');">'.$row['artworkName'].'</a></td>';
									echo '<td>';
									if(!empty($row['taskID'])) echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=task&id='.$row['taskID'].'\');"><img src="images/flags/'.$row['source_flag'].'" title="'.$lang->display($row['source_lang']).'" /> <img src="'.IMG_PATH.'flag_to.gif" title="'.$lang->display('to be translated to').'" /> <img src="images/flags/'.$row['target_flag'].'" title="'.$lang->display($row['target_lang']).'" /></a>';
									echo '</td>';
									echo '<td>'.$row['action'].'</td>';
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
<script type="text/javascript" src="javascripts/ajax.js"></script>
<script type="text/javascript" src="javascripts/dom_drag.js"></script>
<script language="javascript">Drag.init(document.getElementById('handle'),document.getElementById('root'));</script>