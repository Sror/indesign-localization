<?php
$access = array("system","login");
require_once(MODULES.'mod_authorise.php');

$id = (isset($_GET['id'])) ? $_GET['id'] : 0;

$query = sprintf("SELECT messages.*,
				U1.username as s_username, U1.forename AS s_forename, U1.surname AS s_surname,
				U2.username as r_username, U2.forename AS r_forename, U2.surname AS r_surname
				FROM messages
				LEFT JOIN users U1 ON messages.senderID = U1.userID
				LEFT JOIN users U2 ON messages.receiverID = U2.userID
				WHERE messages.messageID = %d
				LIMIT 1",
				$id);
$result = mysql_query($query, $conn) or die(mysql_error());
if(!mysql_num_rows($result)) access_denied();
$row = mysql_fetch_assoc($result);
if ($row['receiverID']!=$_SESSION['userID'] && $row['senderID']!=$_SESSION['userID']) access_denied();
if ($row['receiverID'] == $_SESSION['userID']) {
	$update = sprintf("UPDATE messages
					SET readStatus = 1
					WHERE messageID = %d",
					$id);
	$result = mysql_query($update, $conn) or die(mysql_error());
}
$is_trashed = (($row['receiverID']==$_SESSION['userID'] && $row['receiverSideStatus']==0) || ($row['senderID']==$_SESSION['userID'] && $row['senderSideStatus']==0));

if(!empty($_POST['form'])) {
	if($_POST['form']=="trash") {
		$DB->TrashMsg($id);
		header("Location: index.php?layout=inbox");
	}
	
	if($_POST['form']=="restore") {
		$DB->RestoreMsg($id);
		header("Location: index.php?layout=inbox");
	}
	
	if($_POST['form']=="close") {
		header("Location: index.php?layout=inbox");
	}
	exit();
}
?>