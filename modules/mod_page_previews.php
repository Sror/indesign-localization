<?php
require_once(dirname(__FILE__).'/../config.php');
//disabled for guests access
#$access = array("system","login");
#require_once(MODULES.'mod_authorise.php');

$show_pages = !empty($_GET['show_pages']) ? (int)$_GET['show_pages'] : 0;
echo '<div class="toolIntro">'.$lang->display('Pages').'</div>';
echo '<div class="closeBtn"><a href="javascript:void(0);" onclick="hidediv(\'pageColL\');SetClassName(\'pageTool\',\'pageToolOff\');"><img src="'.IMG_PATH.'close_left.png" title="'.$lang->display('Close').'" /></a></div>';
echo '<div class="clear"></div>';
if(empty($show_pages)) {
	$_SESSION['show_pages'] = 0;
} else {
	$_SESSION['show_pages'] = $show_pages;
	$layout = !empty($_GET['layout']) ? $_GET['layout'] : "" ;
	$artworkID = !empty($_GET['artworkID']) ? (int)$_GET['artworkID'] : 0 ;
	$page = !empty($_GET['page']) ? (int)$_GET['page'] : 0 ;
	$taskID = !empty($_GET['taskID']) ? (int)$_GET['taskID'] : 0 ;
	$istemplate = ($_GET['istemplate']==1) ? true : false ;
	$id = empty($taskID) ? $artworkID : $taskID;

	$query = sprintf("SELECT PreviewFile, Page
						FROM pages
						WHERE ArtworkID = %d
						AND Master = 0
						ORDER BY Page ASC",
						$artworkID);
	$result = mysql_query($query, $conn) or die(mysql_error());
	while($row = mysql_fetch_assoc($result)) {
		$previewFile = $row['PreviewFile'];
		$pageno = $row['Page'];
		if(empty($taskID)) {
			$dir = PREVIEW_DIR;
			if($istemplate) $dir = POSTVIEW_DIR;
			$thumbnail = $dir.THUMBNAILS_DIR.$previewFile;
			if(!file_exists(ROOT.$thumbnail)) {
				$DB->RebuildPageThumbnail($dir,$artworkID,$page);
			}
		} else {
			$dir = POSTVIEW_DIR;
			$thumbnail = $dir.THUMBNAILS_DIR.BareFilename($previewFile)."-$taskID.jpg";
			if(!file_exists(ROOT.$thumbnail)) {
				$DB->RebuildPageThumbnail($dir,$artworkID,$page,$taskID);
			}
		}
		echo "<a href=\"javascript:void(0);\" onclick=\"goToURL('parent','index.php?layout=$layout&id=$id&page=$pageno');\">";
		if($pageno==$page) {
			echo "<div class=\"pageViewOn\">";
		} else {
			echo "<div class=\"pageViewOff\" onmouseover=\"this.className='pageViewOn'\" onmouseout=\"this.className='pageViewOff'\">";
		}
		echo "<div class=\"image\">";
		if(!empty($previewFile) && file_exists(ROOT.$thumbnail)) {
			echo "<img src=\"$thumbnail?".filemtime(ROOT.$thumbnail)."\">";
		} else {
			echo "<img src=\"".IMG_PATH."img_missing.png\" />";
		}
		echo "</div>";
		echo "<div class=\"label\">$pageno</div>";
		echo "</div>";
		echo "</a>";
	}
	echo "<div class=\"clear\"></div>";
}