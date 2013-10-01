<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("system","login");
require_once(MODULES.'mod_authorise.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$query_task = sprintf("SELECT tasks.*,
					artworks.artworkID, artworks.artworkName, artworks.version, artworks.pageCount, artworks.subjectID, artworks.wordCount,
					campaigns.campaignID, campaigns.campaignName, campaigns.ref,
					service_engines.name AS serviceName, service_engines.ext AS serviceExt,
					brands.brandName,
					L1.languageName AS source_lang_name, L1.flag AS source_flag,
					L2.languageName AS target_lang_name, L2.flag AS target_flag,
					U1.userID AS cuid, U1.forename AS cforename, U1.surname AS csurname, U1.email AS cemail,
					U2.userID AS tuid, U2.forename AS tforename, U2.surname AS tsurname, U2.email AS temail,
					U3.userID AS auid, U3.forename AS aforename, U3.surname AS asurname, U3.email AS aemail,
					companies.companyName AS agency,
					status.statusInfo
					FROM tasks
					LEFT JOIN artworks ON tasks.artworkID = artworks.artworkID
					LEFT JOIN campaigns ON artworks.campaignID = campaigns.campaignID
					LEFT JOIN service_engines ON artworks.artworkType = service_engines.id
					LEFT JOIN subjects ON artworks.subjectID = subjects.subjectID
					LEFT JOIN brands ON campaigns.brandID = brands.brandID
					LEFT JOIN languages L1 ON campaigns.sourceLanguageID = L1.languageID
					LEFT JOIN languages L2 ON tasks.desiredLanguageID = L2.languageID
					LEFT JOIN users U1 ON tasks.creatorID = U1.userID
					LEFT JOIN users U2 ON tasks.translatorID = U2.userID
					LEFT JOIN users U3 ON tasks.agentID = U3.userID
					LEFT JOIN companies ON U3.companyID = companies.companyID
					LEFT JOIN status ON tasks.taskStatus = status.statusID
					WHERE tasks.taskID = %d
					LIMIT 1",
					$id);
