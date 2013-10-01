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
					<div class="ico"><?php echo '<img src="'.IMG_PATH.'header/ico_cost.png">'; ?></div>
					<div class="txt"><?php echo $lang->display('Cost Manager'); ?></div>
				</div>
				<div class="options">
					<?php if($issuperadmin) { ?>
					<!-- New -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('New'); ?>">
						<a href="javascript:void(0);" onclick="SubmitForm('listform','new');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_new.png">'; ?></div>
							<div><?php echo $lang->display('New'); ?></div>
						</a>
					</div>
					<!-- Edit -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Edit'); ?>">
						<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) { SubmitForm('listform','edit'); }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_edit.png">'; ?></div>
							<div><?php echo $lang->display('Edit'); ?></div>
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
							<th width="46%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','client','<?php echo $pre; ?>');">
									<?php echo $lang->display('Client'); ?>
								</a>
							</th>
							<th width="24%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','rate','<?php echo $pre; ?>');">
									<?php echo $lang->display('Cost per Page'); ?>
								</a>
							</th>
							<th width="24%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','date','<?php echo $pre; ?>');">
									<?php echo $lang->display('Valid From'); ?>
								</a>
							</th>
							<th width="2%" align="center">
								<a href="javascript:void(0);" onclick="SetOrder('listform','id','<?php echo $pre; ?>');">ID</a>
							</th>
						</tr>
						<?php
							$query =  sprintf("SELECT page_rates.id, page_rates.client_id, page_rates.rate, page_rates.date,
												companies.companyName AS client,
												currencies.currencyAb, currencies.currencySymbol
												FROM page_rates
												LEFT JOIN companies ON page_rates.client_id = companies.companyID
												LEFT JOIN currencies ON page_rates.currency_id = currencies.currencyID
												WHERE page_rates.agency_id = %d
												AND (page_rates.rate LIKE '%s'
												OR companies.companyName LIKE '%s')
												ORDER BY `%s` %s
												LIMIT %d
												OFFSET %d",
												$company_id,
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
									echo '<td><a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout='.$layout.'&task=edit&id='.$row['id'].'\');">';
									echo empty($row['client_id']) ? '<i>'.$lang->display('N/S').'</i>' : $row['client'];
									echo '</a></td>';
									echo '<td>'.$row['currencySymbol'].' '.number_format($row['rate'],2).' '.$row['currencyAb'].'</td>';
									echo '<td>'.date(FORMAT_DATE,strtotime($row['date'])).'</td>';
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