<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("tasks","new");
require_once(MODULES.'mod_authorise.php');

$artworkID = (isset($_GET['artworkID'])) ? (int)$_GET['artworkID'] : 0;
$langID = (isset($_GET['langID'])) ? (int)$_GET['langID'] : 0;
if(empty($artworkID) || empty($langID) || empty($_SESSION['tasks'][$langID])) die("Invalid Task");
$info = $_SESSION['tasks'][$langID];
?>
<form
	id="newform"
	name="newform"
	action="index.php?layout=artwork&id=<?php echo $artworkID; ?>&task=step2"
	method="POST"
	enctype="multipart/form-data"
>
	<div class="arrdwn"><?php echo $lang->display('Assign Translators'); ?></div>
	<div id="TranslatorList">
		<div class="mainwrap">
			<div class="list">
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
								<?php if(array_key_exists(GOOGLE_BOT_ID,$info['translators'])) echo 'checked="checked"'; ?>
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
								<?php if(array_key_exists($row_translator['userID'],$info['translators'])) echo 'checked="checked"'; ?>
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
								value="<?php echo $info['translators'][$row_translator['userID']]['deadline']; ?>"
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
		</div>
	</div>
	<div class="arrdwn"><?php echo $lang->display('Assign Proofreaders'); ?></div>
	<div id="ProofreaderList">
		<div class="mainwrap">
			<div class="list">
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
								<?php if(array_key_exists($row_proofreader['userID'],$info['proofreaders'])) echo 'checked="checked"'; ?>
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
								<?php BuildOrders($found_proofreader,$info['proofreaders'][$row_proofreader['userID']]['order']); ?>
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
								value="<?php echo $info['proofreaders'][$row_proofreader['userID']]['deadline']; ?>"
							/>
							<a href="javascript:void(0);" onclick="displayDatePicker('pdeadline<?php echo "[{$row_proofreader['userID']}]"; ?>');"><img src="<?php echo IMG_PATH; ?>ico_calendar.gif"></a>
						</td>
					</tr>
					<?php }} ?>
				</table>
			</div>
		</div>
	</div>
	<table width="100%" cellspacing="0" cellpadding="3" border="0">
		<tr>
			<td width="30%" class="highlight">* <?php echo $lang->display('Deadline')." (".$lang->display('Sign-off').")"; ?>:</td>
			<td width="70%">
				<input
					type="text"
					class="input"
					onfocus="this.className='inputOn'"
					onblur="this.className='input'"
					name="deadline"
					id="deadline"
					onclick="displayDatePicker('deadline')"
					readonly="readonly"
					value="<?php echo $info['deadline']; ?>"
				/>
				<a href="javascript:void(0);" onclick="displayDatePicker('deadline');"><img src="<?php echo IMG_PATH; ?>ico_calendar.gif"></a>
			</td>
		</tr>
		<tr>
			<td class="highlight" valign="top"><?php echo $lang->display('Notes'); ?>:</td>
			<td>
				<textarea
					class="input"
					onfocus="this.className='inputOn'"
					onblur="this.className='input'"
					name="notes"
					id="notes"
					rows="3"
					cols="80"
				><?php echo $info['notes']; ?></textarea>
			</td>
		</tr>
		<tr>
			<td></td>
			<td>
				<input
					type="button"
					class="btnDo"
					onmousemove="this.className='btnOn'"
					onmouseout="this.className='btnDo'"
					title="<?php echo $lang->display('Update'); ?>"
					value="<?php echo $lang->display('Update'); ?>"
					onclick="validateForm('deadline','Deadline','R'); if(document.returnValue) SubmitForm('newform','save');"
				/>
				<input
					type="reset"
					class="btnOff"
					onmousemove="this.className='btnOn'"
					onmouseout="this.className='btnOff'"
					title="<?php echo $lang->display('Reset'); ?>"
					value="<?php echo $lang->display('Reset'); ?>"
				/>
			</td>
		</tr>
	</table>
	<input type="hidden" name="desiredLanguageID" value="<?php echo $langID; ?>">
	<input type="hidden" name="form" id="form">
</form>