$result_task = mysql_query($query_task, $conn) or die(mysql_error());
if(!mysql_num_rows($result_task)) die("Invalid Task");
$row_task = mysql_fetch_assoc($result_task);
?>
<div class="mainwrap">
	<div class="list">
		<table width="100%" border="0" cellspacing="0" cellpadding="5">
			<tr class="even" onmouseover="this.className='odd'" onmouseout="this.className='even'">
				<td class="highlight"><?php echo $lang->display('Campaign Title'); ?>:</td>
				<td><a href="javascript:void(0);" onclick="hidediv('helper');goToURL('parent','index.php?layout=campaign&id=<?php echo $row_task['campaignID']; ?>');"><?php echo $row_task['campaignName']; ?></a></td>
			</tr>
			<tr class="even" onmouseover="this.className='odd'" onmouseout="this.className='even'">
				<td class="highlight"><?php echo $lang->display('Brand Name'); ?>:</td>
				<td><?php echo $row_task['brandName']; ?></td>
			<tr class="even" onmouseover="this.className='odd'" onmouseout="this.className='even'">
				<td class="highlight"><?php echo $lang->display('Reference'); ?>:</td>
				<td><?php echo !empty($row_task['ref']) ? $row_task['ref'] : '<i>'.$lang->display('N/S').'</i>'; ?></td>
			</tr>
			<tr class="even" onmouseover="this.className='odd'" onmouseout="this.className='even'">
				<td class="highlight"><?php echo $lang->display('Artwork Title'); ?>:</td>
				<td><a href="javascript:void(0);" onclick="hidediv('helper');goToURL('parent','index.php?layout=artwork&id=<?php echo $row_task['artworkID']; ?>');"><?php echo $row_task['artworkName']; ?></a></td>
			</tr>
			<tr class="even" onmouseover="this.className='odd'" onmouseout="this.className='even'">
				<td class="highlight"><?php echo $lang->display('Version'); ?>:</td>
				<td><?php echo $row_task['version']; ?></td>
			</tr>
			<tr class="even" onmouseover="this.className='odd'" onmouseout="this.className='even'">
				<td class="highlight"><?php echo $lang->display('File Type'); ?>:</td>
				<td><?php echo $row_task['serviceName'].' ('.$row_task['serviceExt'].')'; ?></td>
			</tr>
			
			<tr onmouseover="display('font_substitue');" onmouseout="hidediv('font_substitue');">
			<td class="even" onmouseover="this.className='odd'" onmouseout="this.className='even'"><?php echo $lang->display('Fonts'); ?>:</td>
			<td>
			<?php
				$query = sprintf("SELECT artwork_fonts.font_id,
								fonts.family, fonts.name, fonts.installed
								FROM artwork_fonts
								LEFT JOIN fonts ON artwork_fonts.font_id = fonts.id
								WHERE artwork_fonts.artwork_id = %d
								ORDER BY fonts.name ASC",
								$row_task['artworkID']);
				$result = mysql_query($query, $conn) or die(mysql_error());
				echo '<div>';
				echo '<div class="left">';
				echo '<div id="font_usage" class="arrrgt" onclick="ChangeArrow(\'font_usage\');openandclose(\'font_list\');">'.mysql_num_rows($result).'</div>';
				echo '</div>';
				echo '<div class="right" id="font_substitue" style="display:none;">';
				echo '<a href="/index.php?layout=cp_font_sub&taskID='.$id.'&show=Used" title="'.$lang->display('Substitute').'"><img src="'.IMG_PATH.'ico_swap.png" /></a>';
				echo '</div>';
				echo '<div class="clear"></div>';
				echo '</div>';
				echo '<div id="font_list" style="display:none;max-height:200px;overflow:auto;">';
				echo '<table width="100%" cellspacing="0" cellpadding="3" border="0">';
				echo '<tr>';
				echo '<th width="10%" align="center">'.$lang->display('Status').'</th>';
				echo '<th width="45%">'.$lang->display('Original').'</th>';
				echo '<th width="45%">'.$lang->display('Current Font').'</th>';
				echo '</tr>';
				$count = 0;
				
				require_once(CLASSES.'/Font_Substitution.php');
				
				while($row = mysql_fetch_assoc($result)) {
					echo '<tr class="bgWhite" onmouseover="this.className=\'hover\'" onmouseout="this.className=\'bgWhite\'">';
					echo '<td align="center">';
					if($row['installed']) {
						echo '<img src="'.IMG_PATH.'ico_s_tick.png" title="'.$lang->display('Installed').'" /> ';
					} else {
						echo '<img src="'.IMG_PATH.'ico_error.png" title="'.$lang->display('Missing').'" /> ';
					}
					echo '</td>';
					echo '<td>';
					echo '('.$row['family'].') '.$row['name'] ;
					echo '</td>';
					echo '<td>';
					
					$sub_font_info = Font_Substitution::useFont($row['font_id'],$id,'task');
					$sub_font_id = $sub_font_info['font'];
					$sub_type = $sub_font_info['sub_type'];
					$substitute = $DB->get_font_info($sub_font_id);
					echo $sub_type.'('.$substitute['family'].') '.$substitute['name'] ;
					
					echo '</td>';
					echo '</tr>';
				}
				echo '</table>';
				echo '</div>';
				if($count) BuildTipMsg($lang->display('Missing').': '.$lang->display('Fonts').' ('.$count.')<span class="span">|</span><a href="javascript:void(0);" onclick="Popup(\'helper\',\'blur\');DoAjax(\'id='.$artworkID.'\',\'window\',\'modules/mod_art_fonts.php\');">'.$lang->display('Substitute').'</a><span class="span">|</span><a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=cp_font\');">'.$lang->display('Font Manager').'</a>');
			?>
			</td>
			
			<tr class="even" onmouseover="this.className='odd'" onmouseout="this.className='even'">
				<td class="highlight"><?php echo $lang->display('Subject'); ?>:</td>
				<td><?php echo !empty($row_task['subjectID']) ? $lang->display($row_task['subjectTitle']) : '<i>'.$lang->display('N/S').'</i>'; ?></td>
			</tr>
			<tr class="even" onmouseover="this.className='odd'" onmouseout="this.className='even'">
				<td class="highlight"><?php echo $lang->display('Pages'); ?>:</td>
				<td><?php echo $row_task['pageCount']; ?></td>
			</tr>
			<tr class="even" onmouseover="this.className='odd'" onmouseout="this.className='even'">
				<td class="highlight"><?php echo $lang->display('Word Count'); ?>:</td>
				<td><?php echo $row_task['wordCount']; ?></td>
			</tr>
			<tr class="even" onmouseover="this.className='odd'" onmouseout="this.className='even'">
				<td class="highlight"><?php echo $lang->display('Source Language'); ?>:</td>
				<td><?php echo '<img src="images/flags/'.$row_task['source_flag'].'" title="'.$lang->display($row_task['source_lang_name']).'" /> '.$lang->display($row_task['source_lang_name']); ?></td>
			</tr>
			<tr class="even" onmouseover="this.className='odd'" onmouseout="this.className='even'">
				<td class="highlight"><?php echo $lang->display('Desired Language'); ?>:</td>
				<td>
					<?php
						echo '<img src="images/flags/'.$row_task['target_flag'].'" title="'.$lang->display($row_task['target_lang_name']).'" /> '.$lang->display($row_task['target_lang_name']);
						if(!empty($row_task['trial'])) echo ' <img src="'.IMG_PATH.'ico_error.png" title="'.$lang->display('This is a trial run that only deals with headings.').'" />';
					?>
				</td>
			</tr>
			<tr class="even" onmouseover="this.className='odd'" onmouseout="this.className='even'">
				<td class="highlight"><?php echo $lang->display('Task Manager'); ?>:</td>
				<td><?php echo !empty($row_task['creatorID']) ? '<a href="javascript:void(0);" onclick="hidediv(\'helper\');goToURL(\'parent\',\'index.php?layout=user&id='.$row_task['cuid'].'\');">'.$row_task['cforename'].' '.$row_task['csurname'].'</a>' : '<i>'.$lang->display('N/S').'</i>'; ?></td>
			</tr>
			<tr class="even" onmouseover="this.className='odd'" onmouseout="this.className='even'">
				<td class="highlight"><?php echo $lang->display('Agent'); ?>:</td>
				<td>
					<?php
						echo !empty($row_task['agentID']) ? '<a href="javascript:void(0);" onclick="hidediv(\'helper\');goToURL(\'parent\',\'index.php?layout=user&id='.$row_task['auid'].'\');">'.$row_task['aforename'].' '.$row_task['asurname'].'</a> <span class="grey">'.$row_task['agency'].'</span>' : '<i>'.$lang->display('N/S').'</i>';
					?>
				</td>
			</tr>
			<tr class="even" onmouseover="this.className='odd'" onmouseout="this.className='even'">
				<td class="highlight"><?php echo $lang->display('Translator'); ?>:</td>
				<td>
					<?php
						echo !empty($row_task['translatorID']) ? '<a href="javascript:void(0);" onclick="hidediv(\'helper\');goToURL(\'parent\',\'index.php?layout=user&id='.$row_task['tuid'].'\');">'.$row_task['tforename'].' '.$row_task['tsurname'].'</a>' : '<i>'.$lang->display('N/S').'</i>';
						if(!empty($row_task['tdeadline'])) echo ' <span class="grey">'.date(FORMAT_DATE,strtotime($row_task['tdeadline'])).'</span>';
					?>
				</td>
			</tr>
			<tr class="even" onmouseover="this.className='odd'" onmouseout="this.className='even'">
				<td class="highlight" valign="top"><?php echo $lang->display('Proofreader'); ?>:</td>
				<td>
					<?php
						$query = sprintf("SELECT task_proofreaders.order, task_proofreaders.deadline, task_proofreaders.done,
												users.userID, users.forename, users.surname
												FROM task_proofreaders
												LEFT JOIN users ON task_proofreaders.user_id = users.userID
												WHERE task_proofreaders.task_id = %d
												ORDER BY task_proofreaders.order ASC, task_proofreaders.deadline ASC, users.forename ASC",
												$id);
						$result = mysql_query($query, $conn) or die(mysql_error());
						if(mysql_num_rows($result)) {
							while($row = mysql_fetch_assoc($result)) {
								echo '<div>';
								echo $row['order'].'. <a href="javascript:void(0);" onclick="hidediv(\'helper\');goToURL(\'parent\',\'index.php?layout=user&id='.$row['userID'].'\');">'.$row['forename'].' '.$row['surname'].'</a>';
								if(!empty($row['deadline'])) echo ' <span class="grey">'.date(FORMAT_DATE,strtotime($row['deadline'])).'</span>';
								if(!empty($row['done'])) echo ' <img src="'.IMG_PATH.'ico_enable.png" title="'.$lang->display('Done').'" />';
								echo '</div>';
							}
						} else {
							echo '<i>'.$lang->display('N/S').'</i>';
						}
					?>
				</td>
			</tr>
			<tr class="even" onmouseover="this.className='odd'" onmouseout="this.className='even'">
				<td class="highlight"><?php echo $lang->display('Deadline').' ('.$lang->display('Sign-off').')'; ?>:</td>
				<td <?php if(strtotime($row_task['deadline']) <= time()+DAYS_AS_URGENT*24*60*60) echo 'class="red"'; ?>>
					<?php echo date(FORMAT_DATE,strtotime($row_task['deadline'])); ?>
				</td>
			</tr>
            <tr class="even" onmouseover="this.className='odd'" onmouseout="this.className='even'">
				<td class="highlight"><?php echo $lang->display('Notes'); ?>:</td>
				<td><?php echo !empty($row_task['notes']) ? html_display_para($row_task['notes']) : '<i>'.$lang->display('N/S').'</i>'; ?></td>
			</tr>
			<tr class="even" onmouseover="this.className='odd'" onmouseout="this.className='even'">
				<td class="highlight"><?php echo $lang->display('Job Brief'); ?>:</td>
				<td><?php echo !empty($row_task['brief']) ? html_display_para($row_task['brief']) : '<i>'.$lang->display('N/S').'</i>'; ?></td>
			</tr>
			<tr class="even" onmouseover="this.className='odd'" onmouseout="this.className='even'">
				<td class="highlight"><?php echo $lang->display('Attachment'); ?>:</td>
				<td><?php echo !empty($row_task['attachment']) ? '<a href="download.php?attachment&File='.$row_task['attachment'].'&SaveAs='.$row_task['attachment'].'"><img src="'.IMG_PATH.'ico_attachment.png" title="'.$lang->display('Attachment').'">'.$row_task['attachment'].'</a>' : '<i>'.$lang->display('N/S').'</i>'; ?></td>
			</tr>
			<tr class="even" onmouseover="this.className='odd'" onmouseout="this.className='even'">
				<td class="highlight"><?php echo $lang->display('Task Status'); ?>:</td>
				<td>
					<?php BuildTaskStatusIcon($row_task['taskStatus']); ?>
				</td>
			</tr>
		</table>
	</div>
</div>