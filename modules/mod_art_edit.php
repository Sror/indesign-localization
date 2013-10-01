<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("artworks","edit");
require_once(MODULES.'mod_authorise.php');

$ref = isset($_GET['ref']) ? (strpos($_GET['ref'],',') ? substr($_GET['ref'],0,strpos($_GET['ref'],',')) : $_GET['ref']) : 0;
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : "";

$query = sprintf("SELECT *
				FROM artworks
				WHERE artworkID = %d
				LIMIT 1",
				$ref);
$result = mysql_query($query, $conn) or die(mysql_error());
if(!mysql_num_rows($result)) access_denied();
$row = mysql_fetch_assoc($result);

$parent_query = sprintf("SELECT artworkID, parent
						FROM artworks
						WHERE artworkID = %d
						LIMIT 1",
						$ref);
$parent_result = mysql_query($parent_query, $conn) or die(mysql_error());
if(!mysql_num_rows($parent_result)) access_denied();
$parent_row = mysql_fetch_assoc($parent_result);
$parent_id = ($parent_row['parent']==0) ? $parent_row['artworkID'] : $parent_row['parent'];
?>
<form
	action="index.php?layout=artwork&id=<?php echo $ref; ?>"
	name="edit_art_form"
	method="POST"
	enctype="multipart/form-data" 
	onsubmit="hidediv('helper');Popup('loadingme','waiting');"
>
<table width="100%" border="0" cellspacing="0" cellpadding="5">
	<tr>
		<td class="highlight" width="30%">* <?php echo $lang->display('Artwork Title'); ?>:</td>
		<td width="70%">
			<input
				type="text"
				class="input"
				onfocus="this.className='inputOn'"
				onblur="this.className='input'"
				name="ArtworkName"
				id="ArtworkName"
				value="<?php echo $row['artworkName']; ?>"
			/>
		</td>
	</tr>
	<tr>
		<td class="highlight">* <?php echo $lang->display('Version'); ?>:</td>
		<td>
			<input
				type="text"
				class="input"
				onfocus="this.className='inputOn'"
				onblur="this.className='input'"
				name="version"
				id="version"
				value="<?php echo $row['version']; ?>"
				size="10"
				maxlength="20"
			/>
		</td>
	</tr>
	<tr>
		<td class="highlight"><?php echo $lang->display('Subject (Optional)'); ?>:</td>
		<td>
			<select
				class="input"
				onfocus="this.className='inputOn'"
				onblur="this.className='input'"
				name="SubjectID"
				id="SubjectID"
			>
				<?php BuildSubjectList($row['subjectID']); ?>
			</select>
		</td>
	</tr>
	<tr>
		<td class="highlight"><?php echo $lang->display('Default Substitute Font'); ?>:</td>
		<td>
			<select
				class="input"
				onfocus="this.className='inputOn'"
				onblur="this.className='input'"
				name="default_sub_font_id"
				id="default_sub_font_id"
			>
				<?php BuildFontSubList($row['default_sub_font_id']); ?>
			</select>
		</td>
	</tr>
	<tr>
		<td class="highlight" valign="top"><?php echo $lang->display('Default Image Folder'); ?>:</td>
		<td>
			<input
				type="text"
				class="input"
				onfocus="this.className='inputOn'"
				onblur="this.className='input'"
				name="default_img_dir"
				id="default_img_dir"
				onclick="display('local_ftp');ResetDiv('local_ftp');DoAjax('companyID=<?php echo $_SESSION['companyID']; ?>&dir='+this.value,'local_ftp','modules/mod_ftp_dir.php');"
				readonly="readonly"
				value="<?php echo $row['default_img_dir']; ?>"
			/>
			<a href="javascript:void(0);" onclick="setValue('default_img_dir','');hidediv('local_ftp');"><?php echo $lang->display('Reset'); ?></a>
			<div id="local_ftp"></div>
		</td>
	</tr>
