<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("system","cpanel");
require_once(MODULES.'mod_authorise.php');
require_once(CLASSES.'FTP_Sync.php');

$ftp_id = isset($_GET['id']) ? $_GET['id'] : 0;
$remote_ftp_dir = isset($_GET['dir']) ? $_GET['dir'] : '/';
$remote_sort = isset($_GET['sort']) ? $_GET['sort'] : 'name';
$remote_order = isset($_GET['order']) ? $_GET['order'] : 'ASC';
$remote_order_prep = ($remote_order=="ASC")?"DESC":"ASC";
$do = isset($_GET['do']) ? $_GET['do'] : "";
$ref = isset($_GET['ref']) ? trim($_GET['ref'],',') : "";
$name = isset($_GET['name']) ? $_GET['name'] : "";
$keywords = isset($_GET['keywords']) ? $_GET['keywords'] : "";
$ftp_type = isset($_SESSION['ftp_type']) ? $_SESSION['ftp_type'] : FTP_BINARY;

$ftp_query = sprintf("SELECT ftp_host, ftp_username, ftp_password
					FROM ftps
					WHERE id = %d
					LIMIT 1",
					$ftp_id);
$ftp_result = mysql_query($ftp_query, $conn) or die(mysql_error());
if(!mysql_num_rows($ftp_result)) access_denied();
$ftp_row = mysql_fetch_assoc($ftp_result);
$ftp_sync = new FTP_Sync($ftp_row['ftp_host'],$ftp_row['ftp_username'],$ftp_row['ftp_password']);
$ftp_sync->set_ftp_type($ftp_type);

$remote_ftp_dir = $ftp_sync->format_ftp_dir($remote_ftp_dir);

//rename item
if($do=="rename" && !empty($ref) && !empty($name)) {
	$ftp_sync->rename_remote_ftp_item($ref, $name);
}

//mkdir
if($do=="mkdir" && !empty($name)) {
	$ftp_sync->remote_ftp_mkdir($remote_ftp_dir,$name);
}

//delete items
if($do=="delete" && !empty($ref)) {
	$remote_cache_ids = explode(',',$ref);
	foreach($remote_cache_ids as $remote_cache_id) {
		$ftp_sync->delete_remote_ftp_item($remote_cache_id);
	}
}

//sync items
if($do=="sync" && !empty($ref)) {
	$local_cache_ids = explode(',',$ref);
	foreach($local_cache_ids as $local_cache_id) {
		$ftp_sync->sync_item_to_ftp($local_cache_id,$remote_ftp_dir);
	}
}

//rebuild cache for remote ftp if needed
if(!$ftp_sync->is_remote_ftp_cache_usable($ftp_id,$remote_ftp_dir) || !empty($do)) {
	$ftp_sync->rebuild_remote_ftp_cache($ftp_id,$remote_ftp_dir,$ftp_sync->ftp_list_dir_contents($remote_ftp_dir));
}
?>
<div class="breadcrumbs">
	<?php
		echo '<div><img src="'.IMG_PATH.'ico_fopen.png"> '.$remote_ftp_dir.'</div>';
		//search
		echo '<div class="left search">'.$lang->display('Search');
		echo '<input
				type="text"
				class="input"
				onfocus="this.className=\'inputOn\'"
				onblur="this.className=\'input\'"
				id="keyword2"
				name="keyword2"
				title="Keyword"
				value="'.$keywords.'"
			>
			<input
				type="submit"
				class="btnDo"
				onmousemove="this.className=\'btnOn\'"
				onmouseout="this.className=\'btnDo\'"
				value="'.$lang->display('Go').'"
				title="'.$lang->display('Go').'"
				onclick="DoAjax(\'id='.$ftp_id.'&dir='.$remote_ftp_dir.'&keywords=\'+jQueryGetValue(\'keyword2\'),\'remote_ftp\',\'modules/mod_ftp_remote.php\');"
			>
			<input
				type="submit"
				class="btnOff"
				onmousemove="this.className=\'btnOn\'"
				onmouseout="this.className=\'btnOff\'"
				value="'.$lang->display('Reset').'"
				title="'.$lang->display('Reset').'"
				onclick="DoAjax(\'id='.$ftp_id.'&dir='.$remote_ftp_dir.'&keywords=\',\'remote_ftp\',\'modules/mod_ftp_remote.php\');"
			>
		</div>';
		echo '<div class="right" id="remote_cache_info" style="display:block;"><a href="javascript:void(0);" onclick="hidediv(\'remote_cache_info\'); display(\'remote_cache_loader\'); DoAjax(\'id='.$ftp_id.'&dir='.$remote_ftp_dir.'&do=refresh\',\'remote_ftp\',\'modules/mod_ftp_remote.php\');"><img src="'.IMG_PATH.'ico_swap.png" title="'.$lang->display('Refresh').'" /></a> '.$lang->display('Cached').': '.date(FORMAT_TIME,strtotime($ftp_sync->get_remote_ftp_cache_time($ftp_id,$remote_ftp_dir))).'</div>';
		echo '<div class="right" id="remote_cache_loader" style="display:none;"><img src="'.IMG_PATH.'zoomloader.gif"></div>';
		echo '<div class="clear"></div>';
	?>
