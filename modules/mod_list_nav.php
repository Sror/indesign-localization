<div class="nav">
	<div class="left">
		<?php echo $lang->display('Found').' <b>'.$total.'</b><span class="span">|</span>'.$lang->display('Display'); ?>
		<select
			class="input"
			onfocus="this.className='inputOn'"
			onblur="this.className='input'"
			id="limit"
			name="limit"
			onchange="SubmitForm('listform','');"
		>
		<?php
			$display = array(5,10,15,20,50);
			foreach($display as $d) {
				echo '<option value="'.$d.'"';
				if($d == $limit) echo ' selected="selected"';
				echo '>'.$d.'</option>';
			}
			echo '<option value="'.$total.'"';
			if($total == $limit) echo ' selected="selected"';
			echo '>'.$lang->display('All').'</option>';
		?>
		</select>
		/ <?php echo $lang->display('Page'); ?>
	</div>
	<div class="right">
		<?php echo $lang->display('Page').' <b>'.$page.'</b> / '.$pages.'<span class="span">|</span>'.$lang->display('Go to Page'); ?>
		<select
			class="input"
			onfocus="this.className='inputOn'"
			onblur="this.className='input'"
			id="page"
			name="page"
			title="<?php echo $lang->display('Go to Page'); ?>"
			onchange="SubmitForm('listform','');"
		>
		<?php
			for($i=1; $i<=$pages; $i++) {
				echo '<option value="'.$i.'"';
				if($i == $page) echo ' selected="selected"';
				echo '>'.$i.'</option>';
			}
		?>
		</select>
	</div>
	<div class="mid">
		<?php
			if($page>1) {
				$prev = $page - 1;
				echo '<a href="javascript:void(0);" onclick="document.forms[\'listform\'].page.value='.$prev.';SubmitForm(\'listform\',\'\');"><img src="'.IMG_PATH.'toolbar/ico_prev_on.png" title="'.$lang->display('Previous Page').'" /></a> ';
			} else {
				echo '<img src="'.IMG_PATH.'toolbar/ico_prev_off.png" />';
			}
			if($page<$pages) {
				$next = $page + 1;
				echo ' <a href="javascript:void(0);" onclick="document.forms[\'listform\'].page.value='.$next.';SubmitForm(\'listform\',\'\');"><img src="'.IMG_PATH.'toolbar/ico_next_on.png" title="'.$lang->display('Next Page').'" /></a>';
			} else {
				echo '<img src="'.IMG_PATH.'toolbar/ico_next_off.png" />';
			}
		?>
	</div>
	<div class="clear"></div>
</div>