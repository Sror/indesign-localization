<?php
die("<div>".date('j M Y, H:i:s')."</div>");

$conn = mysql_connect('localhost','root','storepoint');
mysql_selectdb('pagl');
mysql_query("SET CHARACTER SET utf8",$conn);
mysql_query("SET NAMES 'utf8'",$conn);


/*
$XML = new DOMDocument('1.0','UTF-8');
$loaded = $XML->load('C:/Server Documents/Output/test.xml');
if($loaded === false) return false;
$campaigns = $XML->getElementsByTagName('CAMPAIGN');
$campaign = $campaigns->item(0);
$artworks = $campaign->getElementsByTagName('ARTWORK');
$artwork = $artworks->item(0);
$progress  = $artwork->getAttribute('PRORESS');
echo "<div>$progress%</div>";
exit;
die("<div>".rand(0,99)."</div>");

require_once(dirname(__FILE__).'/../config.php');
$access = array("artworks","new");
require_once(MODULES.'mod_authorise.php');
*/
$campaign_id = !empty($_GET['campaign_id']) ? $_GET['campaign_id'] : 0;
if(empty($campaign_id)) die("Invalid Campaign");

function get_upload_id($campaign_id, $user_id) {
	$query = sprintf("SELECT id
					FROM artwork_uploads
					WHERE campaign_id = %d
					AND user_id = %d
					LIMIT 1",
					$campaign_id,
					$user_id);
	$result = mysql_query($query,$this->link) or die(mysql_error());
	if(!mysql_num_rows($result)) return false;
	$row = mysql_fetch_assoc($result);
	return $row['id'];
}

$upload_id = !empty($_GET['upload_id']) ? $_GET['upload_id'] : get_upload_id($campaign_id,$_SESSION['userID']);
if(empty($upload_id)) die("Invalid Campaign Upload");

$query_upload = sprintf("SELECT *
						FROM artwork_uploads
						WHERE id = %d
						LIMIT 1",
						$upload_id);
$result_upload = mysql_query($query_upload) or die(mysql_error());
if(!mysql_num_rows($result_upload,$conn)) die("Invalid Campaign Upload");
$row_upload = mysql_fetch_assoc($result_upload);
echo '<div>'.date(FORMAT_TIME,$row_upload['time_start']).'</div>';
$query = sprintf("SELECT *
				FROM artwork_upload_log
				WHERE upload_id = %d
				ORDER BY id ASC",
				$upload_id);
$result = mysql_query($query,$conn) or die(mysql_error());
while($row = mysql_fetch_assoc($result)) {
	echo '<div>'.$row['filename'].' - '.$row['progress'].'%</div>';
}
exit;