<?php
$navStatus = array("cpanel");
require_once(MODULES.'mod_header.php');
BuildPageIntro($lang->display('Control Panel'),$lang->display('CP Intro'));
?>
<div id="wrapperWhite">
	<div class="controlScroll">
		<div class="controlselectScroll">
			<!-- Toolbar -->
			<div class="toolbar">
				<div class="title">
					<div class="ico"><?php echo '<img src="'.IMG_PATH.'header/ico_info.png">'; ?></div>
					<div class="txt"><?php echo $lang->display('Service Information Manager'); ?></div>
				</div>
				<div class="options">
					<!-- Refresh -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Refresh'); ?>">
						<a href="javascript:void(0);" onclick="SubmitForm('listform','refresh');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_refresh.png">'; ?></div>
							<div><?php echo $lang->display('Refresh'); ?></div>
						</a>
					</div>
					<?php if($issuperadmin) { ?>
					<!-- Start -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Start'); ?>">
						<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) { SubmitForm('listform','start'); }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_start.png">'; ?></div>
							<div><?php echo $lang->display('Start'); ?></div>
						</a>
					</div>
					<!-- Pause -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Pause'); ?>">
						<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) { SubmitForm('listform','pause'); }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_pause.png">'; ?></div>
							<div><?php echo $lang->display('Pause'); ?></div>
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
					<?php require_once(MODULES.'mod_list_search.php'); ?>
				</div>
				<div class="list">
					<table id="listview" width="100%" cellpadding="5" cellspacing="0" border="0">
						<tr>
							<th width="2%" align="center">#</th>
							<th width="2%" align="center">
								<input type="checkbox" class="checkbox" name="checkall" id="checkall" onclick="jQueryCheckAll('listform',this.id,'#listview tr');">
							</th>
							<th width="4%" align="center">
								<a href="javascript:void(0);" onclick="SetOrder('listform','name','<?php echo $pre; ?>');">
									<?php echo $lang->display('Type'); ?>
								</a>
							</th>
							<th>
								<a href="javascript:void(0);" onclick="SetOrder('listform','name','<?php echo $pre; ?>');">
									<?php echo $lang->display('Service'); ?>
								</a>
							</th>
							<th>
								<a href="javascript:void(0);" onclick="SetOrder('listform','status','<?php echo $pre; ?>');">
									<?php echo $lang->display('Power'); ?>
								</a>
							</th>
							<th>
								<a href="javascript:void(0);" onclick="SetOrder('listform','status','<?php echo $pre; ?>');">
									<?php echo $lang->display('Status'); ?>
								</a>
							</th>
							<th>
								<a href="javascript:void(0);" onclick="SetOrder('listform','version','<?php echo $pre; ?>');">
									<?php echo $lang->display('Version'); ?>
								</a>
							</th>
							<th width="4%" align="center">
								<a href="javascript:void(0);" onclick="SetOrder('listform','cache','<?php echo $pre; ?>');">
									<?php echo $lang->display('Cache'); ?>
								</a>
							</th>
							<th width="2%" align="center">
								<a href="javascript:void(0);" onclick="SetOrder('listform','id','<?php echo $pre; ?>');">ID</a>
							</th>
						</tr>
						<?php
							$query =  sprintf("SELECT id, name, ext, icon, status, version, cache
											FROM service_engines
											WHERE name LIKE '%s' OR ext LIKE '%s' OR version LIKE '%s'
											ORDER BY `%s` %s
											LIMIT %d
											OFFSET %d",
											"%".mysql_real_escape_string($keyword)."%",
											"%".mysql_real_escape_string($keyword)."%",
											"%".mysql_real_escape_string($keyword)."%",
											mysql_real_escape_string($by),
											mysql_real_escape_string($order),
											$limit,
											$offset);
							$result = mysql_query($query, $conn) or die(mysql_error());
							$found = mysql_num_rows($result);
							if($found) {
								$counter = $offset+1;
								while($row = mysql_fetch_assoc($result)) {
									$style = $counter%2==0 ? 'even' : 'odd';
									echo '<tr class="'.$style.'">';
									echo '<td align="center">'.$counter.'</td>';
									echo '<td align="center"><input type="checkbox" class="checkbox" name="id[]" id="id[]" value="'.$row['id'].'"></td>';
									echo '<td align="center"><img src="'.IMG_PATH.''.$row['icon'].'" title="'.$row['name'].'"></td>';
									echo '<td>'.$row['name'].'</td>';
									echo '<td><img src="'.IMG_PATH.'ico_';
									if($row['status']) echo 'online'; else echo 'offline';
									echo '.gif"></td>';
									echo '<td>';
									if($row['status']) echo $lang->display('Good Service'); else echo '-';
									echo '</td>';
									echo '<td>'.$row['version'].'</td>';
									echo '<td align="center">';
									if($row['cache']==1) {
										if($issuperadmin) echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout='.$layout.'&option=cache&do=disable&id='.$row['id'].'\')">';
										echo '<img src="'.IMG_PATH.'ico_enable.png"';;
										if($issuperadmin) echo ' title="'.$lang->display('Disable').'"></a>';
									} else {
										if($issuperadmin) echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout='.$layout.'&option=cache&do=enable&id='.$row['id'].'\')">';
										echo '<img src="'.IMG_PATH.'ico_disable.png"';;
										if($issuperadmin) echo ' title="'.$lang->display('Enable').'"></a>';
									}
									echo '</td>';
									echo '<td align="center">'.$row['id'].'</td>';
									echo '</tr>';
									$counter++;
								}
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