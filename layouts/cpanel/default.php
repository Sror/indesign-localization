<?php
$navStatus = array("cpanel");
require_once(MODULES.'mod_header.php');
BuildPageIntro($lang->display('Control Panel'),$lang->display('CP Intro'));
?>
<div id="wrapperWhite">
	<div>
		<div class="controlScroll">
			<div class="controlselectScroll">
				<div class="shortcuts">
					<?php
						foreach($cp_options as $option) {
							if($issuperadmin==$option['super'] || !$option['super']) {
								echo '<div class="shortcutOff" onmouseover="this.className=\'shortcutOn\'" onmouseout="this.className=\'shortcutOff\'" title="'.$lang->display($option['display']).'">';
								echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout='.$option['layout'].'\');">';
								echo '<div class="image"><img src="'.IMG_PATH.'header/'.$option['icon'].'" /></div>';
								echo '<div class="label">'.$lang->display($option['display']).'</div>';
								echo '</a>';
								echo '</div>';
							}
						}
					?>
					<div class="clear"></div>
				</div>
			</div>
		</div>
	</div>
	<div class="clear"></div>
</div>