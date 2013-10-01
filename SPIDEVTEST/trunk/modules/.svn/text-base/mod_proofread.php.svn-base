<?php
require_once(dirname(__FILE__).'/../config.php');

$taskID = (isset($_GET['taskID'])) ? $_GET['taskID'] : 0;
$boxID = (isset($_GET['boxID'])) ? $_GET['boxID'] : 0;
$page = (isset($_GET['page'])) ? $_GET['page'] : 1;

$query_task = sprintf("SELECT tasks.artworkID, tasks.desiredLanguageID,
					artworks.subjectID, campaigns.brandID
					FROM tasks
					LEFT JOIN artworks ON tasks.artworkID = artworks.artworkID
					LEFT JOIN campaigns ON artworks.campaignID = campaigns.campaignID
					WHERE taskID = %d
					LIMIT 1",
					$taskID);
$result_task = mysql_query($query_task, $conn) or die(mysql_error());
if(!mysql_num_rows($result_task)) die("Invalid Token");
$row_task = mysql_fetch_assoc($result_task);
$artworkID = $row_task['artworkID'];
?>
<div id="sentencesTable" style="width:100%;">
	<div id="sentencesBox" style="max-height:210px;overflow:auto;">
		<table cellpadding="3" cellspacing="0" border="0">
			<tr>
				<th width="7%" class="indicator"><?php echo $lang->display('Order'); ?></th>
				<th width="86%" class="para"><?php echo $lang->display('Paragraph'); ?></th>
				<th width="7%" class="indicator"><?php echo $lang->display('Ignore'); ?></th>
			</tr>
			<?php
				$Translator = new Translator();
				$result_para = $Translator->get_all_paras($artworkID,$taskID,$boxID);
				$found_para = mysql_num_rows($result_para);
				if($found_para) {
					while($row_para = mysql_fetch_assoc($result_para)) {
						$PL = $row_para['PL'];
						$SO = $DB->GetStoryOrder($PL,$taskID);
						$para_row = $Translator->GetParaByPL($PL);
						if($para_row === false) {
							$ParaText = $row_para['ParaText'];
							$PG = $row_para['ParaGroup'];
						} else {
							$ParaText = $para_row['ParaText'];
							$PG = $para_row['ParaGroup'];
						}
			?>
			<tr id="<?php echo "paraRow$PL"; ?>" class="off">
				<td align="center" valign="middle" class="indicator">
					<div id="story_order_<?php echo $PL ?>">
						<select
							class="input"
							onfocus="this.className='inputOn'"
							onblur="this.className='input'"
							name="order[<?php echo $PL; ?>]"
							id="order[<?php echo $PL; ?>]"
							onchange="ResetDiv('story_order_<?php echo $PL ?>');DoAjax('pl=<?php echo $PL ?>&no=<?php echo $found_para ?>&task_id=<?php echo $taskID ?>&order='+this.value,'story_order_<?php echo $PL ?>','modules/mod_para_order.php');"
						>
						<?php BuildOrders($found_para,$SO); ?>
						</select>
					</div>
				</td>
				<td class="para">
					<div>
						<?php echo "<div>".html_display_para($ParaText)."</div>"; ?>
						<textarea
							class="input"
							id="<?php echo "paragraphBox$PL"; ?>"
							name="<?php echo "paragraphBox$PL"; ?>"
							onkeydown="return catchTab(this,event);"
							onfocus="this.className='inputOn';<?php echo "display('PTMDiv');tBox='paragraphBox$PL';onandoff('paragraphBox$PL','paraRow$PL','statusChecker$PL');ListTM($taskID,$PL,'TMDiv');doResize('paragraphBox$PL',120);"; ?>"
							onchange="<?php echo "SaveTranslation($taskID,$PL,this.value,".PARA_USER.");"; ?>"
							onblur="this.className='input';<?php echo "onandoff('paragraphBox$PL','paraRow$PL','statusChecker$PL');"; ?>"
							rows="1"
						><?php
							$query_trans = sprintf("SELECT paragraphs.ParaText
													FROM paratrans
													LEFT JOIN paragraphs ON paratrans.transParaID = paragraphs.uID
													WHERE paratrans.taskID = %d
													AND paratrans.ParalinkID = %d
													LIMIT 1",
													$taskID,
													$PL);
							$result_trans = mysql_query($query_trans, $conn) or die(mysql_error());
							if(mysql_num_rows($result_trans)) {
								$row_trans = mysql_fetch_assoc($result_trans);
								$trans_para = $row_trans['ParaText'];
								echo "\n$trans_para";
							} else {
								$trans_para = "";
							}
						?></textarea>
					</div>
				</td>
				<td align="center" valign="middle" class="indicator">
					<p id="ignore<?php echo $PL; ?>">
						<input type="checkbox" id="ignore_check_<?php echo $PL; ?>" name="ignore_check_<?php echo $PL; ?>" value="1" <?php if($Translator->CheckParaIgnore($PL,$taskID)) { echo ' checked="checked"';$ignore=0; } else { $ignore=1; } ?> onclick="ResetDiv('<?php echo "ignore$PL"; ?>');DoAjax('id=<?php echo $taskID; ?>&pl=<?php echo $PL; ?>&ignore=<?php echo $ignore; ?>','ignore<?php echo $PL; ?>','modules/mod_para_ignore.php');" />
					</p>
					<p>
						<a href="javascript:void(0);" onclick="ResetDiv('window');hidediv('helper');SetClassName('guestTool','pageToolOff');SetClassName('amendTool','pageToolOff');SetClassName('commentTool','pageToolOn');display('pageColR');ResetDiv('pageColR');AjaxPost('artworkID=<?php echo $artworkID; ?>&taskID=<?php echo $taskID; ?>&page=<?php echo $page; ?>&boxID=<?php echo $boxID; ?>&comment=<?php echo urlencode("====================\n$ParaText\n====================\n$trans_para"); ?>&reset=0','pageColR','modules/mod_page_comments.php');"><img src="<?php echo IMG_PATH; ?>ico_comment.png" title="<?php echo $lang->display('Add a Comment'); ?>" /></a>
					</p>
				</td>
			</tr>
			<?php
					}
				} else {
					echo "<tr><td colspan=\"3\" align=\"center\"><div class=\"alert\">".$lang->display('No paragraph in this textbox.')."</div></td></tr>";
				}
			?>
		</table>
	</div>
