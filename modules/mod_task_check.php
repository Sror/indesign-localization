<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("system","login");
require_once(MODULES.'mod_authorise.php');
$Translator = new Translator();

$id = isset($_GET['id']) ? $_GET['id'] : 0;
$pl = isset($_GET['pl']) ? $_GET['pl'] : 0;
if(isset($_GET['tm_check_option'])) $_SESSION['tm_check_option'] = $_GET['tm_check_option'];
$tm_check_option = isset($_GET['tm_check_option']) ? $_GET['tm_check_option'] : ( isset($_SESSION['tm_check_option']) ? $_SESSION['tm_check_option'] : 0 );
$tm_check_option_tick = $tm_check_option==0 ? 1 : 0;
if(isset($_GET['wc_check_option'])) $_SESSION['wc_check_option'] = $_GET['wc_check_option'];
$wc_check_option = isset($_GET['wc_check_option']) ? $_GET['wc_check_option'] : ( isset($_SESSION['wc_check_option']) ? $_SESSION['wc_check_option'] : 0 );
$wc_check_option_tick = $wc_check_option==0 ? 1 : 0;
if(isset($_GET['ignore_check_option'])) $_SESSION['ignore_check_option'] = $_GET['ignore_check_option'];
$ignore_check_option = isset($_GET['ignore_check_option']) ? $_GET['ignore_check_option'] : ( isset($_SESSION['ignore_check_option']) ? $_SESSION['ignore_check_option'] : 0 );
$ignore_check_option_tick = $ignore_check_option==0 ? 1 : 0;

$row = $DB->get_task_info($id);
if($row === false) die('Invalid Task');
$artwork_id = $row['artworkID'];
$source_lang_id = $row['sourceLanguageID'];
$target_lang_id = $row['desiredLanguageID'];

$str = "0";
$result = $Translator->GetTrans($id);
if($result !== false) {
	while($row = mysql_fetch_assoc($result)) {
		$str .= $row['PL'].",";
	}
}
$str = trim($str,",");
$str = sprintf(" AND paralinks.uID NOT IN (%s)",mysql_real_escape_string($str));
$result = $Translator->get_all_paras($artwork_id,$id,0,0,$str);
echo '<div class="mainwrap">';
echo '<div class="list" style="max-height:500px;overflow:auto;">';
echo '<table width="100%" cellspacing="0" cellpadding="5" border="0">';
echo '<tr>';
echo '<th colspan="4">';
echo '<input type="checkbox" id="tm_check_option" name="tm_check_option" value="1"';
if($tm_check_option) echo ' checked=checked';
echo '  onclick="ResetDiv(\'window\');DoAjax(\'id='.$id.'&pl='.$pl.'&tm_check_option='.$tm_check_option_tick.'&wc_check_option='.$wc_check_option.'&ignore_check_option='.$ignore_check_option.'\',\'window\',\'modules/mod_task_check.php\');"> '.$lang->display('Translation Memory');
echo '<span class="span"></span>';
echo '<input type="checkbox" id="wc_check_option" name="wc_check_option" value="1"';
if($wc_check_option) echo ' checked=checked';
echo '  onclick="ResetDiv(\'window\');DoAjax(\'id='.$id.'&pl='.$pl.'&tm_check_option='.$tm_check_option.'&wc_check_option='.$wc_check_option_tick.'&ignore_check_option='.$ignore_check_option.'\',\'window\',\'modules/mod_task_check.php\');"> '.$lang->display('Word Count').' > 0';
echo '<span class="span"></span>';
echo '<input type="checkbox" id="ignore_check_option" name="ignore_check_option" value="1"';
if($ignore_check_option) echo ' checked=checked';
echo '  onclick="ResetDiv(\'window\');DoAjax(\'id='.$id.'&pl='.$pl.'&tm_check_option='.$tm_check_option.'&wc_check_option='.$wc_check_option.'&ignore_check_option='.$ignore_check_option_tick.'\',\'window\',\'modules/mod_task_check.php\');"> '.$lang->display('Ignore');
echo '</th>';
echo '</tr>';
echo '<tr>';
echo '<th align="center" width="5%">ID</td>';
echo '<th align="center" width="5%">'.$lang->display('Type').'</td>';
echo '<th align="left" width="70%">'.$lang->display('Paragraph').'</td>';
echo '<th width="20%">'.$lang->display('Last Update').'</td>';
echo '</tr>';
$counter = 1;
while($row = mysql_fetch_assoc($result)) {
	$SourcePara = $Translator->GetParaByPL($row['PL']);
	if($SourcePara === false) continue;
	if($Translator->CheckParaIgnore($row['PL'],$id) && !$ignore_check_option) continue;
	$SourceParaGroup = $SourcePara['ParaGroup'];
	$TMPara = $Translator->GetTMPara($id,$SourceParaGroup);
	if($TMPara !== false && !$tm_check_option) continue;
	$Amended = $Translator->GetParaByPL($row['PL']);
	if($Amended===false) {
		$Icon = $row['icon'];
		$ParaType = $row['paraType'];
		$ParaText = $row['ParaText'];
		$TimeRef = $row['timeRef'];
		$WordCount = $row['Words'];
	} else {
		$ParaID = $Amended['ParaID'];
		$ParaText = $Amended['ParaText'];
		$TimeRef = $Amended['timeRef'];
		$WordCount = $Amended['Words'];
		$ParaInfo = $Translator->GetParaTypeByID($ParaID);
		if($ParaInfo===false) continue;
		$Icon = $ParaInfo['icon'];
		$ParaType = $ParaInfo['name'];
	}
	if(!empty($wc_check_option) && empty($WordCount)) continue;
	$class = $pl==$row['PL'] ? 'hover' : ( $counter%2==0 ? 'even' : 'odd' ) ;
	echo '<tr
			class="'.$class.'"
			onmouseover="this.className=\'hover\'"
			onmouseout="this.className=\''.$class.'\'"
			onclick="hidediv(\'helper\');goToURL(\'parent\',\'index.php?layout=translate&id='.$id.'&page='.$row['Page'].'&box='.$row['BoxID'].'&pl='.$row['PL'].'\');"
			style="cursor:pointer;">';
	echo '<td align="center" valign="top">'.$counter.'</td>';
	echo '<td align="center" valign="top"><img src="'.IMG_PATH.''.$Icon.'" title="'.$lang->display($ParaType).'" /></td>';
	echo '<td align="left" valign="top">'.html_display_para($ParaText).'</td>';
	echo '<td valign="top">'.date(FORMAT_TIME,strtotime($TimeRef)).'</td>';
	echo '</tr>';
	$counter++;
}
echo '<tr><td colspan="4">'.$lang->display('Found')." <b>".($counter-1).'</b></td></tr>';
echo '</table>';
echo '</div>';
echo '</div>';
?>