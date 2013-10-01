<?php
BuildHelperDiv($lang->display('Task Home').' - '.$lang->display('Import Report'));
$navStatus[] = empty($task_id) ? "campaigns" : "home" ;
$redirect_layout = empty($task_id) ? "amend" : "task" ;
$redirect_id = empty($task_id) ? $artwork_id : $task_id ;
require_once(MODULES.'mod_header.php');
BuildPageIntro($lang->display('Import Report'));
?>
<div id="wrapperWhite">
	<div class="controlselectScroll">
		<!-- Toolbar -->
		<div class="toolbar">
			<div class="title">
				<div class="ico">
					<?php echo '<img src="'.IMG_PATH.'header/ico_log.png">'; ?>
				</div>
				<div class="txt">
					<?php
						echo $lang->display('Import Report').' <img src="'.IMG_PATH.'arrow_right.png"> '.date(FORMAT_TIME,strtotime($row_import['time_start'])).' ('.$row_import['file_type'];
						if(!empty($row_import['option'])) {
							echo ' <img src="'.IMG_PATH.'ico_enable.png" /> ';
						} else {
							echo ' <img src="'.IMG_PATH.'ico_disable.png" /> ';
						}
						echo $lang->display('Accept Same-as-Source Translation');
						echo ')'; ?>
				</div>
			</div>
			<div class="options">
				<!-- Close -->
				<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Close'); ?>">
					<a href="javascript:void(0);" onclick="goToURL('parent','index.php?layout=<?php echo $redirect_layout; ?>&id=<?php echo $redirect_id; ?>');">
						<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_close.png">'; ?></div>
						<div><?php echo $lang->display('Close'); ?></div>
					</a>
				</div>
			</div>
			<div class="clear"></div>
		</div>
		<?php BuildTipMsg('<a href="http://www.trados.com" target="_blank">'.$lang->display('Trados Alert').'</a>'); ?>
		<!-- Mainwrap -->
		<div class="mainwrap">
			<form
				id="listform"
				name="listform"
				action="index.php?layout=<?php echo $layout; ?>&id=<?php echo $id; ?>"
				method="POST"
				enctype="multipart/form-data"
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
							name="filter_import"
							id="filter_import"
							onchange="SubmitForm('listform','');"
							title="<?php echo $lang->display('Select View'); ?>"
						>
							<?php BuildImportStatusList($filter_import); ?>
						</select>
					</div>
				</div>
				<div class="clear"></div>
			</div>
			<div class="list">
				<table width="100%" cellpadding="5" cellspacing="0" border="0">
					<tr>
						<th width="2%">#</th>
						<th width="45%">
							<a href="javascript:void(0);" onclick="SetOrder('listform','source','<?php echo $pre; ?>');">
								<?php echo $lang->display('Source Language'); ?>
							</a>
						</th>
						<th width="45%">
							<a href="javascript:void(0);" onclick="SetOrder('listform','target','<?php echo $pre; ?>');">
								<?php echo $lang->display('Desired Language'); ?>
							</a>
						</th>
						<th width="6%">
							<a href="javascript:void(0);" onclick="SetOrder('listform','imported','<?php echo $pre; ?>');">
								<?php echo $lang->display('Imported'); ?>
							</a>
						</th>
						<th width="2%">
							<a href="javascript:void(0);" onclick="SetOrder('listform','id','<?php echo $pre; ?>');">ID</a>
						</th>
					</tr>
					<?php
						$query =  sprintf("SELECT *
										FROM task_import_rows
										WHERE import_id = %d
										%s
										AND ( source LIKE '%s'
										OR target LIKE '%s' )
										ORDER BY `%s` %s
										LIMIT %d
										OFFSET %d",
										$id,
										$sub,
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
								$style = ($row['imported']) ? ( ($counter%2==0) ? 'even' : 'odd' ) : 'warn' ;
								echo '<tr class="'.$style.'" onmouseover="this.className=\'hover\'" onmouseout="this.className=\''.$style.'\'">';
								echo '<td>'.$counter.'</td>';
								echo '<td>'.html_display_para($row['source']).'</td>';
								echo '<td>'.html_display_para($row['target']).'</td>';
								echo '<td>';
								if(!empty($row['imported'])) {
									echo '<img src="'.IMG_PATH.'ico_enable.png">';
								} else {
									echo '<img src="'.IMG_PATH.'ico_disable.png">';
								}
								echo '</td>';
								echo '<td>'.$row['id'].'</td>';
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