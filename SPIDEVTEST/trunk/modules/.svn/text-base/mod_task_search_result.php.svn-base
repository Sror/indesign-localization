<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("system","login");
require_once(MODULES.'mod_authorise.php');

$id = isset($_GET['id']) ? $_GET['id'] : 0;
$pl = isset($_GET['pl']) ? $_GET['pl'] : 0;
$type = isset($_GET['type']) ? $_GET['type'] : TYPE_ORIGINAL;
$_SESSION['task_search_type'] = $type;
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : "";
$_SESSION['task_search_keyword'] = $keyword;

$row = $DB->get_task_info($id);
if($row === false) die('Invalid Task');
$artwork_id = $row['artworkID'];
$source_lang_id = $row['sourceLanguageID'];
$target_lang_id = $row['desiredLanguageID'];
$Translator = new Translator();

switch($type) {
	case TYPE_ORIGINAL:
		$str = sprintf(" AND paragraphs.LangID = %d",$source_lang_id);
		$result = $Translator->get_all_paras($artwork_id,$id,0,0,$str);
		break;
	case TYPE_TRANSLATION:
		$trial = $Translator->get_task_trial_status($id) ? " AND boxes.heading = 1" : "";
		$query = sprintf("SELECT paratrans.ParalinkID AS PL, paralinks.BoxID, pages.Page,
						paragraphs.ParaText, paragraphs.timeRef,
						para_types.name AS paraType, para_types.icon
						FROM paratrans
						LEFT JOIN paralinks ON paratrans.ParalinkID = paralinks.uID
						LEFT JOIN boxes ON paralinks.BoxID = boxes.uID
						LEFT JOIN pages ON boxes.PageID = pages.uID
						LEFT JOIN paragraphs ON paratrans.transParaID = paragraphs.uID
						LEFT JOIN para_types ON paragraphs.type_id = para_types.id
						LEFT JOIN box_properties ON ( box_properties.box_id = paralinks.BoxID AND box_properties.task_id IN (0,%d) )
						WHERE paratrans.taskID = %d
						AND paragraphs.LangID = %d
						AND (box_properties.lock IS NULL OR box_properties.lock = 0)
						AND paragraphs.ParaText LIKE '%s'
						$trial
						ORDER BY paralinks.uID",
						$id,
						$id,
						$target_lang_id,
						"%".mysql_real_escape_string($keyword)."%");
		$result = mysql_query($query,$conn) or die(mysql_error());
		break;
}
echo '<div class="mainwrap">';
echo '<div class="list" style="max-height:500px;overflow:auto;">';
echo '<table width="100%" cellspacing="0" cellpadding="5" border="0">';
echo '<tr>';
echo '<th align="center" width="5%">ID</td>';
echo '<th align="center" width="5%">'.$lang->display('Type').'</td>';
echo '<th align="left" width="70%">'.$lang->display('Paragraph').'</td>';
echo '<th align="left" width="20%">'.$lang->display('Last Update').'</td>';
echo '</tr>';
$counter = 1;
while($row = mysql_fetch_assoc($result)) {
	if($type == TYPE_ORIGINAL) {
		$Amended = $Translator->GetParaByPL($row['PL']);
		if($Amended===false) {
			$Icon = $row['icon'];
			$ParaType = $row['paraType'];
			$ParaText = $row['ParaText'];
			$TimeRef = $row['timeRef'];
		} else {
			$ParaID = $Amended['ParaID'];
			$ParaText = $Amended['ParaText'];
			$TimeRef = $Amended['timeRef'];
			$ParaInfo = $Translator->GetParaTypeByID($ParaID);
			if($ParaInfo===false) continue;
			$Icon = $ParaInfo['icon'];
			$ParaType = $ParaInfo['name'];
		}
		$keyword_found = stripos($ParaText,$keyword);
		if($keyword_found === false) continue;
	}
	if($type == TYPE_TRANSLATION) {
		$Icon = $row['icon'];
		$ParaType = $row['paraType'];
		$ParaText = $row['ParaText'];
		$TimeRef = $row['timeRef'];
	}
	$class = $pl==$row['PL'] ? 'hover' : ( $counter%2==0 ? 'even' : 'odd' ) ;
	echo '<tr
			class="'.$class.'"
			onmouseover="this.className=\'hover\'"
			onmouseout="this.className=\''.$class.'\'"
			onclick="goToURL(\'parent\',\'index.php?layout=translate&id='.$id.'&page='.$row['Page'].'&box='.$row['BoxID'].'&pl='.$row['PL'].'\');"
			style="cursor:pointer;">';
	echo '<td align="center" valign="top">'.$counter.'</td>';
	echo '<td align="center" valign="top"><img src="'.IMG_PATH.''.$Icon.'" title="'.$lang->display($ParaType).'" /></td>';
	echo '<td align="left" valign="top">'.html_display_para($ParaText).'</td>';
	echo '<td align="left" valign="top">'.date(FORMAT_TIME,strtotime($TimeRef)).'</td>';
	echo '</tr>';
	$counter++;
}
echo '<tr><td colspan="4">'.$lang->display('Found')." <b>".($counter-1).'</b></td></tr>';
echo '</table>';
echo '</div>';
echo '</div>';
?>