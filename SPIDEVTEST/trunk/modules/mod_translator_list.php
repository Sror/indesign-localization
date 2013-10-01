<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("tasks","edit");
require_once(MODULES.'mod_authorise.php');

$id = !empty($_GET['id']) ? (int)$_GET['id'] : 0 ;
if(!empty($_GET['do']) && !empty($_GET['ref'])) {
	if($_GET['do'] == "delete") {
		$DB->ResetTranslator($id,(int)$_GET['ref']);
	}
	if($_GET['do'] == "assign") {
		$DB->AssignTranslator($id,(int)$_GET['ref']);
	}
}

$query = sprintf("SELECT tasks.desiredLanguageID, tasks.translatorID, tasks.artworkID, tasks.deadline, tasks.brief, tasks.tdeadline,
				artworks.subjectID, artworks.wordCount,
				campaigns.sourceLanguageID
				FROM tasks
				LEFT JOIN artworks ON tasks.artworkID = artworks.artworkID
				LEFT JOIN campaigns ON artworks.campaignID = campaigns.campaignID
				WHERE tasks.taskID = %d
				LIMIT 1",
				$id);
$result = mysql_query($query, $conn) or die(mysql_error());
if(!mysql_num_rows($result)) access_denied();
$row = mysql_fetch_assoc($result);
$artworkID = $row['artworkID'];
$deadline = $row['deadline'];
$tdeadline = $row['tdeadline'];
$brief = $row['brief'];
$source_lang_id = $row['sourceLanguageID'];
$target_lang_id = $row['desiredLanguageID'];
$subject_id = $row['subjectID'];
$word_count = $row['wordCount'];
$translatorID = $row['translatorID'];
?>
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
				name="delete_translator[]"
				id="delete_translator[]"
				value="1"
				onclick="if(confirm('<?php echo $lang->display('Are you sure you want to delete the selected?'); ?>')) { ResetDiv('TranslatorList');DoAjax('id=<?php echo $id; ?>&do=delete&ref=<?php echo $translatorID; ?>','TranslatorList','modules/mod_translator_list.php'); } else return false;"
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
				onclick="ResetDiv('TranslatorList');DoAjax('id=<?php echo $id; ?>&source_lang_id=<?php echo $source_lang_id; ?>&target_lang_id=<?php echo $target_lang_id; ?>&subject_id=<?php echo $subject_id; ?>&word_count=<?php echo $word_count; ?>&list=TranslatorList','TranslatorList','modules/mod_get_user_list.php');"
			/>
		</td>
	</tr>
	<?php } ?>
</table>
