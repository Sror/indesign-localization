<?php
$access = array("system","login");
require_once(MODULES.'mod_authorise.php');

if(!empty($_POST['form'])) {
	
	if($_POST['form']=="send") {
		$recipients = explode(';',$_POST['recipient']);
		foreach($recipients as $recipient) {
			$query = sprintf("SELECT userID
							FROM users
							WHERE username = '%s'
							LIMIT 1",
							mysql_real_escape_string(trim($recipient)));
			$result = mysql_query($query, $conn) or die(mysql_error());
			if(!mysql_num_rows($result)) continue;
			$row = mysql_fetch_assoc($result);
			$update = sprintf("INSERT INTO messages
							(senderID, receiverID, subject, content, messageTime)
							VALUES (%d, %d, '%s', '%s', NOW())",
							$_SESSION['userID'],
							$row['userID'],
							mysql_real_escape_string($_POST['subject']),
							mysql_real_escape_string($_POST['content']));
			$result = mysql_query($update, $conn) or die(mysql_error());
		}
		header("Location: index.php?layout=$layout");
	}
	
	if($_POST['form']=="view") {
		$id = $_POST['id'][0];
		header("Location: index.php?layout=$layout&task=view&id=$id");
	}

	if($_POST['form']=="trash") {
		foreach($_POST['id'] as $id) {
			$DB->TrashMsg($id);
		}
		header("Location: index.php?layout=$layout");
	}
	
	if($_POST['form']=="restore") {
		foreach($_POST['id'] as $id) {
			$DB->RestoreMsg($id);
		}
		header("Location: index.php?layout=$layout");
	}
	exit();
}

$by = isset($_POST['by'])?$_POST['by']:"time";
$order = isset($_POST['order'])?$_POST['order']:"DESC";
$keyword = isset($_POST['keyword'])?$_POST['keyword']:"";
$pre = ($order=="ASC")?"DESC":"ASC";

if(isset($_POST['filter_folder'])) $_SESSION['filter_folder'] = $_POST['filter_folder'];
$folder = (isset($_POST['filter_folder'])) ? $_POST['filter_folder'] : (isset($_SESSION['filter_folder']) ? $_SESSION['filter_folder'] : 'inbox');
switch($folder) {
	case "inbox":
		$sub = sprintf("messages.receiverID = %d
						AND messages.receiverSideStatus = 1",
						$_SESSION['userID']);
	break;
	case "sent":
		$sub = sprintf("messages.senderID = %d
						AND messages.senderSideStatus = 1",
						$_SESSION['userID']);
	break;
	case "trashed":
		$sub = sprintf("((messages.receiverID = %d AND messages.receiverSideStatus = 0)
						OR (messages.senderID = %d AND messages.senderSideStatus = 0))",
						$_SESSION['userID'],
						$_SESSION['userID']);
	break;
}
$query = sprintf("SELECT messages.messageID
					FROM messages
					LEFT JOIN users U1 ON messages.senderID = U1.userID
					LEFT JOIN users U2 ON messages.receiverID = U2.userID
					WHERE %s
					AND (messages.subject LIKE '%s'
					OR U1.username LIKE '%s'
					OR U1.forename LIKE '%s'
					OR U1.surname LIKE '%s'
					OR U2.username LIKE '%s'
					OR U2.forename LIKE '%s'
					OR U2.surname LIKE '%s')",
					$sub,
					"%".mysql_real_escape_string($keyword)."%",
					"%".mysql_real_escape_string($keyword)."%",
					"%".mysql_real_escape_string($keyword)."%",
					"%".mysql_real_escape_string($keyword)."%",
					"%".mysql_real_escape_string($keyword)."%",
					"%".mysql_real_escape_string($keyword)."%",
					"%".mysql_real_escape_string($keyword)."%");
$result = mysql_query($query, $conn) or die(mysql_error());
$total = mysql_num_rows($result);

$limit = isset($_POST['limit'])?(int)$_POST['limit']:RPP;
$pages = (ceil($total/$limit)==0)?1:ceil($total/$limit);
$page = isset($_POST['page'])?(int)$_POST['page']:1;
$offset = $limit*($page-1);
?>