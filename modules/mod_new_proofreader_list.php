<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("tasks","edit");
require_once(MODULES.'mod_authorise.php');

$source_lang_id = (!empty($_GET['source_lang_id'])) ? $_GET['source_lang_id'] : 0;
$target_lang_id = (!empty($_GET['target_lang_id'])) ? $_GET['target_lang_id'] : 0;
$subject_id = (!empty($_GET['subject_id'])) ? $_GET['subject_id'] : 0;
$word_count = (!empty($_GET['word_count'])) ? $_GET['word_count'] : 0;
$ref = !empty($_GET['ref']) ? (int)$_GET['ref'] : 0 ;
if(!empty($_GET['do']) && !empty($ref)) {
	if($_GET['do'] == "delete") {
		unset($_SESSION['proofreaders'][$ref]);
	}
	if($_GET['do'] == "assign") {
		$_SESSION['proofreaders'][$ref] = $ref;
	}
}
?>
<table width="100%" cellspacing="0" cellpadding="3" border="0">
	<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
		<th width="20%"><?php echo $lang->display('Full Name'); ?></th>
		<th width="20%"><?php echo $lang->display('Languages'); ?></th>
		<th width="12%" align="right"><?php echo $lang->display('Base Rate'); ?></th>
		<th width="12%" align="right"><?php echo $lang->display('Cost'); ?></th>
		<th width="10%" align="center"><?php echo $lang->display('Order'); ?></th>
		<th width="15%" align="right"><?php echo $lang->display('Deadline'); ?></th>
		<th width="6%" align="center"><?php echo $lang->display('Done'); ?></th>
		<th width="5%" align="center"><?php echo $lang->display('Delete'); ?></th>
	</tr>
	<?php
		if(!empty($_SESSION['proofreaders'])) {
			$found_proofreader = count($_SESSION['proofreaders']);
			$order = 0;
			foreach($_SESSION['proofreaders'] as $proofreaderID) {
				$order++;
				$row_proofreader = $DB->get_user_info($proofreaderID);
	?>
	<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
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
				value="<?php echo $row_proofreader['deadline']; ?>"
			/>
			<a href="javascript:void(0);" onclick="displayDatePicker('pdeadline<?php echo "[{$row_proofreader['userID']}]"; ?>');"><img src="<?php echo IMG_PATH; ?>ico_calendar.gif"></a>
		</td>
		<td align="center">
			<input
				type="checkbox"
				name="done<?php echo "[{$row_proofreader['userID']}]"; ?>"
				id="done<?php echo "[{$row_proofreader['userID']}]"; ?>"
				value="1"
				<?php if($row_proofreader['done']) echo 'checked="checked"'; ?>
			/>
		</td>
		<td align="center">
			<input
				type="checkbox"
				name="delete_proofreader<?php echo "[{$row_proofreader['userID']}]"; ?>"
				id="delete_proofreader<?php echo "[{$row_proofreader['userID']}]"; ?>"
				value="1"
				onclick="if(confirm('<?php echo $lang->display('Are you sure you want to delete the selected?'); ?>')) { ResetDiv('ProofreaderList');DoAjax('source_lang_id=<?php echo $source_lang_id; ?>&target_lang_id=<?php echo $target_lang_id; ?>&subject_id=<?php echo $subject_id; ?>&word_count=<?php echo $word_count; ?>&do=delete&ref=<?php echo $row_proofreader['userID']; ?>','ProofreaderList','modules/mod_new_proofreader_list.php'); } else return false;"
			/>
		</td>
	</tr>
	<?php }} ?>
</table>