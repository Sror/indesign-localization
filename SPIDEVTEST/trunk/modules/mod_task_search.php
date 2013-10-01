<div class="search">
	<?php echo $lang->display('Search'); ?>:
	<select
		class="input"
		onfocus="this.className='inputOn'"
		onblur="this.className='input'"
		name="task_search_type"
		id="task_search_type"
		title="<?php echo $lang->display('Please Select'); ?>"
	>
		<option value="<?php echo TYPE_ORIGINAL; ?>" <?php if($task_search_type==TYPE_ORIGINAL) echo 'selected="selected"'?>><?php echo $lang->display('Source Artwork'); ?></option>
		<option value="<?php echo TYPE_TRANSLATION; ?>" <?php if($task_search_type==TYPE_TRANSLATION) echo 'selected="selected"'?>><?php echo $lang->display('Translated Artwork'); ?></option>
	</select>
	<input
		type="text"
		class="input"
		onfocus="this.className='inputOn'"
		onblur="this.className='input'"
		id="task_search_keyword"
		name="task_search_keyword"
		value="<?php echo $task_search_keyword; ?>"
		title="Keyword"
	/>
	<input
		type="submit"
		class="btnDo"
		onmousemove="this.className='btnOn'"
		onmouseout="this.className='btnDo'"
		value="<?php echo $lang->display('Go'); ?>"
		title="<?php echo $lang->display('Go'); ?>"
		onclick="validateForm('task_search_keyword','Keyword','R'); if(document.returnValue) {Popup('helper','blur');DoAjax('id=<?php echo $taskID; ?>&type='+document.forms['mainform'].task_search_type.value+'&keyword='+document.forms['mainform'].task_search_keyword.value+'&pl=<?php echo $pl; ?>','window','modules/mod_task_search_result.php');}"
	/>
	<input
		type="button"
		class="btnOff"
		onmousemove="this.className='btnOn'"
		onmouseout="this.className='btnOff'"
		value="<?php echo $lang->display('Reset'); ?>"
		title="<?php echo $lang->display('Reset'); ?>"
		onclick="SubmitForm('mainform','reset');"
	/>
</div>