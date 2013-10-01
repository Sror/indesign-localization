<?php
require_once(dirname(__FILE__).'/../config.php');

if(!isset($agency_id)) $agency_id = (!empty($_GET['agency_id'])) ? (int)$_GET['agency_id'] : 0;
if(!isset($agent_id)) $agent_id = 0;
$query = sprintf("SELECT userID, forename, surname
				FROM users
				WHERE companyID = %d
				AND agent = 1",
				$agency_id);
$result = mysql_query($query, $conn) or die(mysql_error());
echo '<select
		class="input"
		onfocus="this.className=\'inputOn\'"
		onblur="this.className=\'input\'"
		id="agentID"
		name="agentID">';
echo '<option value="">- '.$lang->display('Select Agent').' -</option>';
while($row = mysql_fetch_assoc($result)) {
	echo '<option value="'.$row['userID'].'"';
	if(!empty($agent_id) && $row['userID']==$agent_id) echo ' selected="selected"';
	echo '>'.$row['forename'].' '.$row['surname'].'</option>';
}
echo '</select>';
?>