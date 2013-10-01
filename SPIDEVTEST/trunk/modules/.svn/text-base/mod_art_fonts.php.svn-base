<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("system","login");
require_once(MODULES.'mod_authorise.php');
require_once(CLASSES.'Font_Substitution.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
echo '<div class="mainwrap">';
echo '<form
		id="fontform"
		name="fontform"
		action="index.php?layout=artwork&id='.$id.'"
		method="POST"
		enctype="multipart/form-data"
		onsubmit="hidediv(\'helper\');Popup(\'loadingme\',\'waiting\');">';
echo '<div class="list">';
echo '<table width="100%" cellspacing="0" cellpadding="5" border="0">';
echo '<tr>';
echo '<th align="center" width="10%">'.$lang->display('Status').'</th>';
echo '<th align="left" width="30%">'.$lang->display('Original').'</th>';
echo '<th align="left" width="55%">'.$lang->display('Substitute').'</th>';
echo '<th align="center" width="5%">ID</th>';
echo '</tr>';
$count = 0;
$counter = 1;
$query = sprintf("SELECT artwork_fonts.font_id,
				fonts.family, fonts.name, fonts.installed
				FROM artwork_fonts
				LEFT JOIN fonts ON artwork_fonts.font_id = fonts.id
				WHERE artwork_fonts.artwork_id = %d
				ORDER BY fonts.name ASC",
				$id);
$result = mysql_query($query, $conn) or die(mysql_error());
$n=0;
while($row = mysql_fetch_assoc($result)) {
	$n++;
	$style = $counter%2==0 ? 'even' : 'odd';
	echo '<tr class="'.$style.'" onmouseover="this.className=\'hover\'" onmouseout="this.className=\''.$style.'\'">';
	echo '<td align="center">';
	if($row['installed']) {
		echo '<img src="'.IMG_PATH.'ico_s_tick.png" title="'.$lang->display('Installed').'" /> ';
	} else {
		$count++;
		echo '<img src="'.IMG_PATH.'ico_error.png" title="'.$lang->display('Missing').'" /> ';
	}
	echo '</td>';
	echo '<td>';
	echo '('.$row['family'].') '.$row['name'];
	echo '</td>';
	echo '<td>';
	$sub_font_id = Font_Substitution::get_effectual_font($row['font_id'],$id,'artwork',FALSE);
	$substitute = $DB->get_font_info($sub_font_id);
	printf('<input type="hidden" name="font[%d]" value="%d"/> ',$n,$row['font_id']);
	printf('<select
			class="input"
			onfocus="this.className=\'inputOn\'"
			onblur="this.className=\'input\'"
			name="substitute[%d]">',$n);
	BuildFontSubList($sub_font_id);
	if($row['font_id'] == $sub_font_id) {
		echo '*';
	} else {
		if($sub_font_id == 0){
			echo '*';
			$sub_font_id = Font_Substitution::get_effectual_font($row['font_id'],$id,'artwork');
			$count++;
		}
		$substitute = $DB->get_font_info($sub_font_id);
		echo '('.$substitute['family'].') '.$substitute['name'] ;
	}
	echo '</select>';
	echo '</td>';
	echo '<td align="center">'.$row['id'].'</td>';
	echo '</tr>';
}
echo '<tr>';
echo '<td colspan="3" align="center">';
echo '<input 
		type="submit"
		class="btnDo"
		onmousemove="this.className=\'btnOn\'"
		onmouseout="this.className=\'btnDo\'"
		title="'.$lang->display('Save').'"
		value="'.$lang->display('Save').'"/> ';
echo '<input 
		type="reset"
		class="btnOff"
		onmousemove="this.className=\'btnOn\'"
		onmouseout="this.className=\'btnOff\'"
		title="'.$lang->display('Reset').'"
		value="'.$lang->display('Reset').'"/>';
echo '</td>';
echo '</tr>';
echo '</table>';
echo '</div>';
echo '<input name="update" type="hidden" value="fontform">';
echo '</form>';
echo '</div>';
?>