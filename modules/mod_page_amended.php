<?php
require_once(dirname(__FILE__).'/../config.php');
//disabled for guests access
#$access = array("system","login");
#require_once(MODULES.'mod_authorise.php');

$artworkID = !empty($_GET['artworkID']) ? (int)$_GET['artworkID'] : 0 ;

$token = isset($_REQUEST['token']) ? $_REQUEST['token'] : (isset($_SESSION['token'])?$_SESSION['token']:"");
if(!empty($token)) $_SESSION['token'] = $token;
$query = sprintf("SELECT id
				FROM artwork_guests
				WHERE artwork_id = %d
				AND token = '%s'
				LIMIT 1",
				$artworkID,
				mysql_real_escape_string($token));
$result = mysql_query($query, $conn) or die(mysql_error());
if(mysql_num_rows($result)) {
	$is_guest = 1;
} else {
	$access = array("system","login");
	require_once(MODULES.'mod_authorise.php');
	$is_guest = 0;
}

$page = !empty($_GET['page']) ? (int)$_GET['page'] : 0 ;
$taskID = !empty($_GET['taskID']) ? (int)$_GET['taskID'] : 0 ;

if(!empty($_GET['do']) && !empty($_GET['ref'])) {
	if($_GET['do']=="delamended") {
		$Translator = new Translator();
		$Translator->DeleteAmended($_GET['ref']);
	}
}

echo '<div class="toolIntro">'.$lang->display('Amended').'</div>';
echo '<div class="closeBtn"><a href="javascript:void(0);" onclick="hidediv(\'pageColR\');SetClassName(\'amendTool\',\'pageToolOff\');"><img src="'.IMG_PATH.'close_right.png" title="'.$lang->display('Close').'" /></a></div>';
echo '<div class="clear"></div>';
?>
<div id="amendlist">
	<?php
		$show_all = !empty($_REQUEST['showall']);
		$condition = empty($_REQUEST['showall']) ? sprintf(" AND pages.Page = %d",$page) : "";
		$query = sprintf("SELECT paraedit.id, paraedit.user_id, paraedit.time, users.username, paralinks.BoxID, pages.Page,
						P1.ParaText AS SourcePara, P2.ParaText AS AmendedPara
						FROM paraedit
						LEFT JOIN users ON paraedit.user_id = users.userID
						LEFT JOIN paralinks ON paraedit.pl_id = paralinks.uID
						LEFT JOIN boxes ON paralinks.BoxID = boxes.uID
						LEFT JOIN pages ON boxes.PageID = pages.uID
						LEFT JOIN paragraphs P1 ON paralinks.ParaID = P1.uID
						LEFT JOIN paragraphs P2 ON paraedit.para_id = P2.uID
						WHERE pages.ArtworkID = %d
						AND paraedit.task_id = %d
						%s
						ORDER BY paraedit.time DESC",
						$artworkID,
						$taskID,
						mysql_real_escape_string($condition));
		$result = mysql_query($query, $conn) or die(mysql_error());
		echo '<div class="closeBtn"><input type="checkbox" name="showall" id="showall" value="1" onclick="ResetDiv(\'loader\');DoAjax(\'artworkID='.$artworkID.'&page='.$page.'&taskID='.$taskID.'&showall=\'+(this.checked?1:0),\'pageColR\',\'modules/mod_page_amended.php\');"';
		if($show_all) echo ' checked="checked"';
		echo '/>'.$lang->display('Show All').'</div>';
		echo '<div id="loader" class="toolIntro">'.$lang->display('Found').' '.mysql_num_rows($result).'</div>';
		echo '<div class="clear"></div>';
		echo '<hr />';
		while($row = mysql_fetch_assoc($result)) {
			echo '<div id="amended'.$row['id'].'" class="hover" onmouseover="this.className=\'bgWhite\';display(\'delamended'.$row['id'].'\');" onmouseout="this.className=\'hover\';hidediv(\'delamended'.$row['id'].'\');" style="padding:5px;">';
			echo '<div class="left"><img src="'.IMG_PATH.'ico_amend.png" /></div>';
			echo '<div class="left">';
			echo "<a href=\"javascript:void(0);\" onclick=\"goToURL('parent','index.php?layout=user&id={$row['user_id']}');\">{$row['username']}</a>";
			echo '</div>';
			if(!$is_guest && $acl->acl_check("artworks","edit",$_SESSION['companyID'],$_SESSION['userID'])) {
				echo "<div id=\"delamended{$row['id']}\" class=\"right\" style=\"display:none;\"><a href=\"javascript:void(0);\" onclick=\"ResetDiv('loader');DoAjax('artworkID=$artworkID&page=$page&taskID=$taskID&do=delamended&ref={$row['id']}&showall=".($show_all?1:0)."','pageColR','modules/mod_page_amended.php');\"><img src=\"".IMG_PATH."btn_s_delete.png\" title=\"".$lang->display('Delete')."\"></a></div>";
			}
			echo '<div class="clear"></div>';
			echo '<div class="grey">';
			echo '<div class="left">'.$lang->display('Page').' '.$row['Page'].'</div>';
			if(!empty($row['BoxID'])) {
				if(empty($taskID)) {
					$npv = "artworkID=$artworkID";
					$dest = "mod_art_amend.php";
				} else {
					$npv = "taskID=$taskID";
					$dest = "mod_proofread.php";
				}
				echo '<div class="left"><a href="javascript:void(0);" onclick="Popup(\'helper\',\'blur\');DoAjax(\''.$npv.'&page='.$page.'&boxID='.$row['BoxID'].'\',\'window\',\'modules/'.$dest.'\');"><img src="'.IMG_PATH.'ico_window.png" title="'.$lang->display('Lookup').'" /></a></div>';
			}
			echo '<div class="right">'.date(FORMAT_TIME,$row['time']).'</div>';
			echo '<div class="clear"></div>';
			echo '</div>';
			echo '<div><del>'.html_display_para($row['SourcePara']).'</del></div>';
			echo '<div>'.html_display_para($row['AmendedPara']).'</div>';
			if(!empty($row['attachment'])) {
				echo "<div>";
				echo "<img src=\"".IMG_PATH."ico_attachment.png\" title=\"{$lang->display('Attachment')}\" /> <a href=\"download.php?attachment&File={$row['attachment']}&SaveAs={$row['attachment']}\">".$lang->display('Attachment')."</a>";
				if(ValidateMedia(REPOSITORY_DIR.$row['attachment'])) {
					echo " <a href=\"javascript:void(0);\" onclick=\"Popup('helper','blur');DoAjax('ref={$row['attachment']}','window','modules/mod_media_play.php');\"><img src=\"".IMG_PATH."arrow_right.png\" title=\"{$lang->display('Play')}\" />{$lang->display('Play')}</a>";
				}
				echo "</div>";
			}
			echo "</div>";
			echo "<hr />";
		}
	?>
</div>