<?php
BuildHelperDiv($lang->display('Message Box'));
$navStatus = array();
require_once(MODULES.'mod_header.php');
BuildPageIntro($lang->display('Message Box'),$lang->display('Message Box Intro'));
?>
<div id="wrapperWhite">
	<div class="controlScroll">
		<div class="controlselectScroll">
			<!-- Toolbar -->
			<div class="toolbar">
				<div class="title">
					<div class="ico">
						<?php echo '<img src="'.IMG_PATH.'header/ico_'.$folder.'.png">'; ?></div>
					<div class="txt"><?php echo $lang->display(ucfirst($folder)); ?></div>
				</div>
				<div class="options">
					<!-- Compose -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Compose'); ?>">
						<a href="javascript:void(0);" onclick="Popup('helper','blur');DoAjax('','window','modules/mod_pm_new.php');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_compose.png">'; ?></div>
							<div><?php echo $lang->display('Compose'); ?></div>
						</a>
					</div>
					<!-- View -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('View'); ?>">
						<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) SubmitForm('listform','view');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_view.png">'; ?></div>
							<div><?php echo $lang->display('View'); ?></div>
						</a>
					</div>
					<?php if($folder != "trashed") { ?>
					<!-- Trash -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Trash'); ?>">
						<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) SubmitForm('listform','trash');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_trash.png">'; ?></div>
							<div><?php echo $lang->display('Trash'); ?></div>
						</a>
					</div>
					<?php } else { ?>
					<!-- Restore -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Restore'); ?>">
						<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) { SubmitForm('listform','restore'); }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_restore.png">'; ?></div>
							<div><?php echo $lang->display('Restore'); ?></div>
						</a>
					</div>
					<?php } ?>
				</div>
				<div class="clear"></div>
			</div>
			<!-- Mainwrap -->
			<div class="mainwrap">
				<form
					id="listform"
					name="listform"
					action="index.php?layout=<?php echo $layout; ?>"
					method="POST"
					enctype="multipart/form-data"
					onsubmit="Popup('loadingme','waiting');"
				>
				<div class="option">
					<div class="left">
						<?php require_once(MODULES.'mod_list_search.php'); ?>
					</div>
					<div class="right">
						<div class="filter">
							<select
								class="input"
								onfocus="this.className='inputOn'"
								onblur="this.className='input'"
								name="filter_folder"
								id="filter_folder"
								onchange="SubmitForm('listform','');"
								title="<?php echo $lang->display('Please Select'); ?>"
							>
							<?php BuildMsgFolderList($folder); ?>
							</select>
						</div>
					</div>
					<div class="clear"></div>
				</div>
				<div class="list">
					<table id="listview" width="100%" cellpadding="5" cellspacing="0" border="0">
						<tr>
							<th width="2%">#</th>
							<th width="2%">
								<input type="checkbox" class="checkbox" name="checkall" id="checkall" onclick="jQueryCheckAll('listform',this.id,'#listview tr');">
							</th>
							<th width="4%" align="center">
								<a href="javascript:void(0);" onclick="SetOrder('listform','readStatus','<?php echo $pre; ?>');">
									<?php echo $lang->display('Read'); ?>
								</a>
							</th>
							<th width="15%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','s_forename','<?php echo $pre; ?>');">
									<?php echo $lang->display('From'); ?>
								</a>
							</th>
							<th width="15%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','r_forename','<?php echo $pre; ?>');">
									<?php echo $lang->display('To'); ?>
								</a>
							</th>
							<th width="45%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','subject','<?php echo $pre; ?>');">
									<?php echo $lang->display('Subject'); ?>
								</a>
							</th>
							<th width="15%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','messageTime','<?php echo $pre; ?>');">
									<?php echo $lang->display('Timestamp'); ?>
								</a>
							</th>
							<th width="2%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','id','<?php echo $pre; ?>');">ID</a>
							</th>
						</tr>
						<?php
							$query = sprintf("SELECT messages.messageID AS id, messages.readStatus,
											messages.subject, messages.messageTime AS time,
											U1.username as s_username, U1.forename as s_forename, U1.surname as s_surname,
											U2.username as r_username, U2.forename as r_forename, U2.surname as r_surname
											FROM messages
											LEFT JOIN users U1 ON messages.senderID = U1.userID
											LEFT JOIN users U2 ON messages.receiverID = U2.userID
											WHERE %s
											AND (messages.subject LIKE '%s'
											OR U1.username LIKE '%s'
											OR U1.forename LIKE '%s'
											OR U1.surname LIKE '%s'
											OR U2.username LIKE '%s'
											OR U2.forename LIKE '%s'
											OR U2.surname LIKE '%s')
											ORDER BY `%s` %s
											LIMIT %d
											OFFSET %d",
											$sub,
											"%".mysql_real_escape_string($keyword)."%",
											"%".mysql_real_escape_string($keyword)."%",
											"%".mysql_real_escape_string($keyword)."%",
											"%".mysql_real_escape_string($keyword)."%",
											"%".mysql_real_escape_string($keyword)."%",
											"%".mysql_real_escape_string($keyword)."%",
											"%".mysql_real_escape_string($keyword)."%",
											mysql_real_escape_string($by),
											mysql_real_escape_string($order),
											$limit,
											$offset);
							$result = mysql_query($query, $conn) or die(mysql_error());
							if(mysql_num_rows($result)) {
								$counter = $offset+1;
								while($row = mysql_fetch_assoc($result)) {
									$style = $counter%2==0 ? 'even' : 'odd';
									echo '<tr class="'.$style.'">';
									echo '<td>'.$counter.'</td>';
									echo '<td><input type="checkbox" class="checkbox" name="id[]" id="id[]" value="'.$row['id'].'"></td>';
									echo '<td align="center">';
									if($row['readStatus']==0) {
										echo '<img src="'.IMG_PATH.'ico_message_new.gif" title="'.$lang->display('Unread').'">';
									} else {
										echo '<img src="'.IMG_PATH.'ico_message_read.gif" title="'.$lang->display('Read').'">';
									}
									echo '</td>';
									echo '<td>'.$row['s_forename'].' '.$row['s_surname'].'</td>';
									echo '<td>'.$row['r_forename'].' '.$row['r_surname'].'</td>';
									echo '<td><a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=inbox&task=view&id='.$row['id'].'\');">';
									if(empty($row['subject'])) {
										echo '<i>('.$lang->display('No Subject').')</i>';
									} else {
										echo $row['subject'];
									}
									echo '</td>';
									echo '<td>'.date(FORMAT_TIME,strtotime($row['time'])).'</td>';
									echo '<td>'.$row['id'].'</td>';
									echo '</tr>';
									$counter++;
								}
							} else {
								echo '<tr><td colspan="8" align="center"><i>'.$lang->display('You have no message in this folder.').'</i></td></tr>';
							}
						?>
						<input type="hidden" name="by" id="by" value="<?php echo $by; ?>">
						<input type="hidden" name="order" id="order" value="<?php echo $order; ?>">
					</table>
				</div>
				<?php require_once(MODULES.'mod_list_nav.php'); ?>
				<input type="hidden" name="form" id="form">
				</form>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="javascripts/ajax.js"></script>
<script type="text/javascript" src="javascripts/dom_drag.js"></script>
<script language="javascript">Drag.init(document.getElementById('handle'),document.getElementById('root'));</script>