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
					<div class="ico"><?php echo '<img src="'.IMG_PATH.'header/ico_currency.png">'; ?></div>
					<div class="txt"><?php echo $lang->display('Currency Manager').': '.$lang->display('New'); ?></div>
				</div>
				<div class="options">
					<!-- Save -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Save'); ?>">
						<a href="javascript:void(0);" onclick="validateForm('currencyName','Currency Name','R','currencyAb','Currency Abbreviation','R','currencySymbol','Currency Symbol','R'); if(document.returnValue) SubmitForm('newform','save');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_save.png">'; ?></div>
							<div><?php echo $lang->display('Save'); ?></div>
						</a>
					</div>
					<!-- Apply -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Apply'); ?>">
						<a href="javascript:void(0);" onclick="validateForm('currencyName','Currency Name','R','currencyAb','Currency Abbreviation','R','currencySymbol','Currency Symbol','R'); if(document.returnValue) SubmitForm('newform','apply');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_apply.png">'; ?></div>
							<div><?php echo $lang->display('Apply'); ?></div>
						</a>
					</div>
					<!-- Cancel -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Cancel'); ?>">
						<a href="javascript:void(0);" onclick="SubmitForm('newform','cancel');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_close.png">'; ?></div>
							<div><?php echo $lang->display('Cancel'); ?></div>
						</a>
					</div>
				</div>
				<div class="clear"></div>
			</div>
			<!-- Mainwrap -->
			<div class="mainwrap">
				<form
					id="newform"
					name="newform"
					action="index.php?layout=<?php echo $layout; ?>&task=<?php echo $task; ?>"
					method="POST"
					enctype="multipart/form-data"
				>
				<div>
					<div class="fieldset">
						<fieldset>
							<legend><?php echo $lang->display('Currency'); ?></legend>
							<table width="100%" cellpadding="3" cellspacing="0" border="0">
								<tr>
									<th>* <?php echo $lang->display('Currency Name'); ?></th>
									<td>
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="currencyName"
											id="currencyName"
										>
									</td>
								</tr>
								<tr>
									<th>* <?php echo $lang->display('Currency Abbreviation'); ?></th>
									<td>
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="currencyAb"
											id="currencyAb"
											maxlength="3"
										>
									</td>
								</tr>
								<tr>
									<th>* <?php echo $lang->display('Currency Symbol'); ?></th>
									<td>
										<input
											type="text"
											class="input"
											onfocus="this.className='inputOn'"
											onblur="this.className='input'"
											name="currencySymbol"
											id="currencySymbol"
											maxlength="1"
										>
									</td>
								</tr>
							</table>
						</fieldset>
					</div>
				</div>
				<input type="hidden" name="form" id="form">
				</form>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="javascripts/datepicker.js"></script>