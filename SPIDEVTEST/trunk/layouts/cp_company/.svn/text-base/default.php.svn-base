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
					<div class="ico"><?php echo '<img src="'.IMG_PATH.'header/ico_company.png">'; ?></div>
					<div class="txt"><?php echo $lang->display('Company Manager'); ?></div>
				</div>
				<div class="options">
					<?php if($issuperadmin) { ?>
					<!-- New -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('New'); ?>">
						<a href="javascript:void(0);" onclick="SubmitForm('listform','new');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_new.png">'; ?></div>
							<div><?php echo $lang->display('New'); ?></div>
						</a>
					</div>
					<?php } ?>
					<!-- View -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('View'); ?>">
						<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) { SubmitForm('listform','view'); }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_view.png">'; ?></div>
							<div><?php echo $lang->display('View'); ?></div>
						</a>
					</div>
					<!-- Fonts -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Fonts'); ?>">
						<a href="javascript:void(0);" onclick="var x=$('#listview').find(':checked').val();window.location='/index.php?layout=cp_font_sub&company='+(x?x:0)+'&show=Used';">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'header/ico_font_sub.png">'; ?></div>
							<div><?php echo $lang->display('Fonts'); ?></div>
						</a>
					</div>
					<!-- Edit -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Edit'); ?>">
						<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) { SubmitForm('listform','edit'); }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_edit.png">'; ?></div>
							<div><?php echo $lang->display('Edit'); ?></div>
						</a>
					</div>
					<?php if($issuperadmin) { ?>
					<!-- Delete -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Delete'); ?>">
						<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) { if(confirm('<?php echo $lang->display('Are you sure you want to delete the selected?'); ?>')) { SubmitForm('listform','delete'); } }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_delete.png">'; ?></div>
							<div><?php echo $lang->display('Delete'); ?></div>
						</a>
					</div>
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
					<?php require_once(MODULES.'mod_list_search.php'); ?>
				</div>
				<div class="list">
					<table id="listview" width="100%" cellpadding="5" cellspacing="0" border="0">
						<tr>
							<th width="2%" align="center">#</th>
							<th width="2%" align="center">
								<input type="checkbox" class="checkbox" name="checkall" id="checkall" onclick="jQueryCheckAll('listform',this.id,'#listview tr');">
							</th>
							<th width="38%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','name','<?php echo $pre; ?>');">
									<?php echo $lang->display('Company Name'); ?>
								</a>
							</th>
							<th width="4%" align="center">
								<a href="javascript:void(0);" onclick="SetOrder('listform','agency','<?php echo $pre; ?>');">
									<?php echo $lang->display('Agency'); ?>
								</a>
							</th>
							<th width="4%" align="center">
								<a href="javascript:void(0);" onclick="SetOrder('listform','userno','<?php echo $pre; ?>');">
									<?php echo $lang->display('Users'); ?>
								</a>
							</th>
							<th width="20%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','web','<?php echo $pre; ?>');">
									<?php echo $lang->display('Website'); ?>
								</a>
							</th>
							<th width="12%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','system','<?php echo $pre; ?>');">
									<?php echo $lang->display('System Name'); ?>
								</a>
							</th>
							<th width="12%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','sp','<?php echo $pre; ?>');">
									<?php echo $lang->display('Service Package'); ?>
								</a>
							</th>
							<th width="4%" align="center">
								<a href="javascript:void(0);" onclick="SetOrder('listform','credits','<?php echo $pre; ?>');">
									<?php echo $lang->display('Credits'); ?>
								</a>
							</th>
							<th width="2%" align="center">
								<a href="javascript:void(0);" onclick="SetOrder('listform','id','<?php echo $pre; ?>');">ID</a>
							</th>
						</tr>
						<?php
							$query =  sprintf("SELECT companies.companyID AS id, companies.companyName AS name, companies.credits,
												companies.agency, companies.companyWeb AS web, companies.systemName AS system,
												service_packages.name AS sp,
												COUNT(users.userID) AS userno
												FROM companies
												LEFT JOIN service_packages ON companies.packageID = service_packages.id
												LEFT JOIN users ON companies.companyID = users.companyID
												WHERE %s
												(companies.companyName LIKE '%s'
												OR companies.companyWeb LIKE '%s'
												OR companies.systemName LIKE '%s'
												OR service_packages.name LIKE '%s')
												GROUP BY companies.companyID
												ORDER BY `%s` %s
												LIMIT %d
												OFFSET %d",
												mysql_real_escape_string($sub),
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
									echo '<td><a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout='.$layout.'&task=edit&id='.$row['id'].'\');">'.$row['name'].'</a></td>';
									echo '<td align="center">';
									if($row['agency']==1) {
										echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout='.$layout.'&option=agency&do=disable&id='.$row['id'].'\')"><img src="'.IMG_PATH.'ico_enable.png" title="'.$lang->display('Disable').'"></a>';
									} else {
										echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout='.$layout.'&option=agency&do=enable&id='.$row['id'].'\')"><img src="'.IMG_PATH.'ico_disable.png" title="'.$lang->display('Enable').'"></a>';
									}
									echo '</td>';
									echo '<td align="center">'.$row['userno'].'</td>';
									echo '<td><a href="'.$row['web'].'" target="_blank">'.$row['web'].'</a></td>';
									echo '<td>'.$row['system'].'</td>';
									echo '<td>'.$row['sp'].'</td>';
									echo '<td align="center"><a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=cp_credit&id='.$row['id'].'\')">'.$row['credits'].'</a></td>';
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