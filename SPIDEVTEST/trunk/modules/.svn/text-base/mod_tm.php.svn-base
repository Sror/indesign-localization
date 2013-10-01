<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("system","login");
require_once(MODULES.'mod_authorise.php');

$PL = !empty($_POST['PL']) ? (int)$_POST['PL'] : 0;
$task_id = !empty($_POST['task']) ? (int)$_POST['task'] : 0;

$error = '<div style="padding:10px;">'.$lang->display('No translation memory available.').'</div>';
#if(empty($task_id) || empty($PL)) die($error);

$Translator = new Translator();
$para = $Translator->GetParaByPL($PL,true);
#if($para === false) die($error);
$PG = $para['ParaGroup'];

$task = $DB->get_task_info($task_id);
#if($task === false) die($error);
$lang_id = $task['desiredLanguageID'];
$brand_id = $task['brandID'];
$subject_id = $task['subjectID'];

if(!empty($_POST['do'])) {
	switch($_POST['do']) {
		case "save":
			$trans = !empty($_POST['trans']) ? (string)$_POST['trans'] : "";
			$type_id = !empty($_POST['type']) ? (int)$_POST['type'] : PARA_USER;
			$Translator->AddTranslatedPara($trans,$lang_id,$PG,$PL,$task_id,$_SESSION['userID'],$type_id,$brand_id,$subject_id);
			break;
		case "pick":
			$para_id = !empty($_POST['para']) ? (int)$_POST['para'] : 0;
			if(empty($para_id)) break;
			$Translator->AddParatrans($PL,$task_id,$para_id,$_SESSION['userID']);
			break;
		case "note":
			$notes = !empty($_POST['notes']) ? (string)$_POST['notes'] : "";
			$para_id = !empty($_POST['para']) ? (int)$_POST['para'] : 0;
			if(empty($para_id)) break;
			$Translator->UpdateParaNotes($para_id,$notes);
			break;
		case "remove":
			$para_id = !empty($_POST['para']) ? (int)$_POST['para'] : 0;
			if(empty($para_id)) break;
			$Translator->RemoveTM($para_id,$PL,$task_id);
			break;
	}
}
$query = sprintf("SELECT paragraphs.uID AS ParaID, paragraphs.user_id, paragraphs.ParaText, paragraphs.timeRef, paragraphs.notes, paragraphs.rating,
				paragraphs.subject_id=%d AS thisSubject, paragraphs.brand_id=%d AS thisBrand,
				paraset.ParaID,
				users.username,
				para_types.name AS paraType, para_types.icon,
				brands.brandName,
				subjects.subjectTitle
				FROM paragraphs
				LEFT JOIN paraset ON paragraphs.uID = paraset.ParaID
				LEFT JOIN users ON paragraphs.user_id = users.userID
				LEFT JOIN para_types ON paragraphs.type_id = para_types.id
				LEFT JOIN brands ON paragraphs.brand_id = brands.brandID
				LEFT JOIN subjects ON paragraphs.subject_id = subjects.subjectID
				WHERE paragraphs.LangID = %d AND paraset.ParaGroup = %d
				ORDER BY
				thisSubject DESC,
				thisBrand DESC,
				para_types.order DESC,
				paragraphs.rating DESC,
				paragraphs.timeRef DESC",
				$subject_id,
				$brand_id,
				$lang_id,
				$PG);
$result = mysql_query($query,$conn) or die(mysql_error());
if(!mysql_num_rows($result)) die($error);
while($row = mysql_fetch_assoc($result)) {
	$username = !empty($row['username'])?$row['username']:$lang->display('Unknown');
	$brandName = !empty($row['brandName'])?$row['brandName']:$lang->display('Unbranded');
	$subject = !empty($row['subjectTitle'])?$row['subjectTitle']:$lang->display('N/S');
?>
<div id="tms" class="tm" onMouseOver="this.style.backgroundColor='#FFFFDD';" onMouseOut="this.style.backgroundColor='#C2E0FD';">
	<table cellspacing="0" cellpadding="3" border="0">
		<tr>
			<td width="5%" valign="top">
				<?php echo '<img src="'.IMG_PATH.''.$row['icon'].'" title="'.$lang->display($row['paraType']).'" />'; ?>
			</td>
			<td>
				<a href="javascript:void(0);" onclick="PickTM(<?php echo $task_id.",".$PL.",".$row['ParaID'].",'".htmlspecialchars(mysql_real_escape_string($row['ParaText']),ENT_QUOTES,'UTF-8')."'"; ?>);">
					<?php echo html_display_para($row['ParaText']); ?>
				</a>
			</td>
			<td align="right"><?php BuildRating($row['rating']); ?>
			</td>
		</tr>
		<tr>
			<td valign="top"><?php echo '<img src="'.IMG_PATH.'ico_notes.png" title="'.$lang->display('Notes').'" />'; ?></td>
			<td colspan="2">
				<div id="tm_<?php echo $row['ParaID']; ?>">
				<?php
					if(!empty($row['notes'])) {
						echo html_display_para($row['notes']);
					}
					echo '<a href="javascript:void(0);" onclick="hidediv(\'tm_'.$row['ParaID'].'\');display(\'edit_notes_'.$row['ParaID'].'\');">'.$lang->display('Edit').'...</a>';
				?>
				</div>
				<div id="edit_notes_<?php echo $row['ParaID']; ?>" style="display:none;">
					<textarea
						class="input"
						onfocus="this.className='inputOn';doResize('notes_<?php echo $row['ParaID']; ?>',120);"
						onblur="this.className='input'"
						name="notes_<?php echo $row['ParaID']; ?>"
						id="notes_<?php echo $row['ParaID']; ?>"
						rows="1"
						cols="80"
					><?php echo "\n".$row['notes']; ?></textarea>
					<input
						type="button"
						class="btnDo"
						onmousemove="this.className='btnOn'"
						onmouseout="this.className='btnDo'"
						title="<?php echo $lang->display('Save'); ?>"
						value="<?php echo $lang->display('Save'); ?>"
						onclick="var notes=document.getElementById('notes_<?php echo $row['ParaID']; ?>').value;ResetDiv('edit_notes_<?php echo $row['ParaID']; ?>');NoteTM(<?php echo $row['ParaID']; ?>,notes);"
					/>
					<input
						type="button"
						class="btnOff"
						onmousemove="this.className='btnOn'"
						onmouseout="this.className='btnOff'"
						title="<?php echo $lang->display('Cancel'); ?>"
						value="<?php echo $lang->display('Cancel'); ?>"
						onclick="hidediv('edit_notes_<?php echo $row['ParaID']; ?>');display('tm_<?php echo $row['ParaID']; ?>');"
					/>
				</div>
			</td>
		</tr>
		<tr>
			<td><?php echo '<img src="'.IMG_PATH.'ico_memorydate_on.gif" />'; ?></td>
			<td>
				<?php echo date(FORMAT_TIME,strtotime($row['timeRef'])).' | '.$username.' | '.$brandName.' | '.$subject; ?>
			</td>
			<td align="right">
				<?php
					if($isadmin || $_SESSION['userID']==$row['user_id']) {
						echo '<a href="javascript:void(0);"onclick="RemoveTM('.$row['ParaID'].','.$PL.','.$task_id.');">';
						echo '<img src="'.IMG_PATH.'ico_bin.gif">';
						echo '</a>';
					}
				?>
			</td>
		</tr>
	</table>
</div>
<?php } ?>