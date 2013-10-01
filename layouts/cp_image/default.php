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
					<div class="ico"><?php echo '<img src="'.IMG_PATH.'header/ico_image.png">'; ?></div>
					<div class="txt"><?php echo $lang->display('Image Manager'); ?></div>
				</div>
				<div class="options">
					<!-- New -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('New'); ?>">
						<a href="javascript:void(0);" onclick="SubmitForm('listform','new');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_new.png">'; ?></div>
							<div><?php echo $lang->display('New'); ?></div>
						</a>
					</div>
					<!-- Edit -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Edit'); ?>">
						<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) { SubmitForm('listform','edit'); }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_edit.png">'; ?></div>
							<div><?php echo $lang->display('Edit'); ?></div>
						</a>
					</div>
					<!-- Delete -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Delete'); ?>">
						<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) { if(confirm('<?php echo $lang->display('Are you sure you want to delete the selected?'); ?>')) { SubmitForm('listform','delete'); } }">
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
						<?php require_once(MODULES.'mod_list_search.php'); ?>
					</div>
					<div class="right">
						<div class="filter">
							<select
								class="input"
								onfocus="this.className='inputOn'"
								onblur="this.className='input'"
								name="filter_company"
								id="filter_company"
								onchange="SubmitForm('listform','');"
								title="<?php echo $lang->display('Please Select Company'); ?>"
							>
							<?php BuildCompanyList($company_id,$issuperadmin); ?>
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
							<th>
								<a href="javascript:void(0);" onclick="SetOrder('listform','preview','<?php echo $pre; ?>');">
									<?php echo $lang->display('Preview'); ?>
								</a>
							</th>
							<th>
								<a href="javascript:void(0);" onclick="SetOrder('listform','lang','<?php echo $pre; ?>');">
									<?php echo $lang->display('Source Language'); ?>
								</a>
							</th>
							<th>
								<a href="javascript:void(0);" onclick="SetOrder('listform','brand','<?php echo $pre; ?>');">
									<?php echo $lang->display('Brand Name'); ?>
								</a>
							</th>
							<th>
								<a href="javascript:void(0);" onclick="SetOrder('listform','subject','<?php echo $pre; ?>');">
									<?php echo $lang->display('Subject'); ?>
								</a>
							</th>
							<th>
								<a href="javascript:void(0);" onclick="SetOrder('listform','username','<?php echo $pre; ?>');">
									<?php echo $lang->display('Uploaded by'); ?>
								</a>
							</th>
							<th>
								<a href="javascript:void(0);" onclick="SetOrder('listform','time','<?php echo $pre; ?>');">
									<?php echo $lang->display('Last Update'); ?>
								</a>
							</th>
							<th width="4%" align="center">
								<a href="javascript:void(0);" onclick="SetOrder('listform','imgusage','<?php echo $pre; ?>');">
									<?php echo $lang->display('Usage'); ?>
								</a>
							</th>
							<th width="2%" align="center">
								<a href="javascript:void(0);" onclick="SetOrder('listform','id','<?php echo $pre; ?>');">ID</a>
							</th>
						</tr>
						<?php
							$query =  sprintf("SELECT images.id, images.content, images.user_id, images.time,
												users.username,
												subjects.subjectTitle AS subject,
												brands.brandName AS brand,
												languages.languageName AS lang, languages.flag,
												COUNT(img_usage.id) AS imgusage
												FROM images
												LEFT JOIN users ON users.userID = images.user_id
												LEFT JOIN companies ON users.companyID = companies.companyID
												LEFT JOIN subjects ON subjects.subjectID = images.subject_id
												LEFT JOIN brands ON brands.brandID = images.brand_id
												LEFT JOIN languages ON languages.languageID = images.lang_id
												LEFT JOIN img_usage ON img_usage.img_id = images.id
												WHERE users.companyID = %d
												AND images.type_id = %d
												AND (users.username LIKE '%s'
												OR subjects.subjectTitle LIKE '%s'
												OR brands.brandName LIKE '%s'
												OR languages.languageName LIKE '%s')
												GROUP BY images.id
												ORDER BY `%s` %s
												LIMIT %d
												OFFSET %d",
												$company_id,
												IMG_LIBRARY,
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
									$img_content = $row['content'];
									$tmp_file = ROOT.TMP_DIR.basename($img_content);
									if(!file_exists($tmp_file)) {
										copy($img_content, $tmp_file);
									}
									$style = $counter%2==0 ? 'even' : 'odd';
									echo '<tr class="'.$style.'">';
									echo '<td align="center">'.$counter.'</td>';
									echo '<td align="center"><input type="checkbox" class="checkbox" name="id[]" id="id[]" value="'.$row['id'].'"></td>';
									echo '<td>';
									echo '<div class="preview" onmouseover="display(\'preview'.$row['id'].'\')" onmouseout="hidediv(\'preview'.$row['id'].'\')">';
									if(!empty($img_content) && file_exists($tmp_file)) {
										$preview = TMP_DIR.basename($img_content);
									} else {
										$preview = IMG_PATH.'img_missing.png';
									}
									echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout='.$layout.'&task=edit&id='.$row['id'].'\');"><img src="'.$preview.'" /></a>';
									echo '</div>';
									echo '<div id="preview'.$row['id'].'" class="img" style="display:none;"><img src="'.$preview.'"></div>';
									echo '</td>';
									echo '<td><img src="images/flags/'.$row['flag'].'" title="'.$lang->display($row['lang']).'"> '.$lang->display($row['lang']).'</td>';
									echo '<td>'.$row['brand'].'</td>';
									echo '<td>'.$lang->display($row['subject']).'</td>';
									echo '<td><a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=user&id='.$row['user_id'].'\');">'.$row['username'].'</a></td>';
									echo '<td>'.date(FORMAT_DATE,strtotime($row['time'])).'</td>';
									echo '<td align="center">'.$row['imgusage'].'</td>';
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