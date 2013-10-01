<?php
BuildHelperDiv($info['campaignName']);
$navStatus = array("campaigns");
require_once(MODULES.'mod_header.php');
BuildPageIntro($lang->display('Upload Summary'));
?>
<div id="wrapperWhite">
	<div class="controlScroll">
		<div class="controlselectScroll">
			<!-- Toolbar -->
			<div class="toolbar">
				<div class="title">
					<div class="ico">
						<?php echo '<img src="'.IMG_PATH.'header/ico_campaign.png">'; ?>
					</div>
					<div class="txt">
						<?php
							echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=campaigns\');">'.$lang->display('Campaigns').'</a>';
							echo ' <img src="'.IMG_PATH.'arrow_right.png"> ';
							echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=campaign&id='.$info['campaign_id'].'\');">'.$info['campaignName'].'</a>';
							echo ' <img src="'.IMG_PATH.'arrow_right.png"> '.$lang->display('Upload Summary'); ?>
					</div>
				</div>
				<div class="options">
					<!-- Close -->
						<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Close'); ?>">
							<a href="javascript:void(0);" onclick="SubmitForm('listform','close');">
								<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_close.png">'; ?></div>
								<div><?php echo $lang->display('Close'); ?></div>
							</a>
						</div>
					</div>
				<div class="clear"></div>
			</div>
			<!-- Mainwrap -->
			<div class="mainwrap">
				<form
					id="listform"
					name="listform"
					action="index.php?layout=<?php echo $layout; ?>&id=<?php echo $id; ?>"
					method="POST"
					enctype="multipart/form-data"
					onsubmit="Popup('loadingme','waiting');"
				>
				<div class="option">
					<div class="left">
						<?php
							echo $lang->display('Time Start').': '.date(FORMAT_TIME,$info['time_start']);
							echo '<span class="span"></span>';
							echo $lang->display('Time End').':'.date(FORMAT_TIME,$info['time_end']);
						?>
					</div>
					<div class="right">
						<?php echo $lang->display('Uploaded by').': '.$info['username'].' ['.$info['forename'].' '.$info['surname'].']'; ?>
					</div>
					<div class="clear"></div>
				</div>
				<div class="list">
					<table width="100%" cellpadding="5" cellspacing="0" border="0">
						<tr>
							<th width="2%">#</th>
							<th width="28%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','filename','<?php echo $pre; ?>');">
									<?php echo $lang->display('File Name'); ?>
								</a>
							</th>
							<th width="8%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','pages','<?php echo $pre; ?>');">
									<?php echo $lang->display('Pages'); ?>
								</a>
							</th>
							<th width="8%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','words','<?php echo $pre; ?>');">
									<?php echo $lang->display('Words'); ?>
								</a>
							</th>
							<th width="12%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','time_start','<?php echo $pre; ?>');">
									<?php echo $lang->display('Time Start'); ?>
								</a>
							</th>
							<th width="12%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','time_end','<?php echo $pre; ?>');">
									<?php echo $lang->display('Time End'); ?>
								</a>
							</th>
							<th width="8%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','progress','<?php echo $pre; ?>');">
									<?php echo $lang->display('Progress'); ?>
								</a>
							</th>
							<th width="20%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','error','<?php echo $pre; ?>');">
									<?php echo $lang->display('Status'); ?>
								</a>
							</th>
							<th width="2%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','id','<?php echo $pre; ?>');">ID</a>
							</th>
						</tr>
					<?php
						$query = sprintf("SELECT artwork_upload_log.*, errors.error, artworks.pageCount, artworks.wordCount
										FROM artwork_upload_log
										LEFT JOIN artworks ON artwork_upload_log.artwork_id = artworks.artworkID
										LEFT JOIN errors ON artwork_upload_log.error_id = errors.id
										WHERE artwork_upload_log.upload_id = %d
										ORDER BY `%s` %s
										LIMIT %d
										OFFSET %d",
										$id,
										mysql_real_escape_string($by),
										mysql_real_escape_string($order),
										$limit,
										$offset);
						$result = mysql_query($query, $conn) or die(mysql_error());
						$counter = 1;
						while($row = mysql_fetch_assoc($result)) {
							$style = $counter%2==0 ? 'even': 'odd';
							echo '<tr class="'.$style.'" onmouseover="this.className=\'hover\'" onmouseout="this.className=\''.$style.'\'"';
							if(!empty($row['artwork_id'])) echo ' onclick="goToURL(\'parent\',\'index.php?layout=artwork&id='.$row['artwork_id'].'\');" style="cursor:pointer;" title="'.$lang->display('Artwork Details').'"';
							echo '>';
							echo '<td>'.$counter.'</td>';
							echo '<td>'.$row['filename'].'</td>';
							echo '<td>'.$row['pageCount'].'</td>';
							echo '<td>'.$row['wordCount'].'</td>';
							echo '<td>';
							if(!empty($row['time_start'])) echo date(FORMAT_TIME,$row['time_start']);
							echo '</td>';
							echo '<td>';
							if(!empty($row['time_end'])) echo date(FORMAT_TIME,$row['time_end']);
							echo '</td>';
							echo '<td>'.$row['progress'].'%</td>';
							echo '<td>';
							echo !empty($row['error_id']) ? '<img src="'.IMG_PATH.'ico_error.png" /> '.$lang->display($row['error']) : '<img src="'.IMG_PATH.'ico_enable.png" />';
							echo '</td>';
							echo '<td>'.$row['id'].'</td>';
							echo '</tr>';
							$counter++;
						}
					?>
					</table>
				</div>
				<?php require_once(MODULES.'mod_list_nav.php'); ?>
				<input type="hidden" name="by" id="by" value="<?php echo $by; ?>">
				<input type="hidden" name="order" id="order" value="<?php echo $order; ?>">
				<input type="hidden" name="form" id="form">
				</form>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="javascripts/ajax.js"></script>
<script type="text/javascript" src="javascripts/process.js"></script>
<script type="text/javascript" src="javascripts/datepicker.js"></script>
<script type="text/javascript" src="javascripts/dom_drag.js"></script>
<script language="javascript">Drag.init(document.getElementById('handle'),document.getElementById('root'));</script>