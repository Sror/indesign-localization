<?php
//update user online status
$query = sprintf("UPDATE users SET lastActive = now() WHERE userID = %d", $_SESSION['userID']);
$result = mysql_query($query, $conn) or die(mysql_error());
//get user profile
$query_profileRs = sprintf("SELECT *, (lastActive + %d > now()) as online FROM users
							LEFT JOIN aro_groups ON users.userGroupID = aro_groups.id
							LEFT JOIN companies ON users.companyID = companies.companyID
							WHERE username = '%s'",
							SESSION_TIME,
							mysql_real_escape_string($_SESSION['username']));
$profileRs = mysql_query($query_profileRs, $conn) or die(mysql_error());
$row_profileRs = mysql_fetch_assoc($profileRs);
$totalRows_profileRs = mysql_num_rows($profileRs);
//count online users
$query_online = sprintf("SELECT userID, username FROM users
						WHERE companyID IN (%s)
						AND lastActive + %d > now()
						ORDER BY username ASC",
						mysql_real_escape_string($DB->get_company_list($_SESSION['companyID'])),
						SESSION_TIME);
$result_online = mysql_query($query_online, $conn) or die(mysql_error());
$found_online = mysql_num_rows($result_online);
//count unread messages
$query_unread = sprintf("SELECT * FROM messages
						LEFT JOIN users ON messages.senderID = users.userID
						WHERE receiverID = %d AND readStatus = 0 AND receiverSideStatus = 1
						ORDER BY messageTime DESC",
						$_SESSION['userID']);
$result_unread = mysql_query($query_unread, $conn) or die(mysql_error());
$found_unread = mysql_num_rows($result_unread);

$stats_campaigns = array();
$stats_artworks = array();
$stats_pages = array();
$stats_tasks = array();
$query_stats = sprintf("SELECT campaigns.campaignID, campaigns.campaignName,
						artworks.artworkID, artworks.artworkName,
						pages.uID AS pageID,
						tasks.taskID, tasks.taskStatus
						FROM campaigns
						LEFT JOIN artworks ON ( campaigns.campaignID = artworks.campaignID AND artworks.live = %d )
						LEFT JOIN pages ON artworks.artworkID = pages.ArtworkID
						LEFT JOIN tasks ON artworks.artworkID = tasks.artworkID
						LEFT JOIN users ON campaigns.ownerID = users.userID
						WHERE campaigns.campaignStatus = %d
						ORDER BY
						campaigns.lastEdit DESC,
						artworks.lastUpdate DESC,
						pages.uID ASC,
						tasks.taskID ASC",
						STATUS_ACTIVE,
						STATUS_ACTIVE);
$result_stats = mysql_query($query_stats, $conn) or die(mysql_error());
while($row_stats = mysql_fetch_assoc($result_stats)) {
	if(!$DB->check_campaign_acl($row_stats['campaignID'],$_SESSION['companyID'],$_SESSION['userID'])) continue;
	if(!empty($row_stats['campaignID'])) {
		$stats_campaigns[$row_stats['campaignID']] = $row_stats['campaignName'];
	}
	if(!empty($row_stats['artworkID'])) {
		$stats_artworks[$row_stats['artworkID']] = $row_stats['artworkName'];
	}
	if(!empty($row_stats['pageID'])) {
		$stats_pages[$row_stats['pageID']] = $row_stats['artworkID'];
	}
	if(!empty($row_stats['taskID'])) {
		$stats_tasks[$row_stats['taskID']] = $row_stats['taskStatus'];
	}
}
$campaigns = $stats_campaigns;
$stats_campaigns = count($stats_campaigns);
$stats_artworks = count($stats_artworks);
$stats_pages = count($stats_pages);
$stats_signedoffs = count(array_keys($stats_tasks,10));
$stats_tasks = count($stats_tasks);

$query_mytasks = sprintf("SELECT tasks.taskID
						FROM tasks
						LEFT JOIN artworks ON tasks.artworkID = artworks.artworkID
						LEFT JOIN campaigns ON artworks.campaignID = campaigns.campaignID
						WHERE
						(
						(tasks.translatorID = %d AND tasks.taskStatus = 6)
						OR
						(tasks.creatorID = %d AND tasks.taskStatus = 9)
						OR
						(tasks.agentID = %d AND tasks.taskStatus < 10)
						)
						AND campaigns.campaignStatus = %d
						AND artworks.live = %d",
						$_SESSION['userID'],
						$_SESSION['userID'],
						$_SESSION['userID'],
						STATUS_ACTIVE,
						STATUS_ACTIVE);
