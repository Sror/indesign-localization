<?php
require_once(dirname(__FILE__).'/../config.php');
//disabled for guests access
#$access = array("artworks","edit");
#require_once(MODULES.'mod_authorise.php');

$PL = isset($_GET['PL']) ? $_GET['PL'] : 0;

echo '<div class="autolist" id="autolist_'.$PL.'">';
echo '<div class="label">';
echo '<a href="javascript:void(0);" onclick="hidediv(\'autolist_'.$PL.'\');"><img src="'.IMG_PATH.'btn_close.png" title="'.$lang->display('Close').'"></a>';
echo '</div>';

$Translator = new Translator();
$ParaAmends = $Translator->GetParaAmends($PL);
if($ParaAmends === false) {
	echo '<div class="suggest"><i>'.$lang->display('N/A').'</i></div>';
} else {
	while($row_amends = mysql_fetch_assoc($ParaAmends)) {
		$ParaAmend = $row_amends['ParaText'];
		$ParaAmendTime = $row_amends['time'];
		$username = !empty($row_amends['username']) ? $row_amends['username'] : $lang->display('Unknown');
		echo '<div
				class="suggest"
				onmouseover="this.className=\'suggestOn\'"
				onmouseout="this.className=\'suggest\'">';
		echo '<div><img src="'.IMG_PATH.'ico_amend.png" /> <span class="grey">'.date(FORMAT_TIME,$ParaAmendTime).'</span><span class="span"></span>'.$username.'</div>';
		echo '<div><a href="javascript:void(0);" onclick="setValue(\'para['.$PL.']\',\''.htmlspecialchars(mysql_real_escape_string($ParaAmend)).'\');hidediv(\'autolist_'.$PL.'\');">'.html_display_para($ParaAmend).'</a></div>';
		echo '</div>';
	}
}
echo '</div>';
?>