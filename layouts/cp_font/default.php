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
					<div class="ico"><?php echo '<img src="'.IMG_PATH.'header/ico_font.png">'; ?></div>
					<div class="txt"><?php echo $lang->display('Font Manager'); ?></div>
				</div>
				<div class="options">
					<!-- Scan -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Scan'); ?>">
						<a href="javascript:void(0);" onclick="SubmitForm('listform','scan');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_scan.png">'; ?></div>
							<div><?php echo $lang->display('Scan'); ?></div>
						</a>
					</div>
					<?php if($issuperadmin) { ?>
					<!-- Install -->
					<!--div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Install'); ?>">
						<a href="javascript:void(0);" onclick="SubmitForm('listform','install');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_install.png">'; ?></div>
							<div><?php echo $lang->display('Install'); ?></div>
						</a>
					</div -->
					<!-- Uninstall -->
					<!-- div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Uninstall'); ?>">
						<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) { SubmitForm('listform','uninstall'); }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_uninstall.png">'; ?></div>
							<div><?php echo $lang->display('Uninstall'); ?></div>
						</a>
					</div -->
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
							<?php echo $lang->display('Service'); ?>:
							<select
								class="input"
								onfocus="this.className='inputOn'"
								onblur="this.className='input'"
								name="filter_engine"
								id="filter_engine"
								onchange="SubmitForm('listform','');"
								title="<?php echo $lang->display('Please Select'); ?>"
							>
							<?php BuildEngineList($engine_id); ?>
							</select>
						</div>
					</div>
					<div class="clear"></div>
				</div>
				<div class="list">
					<table id="listview" width="100%" cellpadding="5" cellspacing="0" border="0">
						<tr>
							<th width="2%" align="center">#</th>
							<th width="2%" align="center">
								<input type="checkbox" class="checkbox" name="checkall" id="checkall" onclick="jQueryCheckAll('listform',this.id,'#listview tr');">
							</th>
							<th width="23%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','family','<?php echo $pre; ?>');">
									<?php echo $lang->display('Font Family'); ?>
								</a>
							</th>
							<th width="40%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','name','<?php echo $pre; ?>');">
									<?php echo $lang->display('Font'); ?>
								</a>
							</th>
							<th width="15%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','style','<?php echo $pre; ?>');">
									<?php echo $lang->display('Style'); ?>
								</a>
							</th>
							<th width="10%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','service','<?php echo $pre; ?>');">
									<?php echo $lang->display('Service'); ?>
								</a>
							</th>
							<th width="6%" align="center">
								<a href="javascript:void(0);" onclick="SetOrder('listform','installed','<?php echo $pre; ?>');">
									<?php echo $lang->display('Installed'); ?>
								</a>
							</th>
							<th width="2%" align="center">
								<a href="javascript:void(0);" onclick="SetOrder('listform','id','<?php echo $pre; ?>');">ID</a>
							</th>
						</tr>
						<?php
							$query =  sprintf("SELECT fonts.*,
												service_engines.name AS service
												FROM fonts
												LEFT JOIN service_engines ON fonts.engine_id = service_engines.id
												WHERE fonts.engine_id = %d
												AND ( fonts.family LIKE '%s'
												OR fonts.name LIKE '%s'
												OR fonts.style LIKE '%s'
												OR service_engines.name LIKE '%s' )
												ORDER BY `%s` %s
												LIMIT %d
												OFFSET %d",
												$engine_id,
												"%".mysql_real_escape_string($keyword)."%",
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
									echo '<td>'.$row['family'].'</td>';
									echo '<td>'.$row['name'].'</td>';
									echo '<td>'.$row['style'].'</td>';
									echo '<td>'.$row['service'].'</td>';
									echo '<td align="center">';
									if($row['installed']) {
										echo '<img src="'.IMG_PATH.'ico_enable.png">';
									} else {
										echo '<img src="'.IMG_PATH.'ico_disable.png">';
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