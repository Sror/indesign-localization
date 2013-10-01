<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("tasks","new");
require_once(MODULES.'mod_authorise.php');

$source_lang_id = (!empty($_GET['source_lang_id'])) ? $_GET['source_lang_id'] : 0;
$target_lang_id = (!empty($_GET['target_lang_id'])) ? $_GET['target_lang_id'] : 0;
$subject_id = (!empty($_GET['subject_id'])) ? $_GET['subject_id'] : 0;
$word_count = (!empty($_GET['word_count'])) ? $_GET['word_count'] : 0;
?>
<div class="mainwrap">
	<div class="list">
		<table width="100%" cellspacing="0" cellpadding="3" border="0">
			<tr>
				<th width="45%"><div class="arrdwn"><?php echo $lang->display('Assign Translators'); ?></div></th>
				<th width="55%"><div class="arrdwn"><?php echo $lang->display('Assign Proofreaders'); ?></div></th>
			</tr>
			<tr>
				<td valign="top">
					<div id="TranslatorList">
						<table width="100%" cellspacing="0" cellpadding="3" border="0">
							<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
								<th width="5%" align="center"></th>
								<th width="23%"><?php echo $lang->display('Full Name'); ?></th>
								<th width="23%"><?php echo $lang->display('Languages'); ?></th>
								<th width="12%" align="right"><?php echo $lang->display('Rate'); ?></th>
								<th width="12%" align="right"><?php echo $lang->display('Cost'); ?></th>
								<th width="25%" align="center"><?php echo $lang->display('Deadline'); ?></th>
							</tr>
							<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
								<td>
									<input
										type="radio"
										name="translatorID"
										id="translatorID"
										value="<?php echo GOOGLE_BOT_ID; ?>"
										checked="checked"
									/>
								</td>
								<td>
									<a href="http://translate.google.com" target="_blank"><?php echo $lang->display('Google Bot'); ?></a>
								</td>
								<td><span class="lanRow"><?php echo '<img src="'.IMG_PATH.'ico_tm_google.png">'; ?></span></td>
								<td align="right"><?php echo $lang->display('Free'); ?></td>
								<td align="right"><?php echo $lang->display('Free'); ?></td>
								<td align="center"></td>
							</tr>
							<?php
								$result_translator = $DB->get_users($_SESSION['companyID'],$source_lang_id,$target_lang_id);
								if(mysql_num_rows($result_translator)) {
									while($row_translator = mysql_fetch_assoc($result_translator)) {
							?>
							<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
								<td align="center">
									<input
										type="radio"
										name="translatorID"
										id="translatorID"
										value="<?php echo $row_translator['userID']; ?>"
									/>
								</td>
								<td>
									<?php
										echo " <a href=\"index.php?layout=user&id=".$row_translator['userID']."\" target=\"_blank\">".$row_translator['forename']." ".$row_translator['surname']."</a> ";
										BuildUserSpecs($row_translator['userID'],$subject_id);
									?>
								</td>
								<td><?php BuildUserLangs($row_translator['userID'],$source_lang_id,$target_lang_id); ?></td>
								<td align="right">
									<?php
										$baserate = $DB->get_user_rate($row_translator['userID'],$source_lang_id,$target_lang_id);
										if($baserate === false) {
											$rate = '<img src="'.IMG_PATH.'ico_error.png" title="'.$lang->display('N/A').'">';
											$cost = '<img src="'.IMG_PATH.'ico_error.png" title="'.$lang->display('N/A').'">';
										} else {
											$rate = $baserate['symbol']." ".$baserate['rate'];
											$cost = $baserate['symbol']." ".($baserate['rate']*$word_count);
										}
										echo $rate;
									?>
								</td>
								<td align="right"><?php echo $cost; ?></td>
								<td align="center">
									<input
										type="text"
										class="input"
										onfocus="this.className='inputOn'"
										onblur="this.className='input'"
										name="tdeadline<?php echo "[{$row_translator['userID']}]"; ?>"
										id="tdeadline<?php echo "[{$row_translator['userID']}]"; ?>"
										onclick="displayDatePicker('tdeadline<?php echo "[{$row_translator['userID']}]"; ?>')"
										readonly="readonly"
										size="10"
									/>
									<a href="javascript:void(0);" onclick="displayDatePicker('tdeadline<?php echo "[{$row_translator['userID']}]"; ?>');"><img src="<?php echo IMG_PATH; ?>ico_calendar.gif"></a>
								</td>
							</tr>
							<?php
									}
								} else {
									echo '<tr class="bgWhite" onmouseover="this.className=\'hover\'" onmouseout="this.className=\'bgWhite\'"><td colspan="6" align="center"><i>'.$lang->display('No Translator Mapped').'</i></td></tr>';
								}
							?>
						</table>
					</div>
				</td>
				<td valign="top">
					<div id="ProofreaderList">
						<table width="100%" cellspacing="0" cellpadding="3" border="0">
							<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
								<th width="5%" align="center"></th>
								<th width="23%"><?php echo $lang->display('Full Name'); ?></th>
								<th width="23%"><?php echo $lang->display('Languages'); ?></th>
								<th width="12%" align="right"><?php echo $lang->display('Rate'); ?></th>
								<th width="12%" align="right"><?php echo $lang->display('Cost'); ?></th>
								<th width="10%" align="center"><?php echo $lang->display('Order'); ?></th>
								<th width="15%" align="right"><?php echo $lang->display('Deadline'); ?></th>
							</tr>
							<?php
								$result_proofreader = $DB->get_users($_SESSION['companyID'],$source_lang_id,$target_lang_id);
								$found_proofreader = mysql_num_rows($result_proofreader);
								if($found_proofreader) {
									while($row_proofreader = mysql_fetch_assoc($result_proofreader)) {
							?>
							<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
								<td align="center">
									<input
										type="checkbox"
										name="proofreaderID[]"
										id="proofreaderID[]"
										value="<?php echo $row_proofreader['userID']; ?>"
									/>
								</td>
								<td>
									<?php
										echo " <a href=\"index.php?layout=user&id=".$row_proofreader['userID']."\" target=\"_blank\">".$row_proofreader['forename']." ".$row_proofreader['surname']."</a> ";
										BuildUserSpecs($row_proofreader['userID'],$subject_id);
									?>
								</td>
								<td><?php BuildUserLangs($row_proofreader['userID'],$source_lang_id,$target_lang_id); ?></td>
								<td align="right">
									<?php
										$baserate = $DB->get_user_rate($row_proofreader['userID'],$source_lang_id,$target_lang_id);
										if($baserate === false) {
											$rate = '<img src="'.IMG_PATH.'ico_error.png" title="'.$lang->display('N/A').'">';
											$cost = '<img src="'.IMG_PATH.'ico_error.png" title="'.$lang->display('N/A').'">';
										} else {
											$rate = $baserate['symbol']." ".$baserate['rate'];
											$cost = $baserate['symbol']." ".($baserate['rate']*$word_count);
										}
										echo $rate;
									?>
								</td>
								<td align="right"><?php echo $cost; ?></td>
								<td align="center">
									<select
										class="input"
										onfocus="this.className='inputOn'"
										onblur="this.className='input'"
										name="order<?php echo "[{$row_proofreader['userID']}]"; ?>"
										id="order<?php echo "[{$row_proofreader['userID']}]"; ?>"
									>
										<?php BuildOrders($found_proofreader,$order); ?>
									</select>
								</td>
								<td align="center">
									<input
										type="text"
										class="input"
										onfocus="this.className='inputOn'"
										onblur="this.className='input'"
										name="pdeadline<?php echo "[{$row_proofreader['userID']}]"; ?>"
										id="pdeadline<?php echo "[{$row_proofreader['userID']}]"; ?>"
										onclick="displayDatePicker('pdeadline<?php echo "[{$row_proofreader['userID']}]"; ?>')"
										readonly="readonly"
										size="8"
									/>
									<a href="javascript:void(0);" onclick="displayDatePicker('pdeadline<?php echo "[{$row_proofreader['userID']}]"; ?>');"><img src="<?php echo IMG_PATH; ?>ico_calendar.gif"></a>
								</td>
							</tr>
							<?php }} ?>
						</table>
					</div>
				</td>
			</tr>
		</table>
	</div>
</div>