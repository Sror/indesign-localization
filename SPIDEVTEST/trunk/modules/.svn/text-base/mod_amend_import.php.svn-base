<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("system","login");
require_once(MODULES.'mod_authorise.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

echo '<form
		action="index.php?layout=amend&id='.$id.'"
		name="imForm"
		method="POST"
		enctype="multipart/form-data"
		onsubmit="hidediv(\'helper\');Popup(\'loadingme\',\'waiting\');">';
echo '<table width="100%" cellspacing="0" cellpadding="3" border="0">';
echo '<tr>';
echo '<td width="30%" class="highlight">* '.$lang->display('Import Document').':</td>';
echo '<td width="70%">';
echo '<select
		class="input"
		onfocus="this.className=\'inputOn\'"
		onblur="this.className=\'input\'"
		name="service_tID"
		id="service_tID">';
$query = sprintf("SELECT service_transaction_process.id, service_transaction_process.notes
				FROM service_package_items
				LEFT JOIN service_transaction_process ON service_transaction_process.id = service_package_items.service_tID
				LEFT JOIN service_engines ON service_engines.id = service_transaction_process.serviceID
				WHERE service_package_items.packageID = %d
				AND service_transaction_process.transactionID = %d
				AND service_transaction_process.type_id = %d
				ORDER BY
				service_engines.name ASC,
				service_transaction_process.notes ASC",
				$_SESSION['packageID'],
				SERVICE_IMPORT,
				TYPE_PREWORK);
$result = mysql_query($query, $conn) or die(mysql_error());
while($row = mysql_fetch_assoc($result)) {
	echo '<option value="'.$row['id'].'">'.$lang->display($row['notes']).'</option>';
}
echo '</select> ';
echo '</td>';
echo '</tr>';
echo '<tr>';
echo '<td class="highlight">* '.$lang->display('Select File').':</td>';
echo '<td>';
echo '<input
		type="file"
		class="input"
		onfocus="this.className=\'inputOn\'"
		onblur="this.className=\'input\'"
		name="ImportFile"
		id="ImportFile" />';
echo '</td>';
echo '</tr>';
echo '<tr>';
echo '<td class="highlight"></td>';
echo '<td>';
echo '<div>';
echo '<input
		type="checkbox"
		name="option"
		id="option"
		value="1"
		checked="checked">'.$lang->display('Accept Same-as-Source Translation');
echo '</div>';
echo '<div>';
echo '<input
		type="checkbox"
		name="loose"
		id="loose"
		value="1">'.$lang->display('Loose Match');
echo '</div>';
echo '</td>';
echo '</tr>';
echo '<tr>';
echo '<td class="highlight"></td>';
echo '<td>';
echo '<input
		type="submit"
		class="btnDo"
		onmousemove="this.className=\'btnOn\'"
		onmouseout="this.className=\'btnDo\'"
		value="'.$lang->display('Import').'"
		title="'.$lang->display('Import').'" /> ';
echo '<input
		type="reset"
		class="btnOff"
		onmousemove="this.className=\'btnOn\'"
		onmouseout="this.className=\'btnOff\'"
		value="'.$lang->display('Reset').'"
		title="'.$lang->display('Reset').'" />';
echo '<input type="hidden" name="update" value="imForm" />';
echo '</td>';
echo '</tr>';
echo '</table>';
echo '</form>';
$query = sprintf("SELECT *
				FROM task_imports
				WHERE artwork_id = %d
				AND task_id = 0
				ORDER BY time_start DESC",
				$id);
$result = mysql_query($query, $conn) or die(mysql_error());
if(mysql_num_rows($result)) {
	echo '<div class="mainwrap">';
	echo '<div class="list" style="max-height:500px;overflow:auto;">';
	echo '<table width="100%" cellspacing="0" cellpadding="5" border="0">';
	echo '<tr>';
	echo '<th colspan="6">'.$lang->display('Import Report').'</th>';
	echo '</tr>';
	echo '<tr>';
	echo '<th align="center" width="5%">#</td>';
	echo '<th width="35%">'.$lang->display('Timestamp').'</td>';
	echo '<th width="15%">'.$lang->display('File Type').'</td>';
	echo '<th width="20%">'.$lang->display('Accept Same-as-Source Translation').'</td>';
	echo '<th width="20%">'.$lang->display('Loose Match').'</td>';
	echo '<th align="center" width="5%">ID</td>';
	echo '</tr>';
	$counter = 1;
	while($row = mysql_fetch_assoc($result)) {
		$class= $counter%2==0 ? 'even' : 'odd' ;
		echo '<tr
				class="'.$class.'"
				onmouseover="this.className=\'hover\'"
				onmouseout="this.className=\''.$class.'\'"
				onclick="hidediv(\'helper\');goToURL(\'parent\',\'index.php?layout=task_import&id='.$row['id'].'\');"
				style="cursor:pointer;">';
		echo '<td align="center">'.$counter.'</td>';
		echo '<td>'.date(FORMAT_TIME,strtotime($row['time_start'])).'</td>';
		echo '<td>'.$row['file_type'].'</td>';
		echo '<td>';
		if($row['option']==1) {
			echo '<img src="'.IMG_PATH.'ico_enable.png" />';
		} else {
			echo '<img src="'.IMG_PATH.'ico_disable.png" />';
		}
		echo '</td>';
		echo '<td>';
		if($row['loose']==1) {
			echo '<img src="'.IMG_PATH.'ico_enable.png" />';
		} else {
			echo '<img src="'.IMG_PATH.'ico_disable.png" />';
		}
		echo '</td>';
		echo '<td align="center">'.$row['id'].'</td>';
		$counter++;
	}
}
?>