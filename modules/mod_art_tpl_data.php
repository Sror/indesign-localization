<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("artworks","edit");
require_once(MODULES.'mod_authorise.php');

$artworkID = isset($_GET['id']) ? $_GET['id'] : 0;
$artwork_type = isset($_GET['type']) ? $_GET['type'] : 0;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
?>
<form
	id="dataform"
	name="dataform"
	action="index.php?layout=arttpl&id=<?php echo $artworkID; ?>&page=<?php echo $page; ?>"
	method="POST"
	enctype="multipart/form-data"
>
<div class="mainwrap">
	<div class="list">
		<table width="100%" cellpadding="5" cellspacing="0" border="0">
			<tr>
				<th width="2%">#</th>
				<th width="2%">
					<input type="checkbox" class="checkbox" name="checkall" id="checkall" onclick="GroupCheckbox(this,'id')">
				</th>
				<?php
					$by = isset($_POST['by'])?$_POST['by']:"id";
					$order = isset($_POST['order'])?$_POST['order']:"ASC";
					$pre = ($order=="ASC")?"DESC":"ASC";
					$query_colname = sprintf("SELECT import_map.import_id, import_map.colname, import_map.label
											FROM import_map_para
											LEFT JOIN import_map ON import_map_para.import_map_id = import_map.id
											WHERE import_map_para.artwork_id = %d
											ORDER BY import_map_id ASC",
											$artworkID);
					$result_colname = mysql_query($query_colname, $conn) or die(mysql_error());
					$colname_str = "";
					while($row_colname = mysql_fetch_assoc($result_colname)) {
						$import_id[] = $row_colname['import_id'];
						$colname_str .= mysql_real_escape_string($row_colname['colname']).",";
						echo '<th>'.$row_colname['label'].'</th>';
					}
				?>
				<th width="2%">ID</th>
			</tr>
			<?php
				if(!empty($import_id)) {
					$query_data = sprintf("SELECT id, %s
											FROM import_rows
											WHERE import_id = %d
											ORDER BY `%s` %s",
											trim($colname_str,","),
											$import_id[0],
											mysql_real_escape_string($by),
											mysql_real_escape_string($order));
					$result_data = mysql_query($query_data, $conn) or die(mysql_error());
					$counter = 1;
					while($row_data = mysql_fetch_assoc($result_data)) {
						echo '<tr class="';
						if($counter%2==0) echo 'even'; else echo 'odd';
						echo '" onmouseover="this.className=\'hover\'" onmouseout="this.className=\'';
						if($counter%2==0) echo 'even'; else echo 'odd';
						echo '\'">';
						echo '<td>'.$counter.'</td>';
						echo '<td><input type="checkbox" class="checkbox" name="id[]" id="id[]" value="'.$row_data['id'].'"></td>';
						$colnames = explode(",",trim($colname_str,","));
						foreach($colnames as $colname) {
							echo '<td>'.$row_data[$colname].'</td>';
						}
						echo '<td>'.$row_data['id'].'</td>';
						echo '</tr>';
						$counter++;
					}
				}
			?>
		</table>
	</div>
</div>
<div align="center">
	<input type="button" class="btnDo" onmousemove="this.className='btnOn'" onmouseout="this.className='btnDo'" title="<?php echo $lang->display('Preview'); ?>" value="<?php echo $lang->display('Preview'); ?>" onclick="if(CheckSelected('dataform','id')) { SubmitForm('dataform','preview'); }" />
	<select
		class="input"
		onfocus="this.className='inputOn'"
		onblur="this.className='input'"
		id="service_tID"
		name="service_tID"
	>
		<?php BuildDownloadList($_SESSION['packageID'],$artwork_type,SERVICE_DOWNLOAD,array(TYPE_TEMPLATE),$acl->acl_check("taskworkflow","download",$_SESSION['companyID'],$_SESSION['userID']));?>
	</select>
	<input
		type="button"
		class="btnOff"
		onmousemove="this.className='btnOn'"
		onmouseout="this.className='btnOff'"
		title="<?php echo $lang->display('Go'); ?>"
		value="<?php echo $lang->display('Go'); ?>"
		onclick="if(CheckSelected('dataform','id')) { SubmitForm('dataform','download'); }"
	/>
</div>
<input type="hidden" name="form" id="form">
</form>