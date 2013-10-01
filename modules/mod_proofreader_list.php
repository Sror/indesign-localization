<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("tasks","edit");
require_once(MODULES.'mod_authorise.php');

$id = !empty($_GET['id']) ? (int)$_GET['id'] : 0 ;
if(!empty($_GET['do']) && !empty($_GET['ref'])) {
	if($_GET['do'] == "delete") {
		$DB->DeleteProofreader($id,(int)$_GET['ref']);
	}
	if($_GET['do'] == "assign") {
		$DB->AssignProofreader($id,(int)$_GET['ref']);
	}
}

$query = sprintf("SELECT tasks.desiredLanguageID,
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
$source_lang_id = $row['sourceLanguageID'];
$target_lang_id = $row['desiredLanguageID'];
$subject_id = $row['subjectID'];
$word_count = $row['wordCount'];
?>
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
		$result_proofreader = $DB->get_proofreaders($id);
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
		<td align="center">
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
				onclick="if(confirm('<?php echo $lang->display('Are you sure you want to delete the selected?'); ?>')) { ResetDiv('ProofreaderList');DoAjax('id=<?php echo $id; ?>&do=delete&ref=<?php echo $row_proofreader['user_id']; ?>','ProofreaderList','modules/mod_proofreader_list.php'); } else return false;"
			/>
		</td>
	</tr>
	<?php } ?>
	<tr>
		<td colspan="8" align="center">
			<input
				type="button"
				class="btnDo"
				onmousemove="this.className='btnOn'"
				onmouseout="this.className='btnDo'"
				title="<?php echo $lang->display('Assign Proofreaders'); ?>"
				value="<?php echo $lang->display('Assign Proofreaders'); ?>"
				onclick="ResetDiv('ProofreaderList');DoAjax('id=<?php echo $id; ?>&source_lang_id=<?php echo $source_lang_id; ?>&target_lang_id=<?php echo $target_lang_id; ?>&subject_id=<?php echo $subject_id; ?>&word_count=<?php echo $word_count; ?>&list=ProofreaderList','ProofreaderList','modules/mod_get_user_list.php');"
			/>
		</td>
	</tr>
</table>
