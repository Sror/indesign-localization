<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("artworks","edit");
require_once(MODULES.'mod_authorise.php');

$artworkID = isset($_GET['id']) ? $_GET['id'] : 0;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
?>
<form
	id="importform"
	name="importform"
	method="POST"
	enctype="multipart/form-data"
	action="index.php?layout=arttpl&id=<?php echo $artworkID; ?>&page=<?php echo $page; ?>"
>
	<table width="100%" cellspacing="0" cellpadding="3" border="0">
		<tr>
			<td class="highlight" width="30%">* <?php echo $lang->display('Reference'); ?></td>
			<td width="70%">
				<input
					type="text"
					class="input"
					onfocus="this.className='inputOn'"
					onblur="this.className='input'"
					id="name"
					name="name"
				/>
			</td>
		</tr>
		<tr>
			<td class="highlight">* <?php echo $lang->display('Select File'); ?></td>
			<td>
				<input
					type="file"
					class="input"
					onfocus="this.className='inputOn'"
					onblur="this.className='input'"
					id="csvfile"
					name="csvfile"
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
					onclick="validateForm('name','Reference','R');return document.returnValue;"
					value="<?php echo $lang->display('Import'); ?>"
					title="<?php echo $lang->display('Import'); ?>"
				/>
				<input type="hidden" name="form" value="import" />
			</td>
		</tr>
	</table>
</form>