</div>
<div class="panelBox" id="PTMDiv" style="max-height:210px;overflow:auto;">
	<div class="titleBar">
		<div class="icon"><img src="<?php echo IMG_PATH; ?>ico_translation_memory.gif" title="<?php echo $lang->display('Translation Memory'); ?>" /></div>
		<div class="topic"><?php echo $lang->display('Translation Memory'); ?></div>
		<div class="arrow"><a href="javascript:hidediv('PTMDiv');"><img src="<?php echo IMG_PATH; ?>btn_close.png" title="<?php echo $lang->display('Close'); ?>"></a></div>
		<div class="clear"></div>
	</div>
	<div id="tabScroll">
		<div class="tabcontentstyle">
			<div id="TMDiv">
				<div style="padding:10px;"><?php echo $lang->display('Please click in the textbox to retrieve translation memory.'); ?></div>
				<!-- Translation memory will appear here. -->
			</div>
		</div>
	</div>
</div>
<div class="btnBar">
	<a href="javascript:void(0);" onclick="hidediv('helper');goToURL('parent','index.php?layout=task&id=<?php echo $taskID; ?>&page=<?php echo $page; ?>&do=refresh')">
		<img src="<?php echo IMG_PATH; ?>toolbar/ico_refresh.png" title="<?php echo $lang->display('Refresh'); ?>" />
	</a>
</div>