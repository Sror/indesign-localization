<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("tasks","new");
require_once(MODULES.'mod_authorise.php');

$source_lang_id = (!empty($_GET['source_lang_id'])) ? $_GET['source_lang_id'] : 0;
$target_lang_id = (!empty($_GET['target_lang_id'])) ? $_GET['target_lang_id'] : 0;
$subject_id = (!empty($_GET['subject_id'])) ? $_GET['subject_id'] : 0;
$word_count = (!empty($_GET['word_count'])) ? $_GET['word_count'] : 0;
?>
<div class="highlight">* <?php echo $lang->display('Assign Translators'); ?>:</div>
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
				<?php unset($_SESSION['translators']); ?>
				<tr>
					<td colspan="6" align="center">
						<input
							type="button"
							class="btnDo"
							onmousemove="this.className='btnOn'"
							onmouseout="this.className='btnDo'"
							title="<?php echo $lang->display('Assign Translators'); ?>"
							value="<?php echo $lang->display('Assign Translators'); ?>"
							onclick="ResetDiv('TranslatorList');DoAjax('source_lang_id=<?php echo $source_lang_id; ?>&target_lang_id=<?php echo $target_lang_id; ?>&subject_id=<?php echo $subject_id; ?>&word_count=<?php echo $word_count; ?>&list=TranslatorList','TranslatorList','modules/mod_new_get_user_list.php');"
						/>
					</td>
				</tr>
			</table>
		</div>
	</div>
</div>
<div class="highlight">* <?php echo $lang->display('Assign Proofreaders'); ?>:</div>
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
				<?php unset($_SESSION['proofreaders']); ?>
				<tr>
					<td colspan="8" align="center">
						<input
							type="button"
							class="btnDo"
							onmousemove="this.className='btnOn'"
							onmouseout="this.className='btnDo'"
							title="<?php echo $lang->display('Assign Proofreaders'); ?>"
							value="<?php echo $lang->display('Assign Proofreaders'); ?>"
							onclick="ResetDiv('ProofreaderList');DoAjax('source_lang_id=<?php echo $source_lang_id; ?>&target_lang_id=<?php echo $target_lang_id; ?>&subject_id=<?php echo $subject_id; ?>&word_count=<?php echo $word_count; ?>&list=ProofreaderList','ProofreaderList','modules/mod_new_get_user_list.php');"
						/>
					</td>
				</tr>
			</table>
		</div>
	</div>
</div>