<?php
require_once(dirname(__FILE__).'/../config.php');
//disabled for guests access
#$access = array("artworks","edit");
#require_once(MODULES.'mod_authorise.php');

$artworkID = isset($_GET['artworkID']) ? $_GET['artworkID'] : 0;
$artwork_info = $DB->get_artwork_info($artworkID);
if($artwork_info === false) die("Invalid Artwork ID");

$token = isset($_REQUEST['token']) ? $_REQUEST['token'] : (isset($_SESSION['token'])?$_SESSION['token']:"");
if(!empty($token)) $_SESSION['token'] = $token;
$query = sprintf("SELECT id
				FROM artwork_guests
				WHERE artwork_id = %d
				AND token = '%s'
				LIMIT 1",
				$artworkID,
				mysql_real_escape_string($token));
$result = mysql_query($query,$conn) or die(mysql_error());

if(mysql_num_rows($result)) {
	$is_guest = 1;
} else {
	$access = array("system","login");
	require_once(MODULES.'mod_authorise.php');
	$is_guest = 0;
}

$boxID = isset($_GET['boxID']) ? $_GET['boxID'] : 0;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$edit_page = isset($_GET['edit_page']) ? $_GET['edit_page'] : $page;
$query = sprintf("SELECT boxes.Left, boxes.Right, boxes.Top, boxes.Bottom, boxes.Angle, 
                                boxes.Grouped, boxes.order, boxes.Type, boxes.BoxUID, boxes.StoryRef,
				box_properties.lock, box_properties.resize,
				box_overflows.overflow
				FROM boxes
				LEFT JOIN box_properties ON (box_properties.box_id = boxes.uID AND box_properties.task_id = 0)
				LEFT JOIN box_overflows ON (box_overflows.box_id = boxes.uID AND box_overflows.task_id = 0)
				WHERE boxes.uID  = %d
				LIMIT 1",
				$boxID);
$result = mysql_query($query, $conn) or die(mysql_error());
if(!mysql_num_rows($result)) die("Invalid Box");
$row = mysql_fetch_assoc($result);
$left = $row['Left'];
$right = $row['Right'];
$top = $row['Top'];
$bottom = $row['Bottom'];
$angle = $row['Angle'];
$grouped = $row['Grouped'];
$order = $row['order'];
$type = $row['Type'];
$lock = $row['lock'];
$resize = $row['resize'];
$overflow = $row['overflow'];
$box_uid = $row['BoxUID'];
$story_ref = $row['StoryRef'];
//get updated geometry info
$geo = $DB->GetBoxMoves($artworkID,$boxID);
if($geo) {
	$left = $geo['left'];
	$right = $geo['right'];
	$top = $geo['top'];
	$bottom = $geo['bottom'];
	$angle = $geo['angle'];
}

$IM = new ImageManager();
$content = $IM->GetImageContent($artworkID,$boxID);
?>
<form
	action="index.php?layout=amend&id=<?php echo $artworkID; ?>&page=<?php echo $page; ?>"
	name="amendForm"
	method="POST"
	enctype="multipart/form-data"
