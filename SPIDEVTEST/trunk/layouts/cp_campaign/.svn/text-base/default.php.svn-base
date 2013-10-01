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
					<div class="ico"><?php echo '<img src="'.IMG_PATH.'header/ico_campaign.png">'; ?></div>
					<div class="txt"><?php echo $lang->display('Campaign Manager'); ?></div>
				</div>
				<div class="options">
					<?php if($status == STATUS_ACTIVE) { ?>
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
					<!-- Complete -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Complete'); ?>">
						<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) { SubmitForm('listform','complete'); }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_apply.png">'; ?></div>
							<div><?php echo $lang->display('Complete'); ?></div>
						</a>
					</div>
					<?php } else { ?>
					<!-- Restore -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Restore'); ?>">
						<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) { SubmitForm('listform','restore'); }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_restore.png">'; ?></div>
							<div><?php echo $lang->display('Restore'); ?></div>
						</a>
					</div>
					<?php } ?>
					<?php if($status == STATUS_COMPLETE) { ?>
					<!-- Archive -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Archive'); ?>">
						<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) { SubmitForm('listform','archive'); }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_archive.png">'; ?></div>
							<div><?php echo $lang->display('Archive'); ?></div>
						</a>
					</div>
					<?php } ?>
					<?php if($status == STATUS_ARCHIVED) { ?>
					<!-- Unarchive -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Unarchive'); ?>">
						<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) { SubmitForm('listform','unarchive'); }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_unarchive.png">'; ?></div>
							<div><?php echo $lang->display('Unarchive'); ?></div>
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
					<?php if($status != STATUS_TRASHED) { ?>
					<!-- Trash -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Trash'); ?>">
						<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) { SubmitForm('listform','trash'); }">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_trash.png">'; ?></div>
							<div><?php echo $lang->display('Trash'); ?></div>
						</a>
					</div>
					<?php } else { ?>
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
							<select
								class="input"
								onfocus="this.className='inputOn'"
								onblur="this.className='input'"
								name="campaign_status"
								id="campaign_status"
								onchange="SubmitForm('listform','');"
								title="<?php echo $lang->display('Select Status'); ?>"
							>
							<?php BuildCampStatusList($status); ?>
							</select>
							<select
								class="input"
								onfocus="this.className='inputOn'"
								onblur="this.className='input'"
								name="filter_lang"
								id="filter_lang"
								onchange="SubmitForm('listform','');"
								title="<?php echo $lang->display('Select Language'); ?>"
							>
							<?php BuildLangList($lang_id); ?>
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
							<th width="20%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','name','<?php echo $pre; ?>');">
									<?php echo $lang->display('Campaign Title'); ?>
								</a>
							</th>
							<th width="12%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','language','<?php echo $pre; ?>');">
									<?php echo $lang->display('Source Language'); ?>
								</a>
							</th>
							<th width="4%" align="center">
								<a href="javascript:void(0);" onclick="SetOrder('listform','artworkno','<?php echo $pre; ?>');">
									<?php echo $lang->display('Artworks'); ?>
								</a>
							</th>
							<th width="14%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','company','<?php echo $pre; ?>');">
									<?php echo $lang->display('Company'); ?>
								</a>
							</th>
							<th width="12%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','brand','<?php echo $pre; ?>');">
									<?php echo $lang->display('Brand Name'); ?>
								</a>
							</th>
							<th width="8%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','owner','<?php echo $pre; ?>');">
									<?php echo $lang->display('Owner'); ?>
								</a>
							</th>
							<th width="8%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','lastupdate','<?php echo $pre; ?>');">
									<?php echo $lang->display('Last Update'); ?>
								</a>
							</th>
							<th width="8%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','status','<?php echo $pre; ?>');">
									<?php echo $lang->display('Status'); ?>
								</a>
							</th>
							<th width="8%">
								<a href="javascript:void(0);" onclick="SetOrder('listform','ref','<?php echo $pre; ?>');">
									<?php echo $lang->display('Reference'); ?>
								</a>
							</th>
							<th width="2%" align="center">
								<a href="javascript:void(0);" onclick="SetOrder('listform','id','<?php echo $pre; ?>');">ID</a>
							</th>
						</tr>
						<?php
							$query =  sprintf("SELECT campaigns.campaignID AS id, campaigns.campaignName AS name,
												campaigns.ownerID, campaigns.ref, campaigns.lastEdit AS lastupdate,
												COUNT(artworks.artworkID) AS artworkno,
												languages.flag, languages.languageName AS language,
												users.username AS owner, users.companyID,
												companies.companyName AS company,
												brands.brandName AS brand,
												status.statusInfo AS status
												FROM campaigns
												LEFT JOIN artworks ON campaigns.campaignID = artworks.campaignID
												LEFT JOIN users ON campaigns.ownerID = users.userID
												LEFT JOIN companies ON users.companyID = companies.companyID
												LEFT JOIN languages ON campaigns.sourceLanguageID = languages.languageID
												LEFT JOIN brands ON campaigns.brandID = brands.brandID
												LEFT JOIN status ON campaigns.campaignStatus = status.statusID
												WHERE campaignStatus = %d
												AND users.companyID = %d
												%s
												AND (campaigns.campaignName LIKE '%s'
												OR campaigns.ref LIKE '%s'
												OR languages.languageName LIKE '%s'
												OR companies.companyName LIKE '%s'
												OR brands.brandName LIKE '%s'
												OR users.username LIKE '%s'
												OR status.statusInfo LIKE '%s')
												GROUP BY campaigns.campaignID
												ORDER BY `%s` %s
												LIMIT %d
												OFFSET %d",
												$status,
												$company_id,
												mysql_real_escape_string($sub),
												"%".mysql_real_escape_string($keyword)."%",
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
								$counter = $offset + 1;
								while($row = mysql_fetch_assoc($result)) {
									$style = $counter%2==0 ? 'even' : 'odd';
									echo '<tr class="'.$style.'">';
									echo '<td align="center">'.$counter.'</td>';
									echo '<td align="center"><input type="checkbox" class="checkbox" name="id[]" id="id[]" value="'.$row['id'].'"></td>';
									echo '<td><a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout='.$layout.'&task=edit&id='.$row['id'].'\');">'.$row['name'].'</a></td>';
									echo '<td><img src="images/flags/'.$row['flag'].'" title="'.$lang->display($row['language']).'"> '.$lang->display($row['language']).'</td>';
									echo '<td align="center">'.$row['artworkno'].'</td>';
									echo '<td><a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=company&id='.$row['companyID'].'\');">'.$row['company'].'</a></td>';
									echo '<td>'.$row['brand'].'</td>';
									echo '<td><a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=user&id='.$row['ownerID'].'\');">'.$row['owner'].'</a></td>';
									echo '<td>'.date(FORMAT_DATE,strtotime($row['lastupdate'])).'</td>';
									echo '<td>'.$lang->display($row['status']).'</td>';
									echo '<td>';
									echo !empty($row['ref']) ? $row['ref'] : '<div class="grey">'.$lang->display('N/S').'</div>';
									echo '</td>';
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