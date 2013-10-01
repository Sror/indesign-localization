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
					<div class="txt"><?php echo $lang->display('Cost Manager').': '.$lang->display('Edit'); ?></div>
				</div>
				<div class="options">
					<!-- Save -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Save'); ?>">
						<a href="javascript:void(0);" onclick="validateForm('agency_id','Company','R','currency_id','Currency','R','rate','Cost per Page','RisNum','date','Valid From Date','R'); if(document.returnValue) SubmitForm('editform','save');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_save.png">'; ?></div>
							<div><?php echo $lang->display('Save'); ?></div>
						</a>
					</div>
					<!-- Apply -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Apply'); ?>">
						<a href="javascript:void(0);" onclick="validateForm('agency_id','Company','R','currency_id','Currency','R','rate','Cost per Page','RisNum','date','Valid From Date','R'); if(document.returnValue) SubmitForm('editform','apply');">
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
							<legend><?php echo $lang->display('Cost per Page'); ?></legend>
							<table width="100%" cellpadding="3" cellspacing="0" border="0">
								<tr>
									<th>* <?php echo $lang->display('Agency'); ?></th>
									<td>
										<select
											class="input"
											name="agency_id"
											id="agency_id"
										>
										<?php BuildCompanyList($cost_row['agency_id'],$issuperadmin); ?>
										</select>
									</td>
								</tr>
								<tr>
									<th><?php echo $lang->display('Client'); ?></th>
									<td>
										<select
											class="input"
											name="client_id"
											id="client_id"
										>
										<?php BuildParentCompanyList(0,$cost_row['client_id']); ?>
										</select>
									</td>
								</tr>
								<tr>
									<th>* <?php echo $lang->display('Currency'); ?></th>
									<td>
										<select
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="currency_id"
											id="currency_id"
										>
										<?php BuildCurrencyList($cost_row['currency_id']); ?>
										</select>
									</td>
								</tr>
								<tr>
									<th>* <?php echo $lang->display('Cost per Page'); ?></th>
									<td>
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="rate"
											id="rate"
											value="<?php echo $cost_row['rate']; ?>"
										>
									</td>
								</tr>
							</table>
						</fieldset>
					</div>
				</div>
				<div class="rightwrap">
					<div class="fieldset">
						<fieldset>
							<legend><?php echo $lang->display('Valid From'); ?></legend>
							<table width="100%" cellpadding="3" cellspacing="0" border="0">
								<tr>
									<th>* <?php echo $lang->display('Valid From'); ?></th>
									<td>
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="date"
											id="date"
											onclick="displayDatePicker('date')"
											value="<?php echo $cost_row['date']; ?>"
											readonly="readonly"
										/>
										<a href="javascript:void(0);" onclick="displayDatePicker('date');">
											<img src="<?php echo IMG_PATH; ?>ico_calendar.gif">
										</a>
									</td>
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
<script type="text/javascript" src="javascripts/datepicker.js"></script>