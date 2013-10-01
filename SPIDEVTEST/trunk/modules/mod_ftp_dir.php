<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("system","login");
require_once(MODULES.'mod_authorise.php');
require_once(CLASSES.'FTP_Local.php');

$companyID = !empty($_GET['companyID']) ? $_GET['companyID'] : $_SESSION['companyID'];
$local_ftp_dir = !empty($_GET['dir']) ? $_GET['dir'] : '/';
$filename = !empty($_GET['file']) ? $_GET['file'] : '';
$do = !empty($_GET['do']) ? $_GET['do'] : "";
$keywords = !empty($_GET['keywords']) ? $_GET['keywords'] : "";

$system_name = $DB->get_system_name($companyID);
if($system_name === false) access_denied();

$ftp_local = new FTP_Local();
$local_path_to_ftp = ROOT.FTP_DIR.$system_name;
$local_ftp_dir = $ftp_local->format_ftp_dir($local_ftp_dir);
if(!$ftp_local->is_local_ftp_cache_usable($_SESSION['companyID'],$local_ftp_dir) || !empty($do)) {
	$ftp_local->rebuild_local_ftp_cache($companyID,$local_ftp_dir,$ftp_local->local_list_dir_contents($local_path_to_ftp.$local_ftp_dir));
}

echo '<div class="breadcrumbs">';
echo '<div><img src="'.IMG_PATH.'ico_fopen.png"> '.$local_ftp_dir.'</div>';
//search
echo '<div class="left search">'.$lang->display('Search');
echo '<input
		type="text"
		class="input"
		onfocus="this.className=\'inputOn\'"
		onblur="this.className=\'input\'"
		id="keyword"
		name="keyword"
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
		onclick="DoAjax(\'companyID='.$companyID.'&dir='.$local_ftp_dir.'&file='.$filename.'&keywords=\'+jQueryGetValue(\'keyword\'),\'local_ftp\',\'modules/mod_ftp_dir.php\');"
	>
	<input
		type="submit"
		class="btnOff"
		onmousemove="this.className=\'btnOn\'"
		onmouseout="this.className=\'btnOff\'"
		value="'.$lang->display('Reset').'"
		title="'.$lang->display('Reset').'"
		onclick="DoAjax(\'companyID='.$companyID.'&dir='.$local_ftp_dir.'&file='.$filename.'&keywords=\',\'local_ftp\',\'modules/mod_ftp_dir.php\');"
	>
</div>';
echo '<div class="right" id="local_cache_info" style="display:block;"><a href="javascript:void(0);" onclick="hidediv(\'local_cache_info\'); display(\'local_cache_loader\'); DoAjax(\'companyID='.$companyID.'&dir='.$local_ftp_dir.'&do=refresh\',\'local_ftp\',\'modules/mod_ftp_dir.php\');"><img src="'.IMG_PATH.'ico_swap.png" title="'.$lang->display('Refresh').'"></a> '.$lang->display('Cached').': '.date(FORMAT_TIME,strtotime($ftp_local->get_local_ftp_cache_time($companyID,$local_ftp_dir))).'</div>';
echo '<div class="right" id="local_cache_loader" style="display:none;"><img src="'.IMG_PATH.'zoomloader.gif"></div>';
echo '<div class="clear"></div>';
echo '</div>';
echo "<div class=\"thumbnails\">";
$query = sprintf("SELECT images.content
				FROM images
				LEFT JOIN users ON users.userID = images.user_id
				WHERE images.type_id = %d
				AND users.companyID = %d",
				IMG_LIBRARY,
				$_SESSION['companyID']);
$result = mysql_query($query, $conn) or die(mysql_error());
echo "<div id=\"".ROOT.FTP_DIR."\"
		class=\"thumbnailOff\"
		title=\"".$lang->display('Up')."\">
		<a href=\"javascript:void(0);\"
			onclick=\"ResetDiv('local_ftp');
			DoAjax('dir=".$ftp_local->local_go_parent_dir()."&file=".$filename."','local_ftp','modules/mod_ftp_dir.php');
			setValue('default_img_dir','".$ftp_local->local_go_parent_dir()."');\"
		>
			<div class=\"img\"><img src=\"".IMG_PATH."header/ico_up.png\" /></div>
			<div class=\"txt\">".$lang->display('Up')."</div>
		</a>
	</div>";

$query =  sprintf("SELECT `ftp_cache_local`.*,
					`ftp_cache_local_dir`.`dir`
					FROM `ftp_cache_local`
					LEFT JOIN `ftp_cache_local_dir` ON `ftp_cache_local`.`dir_id` = `ftp_cache_local_dir`.`id`
					WHERE `ftp_cache_local_dir`.`company_id` = %d
					AND `ftp_cache_local_dir`.`dir` = '%s'
					AND (
						`ftp_cache_local_dir`.`dir` LIKE '%s'
						OR
						`ftp_cache_local`.`name` LIKE '%s'
					)
					ORDER BY
					`ftp_cache_local`.`type` ASC,
					`ftp_cache_local`.`name` ASC",
					$companyID,
					mysql_real_escape_string($local_ftp_dir),
					"%".mysql_real_escape_string($keywords)."%",
					"%".mysql_real_escape_string($keywords)."%");
$result = mysql_query($query, $conn) or die(mysql_error());
while($row = mysql_fetch_assoc($result)) {
	$img_content = FTP_DIR.$system_name.$row['dir'].$row['name'];
	$img_path = ROOT.$img_content;
	
	if($row['type']=="dir") {
		echo "<div
				id=\"$img_content\"
				class=\"thumbnailOff\"
				onclick=\"resetClassName('div','thumbnailOn','thumbnailOff');this.className='thumbnailOn';setValue('default_img_dir','".$ftp_local->format_ftp_dir(addslashes($row['dir'].$row['name']))."');\"
				ondblclick=\"ResetDiv('local_ftp');DoAjax('dir=".$row['dir'].$row['name']."&file=".$filename."','local_ftp','modules/mod_ftp_dir.php');\"
				title=\"".$row['name']."\">
					<div class=\"img\"><img src=\"".IMG_PATH."header/ico_folder.png\" /></div>
					<div class=\"txt\">".$row['name']."</div>
			</div>";
	}
	
	if($row['type']=="file") {
		$class = ($row['name']==$filename) ? "thumbnailOn" : "thumbnailOff";
		$valid = ValidateImage($img_path);
		echo "<div
				id=\"$img_content\"
				class=\"$class\"
				onclick=\"resetClassName('div','thumbnailOn','thumbnailOff');this.className='thumbnailOn';setValue('img_content','".addslashes($img_path)."');\"
				title=\"".$row['name']."\">
				<div class=\"img\"";
		if($valid) echo " onmouseover=\"display('preview{$row['id']}');\" onmouseout=\"hidediv('preview{$row['id']}');\"";
		echo "><img src=\"";
		if($valid) {
			echo $img_content;
		} else {
			echo IMG_PATH."header/ico_file.png";
		}
		echo "\" /></div>";
		echo "<div class=\"txt\">".$row['name']."</div>";
		if($valid) echo '<div id="preview'.$row['id'].'" class="preview"><img src="'.$img_content.'" /></div>';
		echo "</div>";
	}
}
echo "<div class=\"clear\"></div>";
echo "</div>";
?>