<?php
BuildHelperDiv($lang->display('Credit Manager').' - '.$lang->display('Top up'));
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
					<div class="ico"><?php echo '<img src="'.IMG_PATH.'header/ico_credits.png">'; ?></div>
					<div class="txt"><?php echo $lang->display('Credit Manager'); ?></div>
				</div>
				<div class="options">
					<!-- Refresh -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Refresh'); ?>">
						<a href="javascript:void(0);" onclick="SubmitForm('listform','refresh');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_refresh.png">'; ?></div>
							<div><?php echo $lang->display('Refresh'); ?></div>
						</a>
					</div>
					<!-- Export -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Export'); ?>">
						<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) {SubmitForm('listform','export');hidediv('loadingme');}">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_export.png">'; ?></div>
							<div><?php echo $lang->display('Export'); ?></div>
						</a>
					</div>
					<?php if($issuperadmin) { ?>
					<!-- Top up -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Top up'); ?>">
						<a href="javascript:void(0);" onclick="Popup('helper','blur');DoAjax('','window','modules/mod_topup.php');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_topup.png">'; ?></div>
							<div><?php echo $lang->display('Top up'); ?></div>
						</a>
					</div>
					<!-- Refund -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Refund'); ?>">
						<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) { if(confirm('<?php echo $lang->display('Are you sure you want to refund this transaction?'); ?>')) { SubmitForm('listform','refund'); } }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_refund.png">'; ?></div>
							<div><?php echo $lang->display('Refund'); ?></div>
						</a>
					</div>
					<!-- Delete -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Delete'); ?>">
						<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) { if(confirm('<?php echo $lang->display('Are you sure you want to delete this transaction?'); ?>')) { SubmitForm('listform','delete'); } }">
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
						<div class="search">
							<?php echo $lang->display('Search'); ?>:
							<select
								class="input"
								onfocus="this.className='inputOn'"
								onblur="this.className='input'"
								name="start_day"
								id="start_day"
								title="<?php echo $lang->display('Day'); ?>"
							>
							<?php BuildDayList($start_day); ?>
							</select>
							/
							<select
								class="input"
								onfocus="this.className='inputOn'"
								onblur="this.className='input'"
								name="start_month"
								id="start_month"
								title="<?php echo $lang->display('Month'); ?>"
							>
							<?php BuildMonthList($start_month); ?>
							</select>
							/
							<select
								class="input"
								onfocus="this.className='inputOn'"
								onblur="this.className='input'"
								name="start_year"
								id="start_year"
								title="<?php echo $lang->display('Year'); ?>"
							>
							<?php BuildYearList($start_year); ?>
							</select>
							<?php echo '<img src="'.IMG_PATH.'arrow_notes.gif" />'; ?>
							<select
								class="input"
								onfocus="this.className='inputOn'"
								onblur="this.className='input'"
								name="end_day"
								id="end_day"
								title="<?php echo $lang->display('Day'); ?>"
							>
							<?php BuildDayList($end_day); ?>
							</select>
							/
							<select
								class="input"
								onfocus="this.className='inputOn'"
								onblur="this.className='input'"
								name="end_month"
								id="end_month"
								title="<?php echo $lang->display('Month'); ?>"
							>
							<?php BuildMonthList($end_month); ?>
							</select>
							/
							<select
								class="input"
								onfocus="this.className='inputOn'"
								onblur="this.className='input'"
								name="end_year"
								id="end_year"
								title="<?php echo $lang->display('Year'); ?>"
							>
							<?php BuildYearList($end_year); ?>
							</select>
							<input
								type="submit"
								class="btnDo"
								onmousemove="this.className='btnOn'"
								onmouseout="this.className='btnDo'"
								value="<?php echo $lang->display('Go'); ?>"
								title="<?php echo $lang->display('Go'); ?>"
							>
						</div>
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
								<a href="javascript:void(0);" onclick="SetOrder('listform','trans_time','<?php echo $pre; ?>');">
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
							<th width="20%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','artworkName','<?php echo $pre; ?>');">
									<?php echo $lang->display('Artwork'); ?>
								</a>
							</th>
							<th width="8%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','source_lang','<?php echo $pre; ?>');">
									<?php echo $lang->display('Task Details'); ?>
								</a>
							</th>
							<th width="20%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','transaction','<?php echo $pre; ?>');">
									<?php echo $lang->display('Transaction'); ?>
								</a>
							</th>
							<th width="6%" align="right">
								<a href="javascript:void(0);" onclick="SetOrder('listform','credit_out','<?php echo $pre; ?>');">
									<?php echo $lang->display('Credit out'); ?>
								</a>
							</th>
							<th width="6%" align="right">
								<a href="javascript:void(0);" onclick="SetOrder('listform','credit_in','<?php echo $pre; ?>');">
									<?php echo $lang->display('Credit in'); ?>
								</a>
							</th>
							<th width="6%" align="right">
								<a href="javascript:void(0);" onclick="SetOrder('listform','balance','<?php echo $pre; ?>');">
									<?php echo $lang->display('Balance'); ?>
								</a>
							</th>
							<th width="2%" align="center">
								<a href="javascript:void(0);" onclick="SetOrder('listform','id','<?php echo $pre; ?>');">ID</a>
							</th>
						</tr>
						<?php
							$query = sprintf("SELECT credits.*, UNIX_TIMESTAMP(credits.time) AS trans_time,
											users.username, users.forename, users.surname,
											campaigns.campaignName,
											artworks.artworkName,
											L1.languageName AS source_lang, L1.flag AS source_flag,
											L2.languageName AS target_lang, L2.flag AS target_flag
											FROM credits
											LEFT JOIN users ON credits.user_id = users.userID
											LEFT JOIN campaigns ON credits.campaign_id = campaigns.campaignID
											LEFT JOIN artworks ON credits.artwork_id = artworks.artworkID
											LEFT JOIN tasks ON credits.task_id = tasks.taskID
											LEFT JOIN languages L1 ON campaigns.sourceLanguageID = L1.languageID
											LEFT JOIN languages L2 ON tasks.desiredLanguageID = L2.languageID
											WHERE credits.company_id = %d
											AND DATE(credits.time) >= '%d-%d-%d'
											AND DATE(credits.time) <= '%d-%d-%d'
											ORDER BY `%s` %s
											LIMIT %d
											OFFSET %d",
											$company_id,
											$start_year,
											$start_month,
											$start_day,
											$end_year,
											$end_month,
											$end_day,
											mysql_real_escape_string($by),
											mysql_real_escape_string($order),
											$limit,
											$offset);
							$result = mysql_query($query, $conn) or die(mysql_error());
							if(mysql_num_rows($result)) {
								$counter = $offset+1;
								$balance = 0;
								while($row = mysql_fetch_assoc($result)) {
									$balance = $balance + $row['credit_in'] - $row['credit_out'];
									$style = $counter%2==0 ? 'even' : 'odd';
									echo '<tr class="'.$style.'">';
									echo '<td align="center">'.$counter.'</td>';
									echo '<td align="center"><input type="checkbox" class="checkbox" name="id[]" id="id[]" value="'.$row['id'].'"></td>';
									echo '<td>'.date(FORMAT_TIME,$row['trans_time']).'</td>';
									echo '<td>';
									echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=user&id='.$row['user_id'].'\');">'.$row['username'].'</a>';
									echo '<div class="grey">'.$row['forename'].' '.$row['surname'].'</div>';
									echo '</td>';
									echo '<td><a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=campaign&id='.$row['campaign_id'].'\');">'.$row['campaignName'].'</a></td>';
									echo '<td><a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=artwork&id='.$row['artwork_id'].'\');">'.$row['artworkName'].'</a></td>';
									echo '<td>';
									if(!empty($row['task_id'])) echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=task&id='.$row['task_id'].'\');"><img src="images/flags/'.$row['source_flag'].'" title="'.$lang->display($row['source_lang']).'" /> <img src="'.IMG_PATH.'flag_to.gif" title="'.$lang->display('to be translated to').'" /> <img src="images/flags/'.$row['target_flag'].'" title="'.$lang->display($row['target_lang']).'" /></a>';
									echo '</td>';
									echo '<td>'.$row['transaction'].'</td>';
									echo '<td align="right">'.$row['credit_out'].'</td>';
									echo '<td align="right">'.$row['credit_in'].'</td>';
									echo '<td align="right">'.$row['balance'].'</td>';
									echo '<td align="center">'.$row['id'].'</td>';
									echo '</tr>';
									$counter++;
								}
							} else {
								echo '<tr><td colspan="9" align="center" class="grey">'.$lang->display('No Transaction within this period.').'</td></tr>';
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