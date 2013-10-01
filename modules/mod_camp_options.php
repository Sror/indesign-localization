<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("campaigns","cache");
require_once(MODULES.'mod_authorise.php');

$campaign_id = !empty($_GET['campaign_id']) ? (int)$_GET['campaign_id'] : 0;
?>
<div class="mainwrap">
	<div class="fieldset">
		<fieldset>
			<legend><?php echo $lang->display('Cache'); ?></legend>
			<form
				action="index.php?layout=campaign&id=<?php echo $campaign_id; ?>"
				name="camp_cache_form"
				method="POST"
				enctype="multipart/form-data"
				onsubmit="if(confirm('<?php echo $lang->display('Are you sure you want to empty the cache?'); ?>')) { hidediv('helper');Popup('loadingme','waiting'); }"
			>
				<table width="100%" border="0" cellspacing="0" cellpadding="5">
					<tr>
						<th width="50%"><?php echo $lang->display('Options'); ?></th>
						<td width="50%">
							<input
								type="checkbox"
								class="input"
								onfocus="this.className='inputOn'"
								onblur="this.className='input'"
								name="artwork_cache"
								id="artwork_cache"
								value="1"
								checked="checked"
							/>
							<?php echo $lang->display('Artworks'); ?>
							<span class="span"></span>
							<input
								type="checkbox"
								class="input"
								onfocus="this.className='inputOn'"
								onblur="this.className='input'"
								name="task_cache"
								id="task_cache"
								value="1"
								checked="checked"
							/>
							<?php echo $lang->display('Tasks'); ?>
						</td>
					</tr>
					<tr>
						<th><?php echo $lang->display('Reset'); ?></th>
						<td>
							<input
								type="submit"
								class="btnDo"
								onmousemove="this.className='btnOn'"
								onmouseout="this.className='btnDo'"
								title="<?php echo $lang->display('Empty Cache'); ?>"
								value="<?php echo $lang->display('Empty Cache'); ?>"
							/>
						</td>
					</tr>
				</table>
				<input name="update" type="hidden" value="camp_cache_form">
			</form>
		</fieldset>
	</div>
</div>