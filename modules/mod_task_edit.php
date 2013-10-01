<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("tasks","edit");
require_once(MODULES.'mod_authorise.php');

$ref = isset($_GET['ref']) ? (strpos($_GET['ref'],',') ? substr($_GET['ref'],0,strpos($_GET['ref'],',')) : $_GET['ref']) : 0;
$target = !empty($_GET['target']) ? $_GET['target'] : "task";
$query = sprintf("SELECT tasks.desiredLanguageID, tasks.translatorID, tasks.artworkID, tasks.deadline, tasks.brief, tasks.tdeadline, tasks.trial,
				artworks.subjectID, artworks.wordCount,
				campaigns.sourceLanguageID
				FROM tasks
				LEFT JOIN artworks ON tasks.artworkID = artworks.artworkID
				LEFT JOIN campaigns ON artworks.campaignID = campaigns.campaignID
				WHERE tasks.taskID = %d
				LIMIT 1",
				$ref);
$result = mysql_query($query, $conn) or die(mysql_error());
if(!mysql_num_rows($result)) access_denied();
$row = mysql_fetch_assoc($result);
$artworkID = $row['artworkID'];
$deadline = $row['deadline'];
$tdeadline = $row['tdeadline'];
$brief = $row['brief'];
$trial = $row['trial'];
$source_lang_id = $row['sourceLanguageID'];
$target_lang_id = $row['desiredLanguageID'];
$subject_id = $row['subjectID'];
$word_count = $row['wordCount'];
$translatorID = $row['translatorID'];
?>
<form
	action="index.php?layout=task&id=<?php echo $ref; ?>"
	name="editTask"
	method="POST"
	enctype="multipart/form-data"
	onsubmit="hidediv('helper');Popup('loadingme','waiting');"
