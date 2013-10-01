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
					<div class="ico"><?php echo '<img src="'.IMG_PATH.'header/ico_user.png">'; ?></div>
					<div class="txt"><?php echo $lang->display('User Manager'); ?></div>
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
							<?php BuildCompanyList($_SESSION['companyID'],$issuperadmin, $filter_company_id); ?>
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
								<a href="javascript:void(0);" onclick="SetOrder('listform','username','<?php echo $pre; ?>');">
									<?php echo $lang->display('Username'); ?>
								</a>
							</th>
							<th>
								<a href="javascript:void(0);" onclick="SetOrder('listform','usergroup','<?php echo $pre; ?>');">
									<?php echo $lang->display('User Group'); ?>
								</a>
							</th>
							<th width="4%" align="center">
								<a href="javascript:void(0);" onclick="SetOrder('listform','agent','<?php echo $pre; ?>');">
									<?php echo $lang->display('Agent'); ?>
								</a>
							</th>
							<th>
								<a href="javascript:void(0);" onclick="SetOrder('listform','forename','<?php echo $pre; ?>');">
									<?php echo $lang->display('Forename'); ?>
								</a>
							</th>
							<th>
								<a href="javascript:void(0);" onclick="SetOrder('listform','surname','<?php echo $pre; ?>');">
									<?php echo $lang->display('Surname'); ?>
								</a>
							</th>
							<th>
								<a href="javascript:void(0);" onclick="SetOrder('listform','company','<?php echo $pre; ?>');">
									<?php echo $lang->display('Company'); ?>
								</a>
							</th>
							<th width="4%" align="center">
								<a href="javascript:void(0);" onclick="SetOrder('listform','active','<?php echo $pre; ?>');">
									<?php echo $lang->display('Active'); ?>
								</a>
							</th>
							<th>
								<a href="javascript:void(0);" onclick="SetOrder('listform','lastLogin','<?php echo $pre; ?>');">
									<?php echo $lang->display('Last Login'); ?>
								</a>
							</th>
							<th width="2%" align="center">
								<a href="javascript:void(0);" onclick="SetOrder('listform','id','<?php echo $pre; ?>');">ID</a>
							</th>
						</tr>
						<?php
							$query =  sprintf("SELECT users.userID AS id, users.username, users.companyID, users.forename, users.surname,
												users.active, users.lastLogin, users.agent,
												aro_groups.name AS usergroup,
												companies.companyName AS company
												FROM users
												LEFT JOIN aro_groups ON users.userGroupID = aro_groups.id
												LEFT JOIN companies ON users.companyID = companies.companyID
												WHERE users.companyID = %d
												AND (users.username LIKE '%s'
												OR users.forename LIKE '%s'
												OR users.surname LIKE '%s'
												OR users.email LIKE '%s'
												OR aro_groups.name LIKE '%s'
												OR companies.companyName LIKE '%s')
												ORDER BY `%s` %s
												LIMIT %d
												OFFSET %d",
												$filter_company_id,
												"%".mysql_real_escape_string($keyword)."%",
												"%".mysql_real_escape_string($keyword)."%",
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
									echo '<td><a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout='.$layout.'&task=edit&id='.$row['id'].'\');">'.$row['username'].'</a></td>';
									echo '<td>'.$lang->display($row['usergroup']).'</td>';
									echo '<td align="center">';
									if($row['agent']==1) {
										echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout='.$layout.'&option=agent&do=disable&id='.$row['id'].'\')"><img src="'.IMG_PATH.'ico_enable.png" title="'.$lang->display('Disable').'" /></a>';
									} else {
										echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout='.$layout.'&option=agent&do=enable&id='.$row['id'].'\')"><img src="'.IMG_PATH.'ico_disable.png" title="'.$lang->display('Enable').'" /></a>';
									}
									echo '</td>';
									echo '<td>'.$row['forename'].'</td>';
									echo '<td>'.$row['surname'].'</td>';
									echo '<td><a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=company&id='.$row['companyID'].'\');">'.$row['company'].'</a></td>';
									echo '<td align="center">';
									if($row['active']==1) {
										if($row['id']!=ADMIN_USERID) echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout='.$layout.'&option=active&do=disable&id='.$row['id'].'\')">';
										echo '<img src="'.IMG_PATH.'ico_enable.png"';
										if($row['id']!=ADMIN_USERID) echo ' title="'.$lang->display('Disable').'"></a>';
									} else {
										echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout='.$layout.'&option=active&do=enable&id='.$row['id'].'\')"><img src="'.IMG_PATH.'ico_disable.png" title="'.$lang->display('Enable').'"></a>';
									}
									echo '</td>';
									echo '<td>'.date(FORMAT_DATE,strtotime($row['lastLogin'])).'</td>';
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