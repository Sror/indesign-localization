<div>
	<div class="artworkTable">
		<div class="languageTable">
			<table cellspacing="0" cellpadding="0" border="0">
				<tr class="title">
					<th width="2%" title="#" align="center">
						<a href="javascript:void(0);">#</a>
					</th>
					<th title="<?php echo $lang->display('Campaign Title'); ?>">
						<a href="javascript:void(0);" onclick="SetOrder('taskform','campaignName','<?php echo $preorder; ?>');">
							<div class="icon"><img src="<?php echo IMG_PATH; ?>ico_campaign.png" /></div>
							<div class="heading"><?php echo $lang->display('Campaign Title'); ?></div>
						</a>
					</th>
					<th title="<?php echo $lang->display('Brand Name'); ?>">
						<a href="javascript:void(0);" onclick="SetOrder('taskform','brandName','<?php echo $preorder; ?>');">
							<div class="icon"><img src="<?php echo IMG_PATH; ?>ico_brand.png" /></div>
							<div class="heading"><?php echo $lang->display('Brand Name'); ?></div>
						</a>
					</th>
					<th title="<?php echo $lang->display('Reference'); ?>">
						<a href="javascript:void(0);" onclick="SetOrder('taskform','ref','<?php echo $preorder; ?>');">
							<div class="icon"><img src="<?php echo IMG_PATH; ?>ico_notes.png" /></div>
							<div class="heading"><?php echo $lang->display('Reference'); ?></div>
						</a>
					</th>
					<th title="<?php echo $lang->display('Artwork Title'); ?>">
						<a href="javascript:void(0);" onclick="SetOrder('taskform','artworkName','<?php echo $preorder; ?>');">
							<div class="icon"><img src="<?php echo IMG_PATH; ?>ico_artwork.png" /></div>
							<div class="heading"><?php echo $lang->display('Artwork Title'); ?></div>
						</a>
					</th>
					<th title="<?php echo $lang->display('Task Description'); ?>">
						<a href="javascript:void(0);" onclick="SetOrder('taskform','sourceLang','<?php echo $preorder; ?>');">
							<div class="icon"><img src="<?php echo IMG_PATH; ?>ico_desc.png" /></div>
							<div class="heading"><?php echo $lang->display('Task Description'); ?></div>
						</a>
					</th>
					<th title="<?php echo $lang->display('Translator'); ?>">
						<a href="javascript:void(0);" onclick="SetOrder('taskform','tforename','<?php echo $preorder; ?>');">
							<div class="icon"><img src="<?php echo IMG_PATH; ?>ico_user.png" /></div>
							<div class="heading"><?php echo $lang->display('Translator'); ?></div>
						</a>
					</th>
					<th title="<?php echo $lang->display('Deadline'); ?>">
						<a href="javascript:void(0);" onclick="SetOrder('taskform','deadline','<?php echo $preorder; ?>');">
							<div class="icon"><img src="<?php echo IMG_PATH; ?>ico_clock.png" /></div>
							<div class="heading"><?php echo $lang->display('Deadline'); ?></div>
						</a>
					</th>
					<th title="<?php echo $lang->display('Task Manager'); ?>">
						<a href="javascript:void(0);" onclick="SetOrder('taskform','mforename','<?php echo $preorder; ?>');">
							<div class="icon"><img src="<?php echo IMG_PATH; ?>ico_owner.png" /></div>
							<div class="heading"><?php echo $lang->display('Task Manager'); ?></div>
						</a>
					</th>
					<th title="<?php echo $lang->display('Progress'); ?>">
						<a href="javascript:void(0);" onclick="SetOrder('taskform','missingWords','<?php echo $preorder; ?>');">
							<div class="icon"><img src="<?php echo IMG_PATH; ?>ico_progress.png" /></div>
							<div class="heading"><?php echo $lang->display('Progress'); ?></div>
						</a>
					</th>
					<th colspan="2" title="<?php echo $lang->display('Task Status'); ?>">
						<a href="javascript:void(0);" onclick="SetOrder('taskform','taskStatus','<?php echo $preorder; ?>');">
							<div class="icon"><img src="<?php echo IMG_PATH; ?>ico_status.png" /></div>
							<div class="heading"><?php echo $lang->display('Task Status'); ?></div>
						</a>
					</th>
					<th width="2%" title="ID" align="center">
						<a href="javascript:void(0);" onclick="SetOrder('taskform','taskID','<?php echo $preorder; ?>');">ID</a>
					</th>
				</tr>
				<?php
					switch($view) {
						case "intray":
							$intray_str = "0";
							foreach($mytasks as $mytask) {
								$intray_str .= ",$mytask";
							}
							$query = sprintf("SELECT tasks.*,
											U1.forename AS tforename, U1.surname AS tsurname,
											U2.forename AS mforename, U2.surname AS msurname,
											status.*,
											artworks.artworkName,
											campaigns.campaignID, campaigns.campaignName, campaigns.ref,
											brands.brandName,
											L1.flag AS sourceFlag, L1.languageName AS sourceLang,
											L2.flag AS desiredFlag, L2.languageName AS targetLang
											FROM tasks
											LEFT JOIN users U1 ON tasks.translatorID = U1.userID
											LEFT JOIN users U2 ON tasks.creatorID = U2.userID
											LEFT JOIN status ON tasks.taskStatus = status.statusID
											LEFT JOIN artworks ON tasks.artworkID = artworks.artworkID
											LEFT JOIN campaigns ON artworks.campaignID = campaigns.campaignID
											LEFT JOIN brands ON campaigns.brandID = brands.brandID
											LEFT JOIN languages L1 ON campaigns.sourceLanguageID = L1.languageID
											LEFT JOIN languages L2 ON tasks.desiredLanguageID = L2.languageID
											WHERE tasks.taskID IN (%s)
											ORDER BY %s %s",
											$intray_str,
											mysql_real_escape_string($by),
											mysql_real_escape_string($order));
							break;
						case "outtray":
							$query_outtray = sprintf("SELECT tasks.taskID
													FROM tasks
													LEFT JOIN artworks ON tasks.artworkID = artworks.artworkID
													LEFT JOIN campaigns ON artworks.campaignID = campaigns.campaignID
													WHERE
													(
													(tasks.translatorID = %d AND tasks.taskStatus > 7)
													OR
													( (tasks.creatorID = %d OR tasks.agentID = %d) AND tasks.taskStatus > 9)
													)
													AND campaigns.campaignStatus = %d
													AND artworks.live = %d",
													$_SESSION['userID'],
													$_SESSION['userID'],
													$_SESSION['userID'],
													STATUS_ACTIVE,
													STATUS_ACTIVE);
							$result_outtray = mysql_query($query_outtray,$conn) or die(mysql_error());
							$outtasks = array();
							while($row_outtray = mysql_fetch_assoc($result_outtray)) {
								$outtasks[] = $row_outtray['taskID'];
							}
							$query_proofread = sprintf("SELECT task_proofreaders.task_id
														FROM task_proofreaders
														LEFT JOIN tasks ON task_proofreaders.task_id = tasks.taskID
														LEFT JOIN artworks ON tasks.artworkID = artworks.artworkID
														LEFT JOIN campaigns ON artworks.campaignID = campaigns.campaignID
														WHERE task_proofreaders.user_id = %d
														AND tasks.taskStatus > 7
														AND task_proofreaders.done > 0
														AND campaigns.campaignStatus = %d
														AND artworks.live = %d",
														$_SESSION['userID'],
														STATUS_ACTIVE,
														STATUS_ACTIVE);
							$result_proofread = mysql_query($query_proofread,$conn) or die(mysql_error());
							while($row_proofread = mysql_fetch_assoc($result_proofread)) {
								if(in_array($row_proofread['task_id'],$outtasks)) continue;
								$outtasks[] = $row_proofread['task_id'];
							}
							$outtray_str = "0";
							foreach($outtasks as $outtask) {
								$outtray_str .= ",$outtask";
							}
							$query = sprintf("SELECT tasks.*,
											U1.forename AS tforename, U1.surname AS tsurname,
											U2.forename AS mforename, U2.surname AS msurname,
											status.*,
											artworks.artworkName,
											campaigns.campaignID, campaigns.campaignName, campaigns.ref,
											brands.brandName,
											L1.flag AS sourceFlag, L1.languageName AS sourceLang,
											L2.flag AS desiredFlag, L2.languageName AS targetLang
											FROM tasks
											LEFT JOIN users U1 ON tasks.translatorID = U1.userID
											LEFT JOIN users U2 ON tasks.creatorID = U2.userID
											LEFT JOIN status ON tasks.taskStatus = status.statusID
											LEFT JOIN artworks ON tasks.artworkID = artworks.artworkID
											LEFT JOIN Campaigns ON artworks.campaignID = campaigns.campaignID
											LEFT JOIN brands ON campaigns.brandID = brands.brandID
											LEFT JOIN languages L1 ON campaigns.sourceLanguageID = L1.languageID
											LEFT JOIN languages L2 ON tasks.desiredLanguageID = L2.languageID
											WHERE tasks.taskID IN (%s)
											ORDER BY %s %s",
											mysql_real_escape_string($outtray_str),
											mysql_real_escape_string($by),
											mysql_real_escape_string($order));
							break;
					}
					$result = mysql_query($query, $conn) or die(mysql_error());
					$counter = 0;
					while($row = mysql_fetch_assoc($result)) {
						$counter++;
						$style = ($counter%2==0) ? 'even' : 'odd';
				?>
				<tr class="<?php echo $style; ?>" onmouseover="this.className='hover'" onmouseout="this.className='<?php echo $style; ?>'" title="<?php echo $lang->display('Go to Task'); ?>" onclick="goToURL('parent','index.php?layout=task&id=<?php echo $row['taskID']; ?>');" style="cursor:pointer;">
					<td align="center"><?php echo $counter; ?></td>
					<td><?php echo DisplayString($row['campaignName']); ?></td>
					<td><?php echo $row['brandName']; ?></td>
					<td><?php echo $row['ref']; ?></td>
					<td><?php echo DisplayString($row['artworkName']); ?></td>
					<td>
						<?php
							echo "<img src=\"images/flags/".$row['sourceFlag']."\" title=\"".$lang->display($row['sourceLang'])."\" /> <img src=\"".IMG_PATH."flag_to.gif\" title=\"".$lang->display('to be translated to')."\" /><img src=\"images/flags/".$row['desiredFlag']."\" title=\"".$lang->display($row['targetLang'])."\" />";
							if(!empty($row['trial'])) echo '<span class="span"></span><img src="'.IMG_PATH.'ico_error.png" title="'.$lang->display('This is a trial run that only deals with headings.').'" />';
						?>
					</td>
					<td>
						<?php
							echo '<div>'.$row['tforename']." ".$row['tsurname'].'</div>';
							echo '<div';
							if(strtotime($row['tdeadline']) <= time()+DAYS_AS_URGENT*24*60*60) echo ' class="red"';
							echo '>';
							if(!empty($row['tdeadline'])) echo date(FORMAT_DATE,strtotime($row['tdeadline']));
							echo '</div>';
						?>
					</td>
					<td>
						<?php
							echo '<div';
							if(strtotime($row['deadline']) <= time()+DAYS_AS_URGENT*24*60*60) echo ' class="red"';
							echo '>';
							echo !empty($row['deadline']) ? date(FORMAT_DATE,strtotime($row['deadline'])) : '<i>'.$lang->display('N/S').'</i>' ;
							echo '</div>';
						?>
					</td>
					<td><?php echo $row['mforename']." ".$row['msurname']; ?></td>
					<td><?php BuildTaskProgressBar($row['taskID'])?></td>
					<td align="center"><?php BuildTaskStatusIcon($row['taskStatus']); ?></td>
					<td><?php echo $lang->display($row['statusInfo']); ?></td>
					<td align="center"><?php echo $row['taskID']; ?></td>
				</tr>
				<?php
					}
					if($counter == 0) {
						echo "<tr><td colspan=\"12\" align=\"center\"><i>".$lang->display('No Task')."<i></td></tr>";
					}
				?>
			</table>
		</div>
	</div>
</div>