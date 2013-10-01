<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("system","login");
require_once(MODULES.'mod_authorise.php');
require_once(CLASSES.'FTP_Local.php');

$companyID = !empty($_GET['companyID']) ? $_GET['companyID'] : $_SESSION['companyID'];
$local_ftp_dir = !empty($_GET['dir']) ? $_GET['dir'] : '/';
$local_sort = isset($_GET['sort']) ? $_GET['sort'] : 'name';
$local_order = isset($_GET['order']) ? $_GET['order'] : 'ASC';
$local_order_prep = ($local_order=="ASC")?"DESC":"ASC";
$do = !empty($_GET['do']) ? $_GET['do'] : "";

$query = sprintf("SELECT systemName
				FROM companies
				WHERE companyID = %d
				LIMIT 1",
				$companyID);
$result = mysql_query($query, $conn) or die(mysql_error());
if(!mysql_num_rows($result)) access_denied();
$row = mysql_fetch_assoc($result);
$system_name = $row['systemName'];

$ftp_local = new FTP_Local();
$local_path_to_ftp = ROOT.FTP_DIR.$system_name;
$local_ftp_dir = $ftp_local->format_ftp_dir($local_ftp_dir);
if(!$ftp_local->is_local_ftp_cache_usable($_SESSION['companyID'],$local_ftp_dir) || !empty($do)) {
	$ftp_local->rebuild_local_ftp_cache($companyID,$local_ftp_dir,$ftp_local->local_list_dir_contents($local_path_to_ftp.$local_ftp_dir));
}
?>
<div class="breadcrumbs">
	<?php
		echo '<div class="left"><img src="'.IMG_PATH.'ico_fopen.png"> '.$local_ftp_dir.'</div>';
		echo '<div class="right" id="local_ftp_cache_info" style="display:block;"><a href="javascript:void(0);" onclick="hidediv(\'local_ftp_cache_info\'); display(\'local_ftp_cache_loader\'); DoAjax(\'companyID='.$companyID.'&dir='.$local_ftp_dir.'&do=refresh\',\'ftp\',\'modules/mod_ftp.php\');"><img src="'.IMG_PATH.'ico_swap.png" title="'.$lang->display('Refresh').'" /></a> '.$lang->display('Cached').': '.date(FORMAT_TIME,strtotime($ftp_local->get_local_ftp_cache_time($_SESSION['companyID'],$local_ftp_dir))).'</div>';
		echo '<div class="right" id="local_ftp_cache_loader" style="display:none;"><img src="'.IMG_PATH.'zoomloader.gif"></div>';
		echo '<div class="clear"></div>';
	?>
