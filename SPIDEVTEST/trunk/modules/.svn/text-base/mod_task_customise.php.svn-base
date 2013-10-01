<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("artworks","edit");
require_once(MODULES.'mod_authorise.php');

$artworkID = isset($_GET['artworkID']) ? $_GET['artworkID'] : 0;
$taskID = isset($_GET['taskID']) ? $_GET['taskID'] : 0;
$boxID = isset($_GET['boxID']) ? $_GET['boxID'] : 0;
$page = isset($_GET['page']) ? $_GET['page'] : 1;

$query = sprintf("SELECT boxes.Left, boxes.Right, boxes.Top, boxes.Bottom, boxes.Angle, boxes.Grouped, boxes.order, boxes.Type, boxes.BoxUID,
				box_properties.lock, box_properties.resize,
				box_overflows.overflow
				FROM boxes
				LEFT JOIN box_properties ON (box_properties.box_id = boxes.uID AND box_properties.task_id = %d)
				LEFT JOIN box_overflows ON (box_overflows.box_id = boxes.uID AND box_overflows.task_id = %d)
				WHERE boxes.uID = %d
				LIMIT 1",
				$taskID,
				$taskID,
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
//get updated geometry info
$geo = $DB->GetBoxMoves($artworkID,$boxID,$taskID);
if($geo) {
	$left = $geo['left'];
	$right = $geo['right'];
	$top = $geo['top'];
	$bottom = $geo['bottom'];
	$angle = $geo['angle'];
}

$IM = new ImageManager();
$content = $IM->GetImageContent($artworkID,$boxID,$taskID);
?>
<form
	action="index.php?layout=customise&id=<?php echo $taskID; ?>&page=<?php echo $page; ?>"
	name="styleForm"
	method="POST"
	enctype="multipart/form-data"
>
	<?php
		echo '<div>';
		if($type == "PICT") BuildImgOption($content);
		echo '<div class="cllear"></div>';
		echo '<div>';
	?>
	<?php if($grouped) BuildTipMsg($lang->display('Group Alert'));?>
	<div class="arrrgt" id="advanced" onclick="ChangeArrow('advanced');showandhide('advancedoptions');">
		<?php echo $lang->display('Advanced Options'); ?>
	</div>
	<div id="advancedoptions" class="greyBar" style="display:none;">
		<table width="100%" cellspacing="0" cellpadding="3" border="0">
			<tr>
				<td width="25%" class="highlight"><?php echo $lang->display('Left'); ?></td>
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
						<?php if($grouped) echo 'disabled'; ?>
					/>
				</td>
				<td width="25%" class="highlight"><?php echo $lang->display('Width'); ?></td>
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
						<?php if($grouped) echo 'disabled'; ?>
					/>
				</td>
			</tr>
			<tr>
				<td class="highlight"><?php echo $lang->display('Top'); ?></td>
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
						<?php if($grouped) echo 'disabled'; ?>
					/>
				</td>
				<td class="highlight"><?php echo $lang->display('Height'); ?></td>
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
						<?php if($grouped) echo 'disabled'; ?>
					/>
				</td>
			</tr>
			<tr>
				<td class="highlight"><?php echo $lang->display('Resize'); ?></td>
				<td>
					<input
						name="resize"
						id="resize"
						type="checkbox"
						value="1"
						<?php if($resize) echo 'checked="checked"'; ?>
					/>
					<?php if($overflow) echo ' <img src="'.IMG_PATH.'ico_error.png" title="'.$lang->display('Text Overflow').'">'; ?>
				</td>
				<td class="highlight"><?php echo $lang->display('Angle'); ?></td>
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
						<?php if($grouped) echo 'disabled'; ?>
					/>
				</td>
			</tr>
			<tr>
				<td class="highlight"><?php echo $lang->display('Lock'); ?></td>
				<td>
					<input
						name="lock"
						id="lock"
						type="checkbox"
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
	</div>
	<div class="btnBar">
		<input
			type="button"
			class="btnDo"
			onmousemove="this.className='btnOn'"
			onmouseout="this.className='btnDo'"
			title="<?php echo $lang->display('Amend'); ?>"
			value="<?php echo $lang->display('Amend'); ?>"
			onclick="validateForm('left','Left','RisNum','boxwidth','Width','RisNum','top','Top','RisNum','boxheight','Height','RisNum'); if(document.returnValue) { SubmitForm('styleForm','amend'); }"
		/>
		<input
			type="button"
			class="btnOff"
			onmousemove="this.className='btnOn'"
			onmouseout="this.className='btnOff'"
			title="<?php echo $lang->display('Restore'); ?>"
			value="<?php echo $lang->display('Restore'); ?>"
			onclick="SubmitForm('styleForm','restore');"
		/>
	</div>
	<input type="hidden" name="box_id" value="<?php echo $boxID; ?>">
	<input type="hidden" name="form" id="form">
</form>