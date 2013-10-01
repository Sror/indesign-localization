<?php
BuildHelperDiv($lang->display('Campaigns'));
$navStatus = array("campaigns");
require_once(MODULES.'mod_header.php');
BuildPageIntro($lang->display('Campaigns'));
?>
<div id="wrapperWhite">
	<div class="controlScroll">
		<div class="controlselectScroll">
			<!-- Toolbar -->
			<div class="toolbar">
				<div class="title">
					<div class="ico">
						<?php echo '<img src="'.IMG_PATH.'header/ico_campaign.png">'; ?>
					</div>
					<div class="txt">
						<?php echo $lang->display('Campaigns'); ?>
						<div class="intro"><?php echo $lang->display('Campaigns Intro'); ?></div>
					</div>
				</div>
				<div class="options">
					<?php if($status == STATUS_ACTIVE) { ?>
						<?php if ($acl->acl_check("campaigns","new",$_SESSION['companyID'],$_SESSION['userID'])) { ?>
						<!-- New -->
						<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('New'); ?>">
							<a href="javascript:void(0);" onclick="Popup('helper','blur');DoAjax('','window','modules/mod_camp_new.php');">
								<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_new.png">'; ?></div>
								<div><?php echo $lang->display('New'); ?></div>
							</a>
						</div>
						<?php } ?>
						<?php if ($acl->acl_check("campaigns","manage",$_SESSION['companyID'],$_SESSION['userID'])) { ?>
						<!-- Complete -->
						<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Complete'); ?>">
							<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) { SubmitForm('listform','complete'); }">
								<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_apply.png">'; ?></div>
								<div><?php echo $lang->display('Complete'); ?></div>
							</a>
						</div>
						<?php } ?>
					<?php } else { ?>
						<?php if ($acl->acl_check("campaigns","manage",$_SESSION['companyID'],$_SESSION['userID'])) { ?>
						<!-- Restore -->
						<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Restore'); ?>">
							<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) { SubmitForm('listform','restore'); }">
								<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_restore.png">'; ?></div>
								<div><?php echo $lang->display('Restore'); ?></div>
							</a>
						</div>
						<?php } ?>
					<?php } ?>
					<?php if($status == STATUS_COMPLETE) { ?>
						<?php if ($acl->acl_check("campaigns","manage",$_SESSION['companyID'],$_SESSION['userID'])) { ?>
						<!-- Archive -->
						<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Archive'); ?>">
							<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) { SubmitForm('listform','archive'); }">
								<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_archive.png">'; ?></div>
								<div><?php echo $lang->display('Archive'); ?></div>
							</a>
						</div>
						<?php } ?>
					<?php } ?>
					<?php if($status == STATUS_ARCHIVED) { ?>
						<?php if ($acl->acl_check("campaigns","manage",$_SESSION['companyID'],$_SESSION['userID'])) { ?>
						<!-- Unarchive -->
						<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Unarchive'); ?>">
							<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) { SubmitForm('listform','unarchive'); }">
								<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_unarchive.png">'; ?></div>
								<div><?php echo $lang->display('Unarchive'); ?></div>
							</a>
						</div>
						<?php } ?>
					<?php } ?>
					<!-- Fonts -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Fonts'); ?>">
						<a href="javascript:void(0);" onclick="window.location='/index.php?layout=cp_font_sub&companyID=<?php echo $_SESSION['companyID']; ?>&show=Used';">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'header/ico_font_sub.png">'; ?></div>
							<div><?php echo $lang->display('Fonts'); ?></div>
						</a>
					</div>
					<?php if($status != STATUS_TRASHED) { ?>
						<?php if ($acl->acl_check("campaigns","trash",$_SESSION['companyID'],$_SESSION['userID'])) { ?>
						<!-- Trash -->
						<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Trash'); ?>">
							<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) { SubmitForm('listform','trash'); }">
								<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_trash.png">'; ?></div>
								<div><?php echo $lang->display('Trash'); ?></div>
							</a>
						</div>
						<?php } ?>
					<?php } else { ?>
						<?php if ($acl->acl_check("campaigns","delete",$_SESSION['companyID'],$_SESSION['userID'])) { ?>
						<!-- Delete -->
						<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Delete'); ?>">
							<a href="javascript:void(0);" onclick="if(CheckSelected('listform','id')) { if(confirm('<?php echo $lang->display('Are you sure you want to delete the selected?'); ?>')) { SubmitForm('listform','delete'); } }">
								<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_delete.png">'; ?></div>
								<div><?php echo $lang->display('Delete'); ?></div>
							</a>
						</div>
						<?php } ?>
					<?php } ?>
				</div>
				<div class="clear"></div>
			</div>
			<?php
				if($status==STATUS_ACTIVE) {
					BuildTipMsg('<a href="javascript:void(0);" onclick="Popup(\'helper\',\'blur\');DoAjax(\'\',\'window\',\'modules/mod_camp_new.php\');">'.$lang->display('Want a document translated? Create your campaign now!').'</a>');
				}
			?>
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
							<?php if($view == "thumbnails") { ?>
								<input
									type="checkbox"
									class="checkbox"
									name="checkall"
									id="checkall"
									onclick="jQueryCheckAll('listform',this.id,'.thumbnailBox .off');"
									title="<?php echo $lang->display('Select All'); ?>"
								/>
								<?php echo $lang->display('Select All'); ?>
								<span class="span"></span>
							<?php } ?>
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
								name="filter_view"
								id="filter_view"
								onchange="SubmitForm('listform','');"
								title="<?php echo $lang->display('Select View'); ?>"
							>
							<?php BuildViewList($view); ?>
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
							<select
								class="input"
								onfocus="this.className='inputOn'"
								onblur="this.className='input'"
								name="filter_brand"
								id="filter_brand"
								onchange="SubmitForm('listform','');"
								title="<?php echo $lang->display('Select Brand'); ?>"
							>
							<?php BuildBrandList($brand_id); ?>
							</select>
						</div>
					</div>
					<div class="clear"></div>
				</div>
				<div class="list">
					<div <?php if($view == "thumbnails") echo 'class="thumbnailBoxMargin"'; ?>>
					<?php if($view == "list") { ?>
						<table id="listview" width="100%" cellpadding="5" cellspacing="0" border="0">
							<tr>
								<th width="2%" align="center">#</th>
								<th width="2%" align="center">
									<input type="checkbox" class="checkbox" name="checkall" id="checkall" onclick="jQueryCheckAll('listform',this.id,'#listview tr');" />
								</th>
								<th width="8%" align="center"><?php echo $lang->display('Action'); ?></th>
								<th width="18%">
									<a href="javascript:void(0);" onclick="SetOrder('listform','campaignName','<?php echo $pre; ?>');">
										<?php echo $lang->display('Campaign Title'); ?>
									</a>
								</th>
								<th width="10%">
									<a href="javascript:void(0);" onclick="SetOrder('listform','brandName','<?php echo $pre; ?>');">
										<?php echo $lang->display('Brand Name'); ?>
									</a>
								</th>
								<th width="12%">
									<a href="javascript:void(0);" onclick="SetOrder('listform','forename','<?php echo $pre; ?>');">
										<?php echo $lang->display('Owner'); ?>
									</a>
								</th>
								<th width="4%" align="center">
									<a href="javascript:void(0);" onclick="SetOrder('listform','artworkCount','<?php echo $pre; ?>');">
										<?php echo $lang->display('Artworks'); ?>
									</a>
								</th>
								<th colspan="2">
									<a href="javascript:void(0);" onclick="SetOrder('listform','languageName','<?php echo $pre; ?>');">
										<?php echo $lang->display('Source Language'); ?>
									</a>
								</th>
								<th width="5%">
									<a href="javascript:void(0);" onclick="SetOrder('listform','statusInfo','<?php echo $pre; ?>');">
										<?php echo $lang->display('Status'); ?>
									</a>
								</th>
								<th width="8%">
									<a href="javascript:void(0);" onclick="SetOrder('listform','lastEdit','<?php echo $pre; ?>');">
										<?php echo $lang->display('Last Update'); ?>
									</a>
								</th>
								<?php if ($acl->acl_check("system","cost",$_SESSION['companyID'],$_SESSION['userID'])) { ?>
								<th width="5%" align="right">
									<a href="javascript:void(0);" onclick="SetOrder('listform','cost','<?php echo $pre; ?>');">
										<?php echo $lang->display('Cost'); ?>
									</a>
								</th>
								<?php } ?>
								<th width="12%">
									<a href="javascript:void(0);" onclick="return false;">
										<?php echo $lang->display('Progress'); ?>
									</a>
								</th>
								<th width="2%" align="center">
									<a href="javascript:void(0);" onclick="SetOrder('listform','campaignID','<?php echo $pre; ?>');">ID</a>
								</th>
							</tr>
					<?php } ?>
					<?php
						$query = sprintf("SELECT campaigns.*,
										COUNT(artworks.artworkID) AS artworkCount, SUM(artworks.cost) AS cost,
										users.forename, users.surname,
										companies.companyName,
										languages.languageName, languages.flag,
										brands.brandName,
										status.statusInfo
										FROM campaigns
										LEFT JOIN artworks ON (campaigns.campaignID = artworks.campaignID AND artworks.live = 1)
										LEFT JOIN users ON campaigns.ownerID = users.userID
										LEFT JOIN companies ON users.companyID = companies.companyID
										LEFT JOIN languages ON campaigns.sourceLanguageID = languages.languageID
										LEFT JOIN brands ON campaigns.brandID = brands.brandID
										LEFT JOIN status ON campaigns.campaignStatus = status.statusID
										WHERE campaigns.campaignStatus = %d
										%s
										AND (campaigns.campaignName LIKE '%s'
										OR campaigns.ref LIKE '%s'
										OR languages.languageName LIKE '%s'
										OR companies.companyName LIKE '%s'
										OR brands.brandName LIKE '%s'
										OR users.username LIKE '%s'
										OR users.forename LIKE '%s'
										OR users.surname LIKE '%s'
										OR status.statusInfo LIKE '%s')
										GROUP BY campaigns.campaignID
										ORDER BY `%s` %s
										LIMIT %d
										OFFSET %d",
										$status,
										mysql_real_escape_string($sub),
										"%".mysql_real_escape_string($keyword)."%",
										"%".mysql_real_escape_string($keyword)."%",
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
						$counter = $offset + 1;
						while($row = mysql_fetch_assoc($result)) {
							if(!$DB->check_campaign_acl($row['campaignID'],$_SESSION['companyID'],$_SESSION['userID'])) continue;
							if($view == "thumbnails") {
								//start of thumbnailBox
								echo '<div class="thumbnailBox" title="'.$row['campaignName'].'">';
								echo '<div class="off" onmouseover="display(\'options_'.$row['campaignID'].'\');" onmouseout="hidediv(\'options_'.$row['campaignID'].'\');">';
								//start of picture
								echo '<div class="pic">';
								echo '<div class="thumbnail">';
								echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=campaign&id='.$row['campaignID'].'\');">';
								$query_page = sprintf("SELECT pages.ArtworkID, pages.Page, pages.PreviewFile
													FROM pages
													LEFT JOIN artworks ON pages.ArtworkID = artworks.artworkID
													LEFT JOIN campaigns ON artworks.campaignID = campaigns.campaignID
													WHERE pages.Page = 1
													AND campaigns.campaignID = %d
													AND artworks.live = 1
													ORDER BY artworks.lastUpdate DESC
													LIMIT 1",
													$row['campaignID']);
								$result_page = mysql_query($query_page, $conn) or die(mysql_error());
								$row_page = mysql_fetch_assoc($result_page);
								if(mysql_num_rows($result_page) && !empty($row_page['PreviewFile']) && file_exists(ROOT.PREVIEW_DIR.$row_page['PreviewFile'])) {
									$preview = PREVIEW_DIR.THUMBNAILS_DIR.$row_page['PreviewFile'];
									if(!file_exists(ROOT.$preview)) {
										$DB->RebuildPageThumbnail(PREVIEW_DIR,$row_page['ArtworkID'],$row_page['Page']);
									}
								} else {
									$preview = IMG_PATH.'img_missing.png';
								}
								echo '<img src="'.$preview.'?'.filemtime(ROOT.$preview).'" />';
								echo "</a>";
								echo '</div>';
								echo '<div class="options" id="options_'.$row['campaignID'].'" style="display:none;">';
								echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=campaign&id='.$row['campaignID'].'\');" title="'.$lang->display('View').'"><img src="'.IMG_PATH.'toolbar/ico_view.png" /></a>';
								if($status==STATUS_ACTIVE) {
									if ($acl->acl_check("campaigns","edit",$_SESSION['companyID'],$_SESSION['userID'])) {
										echo '<span class="span"></span><a href="javascript:void(0);" onclick="Popup(\'helper\',\'blur\');DoAjax(\'ref='.$row['campaignID'].'&redirect=campaigns\',\'window\',\'modules/mod_camp_edit.php\');" title="'.$lang->display('Edit').'"><img src="'.IMG_PATH.'toolbar/ico_edit.png" /></a>';
									}
									if($acl->acl_check("campaigns","trash",$_SESSION['companyID'],$_SESSION['userID'])) {
										echo '<span class="span"></span><a href="javascript:void(0);" onclick="if(confirm(\''.$lang->display('Are you sure you want to trash the selected?').'\')) {CheckTheBoxOnly(\'id['.$row['campaignID'].']\',\'id\');SubmitForm(\'listform\',\'trash\');}" title="'.$lang->display('Trash').'"><img src="'.IMG_PATH.'toolbar/ico_trash.png" /></a>';
									}
								}
								if($status==STATUS_TRASHED) {
									if($acl->acl_check("campaigns","manage",$_SESSION['companyID'],$_SESSION['userID'])) {
										echo '<span class="span"></span><a href="javascript:void(0);" onclick="if(confirm(\''.$lang->display('Are you sure you want to restore the selected?').'\')) {CheckTheBoxOnly(\'id['.$row['campaignID'].']\',\'id\');SubmitForm(\'listform\',\'restore\');}" title="'.$lang->display('Restore').'"><img src="'.IMG_PATH.'toolbar/ico_restore.png" /></a>';
									}
									if($acl->acl_check("campaigns","delete",$_SESSION['companyID'],$_SESSION['userID'])) {
										echo '<span class="span"></span><a href="javascript:void(0);" onclick="if(confirm(\''.$lang->display('Are you sure you want to delete the selected?').'\')) {CheckTheBoxOnly(\'id['.$row['campaignID'].']\',\'id\');SubmitForm(\'listform\',\'delete\');}" title="'.$lang->display('Delete').'"><img src="'.IMG_PATH.'toolbar/ico_delete.png" /></a>';
									}
								}
								echo '</div>';
								echo '</div>';
								//end of picture
								//start of txt
								echo '<div class="txt">';
								echo '<div class="title">';
								echo '<div class="right"><input type="checkbox" class="checkbox" name="id['.$row['campaignID'].']" id="id['.$row['campaignID'].']" value="'.$row['campaignID'].'"></div>';
								echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=campaign&id='.$row['campaignID'].'\');">';
								echo DisplayString($row['campaignName']);
								echo '</a>';
								echo '</div>';
								echo '<div class="version">'.$row['brandName'].'</div>';
								echo '<div><span class="subject">'.$lang->display('Owner').':</span> <a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=user&id='.$row['ownerID'].'\');">'.$row['forename'].' '.$row['surname'].'</a></div>';
								echo '<div class="grey">'.$row['companyName'].'</div>';
								echo '<div><span class="subject">'.$lang->display('Artworks').':</span> '.$row['artworkCount'].'</div>';
								echo '<div><span class="subject">'.$lang->display('Source Language').':</span> <img src="images/flags/'.$row['flag'].'" title="'.$row['languageName'].'" /></div>';
								echo '<div><span class="subject">'.$lang->display('Status').':</span> '.$row['statusInfo'].'</div>';
								echo '<div><span class="subject">'.$lang->display('Last Update').':</span> '.date(FORMAT_DATE,strtotime($row['lastEdit'])).'</div>';
								if ($acl->acl_check("system","cost",$_SESSION['companyID'],$_SESSION['userID'])) {
									echo '<div><span class="subject">'.$lang->display('Cost').':</span> '.CURRENCY_SYMBOL.number_format($row['cost'],2).'</div>';
								}
								echo '<div><span class="subject">'.$lang->display('Reference').':</span> ';
								echo !empty($row['ref']) ? $row['ref'] : '<span class="grey">'.$lang->display('N/S').'</span>';
								echo '</div>';
								BuildCampaignProgressBar($row['campaignID']);
								echo '</div>';
								//end of txt
								echo '<div class="clear"></div>';
								echo '</div>';
								echo '</div>';
								//end of thumbnailBox
							}
							
							if($view == "list") {
								$style = $counter%2==0 ? 'even' : 'odd';
								echo '<tr class="'.$style.'" title="'.$row['campaignName'].'">';
								echo '<td align="center">'.$counter.'</td>';
								echo '<td align="center"><input type="checkbox" class="checkbox" name="id['.$row['campaignID'].']" id="id['.$row['campaignID'].']" value="'.$row['campaignID'].'"></td>';
								echo '<td align="center">';
								echo '<div class="ico">';
								echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=campaign&id='.$row['campaignID'].'\');" title="'.$lang->display('View').'"><img src="'.IMG_PATH.'toolbar/ico_view.png" /></a>';
								if($status==STATUS_ACTIVE) {
									if ($acl->acl_check("campaigns","edit",$_SESSION['companyID'],$_SESSION['userID'])) {
										echo '<span class="span"></span><a href="javascript:void(0);" onclick="Popup(\'helper\',\'blur\');DoAjax(\'ref='.$row['campaignID'].'&redirect=campaigns\',\'window\',\'modules/mod_camp_edit.php\');" title="'.$lang->display('Edit').'"><img src="'.IMG_PATH.'toolbar/ico_edit.png" /></a>';
									}
									if($acl->acl_check("campaigns","trash",$_SESSION['companyID'],$_SESSION['userID'])) {
										echo '<span class="span"></span><a href="javascript:void(0);" onclick="if(confirm(\''.$lang->display('Are you sure you want to trash the selected?').'\')) {CheckTheBoxOnly(\'id['.$row['campaignID'].']\',\'id\');SubmitForm(\'listform\',\'trash\');}" title="'.$lang->display('Trash').'"><img src="'.IMG_PATH.'toolbar/ico_trash.png" /></a>';
									}
								}
								if($status==STATUS_TRASHED) {
									if($acl->acl_check("campaigns","manage",$_SESSION['companyID'],$_SESSION['userID'])) {
										echo '<span class="span"></span><a href="javascript:void(0);" onclick="if(confirm(\''.$lang->display('Are you sure you want to restore the selected?').'\')) {CheckTheBoxOnly(\'id['.$row['campaignID'].']\',\'id\');SubmitForm(\'listform\',\'restore\');}" title="'.$lang->display('Restore').'"><img src="'.IMG_PATH.'toolbar/ico_restore.png" /></a>';
									}
									if($acl->acl_check("campaigns","delete",$_SESSION['companyID'],$_SESSION['userID'])) {
										echo '<span class="span"></span><a href="javascript:void(0);" onclick="if(confirm(\''.$lang->display('Are you sure you want to delete the selected?').'\')) {CheckTheBoxOnly(\'id['.$row['campaignID'].']\',\'id\');SubmitForm(\'listform\',\'delete\');}" title="'.$lang->display('Delete').'"><img src="'.IMG_PATH.'toolbar/ico_delete.png" /></a>';
									}
								}
								echo '</div>';
								echo '</td>';
								echo '<td>';
								echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=campaign&id='.$row['campaignID'].'\');">'.DisplayString($row['campaignName']).'</a>';
								if(!empty($row['ref'])) echo '<div class="grey">'.$row['ref'].'</div>';
								echo '</td>';
								echo '<td>'.$row['brandName'].'</td>';
								echo '<td><a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=user&id='.$row['ownerID'].'\');">'.$row['forename'].' '.$row['surname'].'</a>';
								echo '<div class="grey">'.$row['companyName'].'</div>';
								echo '</td>';
								echo '<td align="center">'.$row['artworkCount'].'</td>';
								echo '<td width="3%" align="center"><img src="images/flags/'.$row['flag'].'" title="'.$lang->display($row['languageName']).'" /></td>';
								echo '<td width="7%">'.$lang->display($row['languageName']).'</td>';
								echo '<td>'.$lang->display($row['statusInfo']).'</td>';
								echo '<td>'.date(FORMAT_DATE,strtotime($row['lastEdit'])).'</td>';
								if ($acl->acl_check("system","cost",$_SESSION['companyID'],$_SESSION['userID'])) {
									echo '<td align="right">'.CURRENCY_SYMBOL.number_format($row['cost'],2).'</td>';
								}
								echo '<td>';
								BuildCampaignProgressBar($row['campaignID']);
								echo '</td>';
								echo '<td align="center">'.$row['campaignID'].'</td>';
								echo '</tr>';
								$counter++;
							}
						}
						if($view == "list") echo '</table>';
					?>
					<div class="clear"></div>
					</div>
				</div>
				<?php require_once(MODULES.'mod_list_nav.php'); ?>
				<input type="hidden" name="by" id="by" value="<?php echo $by; ?>">
				<input type="hidden" name="order" id="order" value="<?php echo $order; ?>">
				<input type="hidden" name="form" id="form">
				</form>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="javascripts/ajax.js"></script>
<script type="text/javascript" src="javascripts/dom_drag.js"></script>
<script language="javascript">Drag.init(document.getElementById('handle'),document.getElementById('root'));</script>