>
	<?php
		echo '<div>';
		if($grouped) BuildTipMsg($lang->display('Group Alert'));
		if($type == "TEXT") {
			$TheBoxID = $DB->GetTheBox($boxID);
			echo '<div id="sentencesTable" style="width:100%;">';
			echo '<div id="sentencesBox" style="max-height:420px;overflow:auto;">';
			echo '<table width="100%" cellspacing="0" cellpadding="3" border="0">';
			echo '<tr>';
			echo '<th width="7%">'.$lang->display('Order').'</th>';
			echo '<th width="5%" class="indicator"><img src="'.IMG_PATH.'ico_merge.png" title="'.$lang->display('Merge').'" /></th>';
			echo '<th width="75%" class="para">'.$lang->display('Paragraph').'</th>';
			echo '<th width="8%"></th>';
			echo '<th width="5%" class="indicator"><img src="'.IMG_PATH.'ico_ignore.png" title="'.$lang->display('Ignore').'" /></th>';
			echo '</tr>';
			$query_para = sprintf("SELECT paralinks.uID as ParalinkID, paralinks.ParaID, paralinks.StoryGroup,
								IF(para_orders.order IS NOT NULL, para_orders.order, paralinks.order) AS StoryOrder,
								paragraphs.ParaText
								FROM paralinks
								LEFT JOIN paragraphs ON paragraphs.uID = paralinks.ParaID
								LEFT JOIN para_orders ON ( para_orders.pl_id = paralinks.uID AND para_orders.task_id = 0 )
								WHERE paralinks.BoxID = %d
								AND paralinks.active = 1
								ORDER BY
								paralinks.StoryGroup ASC,
								StoryOrder ASC,
								paralinks.uID ASC",
								$TheBoxID);
			$result_para = mysql_query($query_para, $conn) or die(mysql_error());
			if(mysql_num_rows($result_para)) {
				$Translator = new Translator();
				$oldSG=null;
				while($row_para = mysql_fetch_assoc($result_para)) {
					$PL = $row_para['ParalinkID'];
					$SG = $row_para['StoryGroup'];
					$ST = $DB->GetStorySum($SG);
					$SO = $DB->GetStoryOrder($PL);
					$SourceParaText = $row_para['ParaText'];
					$AmendedPara = $Translator->GetAmendedPara($PL);
					if($AmendedPara === false) {
						$Para = $SourceParaText;
					} else {
						$Para = $AmendedPara['ParaText'];
					}
					if($oldSG!=$SG){
						//next group
						$oldSG=$SG;
					}
					echo '<tr rel="'.$SG.'" id="paraRow_'.$PL.'" class="off">';
					echo '<td align="center" valign="middle" title="'.$lang->display('Order').'">';
					echo '<div id="story_order_'.$PL.'">';
					echo '<select
							class="input"
							onfocus="this.className=\'inputOn\'"
							onblur="this.className=\'input\'"
							name="order['.$PL.']"
							id="order['.$PL.']"
							onchange="ResetDiv(\'story_order_'.$PL.'\');DoAjax(\'pl='.$PL.'&no='.$ST.'&order=\'+this.value,\'story_order_'.$PL.'\',\'modules/mod_para_order.php\');">';
					BuildOrders($ST,$SO);
					echo '</select>';
					echo '</div>';
					echo '</td>';
					echo '<td align="center" valign="middle" class="indicator" title="'.$lang->display('Merge').'">';
					echo '<input type="checkbox" id="merge['.$PL.']" name="merge['.$PL.']" value="'.$SO.'" />';
					echo '</td>';
					echo '<td class="para">';
					echo '<div>'.html_display_para($SourceParaText).'</div>';
					echo '<textarea
							class="input"
							id="para['.$PL.'][0]"
							name="para['.$PL.'][0]"
							rows="1"
							cols="80"
							onkeydown="return catchTab(this,event);"
							onfocus="this.className=\'inputOn\';onandoff(\'para['.$PL.']\',\'paraRow_'.$PL.'\',\'statusChecker_'.$PL.'\');doResize(\'para['.$PL.']\',120);"
							onblur="this.className=\'input\';onandoff(\'para['.$PL.']\',\'paraRow_'.$PL.'\',\'statusChecker_'.$PL.'\');"';
					if($is_guest) echo 'disabled="disabled"';
					echo '>'."\n$Para".'</textarea>';
					echo '<div id="para_'.$PL.'"></div>';
					echo '<div id="history_'.$PL.'" class="autos"></div>';
					echo '</td>';
					echo '<td align="center" valign="middle">';
					echo '<p>';
					echo '<a href="javascript:void(0);" onclick="ResetDiv(\'window\');hidediv(\'helper\');SetClassName(\'guestTool\',\'pageToolOff\');SetClassName(\'amendTool\',\'pageToolOff\');SetClassName(\'commentTool\',\'pageToolOn\');display(\'pageColR\');ResetDiv(\'pageColR\');AjaxPost(\'artworkID='.$artworkID.'&page='.$page.'&edit_page='.$edit_page.'&boxID='.$boxID.'&comment='.urlencode("====================\n\"".$Para."\"\n====================\n").'&reset=0\',\'pageColR\',\'modules/mod_page_comments.php\');"><img src="'.IMG_PATH.'ico_comment.png" title="'.$lang->display('Add a Comment').'" /></a>';
					echo ' <a href="javascript:void(0);" onclick="ResetDiv(\'history_'.$PL.'\');DoAjax(\'PL='.$PL.'\',\'history_'.$PL.'\',\'modules/mod_art_amend_history.php\');"><img src="'.IMG_PATH.'ico_amend.png" title="'.$lang->display('Amended').'" /></a>';
					echo '</p>';
					echo '<p><a href="javascript:void(0);" onclick="if(confirm(\'Are you sure you want to split this paragraph?\')) splitPara(\'amendForm\',\'para\','.$PL.');"><img src="'.IMG_PATH.'ico_split.png" title="'.$lang->display('Split').'" /></a></p>';
					echo '</td>';
					echo '<td align="center" valign="middle" class="indicator" title="'.$lang->display('Ignore').'">';
					echo '<div id="ignore'.$PL.'">';
					echo '<input type="checkbox" id="ignore_check_'.$PL.'" name="ignore_check_'.$PL.'" value="1"';
					if($Translator->CheckParaIgnore($PL,$taskID)) {
						echo ' checked="checked"';
						$ignore = 0;
					} else {
						$ignore = 1;
					}
					echo ' onclick="ResetDiv(\'ignore'.$PL.'\');DoAjax(\'pl='.$PL.'&ignore='.$ignore.'\',\'ignore'.$PL.'\',\'modules/mod_para_ignore.php\');" />';
					echo '</div>';
					echo '</td>';
					echo '</tr>';
					//$counter++;
				}
			} else {
				echo '<tr><td colspan="4"><div class="alert">'.$lang->display('No paragraph in this textbox.').'</div></td></tr>';
			}
			echo '</table>';
			echo '</div>';
			echo '</div>';
		}
		if($type == "PICT") {
			echo '<a href="javascript:void(0);" onclick="ResetDiv(\'window\');hidediv(\'helper\');SetClassName(\'guestTool\',\'pageToolOff\');SetClassName(\'amendTool\',\'pageToolOff\');SetClassName(\'commentTool\',\'pageToolOn\');display(\'pageColR\');ResetDiv(\'pageColR\');AjaxPost(\'artworkID='.$artworkID.'&page='.$page.'&edit_page='.$edit_page.'&boxID='.$boxID.'&reset=0\',\'pageColR\',\'modules/mod_page_comments.php\');"><img src="'.IMG_PATH.'ico_comment.png" title="'.$lang->display('Add a Comment').'" /> '.$lang->display('Add a Comment').'</a>';
			if(!$is_guest) BuildImgOption($content);
		}
		echo '<div class="clear"></div>';
		echo '</div>';
	?>
	<?php if(!$is_guest) { ?>
	<div class="arrrgt" id="advanced" onclick="ChangeArrow('advanced');showandhide('advancedoptions');">
		<?php echo $lang->display('Advanced Options'); ?>
	</div>
	<div id="advancedoptions" class="greyBar" style="display:none;">
		<table width="100%" cellspacing="0" cellpadding="3" border="0">
			<tr>
				<td width="25%" class="highlight"><?php echo $lang->display('Left'); ?>:</td>
				<td width="25%">
					<input
						type="text"
						class="input"
						onfocus="this.className='inputOn'"
						onblur="this.className='input'"
						name="left"
						id="left"
						value="<?php echo $left; ?>"
						size="2"
						maxlength="4"
						<?php if($grouped) echo 'readonly'; ?>
					/>
				</td>
				<td width="25%" class="highlight"><?php echo $lang->display('Width'); ?>:</td>
				<td width="25%">
					<input
						type="text"
						class="input"
						onfocus="this.className='inputOn'"
						onblur="this.className='input'"
						name="boxwidth"
						id="boxwidth"
						value="<?php echo $right-$left; ?>"
						size="2"
						maxlength="4"
						<?php if($grouped) echo 'readonly'; ?>
					/>
				</td>
			</tr>
			<tr>
				<td class="highlight"><?php echo $lang->display('Top'); ?>:</td>
				<td>
					<input
						type="text"
						class="input"
						onfocus="this.className='inputOn'"
						onblur="this.className='input'"
						name="top"
						id="top"
						value="<?php echo $top; ?>"
						size="2"
						maxlength="4"
						<?php if($grouped) echo 'readonly'; ?>
					/>
				</td>
				<td class="highlight"><?php echo $lang->display('Height'); ?>:</td>
				<td>
					<input
						type="text"
						class="input"
						onfocus="this.className='inputOn'"
						onblur="this.className='input'"
						name="boxheight"
						id="boxheight"
						value="<?php echo $bottom-$top; ?>"
						size="2"
						maxlength="4"
						<?php if($grouped) echo 'readonly'; ?>
					/>
				</td>
			</tr>
			<tr>
				<td class="highlight"><?php echo $lang->display('Resize'); ?>:</td>
				<td>
					<input
						type="checkbox"
						name="resize"
						id="resize"
						value="1"
						<?php if($resize) echo 'checked="checked"'; ?>
					/>
					<?php if($overflow) echo ' <img src="'.IMG_PATH.'ico_error.png" title="'.$lang->display('Text Overflow').'">'; ?>
				</td>
				<td class="highlight"><?php echo $lang->display('Angle'); ?>:</td>
				<td>
					<input
						type="text"
						class="input"
						onfocus="this.className='inputOn'"
						onblur="this.className='input'"
						name="angle"
						id="angle"
						value="<?php echo $angle; ?>"
						size="2"
						maxlength="4"
						<?php if($grouped) echo 'readonly'; ?>
					/>
				</td>
			</tr>
			<tr>
				<td class="highlight"><?php echo $lang->display('Lock'); ?>:</td>
				<td>
					<input
						type="checkbox"
						name="lock"
						id="lock"
						value="1"
						<?php
							if($lock) echo 'checked="checked"';
							if($grouped) echo 'onclick="return false;"';
						?>
					/>
				</td>
				<td class="highlight"><?php echo $lang->display('Reference'); ?>:</td>
				<td><?php echo $box_uid; ?></td>
			</tr>
		</table>
                    <?php echo "Story Reference: $story_ref";?>
	</div>
	<div>
		<input
			type="checkbox"
			name="auto_refresh"
			id="auto_refresh"
			value="1"
			checked="checked"
		/>
		<?php echo $lang->display('Refresh Previews'); ?>
	</div>
	<div class="btnBar">
		<input
			type="button"
			class="btnDo"
			onmousemove="this.className='btnOn'"
			onmouseout="this.className='btnDo'"
			title="<?php echo $lang->display('Amend Artwork'); ?>"
			value="<?php echo $lang->display('Amend Artwork'); ?>"
			onclick="validateForm('left','Left','RisNum','boxwidth','Width','RisNum','top','Top','RisNum','boxheight','Height','RisNum'); if(document.returnValue) { SubmitForm('amendForm','amend');process_start('<?php echo $artwork_info['fileName']; ?>'); }"
		/>
		<input
			type="button"
			class="btnOff"
			onmousemove="this.className='btnOn'"
			onmouseout="this.className='btnOff'"
			title="<?php echo $lang->display('Restore'); ?>"
			value="<?php echo $lang->display('Restore'); ?>"
			onclick="if(confirm('Are you sure you want to restore all the amendments, merges and orders?')) { SubmitForm('amendForm','restore');process_start('<?php echo $artwork_info['fileName']; ?>'); }"
		/>
	</div>
	<?php } ?>
	<input type="hidden" name="box_id" value="<?php echo $boxID; ?>">
	<input type="hidden" name="form" id="form">
</form>