<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("tasks","new");
require_once(MODULES.'mod_authorise.php');

if(!empty($_GET['do']) && !empty($_GET['ref'])) {
	if($_GET['do'] == "delete") {
		$ref = (int)$_GET['ref'];
		unset($_SESSION['tasks'][$ref]);
	}
}
?>
<div class="list">
	<table width="100%" cellspacing="0" cellpadding="3" border="0">
		<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
			<th width="4%" colspan="2"></th>
			<th width="18%" colspan="2"><?php echo $lang->display('Languages'); ?></th>
			<th width="8%"><?php echo $lang->display('Story Group'); ?></th>
			<th width="25%"><?php echo $lang->display('Translator'); ?></th>
			<th width="25%"><?php echo $lang->display('Proofreader'); ?></th>
			<th width="8%"><?php echo $lang->display('Deadline'); ?></th>
			<th width="20%"><?php echo $lang->display('Notes'); ?></th>
		</tr>
		<?php
			if(!empty($_SESSION['tasks'])) {
				foreach($_SESSION['tasks'] as $langID => $info) {
					$lang_info = $DB->get_lang_info($langID);
					if($lang_info === false) continue;
		?>
		<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
			<td><a href="javascript:void(0);" onclick="if(confirm('<?php echo $lang->display('Are you sure you want to delete the selected?'); ?>')) { ResetDiv('tasklist');DoAjax('do=delete&ref=<?php echo $langID; ?>','tasklist','modules/mod_new_task_details.php'); } else return false;"><img src="<?php echo IMG_PATH; ?>ico_disable.png" title="<?php echo $lang->display('Delete'); ?>" /></a></td>
			<td><a href="javascript:void(0);" onclick="Popup('helper','blur');DoAjax('artworkID=<?php echo $artworkID; ?>&langID=<?php echo $langID; ?>','window','modules/mod_new_task_edit.php');"><img src="<?php echo IMG_PATH; ?>ico_edit.png" title="<?php echo $lang->display('Edit'); ?>" /></a></td>
			<td align="center"><img src="images/flags/<?php echo $lang_info['flag']; ?>" title="<?php echo $lang_info['languageName']; ?>" /></td>
			<td><?php echo $lang_info['languageName']; ?></td>
                        <td><?php
                            $storyName = $DB->artwork_story_groups($info['storyGroup']);
                            echo $storyName; ?>
                        </td>
			<td>
				<ul>
				<?php
					foreach($info['translators'] as $translatorID=>$translatorInfo) {
						$row_translator = $DB->get_user_info($translatorID);
						if($row_translator === false) continue;
						echo "<li><a href=\"index.php?layout=user&id={$row_translator['userID']}\" target=\"_blank\">{$row_translator['forename']} {$row_translator['surname']}</a> <span class=\"grey\">{$translatorInfo['deadline']}</span></li>";
					}
				?>
				</ul>
			</td>
			<td>
				<?php
					foreach($info['proofreaders'] as $proofreaderID=>$proofreaderInfo) {
						$row_proofreader = $DB->get_user_info($proofreaderID);
						if($row_proofreader === false) continue;
						echo "<div>{$proofreaderInfo['order']}. <a href=\"index.php?layout=user&id={$row_proofreader['userID']}\" target=\"_blank\">{$row_proofreader['forename']} {$row_proofreader['surname']}</a> <span class=\"grey\">{$proofreaderInfo['deadline']}</span></div>";
					}
				?>
			</td>
			<td><?php echo $info['deadline']; ?></td>
			<td title="<?php echo nl2br($info['notes']); ?>"><?php echo DisplayString(nl2br($info['notes'])); ?></td>
		</tr>
		<?php
				}
			} else {
				echo '<tr class="bgWhite" onmouseover="this.className=\'hover\'" onmouseout="this.className=\'bgWhite\'"><td colspan="6" align="center"><i>'.$lang->display('No Task').'</i></td></tr>';
			}
		?>
	</table>
</div>
