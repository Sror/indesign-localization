<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("system","login");
require_once(MODULES.'mod_authorise.php');

$artwork_id = isset($_GET['artwork_id']) ? (int)$_GET['artwork_id'] : 0;
$task_id = isset($_GET['task_id']) ? (int)$_GET['task_id'] : 0;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
echo '<div class="mainwrap">';
echo '<form
		id="fontform"
		name="fontform"
		action="index.php?layout=customise&id='.$task_id.'&page='.$page.'"
		method="POST"
		enctype="multipart/form-data"
		onsubmit="hidediv(\'helper\');Popup(\'loadingme\',\'waiting\');">';
echo '<div class="list">';
echo '<table width="100%" cellspacing="0" cellpadding="5" border="0">';
echo '<tr>';
echo '<th align="center">'.$lang->display('Status').'</th>';
echo '<th align="left">'.$lang->display('Original').'</th>';
echo '<th align="left">'.$lang->display('Substitute').'</th>';
echo '</tr>';
$count = 0;
$query = sprintf("SELECT artwork_fonts.font_id, artwork_fonts.sub_font_id,
				fonts.family, fonts.name, fonts.installed
				FROM artwork_fonts
				LEFT JOIN fonts ON artwork_fonts.font_id = fonts.id
				WHERE artwork_fonts.artwork_id = %d
				ORDER BY fonts.name ASC",
				$artwork_id);
$result = mysql_query($query, $conn) or die(mysql_error());
while($row = mysql_fetch_assoc($result)) {
	echo '<tr>';
	echo '<td align="center">';
	if($row['installed']) {
		echo '<img src="'.IMG_PATH.'ico_s_tick.png" title="'.$lang->display('Installed').'" /> ';
	} else {
		$count++;
		echo '<img src="'.IMG_PATH.'ico_error.png" title="'.$lang->display('Missing').'" /> ';
	}
	echo '</td>';
	echo '<td>';
	echo !empty($row['name']) ? $row['name'] : '('.$row['family'].')';
	echo '</td>';
	echo '<td>';
	echo '<select
			class="input"
			onfocus="this.className=\'inputOn\'"
			onblur="this.className=\'input\'"
			name="substitute['.$row['font_id'].']"
			id="substitute['.$row['font_id'].']">';
	$query_sub = sprintf("SELECT sub_font_id
					FROM task_font_subs
					WHERE task_id = %d
					AND font_id = %d
					LIMIT 1",
					$task_id,
					$row['font_id']);
	$result_sub = mysql_query($query_sub, $conn) or die(mysql_error());
	if(!mysql_num_rows($result_sub)) $sub_font_id = 0;
	$row_sub = mysql_fetch_assoc($result_sub);
	$sub_font_id = $row_sub['sub_font_id'];
	BuildFontSubList($sub_font_id);
	echo '</select>';
	echo '</td>';
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