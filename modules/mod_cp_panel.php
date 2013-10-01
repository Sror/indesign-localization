<?php
echo '<div id="rightPanel">';
echo '<div class="headerBar">'.$lang->display('Options').'</div>';
foreach($cp_options as $option) {
	if($issuperadmin==$option['super'] || !$option['super']) {
		echo '<div title="'.$lang->display($option['display']).'" class="controlBtn_';
		echo $layout==$option['layout'] ? 'on' : 'off';
		echo '" onmouseover="this.className=\'controlBtn_on\'"';
		if($layout!=$option['layout']) echo ' onmouseout="this.className=\'controlBtn_off\'">';
		echo '<a href="index.php?layout='.$option['layout'].'">';
		echo '<div class="icon"><img src="'.IMG_PATH.'header/'.$option['icon'].'" /></div>';
		echo '<div class="topic">'.$lang->display($option['display']).'</div>';
		echo '<div class="clear"></div>';
		echo '</a>';
		echo '</div>';
	}
}
echo '</div>';
?>