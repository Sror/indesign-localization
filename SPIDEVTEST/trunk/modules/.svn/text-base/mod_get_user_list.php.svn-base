<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("tasks","edit");
require_once(MODULES.'mod_authorise.php');

$id = (!empty($_GET['id'])) ? $_GET['id'] : 0;
$source_lang_id = (!empty($_GET['source_lang_id'])) ? $_GET['source_lang_id'] : 0;
$target_lang_id = (!empty($_GET['target_lang_id'])) ? $_GET['target_lang_id'] : 0;
$subject_id = (!empty($_GET['subject_id'])) ? $_GET['subject_id'] : 0;
$word_count = (!empty($_GET['word_count'])) ? $_GET['word_count'] : 0;
$list = (!empty($_GET['list'])) ? $_GET['list'] : "TranslatorList";
switch($list) {
	case "TranslatorList":
		$target = "translator";
		break;
	case "ProofreaderList":
		$target = "proofreader";
		break;
}
?>
<table width="100%" cellspacing="0" cellpadding="3" border="0">
	<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
		<th width="5%" align="center"></th>
		<th width="23%"><?php echo $lang->display('Full Name'); ?></th>
		<th width="23%"><?php echo $lang->display('Language Capability'); ?></th>
		<th width="12%" align="right"><?php echo $lang->display('Base Rate'); ?></th>
		<th width="12%" align="right"><?php echo $lang->display('Cost'); ?></th>
	</tr>
	<?php if($list == "TranslatorList") { ?>
	<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
		<td>
			<input
				type="radio"
				name="translatorID"
				id="translatorID"
				value="<?php echo GOOGLE_BOT_ID; ?>"
				onclick="ResetDiv('<?php echo $list; ?>');DoAjax('id=<?php echo $id; ?>&do=assign&ref=<?php echo GOOGLE_BOT_ID; ?>','<?php echo $list; ?>','modules/mod_<?php echo $target; ?>_list.php');"
			/>
		</td>
		<td>
			<a href="http://translate.google.com" target="_blank"><?php echo $lang->display('Google Bot'); ?></a>
		</td>
		<td><span class="lanRow"><?php echo '<img src="'.IMG_PATH.'ico_tm_google.png">'; ?></span></td>
		<td align="right"><?php echo $lang->display('Free'); ?></td>
		<td align="right"><?php echo $lang->display('Free'); ?></td>
	</tr>
	<?php } ?>
	<?php
		$result_user = $DB->get_users($_SESSION['companyID'],$source_lang_id,$target_lang_id);
		if(mysql_num_rows($result_user)) {
			while($row_user = mysql_fetch_assoc($result_user)) {
	?>
	<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
		<td>
			<input
				type="radio"
				name="userID"
				id="userID"
				value="<?php echo $row_user['userID']; ?>"
				onclick="ResetDiv('<?php echo $list; ?>');DoAjax('id=<?php echo $id; ?>&do=assign&ref=<?php echo $row_user['userID']; ?>','<?php echo $list; ?>','modules/mod_<?php echo $target; ?>_list.php');"
			/>
		</td>
		<td>
			<?php
				echo " <a href=\"index.php?layout=user&id=".$row_user['userID']."\" target=\"_blank\">".$row_user['forename']." ".$row_user['surname']."</a> ";
				BuildUserSpecs($row_user['userID'],$subject_id);
			?>
		</td>
		<td><?php BuildUserLangs($row_user['userID'],$source_lang_id,$target_lang_id); ?></td>
		<td align="right">
			<?php
				$baserate = $DB->get_user_rate($row_user['userID'],$source_lang_id,$target_lang_id);
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
	</tr>
	<?php
			}
		} else {
			echo "<tr><td colspan=\"5\"><i>".$lang->display('N/A')."</i></td></tr>";
		}
	?>
</table>