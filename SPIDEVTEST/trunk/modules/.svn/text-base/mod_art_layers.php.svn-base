<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("system","login");
require_once(MODULES.'mod_authorise.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
echo '<div class="mainwrap">';
echo '<form
		id="layerform"
		name="layerform"
		action="index.php?layout=artwork&id='.$id.'"
		method="POST"
		enctype="multipart/form-data">';
echo '<div class="list">';
echo '<table width="100%" cellspacing="0" cellpadding="5" border="0">';
echo '<tr>';
echo '<th align="left" width="5%"><input type="checkbox" class="checkbox" name="checkall" id="checkall" onclick="GroupCheckbox(this,\'id\');" /></th>';
echo '<th align="left" width="10%">ID</th>';
echo '<th align="left" width="40%">'.$lang->display('Name').'</th>';
echo '<th align="left" width="15%">'.$lang->display('Colour').'</th>';
echo '<th align="center" width="15%">'.$lang->display('Visible').'<br /><input type="checkbox" class="checkbox" name="allvisible" id="allvisible" onclick="GroupCheckbox(this,\'visible\');ForceGroupCheckbox(this,\'id\');" /></th>';
echo '<th align="center" width="15%">'.$lang->display('Locked').'<br /><input type="checkbox" class="checkbox" name="alllocked" id="alllocked" onclick="GroupCheckbox(this,\'locked\');ForceGroupCheckbox(this,\'id\');" /></th>';
echo '</tr>';
$count = 0;
$query = sprintf("SELECT *
				FROM artwork_layers
				WHERE artwork_id = %d
				ORDER BY ref ASC",
				$id);
$result = mysql_query($query, $conn) or die(mysql_error());
$counter = 1;
while($row = mysql_fetch_assoc($result)) {
	echo '<tr class="';
	if($counter%2==0) echo 'even'; else echo 'odd';
	echo '" onmouseover="this.className=\'hover\'" onmouseout="this.className=\'';
	if($counter%2==0) echo 'even'; else echo 'odd';
	echo '\'">';
	echo '<td><input type="checkbox" class="checkbox" name="id['.$row['id'].']" id="id['.$row['id'].']" value="'.$row['id'].'"></td>';
	echo '<td>'.$row['ref'].'</td>';
	echo '<td>'.$row['name'].'</td>';
	echo '<td>';
	echo '<input 
			class="color"
			id="colour['.$row['id'].']"
			name="colour['.$row['id'].']"
			value="'.$row['colour'].'"
			size="6"
			maxlength="6"
			onfocus="CheckTheBox(\'id['.$row['id'].']\');" />';
	echo '</td>';
	echo '<td align="center">';
	echo '<input 
			type="checkbox"
			class="checkbox"
			id="visible['.$row['id'].']"
			name="visible['.$row['id'].']"
			value="1"
			onfocus="CheckTheBox(\'id['.$row['id'].']\');"';
	if($row['visible']) echo 'checked="checked"';
	echo ' />';
	echo '</td>';
	echo '<td align="center">';
	echo '<input 
			type="checkbox"
			class="checkbox"
			id="locked['.$row['id'].']"
			name="locked['.$row['id'].']"
			value="1"
			onfocus="CheckTheBox(\'id['.$row['id'].']\');"';
	if($row['locked']) echo 'checked="checked"';
	echo ' />';
	echo '</td>';
	echo '</tr>';
	$counter++;
}
echo '<tr>';
echo '<td colspan="5" align="center">';
echo '<input 
		type="button"
		class="btnDo"
		onmousemove="this.className=\'btnOn\'"
		onmouseout="this.className=\'btnDo\'"
		title="'.$lang->display('Save').'"
		value="'.$lang->display('Save').'"
		onclick="if(CheckSelected(\'layerform\',\'id\')) SubmitForm(\'layerform\',\'layers\');"/> ';
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
echo '<input name="form" type="hidden">';
echo '</form>';
echo '</div>';
?>