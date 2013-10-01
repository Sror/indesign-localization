<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("system","login");
require_once(MODULES.'mod_authorise.php');

$recipient = !empty($_GET['recipient']) ? trim($_GET['recipient']) : "";
if(strpos($recipient,';')) {
	$keywords = explode(';',$recipient);
	$keyword = trim(array_pop($keywords));
	$reserve = implode(';',$keywords);
	$reserve .= '; ';
} else {
	$keyword = $recipient;
	$reserve = "";
}
if(!empty($keyword)) {
	$query = sprintf("SELECT users.username, users.forename, users.surname, companies.companyName
					FROM users
					LEFT JOIN companies ON users.companyID = companies.companyID
					WHERE users.companyID IN (%s)
					AND ( users.username LIKE '%s'
					OR users.forename LIKE '%s'
					OR users.surname LIKE '%s'
					OR companies.companyName LIKE '%s' )",
					mysql_real_escape_string($DB->get_company_list($_SESSION['companyID'])),
					"%".mysql_real_escape_string($keyword)."%",
					"%".mysql_real_escape_string($keyword)."%",
					"%".mysql_real_escape_string($keyword)."%",
					"%".mysql_real_escape_string($keyword)."%");
	$result = mysql_query($query, $conn) or die(mysql_error());
	if(mysql_num_rows($result)) {
		echo '<div class="autolist" id="autolist">';
		echo '<div class="label">';
		echo '<a href="javascript:void(0);" onclick="hidediv(\'autolist\');"><img src="'.IMG_PATH.'btn_close.png" title="'.$lang->display('Close').'"></a>';
		echo '</div>';
		while($row=mysql_fetch_assoc($result)) {
			echo '<div class="suggest"
					onmouseover="this.className=\'suggestOn\'"
					onmouseout="this.className=\'suggest\'"
					onclick="hidediv(\'autolist\');setValue(\'recipient\',\''.$reserve.$row['username'].';\');">';
			echo $row['forename'].' '.$row['surname'].' ['.$row['username'].'] '.$row['companyName'];
			echo '</div>';
		}
		echo '</div>';
	}
}
?>