$result_mytasks = mysql_query($query_mytasks, $conn) or die(mysql_error());
$mytasks = array();
while($row_mytasks = mysql_fetch_assoc($result_mytasks)) {
	$mytasks[] = $row_mytasks['taskID'];
}
$query_myproofread = sprintf("SELECT task_proofreaders.task_id
							FROM task_proofreaders
							LEFT JOIN tasks ON task_proofreaders.task_id = tasks.taskID
							LEFT JOIN artworks ON tasks.artworkID = artworks.artworkID
							LEFT JOIN campaigns ON artworks.campaignID = campaigns.campaignID
							WHERE task_proofreaders.user_id = %d
							AND tasks.taskStatus = 8
							AND task_proofreaders.done = 0
							AND campaigns.campaignStatus = %d
							AND artworks.live = %d",
							$_SESSION['userID'],
							STATUS_ACTIVE,
							STATUS_ACTIVE);
$result_myproofread = mysql_query($query_myproofread,$conn) or die(mysql_error());
while($row_myproofread = mysql_fetch_assoc($result_myproofread)) {
	if(in_array($row_myproofread['task_id'],$mytasks)) continue;
	$mytasks[] = $row_myproofread['task_id'];
}
$stats_mytasks = count($mytasks);
?>
<div id="header">
	<div id="globalNav_left">
		<div class="logo">
			<?php echo '<a href="index.php?layout=home"><img src="'.SYSTEM_LOGO_SMALL.'" title="'.SYSTEM_NAME.' '.SYSTEM_VERSION.'" /></a>';?>
		</div>
		<div class="nav">
			<div class="row">
				<div align="center">
					<a href="http://support.sp-int.com" target="_blank"><?php echo $lang->display('System Support'); ?></a>
					<span class="span">|</span>
					<a href="http://www.transcreationportal.com/index.php" target="_blank">Transcreation Portal</a>
					<span class="span">|</span>
					<a href="javascript:void(0);" onclick="openandclose('tip_online');"><?php echo $lang->display('Online Users')." (".$found_online.")"; ?></a>
					<span class="span">|</span>
					<a href="javascript:void(0);" onclick="goToURL('parent','index.php?layout=inbox');"><?php echo $lang->display('Inbox')." (".$found_unread.")"; ?></a>
				</div>
				<div class="clear"></div>
			</div>
			<?php
				$tabs = array("home","mytasks","campaigns","profile","cpanel");
				$tabStatus = array();
				foreach($tabs as $tab) {
					if(in_array($tab,$navStatus)) {
						$tabStatus[$tab] = "on";
					} else {
						$tabStatus[$tab] = "off";
					}
				}
				//Home
				echo '<div class="item">';
				echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=home\');">';
				echo '<div
						title="'.$lang->display('Home').'"
						class="item_'.$tabStatus['home'].'"
						onmouseover="this.className=\'item_on\'"';
				if($tabStatus['home']=="off") echo 'onmouseout="this.className=\'item_off\'"';
				echo '>'.$lang->display('Home').'</div>';
				echo '</a>';
				echo '</div>';
				//My Tasks
				echo '<div class="item" onmouseover="display(\'task_options\');" onmouseout="hidediv(\'task_options\');">';
				echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=mytasks\');">';
				echo '<div
						title="'.$lang->display('My Tasks').', '.$stats_mytasks.' '.$lang->display('Active').'"
						class="item_'.$tabStatus['mytasks'].'"
						onmouseover="this.className=\'item_on\'"';
				if($tabStatus['mytasks']=="off") echo 'onmouseout="this.className=\'item_off\'"';
				echo '>'.$lang->display('My Tasks');
				if(!empty($stats_mytasks)) echo ' ('.$stats_mytasks.')';
				echo '</div>';
				echo '</a>';
				echo '<div id="task_options" class="menu" style="display:none">';
				$task_options = array(
					array("layout"=>"mytasks", "view"=>"intray", "display"=>"Intray", "icon"=>"ico_intray.png"),
					array("layout"=>"mytasks", "view"=>"outtray", "display"=>"Outtray", "icon"=>"ico_outtray.png")
				);
				foreach($task_options as $option) {
					echo '<div title="'.$lang->display($option['display']).'" class="controlBtn_';
					$task_view = (isset($_GET['view'])) ? $_GET['view'] : "intray";
					$selected = ($layout==$option['layout'] && $task_view==$option['view']);
					echo $selected ? 'on' : 'off';
					echo '" onmouseover="this.className=\'controlBtn_on\'"';
					if(!$selected) echo ' onmouseout="this.className=\'controlBtn_off\'">';
					echo '<a href="javascript:void(0);" onclick="hidediv(\'task_options\');goToURL(\'parent\',\'index.php?layout='.$option['layout'].'&view='.$option['view'].'\');">';
					echo '<div class="icon"><img src="'.IMG_PATH.'header/'.$option['icon'].'" /></div>';
					echo '<div class="topic">'.$lang->display($option['display']);
					if($option['view']==intray && !empty($stats_mytasks)) echo ' ('.$stats_mytasks.')';
					echo '</div>';
					echo '<div class="clear"></div>';
					echo '</a>';
					echo '</div>';
				}
				echo '</div>';
				echo '</div>';
				//Campaigns
				if($acl->acl_check("system","campaigns",$_SESSION['companyID'],$_SESSION['userID'])) {
					echo '<div class="item" onmouseover="display(\'campaigns\');" onmouseout="hidediv(\'campaigns\');">';
					echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=campaigns\');">';
					echo '<div
							title="'.$lang->display('Campaigns').', '.$stats_campaigns.' '.$lang->display('Active').'"
							class="item_'.$tabStatus['campaigns'].'"
							onmouseover="this.className=\'item_on\'"';
					if($tabStatus['campaigns']=="off") echo 'onmouseout="this.className=\'item_off\'"';
					echo '>'.$lang->display('Campaigns');
					if($stats_campaigns) echo ' ('.$stats_campaigns.')';
					echo '</div>';
					echo '</a>';
					echo '<div id="campaigns" class="menu" style="display:none">';
					foreach($campaigns as $campaign_id=>$campaign_name) {
						echo '<div title="'.$campaign_name.'" class="controlBtn_';
						$id = (isset($_GET['id'])) ? $_GET['id'] : 0;
						$selected = ($layout=="campaign" && $id==$campaign_id);
						echo $selected ? 'on' : 'off';
						echo '" onmouseover="this.className=\'controlBtn_on\'"';
						if(!$selected) echo ' onmouseout="this.className=\'controlBtn_off\'">';
						echo '<a href="javascript:void(0);" onclick="hidediv(\'campaigns\');goToURL(\'parent\',\'index.php?layout=campaign&id='.$campaign_id.'\');">';
						echo '<div class="icon"><img src="'.IMG_PATH.'header/ico_campaign.png" /></div>';
						echo '<div class="topic">'.$campaign_name.'</div>';
						echo '<div class="clear"></div>';
						echo '</a>';
						echo '</div>';
					}
					echo '</div>';
					echo '</div>';
				}
				//My Profile
				echo '<div class="item">';
				echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=profile\');">';
				echo '<div
						title="'.$lang->display('My Profile').'"
						class="item_'.$tabStatus['profile'].'"
						onmouseover="this.className=\'item_on\'"';
				if($tabStatus['profile']=="off") echo 'onmouseout="this.className=\'item_off\'"';
				echo '>'.$lang->display('My Profile').'</div>';
				echo '</a>';
				echo '</div>';
				//Control Panel
				if($acl->acl_check("system","cpanel",$_SESSION['companyID'],$_SESSION['userID'])) {
					echo '<div class="item" onmouseover="display(\'cp_options\');" onmouseout="hidediv(\'cp_options\');">';
					echo '<a href="javascript:void(0);" onclick="hidediv(\'cp_options\');goToURL(\'parent\',\'index.php?layout=cpanel\');">';
					echo '<div
							title="'.$lang->display('Control Panel').'"
							class="item_'.$tabStatus['cpanel'].'"
							onmouseover="this.className=\'item_on\'"';
					if($tabStatus['cpanel']=="off") echo 'onmouseout="this.className=\'item_off\'"';
					echo '>'.$lang->display('Control Panel').'</div>';
					echo '</a>';
					echo '<div id="cp_options" class="menu" style="display:none">';
					$cp_options = array(
						array("layout"=>"cp_credit", "display"=>"Credit Manager", "icon"=>"ico_credits.png", "super"=>false),
						array("layout"=>"cp_company", "display"=>"Company Manager", "icon"=>"ico_company.png", "super"=>false),
						array("layout"=>"cp_user", "display"=>"User Manager", "icon"=>"ico_user.png", "super"=>false),
						array("layout"=>"cp_brand", "display"=>"Brand Manager", "icon"=>"ico_brand.png", "super"=>false),
						array("layout"=>"cp_file", "display"=>"File Manager", "icon"=>"ico_image.png", "super"=>false),
						array("layout"=>"cp_ftp", "display"=>"FTP Manager", "icon"=>"ico_ftp.png", "super"=>false),
						array("layout"=>"cp_campaign", "display"=>"Campaign Manager", "icon"=>"ico_campaign.png", "super"=>false),
						array("layout"=>"cp_artwork", "display"=>"Artwork Manager", "icon"=>"ico_artwork.png", "super"=>false),
						array("layout"=>"cp_task", "display"=>"Task Manager", "icon"=>"ico_task.png", "super"=>false),
						array("layout"=>"cp_report", "display"=>"Sign-off Report", "icon"=>"ico_report.png", "super"=>false),
						#array("layout"=>"cp_image", "display"=>"Image Manager", "icon"=>"ico_image.png", "super"=>false),
						array("layout"=>"cp_joboptions", "display"=>"Joboption Manager", "icon"=>"ico_joboptions.png","super"=>false),
						array("layout"=>"cp_tm", "display"=>"Translation Memory Manager", "icon"=>"ico_translation_memory.png", "super"=>false),
						array("layout"=>"cp_font", "display"=>"Font Manager", "icon"=>"ico_font.png", "super"=>false),
						array("layout"=>"cp_font_sub", "display"=>"Font Substitution Manager", "icon"=>"ico_font_sub.png", "super"=>false),
						array("layout"=>"cp_lang", "display"=>"Language Manager", "icon"=>"ico_language.png", "super"=>false),
						array("layout"=>"cp_currency", "display"=>"Currency Manager", "icon"=>"ico_currency.png", "super"=>false),
						array("layout"=>"cp_subject", "display"=>"Subject Manager", "icon"=>"ico_subject.png", "super"=>false),
						array("layout"=>"cp_info", "display"=>"Service Information Manager", "icon"=>"ico_info.png", "super"=>false),
						array("layout"=>"cp_log", "display"=>"System Log Manager", "icon"=>"ico_log.png", "super"=>false),
						array("layout"=>"cp_sp", "display"=>"Service Package Manager", "icon"=>"ico_sp.png", "super"=>true),
						array("layout"=>"cp_cost", "display"=>"Cost Manager", "icon"=>"ico_cost.png", "super"=>true),
						array("layout"=>"cp_acl", "display"=>"ACL Manager", "icon"=>"ico_ftp.png", "super"=>true)
					);
					foreach($cp_options as $option) {
						if($issuperadmin==$option['super'] || !$option['super']) {
							echo '<div title="'.$lang->display($option['display']).'" class="controlBtn_';
							$selected = ($layout==$option['layout']);
							echo $selected ? 'on' : 'off';
							echo '" onmouseover="this.className=\'controlBtn_on\'"';
							if(!$selected) echo ' onmouseout="this.className=\'controlBtn_off\'">';
							echo '<a href="javascript:void(0);" onclick="hidediv(\'cp_options\');goToURL(\'parent\',\'index.php?layout='.$option['layout'].'\');">';
							echo '<div class="icon"><img src="'.IMG_PATH.'header/'.$option['icon'].'" /></div>';
							echo '<div class="topic">'.$lang->display($option['display']).'</div>';
							echo '<div class="clear"></div>';
							echo '</a>';
							echo '</div>';
						}
					}
					echo '</div>';
					echo '</div>';
				}
			?>
		</div>
	</div>
	<div id="globalNav_right">
		<div class="row">
			<div class="lang">
				<?php BuildLangDropdown($langCode); ?>
			</div>
			<div class="right">
				<div class="iconLink" title="* <?php echo $lang->display('Credits').': '.$lang->display('Subject to availability.'); ?>">
					<div class="icon"><img src="<?php echo IMG_PATH?>ico_coin.png" /></div>
					<div class="link"><a href="javascript:void(0);" onclick="openandclose('tip_credits');"><b><?php echo $lang->display('Credits').': '.$credits_available; ?></b></a></div>
				</div>
			</div>
			<div class="clear"></div>
		</div>
		<div class="row">
			<div class="iconLink">
				<div class="link">
					<?php echo $row_profileRs['username'].'<span class="span">|</span>'.$row_profileRs['companyName'].'<span class="span">|</span>'.$lang->display($row_profileRs['name']).'<span class="span">|</span><a href="index.php?layout=login&do=logout">'.$lang->display('Logout').'</a>'; ?>
				</div>
			</div>
		</div>
		<div class="clear"></div>
	</div>
	<div class="clear"></div>
