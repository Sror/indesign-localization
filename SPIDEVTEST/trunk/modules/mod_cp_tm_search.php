<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("system","cpanel");
require_once(MODULES.'mod_authorise.php');

$fromLangID = isset($_POST['fromLangID'])?$_POST['fromLangID']:0;
$toLangID = isset($_POST['toLangID'])?$_POST['toLangID']:0;
$source = isset($_POST['source'])?$_POST['source']:"";

//may want to LIKE instead of = to get the exact match of a para
$source_query = sprintf("SELECT paraset.ParaID, paraset.ParaGroup
						FROM paragraphs
						LEFT JOIN paraset ON paraset.ParaID = paragraphs.uID
						WHERE paragraphs.LangID = %d
						AND paragraphs.ParaText = '%s'
						LIMIT 1",
						$fromLangID,
						mysql_real_escape_string($source));
$source_result = mysql_query($source_query, $conn) or die(mysql_error());
if(mysql_num_rows($source_result)) {
	$source_row = mysql_fetch_assoc($source_result);
	$sourceParaID = $source_row['ParaID'];
	$PG = $source_row['ParaGroup'];
	$query = sprintf("SELECT paragraphs.ParaText, paragraphs.timeRef, paragraphs.notes, paragraphs.rating,
					paraset.ParaID, paraset.ParaGroup,
					users.username, para_types.name AS paraType, para_types.icon,
					brands.brandName,
					subjects.subjectTitle
					FROM paragraphs
					LEFT JOIN paraset ON paraset.ParaID = paragraphs.uID
					LEFT JOIN users ON paragraphs.user_id = users.userID
					LEFT JOIN para_types ON paragraphs.type_id = para_types.id
					LEFT JOIN brands ON paragraphs.brand_id = brands.brandID
					LEFT JOIN subjects ON paragraphs.subject_id = subjects.subjectID
					WHERE paragraphs.LangID = %d
					AND paraset.ParaGroup = %d
					ORDER BY
					para_types.order DESC,
					paragraphs.rating DESC,
					paragraphs.timeRef DESC",
					$toLangID,
					$PG);
	$result = mysql_query($query, $conn) or die(mysql_error());
	if(mysql_num_rows($result)) {
		while($row = mysql_fetch_assoc($result)) {
			$username = !empty($row['username'])?$row['username']:$lang->display('Unknown');
			$brandName = !empty($row['brandName'])?$row['brandName']:$lang->display('Unbranded');
			$subject = !empty($row['subjectTitle'])?$row['subjectTitle']:$lang->display('N/S');
			echo '<div class="tm" onMouseOver="this.style.backgroundColor=\'#FFFFDD\';" onMouseOut="this.style.backgroundColor=\'#C2E0FD\';">';
			echo '<table width="100%" cellspacing="0" cellpadding="3" border="0">';
			echo '<tr valign="top">';
			echo '<td width="10%"><img src="'.IMG_PATH.''.$row['icon'].'" title="'.$lang->display($row['paraType']).'"></td>';
			echo '<td>'.html_display_para($row['ParaText']).'</td>';
			echo '<td align="right">';
			BuildRating($row['rating']);
			echo '</td>';
			echo '</tr>';
			if(!empty($row['notes'])) {
				echo '<tr>';
				echo '<td valign="top" title="'.$lang->display('Notes').'">';
				echo '<img src="'.IMG_PATH.'ico_notes.png" />';
				echo '</td>';
				echo '<td colspan="2">';
				echo html_display_para($row['notes']);
				echo '</td>';
				echo '</tr>';
			}
			echo '<tr>';
			echo '<td><img src="'.IMG_PATH.'ico_memorydate_on.gif"></td>';
			echo '<td>'.$row['timeRef'].' | '.$username.' | '.$brandName.' | '.$subject.'</td>';
			echo '<td align="right">';
			echo '<a href="javascript:void(0);" onclick="RemoveTM('.$row['ParaID'].',0,0);document.getElementById(\'search\').click();"><img src="'.IMG_PATH.'ico_bin.gif"></a>';
			echo '</td>';
			echo '</tr>';
			echo '</table>';
			echo '</div>';
		}
	} else {
		BuildTipMsg($lang->display('N/A'));
	}
} else {
	BuildTipMsg($lang->display('N/A'));
}
?>