</div>
<div class="ftpPanel">
	<form
		id="remote_form"
		name="remote_form"
		action="index.php?layout=cp_file&id=<?php echo $ftp_id; ?>"
		method="POST"
		enctype="multipart/form-data"
	>
		<div class="list">
			<table width="100%" cellpadding="5" cellspacing="0" border="0">
				<tr>
					<th width="2%">
						<input type="checkbox" class="checkbox" name="checkall" id="checkall" onclick="GroupCheckbox(this,'id')">
					</th>
					<th>
						<a href="javascript:void(0);" onclick="hidediv('remote_cache_info'); display('remote_cache_loader');DoAjax('id=<?php echo $ftp_id; ?>&dir=<?php echo $remote_ftp_dir; ?>&sort=name&order=<?php echo $remote_order_prep; ?>','remote_ftp','modules/mod_ftp_remote.php');">
							<?php echo $lang->display('Name'); ?>
						</a>
					</th>
					<th align="right">
						<a href="javascript:void(0);" onclick="hidediv('remote_cache_info'); display('remote_cache_loader');DoAjax('id=<?php echo $ftp_id; ?>&dir=<?php echo $remote_ftp_dir; ?>&sort=size&order=<?php echo $remote_order_prep; ?>','remote_ftp','modules/mod_ftp_remote.php');">
							<?php echo $lang->display('Size'); ?>
						</a>
					</th>
					<th width="4%">
						<a href="javascript:void(0);" onclick="hidediv('remote_cache_info'); display('remote_cache_loader');DoAjax('id=<?php echo $ftp_id; ?>&dir=<?php echo $remote_ftp_dir; ?>&sort=type&order=<?php echo $remote_order_prep; ?>','remote_ftp','modules/mod_ftp_remote.php');">
							<?php echo $lang->display('Type'); ?>
						</a>
					</th>
					<th>
						<a href="javascript:void(0);" onclick="hidediv('remote_cache_info'); display('remote_cache_loader');DoAjax('id=<?php echo $ftp_id; ?>&dir=<?php echo $remote_ftp_dir; ?>&sort=date&order=<?php echo $remote_order_prep; ?>','remote_ftp','modules/mod_ftp_remote.php');">
							<?php echo $lang->display('Date Modified'); ?>
						</a>
					</th>
					<th width="4%">
						<a href="javascript:void(0);" onclick="hidediv('remote_cache_info'); display('remote_cache_loader');DoAjax('id=<?php echo $ftp_id; ?>&dir=<?php echo $remote_ftp_dir; ?>&sort=chmod&order=<?php echo $remote_order_prep; ?>','remote_ftp','modules/mod_ftp_remote.php');">
							<?php echo $lang->display('Mode'); ?>
						</a>
					</th>
				</tr>
				<?php
					echo '<tr class="odd" onmouseover="this.className=\'hover\'" onmouseout="this.className=\'odd\'">';
					echo '<td></td>';
					echo '<td colspan="5"><a href="javascript:void(0);" onclick="hidediv(\'remote_cache_info\'); display(\'remote_cache_loader\'); DoAjax(\'id='.$ftp_id.'&dir='.$ftp_sync->ftp_go_parent_dir().'\',\'remote_ftp\',\'modules/mod_ftp_remote.php\');"><img src="'.IMG_PATH.'ico_up.png" title="'.$lang->display('Up').'" /></a></td>';
					echo '</tr>';
					$query =  sprintf("SELECT `ftp_cache_remote`.*
										FROM `ftp_cache_remote`
										LEFT JOIN `ftp_cache_remote_dir` ON `ftp_cache_remote`.`dir_id` = `ftp_cache_remote_dir`.`id`
										WHERE `ftp_cache_remote_dir`.`ftp_id` = %d
										AND `ftp_cache_remote_dir`.dir = '%s'
										AND (
											`ftp_cache_remote_dir`.`dir` LIKE '%s'
											OR
											`ftp_cache_remote`.`name` LIKE '%s'
										)
										ORDER BY
										`ftp_cache_remote`.`type` ASC,
										`ftp_cache_remote`.`%s` %s",
										$ftp_id,
										mysql_real_escape_string($remote_ftp_dir),
										"%".mysql_real_escape_string($keywords)."%",
										"%".mysql_real_escape_string($keywords)."%",
										$remote_sort,
										$remote_order);
					$result = mysql_query($query, $conn) or die(mysql_error());
					$counter = 1;
					while($row = mysql_fetch_assoc($result)) {
						echo '<tr class="';
						if($counter%2==0) echo 'odd'; else echo 'even';
						echo '" onmouseover="this.className=\'hover\'" onmouseout="this.className=\'';
						if($counter%2==0) echo 'odd'; else echo 'even';
						echo '\'"';
						if($row['type']=="dir") {
							echo 'ondblclick="hidediv(\'remote_cache_info\'); display(\'remote_cache_loader\'); DoAjax(\'id='.$ftp_id.'&dir='.$remote_ftp_dir.$row['name'].'\',\'remote_ftp\',\'modules/mod_ftp_remote.php\');"';
						}
						echo '>';
						echo '<td><input type="checkbox" class="checkbox" name="id[]" id="id[]" value="'.$row['id'].'"></td>';
						echo '<td>';
						if($row['type']=="dir") {
							echo '<img src="'.IMG_PATH.'ico_folder.png"> ';
						}
						echo $row['name'];
						echo '</td>';
						echo '<td align="right">'.convert_byte($row['size']).'</td>';
						echo '<td>'.$row['type'].'</td>';
						echo '<td>'.date(FORMAT_TIME,strtotime($row['date'])).'</td>';
						echo '<td>'.$row['chmod'].'</td>';
						echo '</tr>';
						$counter++;
					}
				?>
			</table>
		</div>
		<input type="hidden" name="form" id="form">
		<input type="hidden" name="ftp" id="ftp" value="remote">
		<input type="hidden" name="path" id="path" value="<?php echo $remote_ftp_dir; ?>">
	</form>
</div>