<?php
	$query = sprintf("SELECT * FROM tasks
					WHERE artworkID = %d AND taskStatus = 10", $ref);
	$result = mysql_query($query, $conn) or die(mysql_error());
	if(mysql_num_rows($result)) {
		echo '<tr><td colspan="2">';
		BuildTipMsg($lang->display('Version control for this artwork has been disabled since one or more of the tasks were signed off.'));
		echo '</td></tr>';
	} else {
?>
	<tr>
		<td colspan="2">
                        <!-- Version Control v1 -->
			<div class="arrrgt" id="advanced" onclick="ChangeArrow('advanced');showandhide('advancedoptions');">
				<?php echo $lang->display('Advanced Options'); ?>
			</div>
			<div id="advancedoptions" class="greyBar" style="display:none;">
				<div class="arrrgt" id="restoreArrow" onclick="ResetArrow('newArrow');hidediv('newVersion');ChangeArrow('restoreArrow');openandclose('restoreVersion');">
					<?php echo $lang->display('Version Restore'); ?>
				</div>
				<div id="restoreVersion" style="display:none;">
					<table width="100%" border="0" cellspacing="0" cellpadding="5">
						<tr>
							<td class="highlight" width="30%">* <?php echo $lang->display('Restore To'); ?>:</td>
							<td width="70%">
								<select
									class="input"
									onfocus="this.className='inputOn'"
									onblur="this.className='input'"
									name="restore"
									id="restore"
								>
									<?php
										$query = sprintf("SELECT artworkID, version
															FROM artworks
															WHERE parent = %d
															OR artworkID = %d", $parent_id, $parent_id);
										$result = mysql_query($query, $conn) or die(mysql_error());
										while($row = mysql_fetch_assoc($result)) {
											echo '<option value="'.$row['artworkID'].'"';
											if($row['artworkID']==$ref) echo 'selected="selected"';
											echo '>'.$row['version'].'</option>';
										}
									?>
								</select>
								<img src="<?php echo IMG_PATH."arrow_gold_rgt.png"; ?>">
								<a href="javascript:openBrWindow('index.php?layout=artwork&id=','restore','','status=1,toolbar=1,location=1,menubar=1,resizable=1,scrollbars=1,width=1024,height=768');">
									<?php echo $lang->display('Preview')." (".$lang->display('Open in New Window').")"; ?>
								</a>
							</td>
						</tr>
					</table>
				</div>
				<div class="arrrgt" id="newArrow" onclick="ResetArrow('restoreArrow');hidediv('restoreVersion');ChangeArrow('newArrow');openandclose('newVersion');">
					<?php echo $lang->display('Upload New Version'); ?>
				</div>
                                
				<div id="newVersion" style="display:none;">
					<table width="100%" border="0" cellspacing="0" cellpadding="5">
						<tr>
							<td class="highlight" width="30%"><?php echo $lang->display('Version'); ?>:</td>
							<td width="70%">
								<input
									type="text"
									class="input"
									onfocus="this.className='inputOn'"
									onblur="this.className='input'"
									name="new_version"
									id="new_version"
									size="10"
									maxlength="20"
								/>
							</td>
						</tr>
						<tr>
							<td class="highlight">* <?php echo $lang->display('Parser Type'); ?>:</td>
							<td>
								<select
									class="input"
									onfocus="this.className='inputOn'"
									onblur="this.className='input'"
									name="parse_type"
									id="parse_type"
								>
									<?php BuildParserType(); ?>
								</select>
							</td>
						</tr>
						<tr>
							<td class="highlight" valign="top">* <?php echo $lang->display('Select File'); ?>:</td>
							<td><?php BuildUploadOption($_SESSION['companyID'],false);?></td>
						</tr>
					</table>
				</div>
				<div>
					<input type="checkbox" id="update_prework" name="update_prework" value="1" />
					<?php echo $lang->display('Update all the comments and amendments at prework stage'); ?>
				</div>
				<div>
					<input type="checkbox" id="update_task" name="update_task" value="1" />
					<?php echo $lang->display('Update all the comments and amendments at translation stage'); ?>
				</div>
			</div>
                        <!-- End Version Control v1 -->
			<!-- Version Control v2 -- >
			<div class="arrrgt" id="advanced2" onclick="ChangeArrow('advanced2');showandhide('advancedoptions2');">
				<?php echo $lang->display('Advanced Options'); ?>
			</div>
			<div id="advancedoptions2" class="greyBar" style="display:none;">
				<div class="arrrgt" id="restoreArrow2" onclick="ResetArrow('newArrow');hidediv('newVersion2');ChangeArrow('restoreArrow2');openandclose('restoreVersion2');">
					<?php echo $lang->display('Version Restore'); ?>
				</div>
				<div id="restoreVersion2" style="display:none;">
					<table width="100%" border="0" cellspacing="0" cellpadding="5">
						<tr>
							<td class="highlight" width="30%">* <?php echo $lang->display('Restore To'); ?>:</td>
							<td width="70%">
								<select
									class="input"
									onfocus="this.className='inputOn'"
									onblur="this.className='input'"
									name="restore2"
									id="restore2"
								>
									<?php
										$query = sprintf("SELECT artworkID, version
															FROM artworks
															WHERE parent = %d
															OR artworkID = %d", $parent_id, $parent_id);
										$result = mysql_query($query, $conn) or die(mysql_error());
										while($row = mysql_fetch_assoc($result)) {
											echo '<option value="'.$row['artworkID'].'"';
											if($row['artworkID']==$ref) echo 'selected="selected"';
											echo '>'.$row['version'].'</option>';
										}
									?>
								</select>
								<img src="<?php echo IMG_PATH."arrow_gold_rgt.png"; ?>">
								<a href="javascript:openBrWindow('index.php?layout=artwork&id=','restore','','status=1,toolbar=1,location=1,menubar=1,resizable=1,scrollbars=1,width=1024,height=768');">
									<?php echo $lang->display('Preview')." (".$lang->display('Open in New Window').")"; ?>
								</a>
							</td>
						</tr>
					</table>
				</div>
				<div class="arrrgt" id="newArrow2" onclick="ResetArrow('restoreArrow2');hidediv('restoreVersion2');ChangeArrow('newArrow2');openandclose('newVersion2');">
					<?php echo $lang->display('Upload New Version'); ?>
				</div>
				<div id="newVersion2" style="display:none;">
					<table width="100%" border="0" cellspacing="0" cellpadding="5">
						<tr>
							<td class="highlight" width="30%"><?php echo $lang->display('Version'); ?>:</td>
							<td width="70%">
								<input
									type="text"
									class="input"
									onfocus="this.className='inputOn'"
									onblur="this.className='input'"
									name="new_version2"
									id="new_version2"
									size="10"
									maxlength="20"
								/>
							</td>
						</tr>
						<tr>
							<td class="highlight" valign="top">* <?php echo $lang->display('Select File'); ?>:</td>
							<td><?php BuildUploadOption($_SESSION['companyID'],false);?></td>
						</tr>
					</table>
				</div>
                                <div>
					<input type="checkbox" id="update_prework2" name="update_prework" value="1" checked="checked" />
					<?php echo $lang->display('Update all the comments and amendments at prework stage'); ?>
				</div>
				<div>
					<input type="checkbox" id="update_task2" name="update_task" value="1" checked="checked" />
					<?php echo $lang->display('Update all the comments and amendments at translation stage'); ?>
				</div>
                            
			</div>
			<!-- End Version Control v2 -->
		</td>
	</tr>
<?php } ?>
	<tr>
		<td class="highlight"></td>
		<td>
			<input
				type="submit"
				class="btnDo"
				onmousemove="this.className='btnOn'"
				onmouseout="this.className='btnDo'"
				title="<?php echo $lang->display('Update'); ?>"
				value="<?php echo $lang->display('Update'); ?>"
				onclick="validateForm('ArtworkName','Artwork name','R','version','Version','R');return document.returnValue;"
			/>
			<input
				type="reset"
				class="btnOff"
				onmousemove="this.className='btnOn'"
				onmouseout="this.className='btnOff'"
				title="<?php echo $lang->display('Reset'); ?>"
				value="<?php echo $lang->display('Reset'); ?>"
			/>
		</td>
	</tr>
</table>
<input name="redirect" type="hidden" value="<?php echo $redirect; ?>">
<input name="update" type="hidden" value="edit_art_form">
</form>