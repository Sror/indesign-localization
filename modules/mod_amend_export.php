<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("system","login");
require_once(MODULES.'mod_authorise.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
echo '<form
		action="index.php?layout=amend&id='.$id.'"
		name="exForm"
		method="POST"
		enctype="multipart/form-data"
		onsubmit="display(\'reminder\');">';
echo '<table width="100%" cellspacing="0" cellpadding="3" border="0">';
echo '<tr>';
echo '<td width="30%" class="highlight">* '.$lang->display('Export Document').'</td>';
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
				SERVICE_EXPORT,
				TYPE_PREWORK);
$result = mysql_query($query, $conn) or die(mysql_error());
while($row = mysql_fetch_assoc($result)) {
	echo '<option value="'.$row['id'].'">'.$lang->display($row['notes']).'</option>';
}
echo '</select> ';
echo '</td>';
echo '</tr>';
echo '<tr>';
echo '<td colspan="2">';
echo '<div class="arrrgt" id="advanced" onclick="ChangeArrow(\'advanced\');showandhide(\'advancedoptions\');">'.$lang->display('Advanced Options').'</div>';
echo '<div id="advancedoptions" class="greyBar" style="display:none;">';
echo '<table width="100%" border="0" cellspacing="0" cellpadding="5">';
echo '<tr>';
echo '<td class="highlight" width="30%" valign="top">'.$lang->display('Email').'</td>';
echo '<td width="70%">';
echo '<textarea
		class="input"
		onfocus="this.className=\'inputOn\'"
		onblur="this.className=\'input\'"
		name="emails"
		id="emails"
		rows="3"
		cols="75"></textarea>';
echo '</td>';
echo '</tr>';
echo '<tr>';
echo '<td class="highlight" valign="top">'.$lang->display('Job Brief').'</td>';
echo '<td>';
echo '<textarea
		class="input"
		onfocus="this.className=\'inputOn\'"
		onblur="this.className=\'input\'"
		name="brief"
		id="brief"
		rows="3"
		cols="75">Please proofred the attached file.</textarea>';
echo '</td>';
echo '</tr>';
echo '</table>';
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
		value="'.$lang->display('Export').'"
		title="'.$lang->display('Export').'" />';
echo '<input type="hidden" name="update" value="exForm" />';
echo '<div id="reminder" style="display:none;">'.$lang->display('Processing your request. Please wait').'...</div>';
echo '</td>';
echo '</tr>';
echo '</table>';
echo '</form>';
?>