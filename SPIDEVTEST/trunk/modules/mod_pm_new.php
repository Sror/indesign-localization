<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("system","login");
require_once(MODULES.'mod_authorise.php');

$id = !empty($_GET['id']) ? (int)$_GET['id'] : 0;
$do = !empty($_GET['do']) ? $_GET['do'] : "";
switch($do) {
	case "reply":
		$prefix = "RE: ";
	break;
	case "forward":
		$prefix = "FW: ";
	break;
	default:
		$prefix = "";
}
$recipient = !empty($_GET['recipient']) ? $_GET['recipient'] : "";
$subject = "";
$content = "";
if(!empty($id)) {
	$query = sprintf("SELECT messages.*,
					U1.username AS s_username, U1.forename AS s_forename, U1.surname AS s_surname,
					U2.username AS r_username, U2.forename AS r_forename, U2.surname AS r_surname
					FROM messages
					LEFT JOIN users U1 ON messages.senderID = U1.userID
					LEFT JOIN users U2 ON messages.receiverID = U2.userID
					WHERE messages.messageID = %d
					LIMIT 1",
					$id);
	$result = mysql_query($query, $conn) or die(mysql_error());
	if(mysql_num_rows($result)) {
		$row = mysql_fetch_assoc($result);
		$recipient = $row['s_username'];
		$subject = $row['subject'];
		$content = "\n\n\n------Original Message------\nFrom: ".$row['s_forename']." ".$row['s_surname']."\nTo: ".$row['r_forename']." ".$row['r_surname']."\nSent: ".date(FORMAT_TIME,strtotime($row['messageTime']))."\nSubject: ".$row['subject']."\n".$row['content'];
	}
}
?>
<form
	action="index.php?layout=inbox"
	method="POST"
	enctype="multipart/form-data"
	id="compose_form"
	name="compose_form"
>
	<table width="100%" border="0" cellspacing="5" cellpadding="0">
		<tr>
			<td width="10%" class="highlight"><?php echo $lang->display('To'); ?>:</td>
			<td width="90%">
				<input
					type="text"
					class="input"
					onfocus="this.className='inputOn'"
					onblur="this.className='input'"
					id="recipient"
					name="recipient"
					maxlength="100"
					style="width:100%"
					value="<?php echo $recipient; ?>"
					onkeyup="DoAjax('recipient='+this.value,'contacts','modules/mod_pm_contacts.php');"
				/>
				<div id="contacts" class="autos"></div>
			</td>
		</tr>
		<tr>
			<td class="highlight"><?php echo $lang->display('Subject'); ?>:</td>
			<td>
				<input
					type="text"
					class="input"
					onfocus="this.className='inputOn'"
					onblur="this.className='input'"
					id="subject"
					name="subject"
					maxlength="100"
					style="width:100%"
					value="<?php echo $prefix.$subject; ?>"
				/>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<textarea
					class="input"
					onfocus="this.className='inputOn'"
					onblur="this.className='input'"
					name="content"
					rows="20"
					style="width:100%"
				><?php echo $content; ?></textarea>
			</td>
		</tr>
		<tr>
			<td colspan="2" align="left">
				<input 
					type="button"
					class="btnDo"
					onmousemove="this.className='btnOn'"
					onmouseout="this.className='btnDo'"
					title="<?php echo $lang->display('Send'); ?>"
					value="<?php echo $lang->display('Send'); ?>"
					onclick="validateForm('recipient','Recipient','R','content','Content','R');if(document.returnValue) SubmitForm('compose_form','send');"
				/>
				<input 
					type="reset"
					class="btnOff"
					onmousemove="this.className='btnOn'"
					onmouseout="this.className='btnOff'"
					title="<?php echo $lang->display('Reset'); ?>"
					value="<?php echo $lang->display('Reset'); ?>"
				/>
			</td>
		</tr>
	</table>
    <input type="hidden" name="form" id="form">
</form>