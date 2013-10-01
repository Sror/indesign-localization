<?php
BuildHelperDiv($lang->display('Joboption Manager').' - '.$lang->display('Joboption Manager'));
$navStatus = array("cpanel");
require_once(MODULES.'mod_header.php');
BuildPageIntro($lang->display('Control Panel'),$lang->display('CP Intro'));
require_once(MODULES.'mod_filesystem.php');

/******************************************************************************
	Miscelanious functions - should probably be globally availible
******************************************************************************//*
function ByteNormalise($bytes){
	$mul = array('B','KiB','MiB','GiB','TiB','PiB','YiB');
	$lim = 1024;
	foreach($mul as $n=>$suffix){
		if($bytes < $lim)break;
		$lim *= 1024;
	}
	return rtrim(rtrim(sprintf("%.3f",$bytes/($lim/1024)),'0'),'.').$suffix;
}*/
/******************************************************************************
	Processing of the forms - all but the handeling of download are done
	here so that errors can be reported in the page, download sends a
	file so needs to before any headers
******************************************************************************/
if(!empty($_POST['form'])){
	// Upload a new file
	if($_POST['form']=='upload'){
		if($_FILES['file']['error'] > 0){
			// Return some error information
			?>
			<script>$(function(){
				alert("There was an error uploading the file");
			});</script>';
			<?php
		}else{
			// move the temporary file to a new file
			if($_POST['name'])
				$dest = filename_encode($_POST['name']) . JOBOPTIONS_EXTENSION;
			else
				$dest = filename_encode(pathinfo($_FILES['file']['name'],PATHINFO_FILENAME)) . JOBOPTIONS_EXTENSION;
			
			if(strlen($dest)>255){
				// return an error as the name is too long
				?>
				<script>$(function(){
					alert('The chosen filename is too long (over 255 characters, some characters count double)';
					});
				</script>;
				<?php
			}elseif(file_exists($dest=(JOBOPTIONS_DIR . $dest)) && !isset($_POST['replace'])){?>
				<script>$(document).ready(function(){
					alert('A file with the same name already exists');
				});
				</script>
				<?php
			}else{
				rename($_FILES['file']['tmp_name'], $dest);
			}
		}
		
	// Rename the selected items, empty names are allowed although unlikely
	}elseif($_POST['form']=='rename'){
		foreach($_POST['modify'] as $i){
			$i = (int)$i;
			// Rename an existing file
			if(($old=$_POST['oldname'][$i]) !== ($new=$_POST['newname'][$i])){
				if($_POST['newname'][$i]==''){
					echo "
					<script>$(function(){
						alert('Empty file name is invalid');
					});</script>";
					continue;
				}
				/*	Stop bad windows names all together?
				*/
				$orig = JOBOPTIONS_DIR . filename_encode($_POST['oldname'][$i]) . JOBOPTIONS_EXTENSION;
				$newname = filename_encode($_POST['newname'][$i]) . JOBOPTIONS_EXTENSION;
				// could check for issue with new names here then notify
				if(!file_exists($orig)){
					echo "
					<script>$(functino(){
						alert('${orig} The chosen file no longer exists');
					});</script>";
					continue; // could notify user in case of legitimate error
				}elseif(strlen($newname)>255){
					echo "
					<script>$f(function(){
						alert('The chosen filename is too long (over 255 characters, some characters count double)');
					});</script>";
					continue; // should notify user that name is too long
				}elseif(file_exists($newname=JOBOPTIONS_DIR . $newname)){
					echo "
					<script>$(function(){
						alert('Unable to rename ${old} to ${new} as a file by that name already exists');
					});</script>";
					continue;
				}
				rename($orig, $newname);
			}
		}
		
	// Remove selected files, browser seems to remove nulls so nulls in file names are an issue
	}elseif($_POST['form']=='delete'){
		foreach($_POST['modify'] as $i){
			$loc = JOBOPTIONS_DIR . filename_encode($_POST['oldname'][(int)$i]);
			// change the file extension so removed files can be recovered if need be unless
			// a file by the same name has been deleted since
			@rename($loc . JOBOPTIONS_EXTENSION, $loc . JOBOPTIONS_DEL);
		}
	}
}
/******************************************************************************
	Display Joboption listing
******************************************************************************/
//$sortBy		= in_array($_POST['by'],array('name','size','modified')) ? $_POST['by'] : 'name';
$sortOrder	= $_POST['order']=='asc'? 'asc' : 'dsc';
?>
<script type="text/javascript" src="javascripts/jquery/jquery-1.6.2.js"></script>
<div id="wrapperWhite">
	<div class="controlScroll">
		<div class="controlselectScroll">
			<!-- Toolbar -->
			<div class="toolbar">
				<div class="title">
					<div class="ico"><?php echo '<img src="'.IMG_PATH.'header/ico_joboptions.png">'; ?></div>
					<div class="txt"><?php echo $lang->display('Joboption Manager'); ?></div>
				</div>
				<div class="options">
					<!-- Rename -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Rename'); ?>">
						<a href="javascript:void(0);" onclick="if(CheckSelected('listform','modify')) {SubmitForm('listform','rename');hidediv('loadingme');}">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_save.png">'; ?></div>
							<div><?php echo $lang->display('Save'); ?></div>
						</a>
					</div>
					<!-- Upload -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Upload'); ?>">
						<a href="javascript:void(0);" onclick="Popup('helper','blur');DoAjax('to=<?php echo $layout; ?>','window','modules/mod_upload.php');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_upload.png">'; ?></div>
							<div><?php echo $lang->display('Upload'); ?></div>
						</a>
					</div>
					<!-- Delete -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Delete'); ?>">
						<a href="javascript:void(0);" onclick="if(CheckSelected('listform','modify')) { if(confirm('<?php echo $lang->display('Are you sure you want to delete the selected joboptions?'); ?>')) { SubmitForm('listform','delete'); } }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_delete.png">'; ?></div>
							<div><?php echo $lang->display('Delete'); ?></div>
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
					action="index.php?layout=<?php echo $layout; ?>"
					method="POST"
					enctype="multipart/form-data"
					onsubmit="Popup('loadingme','waiting');"
				>
				<div class="option">
					<div class="left">
						<!-- Left hand filter options -->
						<label for="sortorder">Order: </label>
						<select
							class="input"
							onfocus="this.className='inputOn'"
							onblur="this.className='input'"
							id="sortorder"
						>
							<option value="asc" <?php if($sortOrder=='asc')echo 'selected';?>>Ascending</option>
							<option value="dsc" <?php if($sortOrder=='dsc')echo 'selected';?>>Descending</option>
						</select>
						<!-- Submits files but gets rid of changes - fix -->
						<input type="button" title="Go" value="Go" onmouseout="this.className='btnDo'" onmousemove="this.className='btnOn'" class="btnDo" onclick="SetOrder('listform',$('#sortby option:selected').val(),$('#sortorder option:selected').val());">
					</div>
					<div class="clear"></div>
				</div>
				<div class="list">
					<table id="listview" width="100%" cellpadding="5" cellspacing="0" border="0">
						<tr>
							<th width="1em" align="center">
								<input type="checkbox" class="checkbox" name="checkall" id="checkall" onclick="jQueryCheckAll('listform',this.id,'#listview tr');">
							</th>
							<th width="100%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','name','<?php echo ($sortOrder=='asc'?'dsc':'asc'); ?>');">
									<?php echo $lang->display('File Name'); ?>
								</a>
							</th>
							<th>
								<?php echo $lang->display('Download'); ?>
							</th>
						</tr>
						<?php
						$files = list_joboptions($sortOrder=='asc');
						$i=0;
						echo "<tr><td colspan=\"3\">${x}</td></tr>";
						if(count($files)==0)
							echo '<tr><td colspan="3" align="center">There are no joboptions to display</td></tr>';
						else
							foreach($files as $name=>$file){
								$name = htmlspecialchars($name);
								$location = JOBOPTIONS . urlencode($file);
								echo "
									<tr>
										
										<td><input name=\"modify[]\" type=\"checkbox\" value=\"${i}\"/></td>
										<td>
											<input name=\"oldname[]\" value=\"${name}\" type=\"text\" readonly style=\"border:none;background-color:inherit;color:inherit;width:100%\"/></br/>
											<input name=\"newname[]\" value=\"${name}\" type=\"text\" pattern='[^	\\/\?<>\*:\"]*[^	\\/\?<>\*: \.\"]+' style=\"width:100%\"/></td>
										<td><a target=\"_blank\" href=\"${location}\">" . $lang->display('Download') . "</a></td>
									</tr>";
								$i++;
							}
						?>
						<input type="hidden" name="by" id="by" value="">
						<input type="hidden" name="order" id="order" value="<?php echo $sortOrder; ?>">
					</table>
				</div><input type="hidden" name="form" id="form">
				</form>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="javascripts/ajax.js"></script>
<script type="text/javascript" src="javascripts/datepicker.js"></script>
<script type="text/javascript" src="javascripts/dom_drag.js"></script>
<script language="javascript">Drag.init(document.getElementById('handle'),document.getElementById('root'));</script>