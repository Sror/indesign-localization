<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("system","login");
require_once(MODULES.'mod_authorise.php');

$artworkID = isset($_GET['artworkID']) ? (int)$_GET['artworkID'] : 0;

$sent = false;
if(isset($_GET['do'])) {
	$query = sprintf("SELECT artworkName FROM artworks WHERE artworkID = %d LIMIT 1",$artworkID);
	$result = mysql_query($query,$conn) or die(mysql_error());
	if(mysql_num_rows($result)) {
		$row = mysql_fetch_assoc($result);
		$artwork = $row['artworkName'];
		require_once(CLASSES.'Mailer.php');
		$mailer = new Mailer();
		if($_GET['do']=="addguests") {
			$guests = array();
			//guests by emails
			$emails = explode(",",$_GET['guests']);
			foreach($emails as $email) {
				$email = trim($email);
				$guests[$email] = $email;
			}
			//guests by users
			$user_guests = explode(",",$_GET['user_guests']);
			$keeps =  array();
			foreach($user_guests as $user_guest) {
				if(empty($user_guest) || !is_numeric($user_guest)) continue;
				$key = array_search($user_guest,$keeps);
				if($key === false) {
					$keeps[] = $user_guest;
				} else {
					unset($keeps[$key]);
				}
			}
			foreach($keeps as $keep) {
				$query = sprintf("SELECT email, forename, surname
								FROM users
								WHERE userID = %d
								LIMIT 1",
								$keep);
				$result = mysql_query($query,$conn) or die(mysql_error());
				if(!mysql_num_rows($result)) continue;
				$row = mysql_fetch_assoc($result);
				$guests[$row['email']] = $row['forename']." ".$row['surname'];
			}
			//send invitations
			foreach($guests as $address=>$name) {
				if(empty($address) || empty($name)) continue;
				$token = $DB->AddGuest($artworkID,$_SESSION['userID'],$address,$name);
				//send email
				$subject = SYSTEM_NAME.": Guest Invitation";
				$body = "Dear $name,";
				$body .= "\n\nRE: $address";
				$body .= "\n\nYou're invited to the prework stage of '$artwork' at ".SYSTEM_NAME.".";
				$body .= "\n\nTo join and add comments Please follow the link below:";
				$body .= "\n\n".SITE_URL."index.php?layout=amend&id=$artworkID&token=$token";
				$body .= "\n\nKind Regards,";
				$body .= "\n\n".COMPANY_NAME;
				$sent = $mailer->send_mail($name,$address,$subject,$body);
			}
		}

		if($_GET['do']=="delguest") {
			$ref = isset($_GET['ref']) ? (int)$_GET['ref'] : 0;
			if(!empty($ref)) {
				$query = sprintf("SELECT email
								FROM artwork_guests
								WHERE id = %d
								LIMIT 1",
								$ref);
				$result = mysql_query($query, $conn) or die(mysql_error());
				$row = mysql_fetch_assoc($result);
				$address = $row['email'];
				$DB->RemoveGuest($_GET['ref']);
				//send email
				$subject = SYSTEM_NAME.": Guest Invitation Withdrawal";
				$body = "Dear Guest,";
				$body .= "\n\nRE: $address";
				$body .= "\n\nYour invitation to the prework stage of '$artwork' at ".SYSTEM_NAME." has been withdrawn.";
				$body .= "\n\nKind Regards,";
				$body .= "\n\n".COMPANY_NAME;
				$mailer->send_mail($address,$address,$subject,$body);
			}
		}
	}
}

echo '<div class="toolIntro">'.$lang->display('Guests').'</div>';
echo '<div class="closeBtn"><a href="javascript:void(0);" onclick="hidediv(\'pageColR\');SetClassName(\'guestTool\',\'pageToolOff\');"><img src="'.IMG_PATH.'close_right.png" title="'.$lang->display('Close').'" /></a></div>';
echo '<div class="clear"></div>';
?>
<?php if($acl->acl_check("taskworkflow","addguests",$_SESSION['companyID'],$_SESSION['userID'])) { ?>
<div id="addguests">
	<table width="100%" cellspacing="0" cellpadding="3" border="0">
		<tr>
			<td><?php BuildCampaignACL($_SESSION['companyID'],$_SESSION['userID'],0,true); ?></td>
		</tr>
		<tr>
			<td><b><?php echo $lang->display('Email');?></b></td>
		</tr>
		<tr>
			<td>
				<textarea
					class="input"
					onfocus="this.className='inputOn';doResize('guests',60);"
					onblur="this.className='input'"
					id="guests"
					name="guests"
					rows="1"
					cols="27"
				></textarea>
				<input type="hidden" id="user_guests" name="user_guests" />
			</td>
		</tr>
		<tr>
			<td>
				<input
					type="button"
					class="btnDo"
					onmousemove="this.className='btnOn'"
					onmouseout="this.className='btnDo'"
					title="<?php echo $lang->display('Add Guests'); ?>"
					value="<?php echo $lang->display('Add Guests'); ?>"
					onclick="DoAjax('artworkID=<?php echo $artworkID; ?>&do=addguests&user_guests='+document.getElementById('user_guests').value+'&guests='+document.getElementById('guests').value,'pageColR','modules/mod_art_guests.php');ResetDiv('addguests');"
				/>
			</td>
		</tr>
	</table>
</div>
<?php } ?>
<?php
	if($sent === true) echo '<div><img src="'.IMG_PATH.'ico_enable.png" /> '.$lang->display('Sent').'</div>';
	$query = sprintf("SELECT id, email
					FROM artwork_guests
					WHERE artwork_id = %d
					ORDER BY id ASC",
					$artworkID);
	$result = mysql_query($query, $conn) or die(mysql_error());
	echo '<div id="loader" class="toolIntro">'.$lang->display('Found').' '.mysql_num_rows($result).'</div>';
	echo '<div class="clear"></div>';
	echo '<hr />';
	while($row = mysql_fetch_assoc($result)) {
		echo '<div id="guest'.$row['id'].'" class="hover" onmouseover="this.className=\'bgWhite\'" onmouseout="this.className=\'hover\'">';
		if($isadmin) {
			echo "<a href=\"javascript:void(0);\" onclick=\"ResetDiv('loader');DoAjax('artworkID=$artworkID&do=delguest&ref={$row['id']}','pageColR','modules/mod_art_guests.php');\"><img src=\"".IMG_PATH."btn_s_delete.png\" title=\"".$lang->display('Delete')."\"></a> ";
		} else {
			echo "<img src=\"".IMG_PATH."ico_s_tick.png\" />";
		}
		echo "<a href=\"mailto:{$row['email']}\">";
		echo $row['email'];
		if(!empty($row['name'])) {
			echo " ({$row['name']})";
		}
		echo "</a>";
		echo "</div>";
		echo "<hr />";
	}
?>