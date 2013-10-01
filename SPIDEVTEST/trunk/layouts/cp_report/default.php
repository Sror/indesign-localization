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
					<div class="ico"><?php echo '<img src="'.IMG_PATH.'header/ico_report.png">'; ?></div>
					<div class="txt"><?php echo $lang->display('Sign-off Report'); ?></div>
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
				</div>
				<div class="clear"></div>
			</div>
			<?php BuildTipMsg('<a href="javascript:void(0);" onclick="SubmitForm(\'listform\',\'refresh\');">'.$lang->display('Signoff Report Message').'</a>'); ?>
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
							<th width="20%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','date','<?php echo $pre; ?>');">
									<?php echo $lang->display('Date'); ?>
								</a>
							</th>
							<th width="20%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','pages','<?php echo $pre; ?>');">
									<?php echo $lang->display('Pages'); ?>
								</a>
							</th>
							<th width="20%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','cost','<?php echo $pre; ?>');">
									<?php echo $lang->display('Cost'); ?>
								</a>
							</th>
							<th width="20%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','words_total','<?php echo $pre; ?>');">
									<?php echo $lang->display('Word Count'); ?>
								</a>
							</th>
							<th width="20%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','words_tm','<?php echo $pre; ?>');">
									<?php echo $lang->display('Translation Memory'); ?>
								</a>
							</th>
							<th width="14%" align="center">
								<a href="javascript:void(0);" onclick="SetOrder('listform','tm_usage','<?php echo $pre; ?>');">
									<?php echo $lang->display('Usage'); ?>
								</a>
							</th>
							<th width="2%" align="center">
								<a href="javascript:void(0);" onclick="SetOrder('listform','id','<?php echo $pre; ?>');">ID</a>
							</th>
						</tr>
						<?php
							$query =  sprintf("SELECT id, date, pages, cost, words_tm, words_total, words_tm/words_total AS tm_usage
												FROM signoff_report_cache
												WHERE company_id = %d
												AND date >= '%d-%d-%d'
												AND date <= '%d-%d-%d'
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
								$pages = 0;
								$cost = 0;
								$words_tm = 0;
								$words_total = 0;
								while($row = mysql_fetch_assoc($result)) {
									$style = $counter%2==0 ? 'even' : 'odd';
									echo '<tr class="'.$style.'">';
									echo '<td align="center">'.$counter.'</td>';
									echo '<td align="center"><input type="checkbox" class="checkbox" name="id[]" id="id[]" value="'.$row['id'].'"></td>';
									echo '<td>'.date(FORMAT_DATE,strtotime($row['date'])).'</td>';
									echo '<td>'.$row['pages'].'</td>';
									echo '<td>'.CURRENCY_SYMBOL.' '.number_format($row['cost'],2).'</td>';
									echo '<td>'.$row['words_total'].'</td>';
									echo '<td>'.$row['words_tm'].'</td>';
									$usage = ($row['tm_usage']==1) ? 100 : number_format($row['tm_usage']*100,1);
									echo '<td align="center">'.$usage.'%</td>';
									echo '<td align="center">'.$row['id'].'</td>';
									echo '</tr>';
									$counter++;
									$pages += $row['pages'];
									$cost += $row['cost'];
									$words_tm += $row['words_tm'];
									$words_total = $row['words_total'];
								}
								echo '<tr class="subject">';
								echo '<td colspan="3"><b>'.$lang->display('Total').':</b></td>';
								echo '<td>'.$pages.'</td>';
								echo '<td>'.CURRENCY_SYMBOL.' '.number_format($cost,2).'</td>';
								echo '<td>'.$words_total.'</td>';
								echo '<td>'.$words_tm.'</td>';
								$usage = ($words_tm==$words_total) ? 100 : number_format($words_tm/$words_total*100,1);
								echo '<td colspan="2" align="center">'.$usage.'%</td>';
								echo '</tr>';
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