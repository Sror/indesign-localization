<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("system","login");
require_once(MODULES.'mod_authorise.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$query = sprintf("SELECT campaigns.*,
				COUNT(artworks.artworkID) AS artworkCount, SUM(artworks.pageCount) AS sum_pages,
				SUM(artworks.wordCount) AS sum_words, SUM(artworks.cost) AS sum_cost,
				users.username,
				companies.companyID, companies.companyName,
				languages.languageName, languages.flag,
				brands.brandName,
				status.statusInfo,
				fonts.name AS default_sub_font_name
				FROM campaigns
				LEFT JOIN artworks ON (campaigns.campaignID = artworks.campaignID AND artworks.live = %d)
				LEFT JOIN users ON campaigns.ownerID = users.userID
				LEFT JOIN companies ON users.companyID = companies.companyID
				LEFT JOIN languages ON campaigns.sourceLanguageID = languages.languageID
				LEFT JOIN brands ON campaigns.brandID = brands.brandID
				LEFT JOIN status ON campaigns.campaignStatus = status.statusID
				LEFT JOIN fonts ON campaigns.default_sub_font_id = fonts.id
				WHERE campaigns.campaignID = %d
				GROUP BY campaigns.campaignID
				LIMIT 1",
				STATUS_ACTIVE,
				$id);
