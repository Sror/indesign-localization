<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("tasks","new");
require_once(MODULES.'mod_authorise.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
?>
<table width="100%" cellspacing="0" cellpadding="3" border="0">
	<tr>
		<td width="50%">
			<div class="arrdwn"><?php echo $lang->display('New Task Step 1 Title'); ?></div>
		</td>
		<td width="50%" align="right">
			<?php BuildStepIndicator(1); ?>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<div class="greyBar">
				<?php $checked = !empty($_SESSION['task_type']) ? $_SESSION['task_type'] : "assign"; ?>
				<p><input type="radio" name="type" value="assign"<?php if($checked=="assign") echo ' checked="checked"'; ?> /> <?php echo $lang->display('Assign Translators/Proofreaders'); ?></p>
				<p><input type="radio" name="type" value="delegate"<?php if($checked=="delegate") echo ' checked="checked"'; ?> /> <?php echo $lang->display('Delegate to Agencies'); ?></p>
			</div>
		</td>
	</tr>
	<tr>
		<td></td>
		<td align="right">
			<input
				type="button"
				class="btnDo"
				onmousemove="this.className='btnOn'"
				onmouseout="this.className='btnDo'"
				title="<?php echo $lang->display('Next'); ?>"
				value="<?php echo $lang->display('Next'); ?>"
				onclick="DoAjax('id=<?php echo $id; ?>&type='+jQueryGetRadioValue(),'window','modules/mod_task_new_step_2.php');"
			/>
		</td>
	</tr>
</table>