>
	<div class="highlight">* <?php echo $lang->display('Translator'); ?>:</div>
	<div class="mainwrap">
		<div class="list">
			<div id="TranslatorList">
				<table width="100%" cellspacing="0" cellpadding="3" border="0">
					<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
						<th width="23%"><?php echo $lang->display('Full Name'); ?></th>
						<th width="23%"><?php echo $lang->display('Language Capability'); ?></th>
						<th width="12%" align="right"><?php echo $lang->display('Base Rate'); ?></th>
						<th width="12%" align="right"><?php echo $lang->display('Cost'); ?></th>
						<th width="25%" align="center"><?php echo $lang->display('Deadline'); ?></th>
						<th width="5%" align="center"><?php echo $lang->display('Delete'); ?></th>
					</tr>
					<?php
						if(!empty($translatorID)) {
							$row_translator = $DB->get_user_info($translatorID);
					?>
					<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
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
								name="tdeadline"
								id="tdeadline"
								onclick="displayDatePicker('tdeadline')"
								readonly="readonly"
								size="16"
								value="<?php echo $tdeadline; ?>"
							/>
							<a href="javascript:void(0);" onclick="displayDatePicker('tdeadline');"><img src="<?php echo IMG_PATH; ?>ico_calendar.gif"></a>
						</td>
						<td align="center">
							<input
								type="checkbox"
								name="delete_translator"
								id="delete_translator"
								value="1"
								onclick="if(confirm('<?php echo $lang->display('Are you sure you want to delete the selected?'); ?>')) { ResetDiv('TranslatorList');DoAjax('id=<?php echo $ref; ?>&do=delete&ref=<?php echo $translatorID; ?>','TranslatorList','modules/mod_translator_list.php'); } else return false;"
							/>
						</td>
					</tr>
					<?php } else { ?>
					<tr>
						<td colspan="6" align="center">
							<input
								type="button"
								class="btnDo"
								onmousemove="this.className='btnOn'"
								onmouseout="this.className='btnDo'"
								title="<?php echo $lang->display('Assign Translators'); ?>"
								value="<?php echo $lang->display('Assign Translators'); ?>"
								onclick="ResetDiv('TranslatorList');DoAjax('id=<?php echo $ref; ?>&source_lang_id=<?php echo $source_lang_id; ?>&target_lang_id=<?php echo $target_lang_id; ?>&subject_id=<?php echo $subject_id; ?>&word_count=<?php echo $word_count; ?>&list=TranslatorList','TranslatorList','modules/mod_get_user_list.php');"
							/>
						</td>
					</tr>
					<?php } ?>
				</table>
			</div>
		</div>
	</div>
	<div class="highlight"><?php echo $lang->display('Proofreader'); ?>:</div>
	<div class="mainwrap">
		<div class="list">
			<div id="ProofreaderList">
				<table width="100%" cellspacing="0" cellpadding="3" border="0">
					<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
						<th width="20%"><?php echo $lang->display('Full Name'); ?></th>
						<th width="20%"><?php echo $lang->display('Language Capability'); ?></th>
						<th width="12%" align="right"><?php echo $lang->display('Base Rate'); ?></th>
						<th width="12%" align="right"><?php echo $lang->display('Cost'); ?></th>
						<th width="10%" align="center"><?php echo $lang->display('Order'); ?></th>
						<th width="15%" align="right"><?php echo $lang->display('Deadline'); ?></th>
						<th width="6%" align="center"><?php echo $lang->display('Done'); ?></th>
						<th width="5%" align="center"><?php echo $lang->display('Delete'); ?></th>
					</tr>
					<?php
						$result_proofreader = $DB->get_proofreaders($ref);
						$found_proofreader = mysql_num_rows($result_proofreader);
						while($row_proofreader = mysql_fetch_assoc($result_proofreader)) {
					?>
					<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
						<td>
							<?php
								echo " <a href=\"index.php?layout=user&id=".$row_proofreader['user_id']."\" target=\"_blank\">".$row_proofreader['forename']." ".$row_proofreader['surname']."</a> ";
								BuildUserSpecs($row_proofreader['user_id'],$subject_id);
							?>
						</td>
						<td><?php BuildUserLangs($row_proofreader['user_id'],$source_lang_id,$target_lang_id); ?></td>
						<td align="right">
							<?php
								$baserate = $DB->get_user_rate($row_proofreader['user_id'],$source_lang_id,$target_lang_id);
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
								name="order[<?php echo $row_proofreader['id']; ?>]"
								id="order[<?php echo $row_proofreader['id']; ?>]"
							>
								<?php BuildOrders($found_proofreader,$row_proofreader['order']); ?>
							</select>
						</td>
						<td align="right">
							<input
								type="text"
								class="input"
								onfocus="this.className='inputOn'"
								onblur="this.className='input'"
								name="pdeadline[<?php echo $row_proofreader['id']; ?>]"
								id="pdeadline[<?php echo $row_proofreader['id']; ?>]"
								onclick="displayDatePicker('pdeadline[<?php echo $row_proofreader['id']; ?>]')"
								readonly="readonly"
								size="8"
								value="<?php echo $row_proofreader['deadline']; ?>"
							/>
							<a href="javascript:void(0);" onclick="displayDatePicker('pdeadline[<?php echo $row_proofreader['id']; ?>]');"><img src="<?php echo IMG_PATH; ?>ico_calendar.gif"></a>
						</td>
						<td align="center">
							<input
								type="checkbox"
								name="done[<?php echo $row_proofreader['id']; ?>]"
								id="done[<?php echo $row_proofreader['id']; ?>]"
								value="1"
								<?php if($row_proofreader['done']) echo 'checked="checked"'; ?>
							/>
						</td>
						<td align="center">
							<input
								type="checkbox"
								name="delete_proofreader[]"
								id="delete_proofreader[]"
								value="1"
								onclick="if(confirm('<?php echo $lang->display('Are you sure you want to delete the selected?'); ?>')) { ResetDiv('ProofreaderList');DoAjax('id=<?php echo $ref; ?>&do=delete&ref=<?php echo $row_proofreader['user_id']; ?>','ProofreaderList','modules/mod_proofreader_list.php'); } else return false;"
							/>
						</td>
					</tr>
					<?php
						}
					?>
					<tr>
						<td colspan="8" align="center">
							<input
								type="button"
								class="btnDo"
								onmousemove="this.className='btnOn'"
								onmouseout="this.className='btnDo'"
								title="<?php echo $lang->display('Assign Proofreaders'); ?>"
								value="<?php echo $lang->display('Assign Proofreaders'); ?>"
								onclick="ResetDiv('ProofreaderList');DoAjax('id=<?php echo $ref; ?>&source_lang_id=<?php echo $source_lang_id; ?>&target_lang_id=<?php echo $target_lang_id; ?>&subject_id=<?php echo $subject_id; ?>&word_count=<?php echo $word_count; ?>&list=ProofreaderList','ProofreaderList','modules/mod_get_user_list.php');"
							/>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</div>
	<table width="100%" cellspacing="0" cellpadding="3" border="0">
		<?php if($target == "artwork") { ?>
		<tr>
			<td width="30%" class="highlight">* <?php echo $lang->display('Deadline'); ?>:</td>
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
					value="<?php echo $deadline; ?>"
				/>
				<a href="javascript:void(0);" onclick="displayDatePicker('deadline');"><img src="<?php echo IMG_PATH; ?>ico_calendar.gif"></a>
			</td>
		</tr>
		<tr>
			<td class="highlight" valign="top"><?php echo $lang->display('Job Brief'); ?>:</td>
			<td>
				<textarea
					class="input"
					onfocus="this.className='inputOn'"
					onblur="this.className='input'"
					name="brief"
					id="brief"
					rows="5"
					cols="80"
				><?php echo "\n".$brief; ?></textarea>
			</td>
		</tr>
		<tr>
			<td class="highlight"><?php echo $lang->display('Attachment'); ?>:</td>
			<td>
				<input
					type="file"
					class="input"
					onfocus="this.className='inputOn'"
					onblur="this.className='input'"
					name="attachment"
					id="attachment"
				/>
			</td>
		</tr>
		<tr>
			<td></td>
			<td>
				<input
					type="checkbox"
					name="trial"
					id="trial"
					value="1"
					<?php if($trial) echo ' checked="checked"'; ?>
				/>
				<?php echo $lang->display('This is a trial run that only deals with headings.'); ?>
			</td>
		</tr>
		<?php
			} else {
				echo '<input type="hidden" name="trial" id="trial" value="'.$trial.'">';
			}
		?>
		<tr>
			<td colspan="2" align="center">
				<input
					type="submit"
					class="btnDo"
					onmousemove="this.className='btnOn'"
					onmouseout="this.className='btnDo'"
					title="<?php echo $lang->display('Confirm'); ?>"
					value="<?php echo $lang->display('Confirm'); ?>"
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
	<input name="target" type="hidden" value="<?php echo $target; ?>">
	<input name="update" type="hidden" value="editTask">
</form>