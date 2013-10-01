<?php
/*	The aim of this file is to provide a common way to allow the uploading of
	files via a magical ajax boxes
	
	TODO:
	* Allow multiple uploads
	* Report upload progress
*/
$upload = array();
require_once(dirname(__FILE__).'./../config.php');
?>
<form
	name="upload"
	id="upload"
	action="index.php?layout=<?php echo $_GET['to']; ?>"
	method="POST"
	enctype="multipart/form-data"
	onsubmit="Popup('loadingme','waiting');"
>
<table width="100%" border="0" cellspacing="0" cellpadding="3">
	<tr><!-- Name -->
		<td width="30%" class="highlight"><?php echo $lang->display('Name'); ?>:</td>
		<td width="70%">
			<input
				type="text"
				class="input"
				onfocus="this.className='inputOn'"
				onblur="this.className='input'"
				name="name"
			/>
		</td>
	</tr>
	<tr><!-- File -->
		<td class="highlight">* <?php echo $lang->display('Select File'); ?>:</td>
		<td>
			<input
				type="file"
				class="input"
				onfocus="this.className='inputOn'"
				onblur="this.className='input'"
				name="file"
			/>
		</td>
	</tr>
	<tr><!-- Replacement, only used the word as it's closest in the translations -->
		<td class="highlight"><?php echo $lang->display('Replacement'); ?>:</td>
		<td>
			<input
				class="input"
				type="checkbox"
				onfocus="this.className='inputOn'"
				onblur="this.className='input'"
				name="replace"
			/>
		</td>
	</tr>
	<tr>
		<td></td>
		<td>
			<input
				type="submit"
				class="btnDo"
				onmousemove="this.className='btnOn'"
				onmouseout="this.className='btnDo'"
				title="<?php echo $lang->display('Upload Artworks'); ?>"
				value="<?php echo $lang->display('Upload Artworks'); ?>"
				onclick="SubmitForm('upload','upload')"
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
<input type="hidden" id="form" name="form" />
</form>