</div>
<div class="ftpPanel">
	<div class="mainwrap">
		<div class="list">
			<table width="100%" cellpadding="5" cellspacing="0" border="0">
				<tr>
					<th width="2%">
						<input type="checkbox" class="checkbox" name="checkall" id="checkall" onclick="GroupCheckbox(this,'id')">
					</th>
					<th>
						<a href="javascript:void(0);" onclick="hidediv('local_ftp_cache_info'); display('local_ftp_cache_loader'); DoAjax('companyID=<?php echo $companyID; ?>&dir=<?php echo $ftp_local->local_get_current_dir(); ?>&sort=name&order=<?php echo $local_order_prep; ?>','ftp','modules/mod_ftp.php');">
							<?php echo $lang->display('Name'); ?>
						</a>
					</th>
					<th align="right">
						<a href="javascript:void(0);" onclick="hidediv('local_ftp_cache_info'); display('local_ftp_cache_loader'); DoAjax('companyID=<?php echo $companyID; ?>&dir=<?php echo $ftp_local->local_get_current_dir(); ?>&sort=size&order=<?php echo $local_order_prep; ?>','ftp','modules/mod_ftp.php');">
							<?php echo $lang->display('Size'); ?>
						</a>
					</th>
					<th width="4%">
						<a href="javascript:void(0);" onclick="hidediv('local_ftp_cache_info'); display('local_ftp_cache_loader'); DoAjax('companyID=<?php echo $companyID; ?>&dir=<?php echo $ftp_local->local_get_current_dir(); ?>&sort=type&order=<?php echo $local_order_prep; ?>','ftp','modules/mod_ftp.php');">
							<?php echo $lang->display('Type'); ?>
						</a>
					</th>
					<th>
						<a href="javascript:void(0);" onclick="hidediv('local_ftp_cache_info'); display('local_ftp_cache_loader'); DoAjax('companyID=<?php echo $companyID; ?>&dir=<?php echo $ftp_local->local_get_current_dir(); ?>&sort=date&order=<?php echo $local_order_prep; ?>','ftp','modules/mod_ftp.php');">
							<?php echo $lang->display('Date Modified'); ?>
						</a>
					</th>
				</tr>
				<?php
					echo '<tr class="odd" onmouseover="this.className=\'hover\'" onmouseout="this.className=\'odd\'">';
					echo '<td></td>';
					echo '<td colspan="5"><a href="javascript:void(0);" onclick="hidediv(\'local_ftp_cache_info\'); display(\'local_ftp_cache_loader\'); DoAjax(\'companyID='.$companyID.'&dir='.$ftp_local->local_go_parent_dir().'\',\'ftp\',\'modules/mod_ftp.php\');"><img src="'.IMG_PATH.'ico_up.png" title="'.$lang->display('Up').'" /></a></td>';
					echo '</tr>';
					$query =  sprintf("SELECT `ftp_cache_local`.*
										FROM `ftp_cache_local`
										LEFT JOIN `ftp_cache_local_dir` ON `ftp_cache_local`.`dir_id` = `ftp_cache_local_dir`.`id`
										WHERE `ftp_cache_local_dir`.`company_id` = %d
										AND `ftp_cache_local_dir`.`dir` = '%s'
										ORDER BY
										`ftp_cache_local`.`type` ASC,
										`ftp_cache_local`.`%s` %s",
										$_SESSION['companyID'],
										mysql_real_escape_string($local_ftp_dir),
										$local_sort,
										$local_order);
					$result = mysql_query($query, $conn) or die(mysql_error());
					$counter = 1;
					while($row = mysql_fetch_assoc($result)) {
						$is_dir = ($row['type']=="dir");
						echo '<tr class="';
						if($counter%2==0) echo 'odd'; else echo 'even';
						echo '" onmouseover="this.className=\'hover\'" onmouseout="this.className=\'';
						if($counter%2==0) echo 'odd'; else echo 'even';
						echo '\'"';
						if($is_dir) {
							echo 'ondblclick="hidediv(\'local_ftp_cache_info\'); display(\'local_ftp_cache_loader\'); DoAjax(\'companyID='.$companyID.'&dir='.$local_ftp_dir.$row['name'].'\',\'ftp\',\'modules/mod_ftp.php\');"';
						}
						echo '>';
						echo '<td><input type="checkbox" class="checkbox" name="ftpFile[]" id="ftpFile[]" value="'.$local_path_to_ftp.$local_ftp_dir.$row['name'].'"';
						if($is_dir) echo ' disabled="disabled"';
						echo '></td>';
						echo '<td>';
						if($is_dir) echo '<img src="'.IMG_PATH.'ico_folder.png"> ';
						echo $row['name'];
						echo '</td>';
						echo '<td align="right">'.convert_byte($row['size']).'</td>';
						echo '<td>'.$row['type'].'</td>';
						echo '<td>'.date(FORMAT_TIME,strtotime($row['date'])).'</td>';
						echo '</tr>';
						$counter++;
					}
				?>
			</table>
		</div>
	</div>
</div>
<div>
	<input type="checkbox" name="keep" value="1" checked="checked"> <?php echo $lang->display('Keep Copy on FTP'); ?>
</div>