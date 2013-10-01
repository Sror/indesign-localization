<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("system","login");
require_once(MODULES.'mod_authorise.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

echo '<table cellspacing="0" cellpadding="3" border="0">';
echo '<tr>';
echo '<td>';
echo '<h1>'.$lang->display('Terms & Conditions').'</h1>';
echo '<p>'.$lang->display('Task T&C Content').'</p>';
echo '<h1>SDL Trados</h1>';
echo '<p>'.$lang->display('Trados Instructions').'</p>';
echo '</td>';
echo '</tr>';
echo '<tr>';
echo '<td align="center">';
echo '<input
		type="submit"
		class="btnDo"
		onmousemove="this.className=\'btnOn\'"
		onmouseout="this.className=\'btnDo\'"
		value="'.$lang->display('Accept').'"
		title="'.$lang->display('Accept').'"
		onclick="ResetDiv(\'window\');DoAjax(\'id='.$id.'\',\'window\',\'modules/mod_task_export.php\');">';
echo ' <input
		type="button"
		class="btnOff"
		onmousemove="this.className=\'btnOn\'"
		onmouseout="this.className=\'btnOff\'"
		value="'.$lang->display('Decline').'"
		title="'.$lang->display('Decline').'"
		onclick="ResetDiv(\'window\');hidediv(\'helper\');">';
echo '</form>';
echo '</td>';
echo '</tr>';
echo '</table>';
?>