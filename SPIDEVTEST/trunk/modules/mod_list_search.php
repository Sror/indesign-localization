<div class="search">
	<?php echo $lang->display('Search'); ?>:
	<input
		type="text"
		class="input"
		onfocus="this.className='inputOn'"
		onblur="this.className='input'"
		id="keyword"
		name="keyword"
		value="<?php echo $keyword; ?>"
		title="Keyword"
	>
	<input
		type="submit"
		class="btnDo"
		onmousemove="this.className='btnOn'"
		onmouseout="this.className='btnDo'"
		value="<?php echo $lang->display('Go'); ?>"
		title="<?php echo $lang->display('Go'); ?>"
	>
	<input
		type="submit"
		class="btnOff"
		onmousemove="this.className='btnOn'"
		onmouseout="this.className='btnOff'"
		value="<?php echo $lang->display('Reset'); ?>"
		title="<?php echo $lang->display('Reset'); ?>"
		onclick="document.forms['listform'].keyword.value=''"
	>
</div>