$result = mysql_query($query, $conn) or die(mysql_error());
if(!mysql_num_rows($result)) access_denied();
$row = mysql_fetch_assoc($result);
?>
<div class="mainwrap">
	<div class="list">
		<table width="100%" cellspacing="0" cellpadding="5" border="0">
			<tr class="even" onmouseover="this.className='odd'" onmouseout="this.className='even'">
				<td class="highlight" width="35%"><?php echo $lang->display('Campaign Title'); ?></td>
				<td width="55%"><?php echo $row['campaignName']; ?></td>
				<td width="10%" align="center">
					<?php
						if($row['campaignStatus']==STATUS_ACTIVE && $acl->acl_check("campaigns","edit",$_SESSION['companyID'],$_SESSION['userID'])) {
							echo '<a href="javascript:void(0);" onclick="ResetDiv(\'window\');DoAjax(\'ref='.$id.'&redirect=campaign\',\'window\',\'modules/mod_camp_edit.php\');"><img src="'.IMG_PATH.'ico_edit.png" title="'.$lang->display('Edit').'"></a>';
						} else {
							echo '<img src="'.IMG_PATH.'ico_locked.png" title="'.$lang->display('Edit Locked').'" />';
						}
					?>
				</td>
			</tr>
			<tr class="even" onmouseover="this.className='odd'" onmouseout="this.className='even'">
				<td class="highlight"><?php echo $lang->display('Campaign Owner'); ?></td>
				<td colspan="2"><a href="javascript:void(0);" onclick="hidediv('helper');goToURL('parent','index.php?layout=user&id=<?php echo $row['ownerID']; ?>');"><?php echo $row['username']; ?></a></td>
			</tr>
			<tr class="even" onmouseover="this.className='odd'" onmouseout="this.className='even'">
				<td class="highlight"><?php echo $lang->display('Brand Name'); ?></td>
				<td colspan="2"><?php echo $row['brandName']; ?></td>
			</tr>
			<tr class="even" onmouseover="this.className='odd'" onmouseout="this.className='even'">
				<td class="highlight"><?php echo $lang->display('Source Language'); ?></td>
				<td colspan="2"><img src="images/flags/<?php echo $row['flag']; ?>" title="<?php echo $lang->display($row['languageName']); ?>" /> <?php echo $lang->display($row['languageName']); ?></td>
			</tr>
			<tr class="even" onmouseover="this.className='odd'" onmouseout="this.className='even'">
				<td class="highlight"><?php echo $lang->display('Campaign Status'); ?></td>
				<td colspan="2"><?php echo $lang->display($row['statusInfo'])?></td>
			</tr>
			<tr class="even" onmouseover="this.className='odd'" onmouseout="this.className='even'">
				<td class="highlight"><?php echo $lang->display('Default Substitute Font'); ?></td>
				<td colspan="2"><?php echo !empty($row['default_sub_font_id']) ? $row['default_sub_font_name'] : '<i>'.$lang->display('N/S').'</i>'; ?></td>
			</tr>
			<tr class="even" onmouseover="this.className='odd'" onmouseout="this.className='even'">
				<td class="highlight"><?php echo $lang->display('Default Image Folder'); ?></td>
				<td colspan="2"><?php echo !empty($row['default_img_dir']) ? '<img src="'.IMG_PATH.'ico_home.png" />'.$row['default_img_dir'] : '<i>'.$lang->display('N/S').'</i>'; ?></td>
			</tr>
			<tr class="even" onmouseover="this.className='odd'" onmouseout="this.className='even'">
				<td class="highlight"><?php echo $lang->display('Reference'); ?></td>
				<td colspan="2"><?php echo !empty($row['ref']) ? $row['ref'] : '<i>'.$lang->display('N/S').'</i>'; ?></td>
			</tr>
			<tr class="even" onmouseover="this.className='odd'" onmouseout="this.className='even'">
				<td class="highlight"><?php echo $lang->display('Artworks'); ?></td>
				<td colspan="2"><?php echo $row['artworkCount']; ?></td>
			</tr>
			<tr class="even" onmouseover="this.className='odd'" onmouseout="this.className='even'">
				<td class="highlight"><?php echo $lang->display('Pages'); ?></td>
				<td colspan="2"><?php echo $row['sum_pages']; ?></td>
			</tr>
			<tr class="even" onmouseover="this.className='odd'" onmouseout="this.className='even'">
				<td class="highlight"><?php echo $lang->display('Words'); ?></td>
				<td colspan="2"><?php echo $row['sum_words']; ?></td>
			</tr>
			<tr class="even" onmouseover="this.className='odd'" onmouseout="this.className='even'">
				<td class="highlight"><?php echo $lang->display('Active Tasks'); ?></td>
				<td>
					<?php
						$query = sprintf("SELECT taskID FROM tasks
										LEFT JOIN artworks ON tasks.artworkID = artworks.artworkID
										LEFT JOIN campaigns ON artworks.campaignID = campaigns.campaignID
										WHERE campaigns.campaignID = %d AND tasks.taskStatus IN (6,8,9,10)",
										$id);
						$result = mysql_query($query, $conn) or die(mysql_error());
						$found = mysql_num_rows($result);
						echo $found;
					?>
				</td>
				<td align="center">
					<?php
						if($found) {
							echo "<a href=\"javascript:void(0);\" onclick=\"hidediv('helper');goToURL('parent','index.php?layout=campaign&id=$id&do=pauseall');\"><img src=\"".IMG_PATH."ico_s_pause.gif\" title=\"".$lang->display('Pause All')."\"></a>";
						}
					?>
				</td>
			</tr>
			<tr class="even" onmouseover="this.className='odd'" onmouseout="this.className='even'">
				<td class="highlight"><?php echo $lang->display('Inactive Tasks'); ?></td>
				<td>
					<?php
						$query = sprintf("SELECT taskID FROM tasks
										LEFT JOIN artworks ON tasks.artworkID = artworks.artworkID
										LEFT JOIN campaigns ON artworks.campaignID = campaigns.campaignID
										WHERE campaigns.campaignID = %d AND tasks.taskStatus IN (5,7)",
										$id);
						$result = mysql_query($query, $conn) or die(mysql_error());
						$found = mysql_num_rows($result);
						echo $found;
					?>
				</td>
				<td align="center">
					<?php
						if($found) {
							echo "<a href=\"javascript:void(0);\" onclick=\"hidediv('helper');goToURL('parent','index.php?layout=campaign&id=$id&do=startall');\"><img src=\"".IMG_PATH."ico_s_go.gif\" title=\"".$lang->display('Start All')."\"></a>";
						}
					?>
				</td>
			</tr>
			<?php if ($acl->acl_check("system","cost",$_SESSION['companyID'],$_SESSION['userID'])) { ?>
			<tr class="even" onmouseover="this.className='odd'" onmouseout="this.className='even'">
				<td class="highlight"><?php echo $lang->display('Cost'); ?></td>
				<td colspan="2"><?php echo CURRENCY_SYMBOL.number_format($row['sum_cost'],2); ?></td>
			</tr>
			<?php } ?>
			<tr class="even" onmouseover="this.className='odd'" onmouseout="this.className='even'">
				<td class="highlight"><?php echo $lang->display('Last Update'); ?></td>
				<td colspan="2"><?php echo date(FORMAT_TIME,strtotime($row['lastEdit'])); ?></td>
			</tr>
		</table>
	</div>
</div>