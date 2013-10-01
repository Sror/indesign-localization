<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("system","login");
require_once(MODULES.'mod_authorise.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$IM = new ImageManager();
$IM->AutoLookup($_SESSION['userID'],$id);

$default_img_dir = $IM->get_default_img_dir($artworkID);
$query = sprintf("SELECT img_links.id AS img_link_id, images.content
				FROM img_links
				LEFT JOIN images ON img_links.img_id = images.id
				LEFT JOIN boxes ON img_links.box_id = boxes.uID
				LEFT JOIN pages ON boxes.PageID = pages.uID
				LEFT JOIN artworks ON pages.ArtworkID = artworks.artworkID
				WHERE artworks.artworkID = %d
				ORDER BY img_links.img_id ASC",
				$id);
$result = mysql_query($query, $conn) or die(mysql_error());
$count = 0;
while($row = mysql_fetch_assoc($result)) {
	if(!$IM->CheckImageStatus($id,$row['img_link_id'])) $count++;
}
if($count) {
	BuildTipMsg($lang->display('Missing').': '.$lang->display('Images').' ('.$count.') <a href="javascript:void(0)" onclick="DoAjax(\'id='.$id.'\',\'autolookup\',\'modules/mod_art_img_lookup.php\');"><img src="'.IMG_PATH.'ico_lookup.png" /> '.$lang->display('Auto Lookup').'</a>');
}