</div>
<?php
// active stats
if($acl->acl_check("system","dashboard",$_SESSION['companyID'],$_SESSION['userID'])) {
	echo '<div class="stats">';
	echo $lang->display('My Active Projects').'<span class="span">:</span>'.$lang->display('Campaigns').' ('.$stats_campaigns.')<span class="span">|</span>'.$lang->display('Artworks').' ('.$stats_artworks.')<span class="span">|</span>'.$lang->display('Pages').' ('.$stats_pages.')<span class="span">|</span>'.$lang->display('Tasks').' ('.$stats_tasks.')<span class="span">|</span>'.$lang->display('Signed Off').' ('.$stats_signedoffs.')';
	echo '</div>';
}
// users online
echo '<div id="tip_online" style="display:none;">';
$str_online = $lang->display('Online Users').':';
while($row_online = mysql_fetch_assoc($result_online)) {
	$str_online .= ' <a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=user&id='.$row_online['userID'].'\');">'.$row_online['username'].'</a>; ';
}
BuildTipMsg($str_online,'tip_online');
echo '</div>';
// credit usage
$result_credits = $DB->get_credit_usage($_SESSION['companyID'],$_SESSION['userID']);
echo '<div id="tip_credits" style="display:none;">';
$str_credits = '<div class="mainwrap" style="max-height:300px;overflow:auto;">';
$str_credits .= '<div class="list">';
$str_credits .= '<table width="100%" cellpadding="5" cellspacing="0" border="0">';
$str_credits .= '<tr>';
$str_credits .= '<th width="4%" align="center">ID</th>';
$str_credits .= '<th width="10%">'.$lang->display('Timestamp').'</th>';
$str_credits .= '<th width="16%">'.$lang->display('Campaign').'</th>';
$str_credits .= '<th width="24%">'.$lang->display('Artwork').'</th>';
$str_credits .= '<th width="8%">'.$lang->display('Task Details').'</th>';
$str_credits .= '<th width="32%">'.$lang->display('Transaction').'</th>';
$str_credits .= '<th width="6%" align="right">'.$lang->display('Credit out').'</th>';
$str_credits .= '</tr>';
if($result_credits === false) {
	$str_credits .= '<tr><td colspan="7" align="center" class="grey">'.$lang->display('No Transaction within this period.').'</td></tr>';
} else {
	$counter = 1;
	while($row_credits = mysql_fetch_assoc($result_credits)) {
		$style = $counter%2==0 ? 'even' : 'odd';
		$str_credits .= '<tr class="'.$style.'" onmouseover="this.className=\'hover\'" onmouseout="this.className=\''.$style.'\'">';
		$str_credits .= '<td align="center">'.$row_credits['id'].'</td>';
		$str_credits .= '<td>'.date(FORMAT_TIME,$row_credits['trans_time']).'</td>';
		$str_credits .= '<td><a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=campaign&id='.$row_credits['campaign_id'].'\');">'.$row_credits['campaignName'].'</a></td>';
		$str_credits .= '<td><a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=artwork&id='.$row_credits['artwork_id'].'\');">'.$row_credits['artworkName'].'</a></td>';
		$str_credits .= '<td>';
		if(!empty($row_credits['task_id'])) $str_credits .= '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=task&id='.$row_credits['task_id'].'\');"><img src="images/flags/'.$row_credits['source_flag'].'" title="'.$lang->display($row_credits['source_lang']).'" /> <img src="'.IMG_PATH.'flag_to.gif" title="'.$lang->display('to be translated to').'" /> <img src="images/flags/'.$row_credits['target_flag'].'" title="'.$lang->display($row_credits['target_lang']).'" /></a>';
		$str_credits .= '</td>';
		$str_credits .= '<td>'.$row_credits['transaction'].'</td>';
		$str_credits .= '<td align="right">'.$row_credits['credit_out'].'</td>';
		$str_credits .= '</tr>';
		$counter++;
	}
}
$str_credits .= '</table>';
$str_credits .= '</div>';
$str_credits .= '</div>';
BuildTipMsg($str_credits,'tip_credits');
echo '</div>';