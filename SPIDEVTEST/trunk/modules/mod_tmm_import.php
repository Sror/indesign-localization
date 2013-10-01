<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("system","cpanel");
require_once(MODULES.'mod_authorise.php');
?>

<form onsubmit="hidediv('helper');Popup('loadingme','waiting');" enctype="multipart/form-data" method="POST" name="tmm_export" action=""><!--index.php?layout=cp_tm -->
<input type="hidden" name="action" value="import" />
	<table width="100%" border="0" cellspacing="0" cellpadding="5">
		<tr>
			<td><?php echo $lang->display('Please Select Company'); ?></td>
			<td>
				<select
					class="input"
					onfocus="this.className='inputOn'"
					onblur="this.className='input'"					name="filter_company"					id="filter_company"					title="<?php echo $lang->display('Please Select Company'); ?>"				>				<?php BuildCompanyList($company_id,$issuperadmin); ?>				</select>
			</td>
		</tr>
		<tr>
			<td><?php echo $lang->display('Source Language'); ?></td>
			<td>
				<select
					class="input"
					onfocus="this.className='inputOn'"
					onblur="this.className='input'"
					name="inlang_id"
					id="inlang_id"
				>
					<?php BuildLangList($_SESSION['userDefaultLangID']); ?>
				</select>
			</td>
		</tr>
		<tr>
			<td><?php echo $lang->display('Target Language'); ?></td>
			<td>
				<select
					class="input"
					onfocus="this.className='inputOn'"
					onblur="this.className='input'"
					name="outlang_id"
					id="outlang_id"
				>
					<?php BuildLangList(); ?>
				</select>
			</td>
		</tr>
		
		<tr>
			<td><?php echo $lang->display('Type'); ?></td>
			<td>
				<select
					class="input"
					name="filter_tm_type"
					id="filter_tm_type"
					title="<?php echo $lang->display('Type'); ?>"
				>
				<?php BuildTMTypeList(4); ?>
				</select>
			</td>
		</tr>
	
		<tr>
			<td><?php echo $lang->display('Only update existing'); ?></td>
			<td>
				<input
					class="input"
					onfocus="this.className='inputOn'"
					onblur="this.className='input'"
					name="update_only"
					id="update_only"
					type="checkbox"
				/>
			</td>
		</tr>
		
		<tr>
			<td><?php echo $lang->display('File'); ?></td>
			<td>
				<input
					class="input"
					onfocus="this.className='inputOn'"
					onblur="this.className='input'"
					name="upload_file"
					id="upload_file"
					type="file"
				/>
			</td>
		</tr>
		<tr>
			<td class="highlight"></td>
			<td>
				<input
					type="submit"
					class="btnDo"
					onmousemove="this.className='btnOn'"
					onmouseout="this.className='btnDo'"
					title="<?php echo $lang->display('Import'); ?>"
					value="<?php echo $lang->display('Import'); ?>"
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
</form>