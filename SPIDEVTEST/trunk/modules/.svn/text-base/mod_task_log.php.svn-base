<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("taskworkflow","tasklog");
require_once(MODULES.'mod_authorise.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$query = sprintf("SELECT * FROM tasklog
							LEFT JOIN users ON tasklog.userID = users.userID
							WHERE taskID = %d
							ORDER BY tasklog.time DESC",
							$id);
$result = mysql_query($query, $conn) or die(mysql_error());
while($row = mysql_fetch_assoc($result)) {
	echo '<div class="bgOption" onmouseover="this.className=\'hover\'" onmouseout="this.className=\'bgOption\'">';
	echo '<div class="right">';
	echo !empty($row['reference']) ? $row['reference'] : '<i>'.$lang->display('N/S').'</i>';
	echo '</div>';
	echo '<div>';
	echo '<span class="grey">[ '.date(FORMAT_TIME,strtotime($row['time'])).' ]</span> <a href="index.php?layout=user&id='.$row['userID'].'">'.$row['forename'].' '.$row['surname'].'</a>';
	echo '</div>';
	echo '<div>';
	echo '<img src="'.IMG_PATH.'arrow_notes.gif" /> '.$lang->display($row['action']);
	echo '</div>';
	echo '<div class="clear"></div>';
	echo '</div>';
}
?>