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
					<div class="ico"><?php echo '<img src="'.IMG_PATH.'header/ico_ftp.png">'; ?></div>
					<div class="txt"><?php echo $lang->display('FTP Manager'); ?></div>
				</div>
				<div class="options">
					<!-- New -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('New'); ?>">
						<a href="javascript:void(0);" onclick="SubmitForm('listform','new');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_new.png">'; ?></div>
							<div><?php echo $lang->display('New'); ?></div>
						</a>
					</div>
					<!-- View -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('View'); ?>">
						<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) { SubmitForm('listform','view'); }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_view.png">'; ?></div>
							<div><?php echo $lang->display('View'); ?></div>
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
					action="index.php?layout=<?php echo $layout; ?>&task=manage"
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
							<th width="30%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','ftp_host','<?php echo $pre; ?>');">
									<?php echo $lang->display('FTP Host'); ?>
								</a>
							</th>
							<th width="20%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','ftp_username','<?php echo $pre; ?>');">
									<?php echo $lang->display('Username'); ?>
								</a>
							</th>
							<th width="20%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','ftp_dir','<?php echo $pre; ?>');">
									<?php echo $lang->display('Default Directory'); ?>
								</a>
							</th>
							<th width="16%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','company_name','<?php echo $pre; ?>');">
									<?php echo $lang->display('Company Name'); ?>
								</a>
							</th>
							<th width="8%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','system_name','<?php echo $pre; ?>');">
									<?php echo $lang->display('System Name')." / FTP"; ?>
								</a>
							</th>
							<th width="2%" align="center">
								<a href="javascript:void(0);" onclick="SetOrder('listform','id','<?php echo $pre; ?>');">ID</a>
							</th>
						</tr>
						<?php
							$query =  sprintf("SELECT ftps.id, ftps.ftp_host, ftps.ftp_username, ftps.ftp_dir,
												companies.companyName AS company_name, companies.systemName AS system_name
												FROM ftps
												LEFT JOIN companies ON ftps.company_id = companies.companyID
												WHERE ftps.company_id = %d
												AND (companies.companyName LIKE '%s'
												OR companies.systemName LIKE '%s'
												OR ftps.ftp_host LIKE '%s'
												OR ftps.ftp_username LIKE '%s')
												ORDER BY `%s` %s
												LIMIT %d
												OFFSET %d",
												$company_id,
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
									echo '<td><a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout='.$layout.'&task=edit&id='.$row['id'].'\');">'.$row['ftp_host'].'</a></td>';
									echo '<td>'.$row['ftp_username'].'</td>';
									echo '<td>'.$row['ftp_dir'].'</td>';
									echo '<td>'.$row['company_name'].'</td>';
									echo '<td>'.$row['system_name'].'</td>';
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