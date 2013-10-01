<?php
require_once(dirname(__FILE__).'/../config.php');
//disabled for guests access
#$access = array("system","login");
#require_once(MODULES.'mod_authorise.php');

$artworkID = !empty($_REQUEST['artworkID']) ? (int)$_REQUEST['artworkID'] : 0 ;
$token = isset($_REQUEST['token']) ? $_REQUEST['token'] : (isset($_SESSION['token'])?$_SESSION['token']:"");
if(!empty($token)) $_SESSION['token'] = $token;
$query = sprintf("SELECT id, name
				FROM artwork_guests
				WHERE artwork_id = %d
				AND token = '%s'
				LIMIT 1",
				$artworkID,
				mysql_real_escape_string($token));
$result = mysql_query($query, $conn) or die(mysql_error());
if(mysql_num_rows($result)) {
	$row = mysql_fetch_assoc($result);
	$user_id = $row['id'];
	$guest_name = $row['name'];
	$is_guest = 1;
} else {
	$access = array("system","login");
	require_once(MODULES.'mod_authorise.php');
	$user_id = $_SESSION['userID'];
	$is_guest = 0;
}

$page = !empty($_REQUEST['page']) ? (int)$_REQUEST['page'] : 0 ;
$edit_page = !empty($_REQUEST['edit_page']) ? $_REQUEST['edit_page'] : $page;
$boxID = !empty($_REQUEST['boxID']) ? (int)$_REQUEST['boxID'] : 0 ;
$taskID = !empty($_REQUEST['taskID']) ? (int)$_REQUEST['taskID'] : 0 ;
$do = !empty($_REQUEST['do']) ? $_REQUEST['do'] : "" ;
$ref = !empty($_REQUEST['ref']) ? (int)$_REQUEST['ref'] : 0 ;
$name = !empty($_REQUEST['name']) ? $_REQUEST['name'] : "" ;
$comment = !empty($_REQUEST['comment']) ? $_REQUEST['comment'] : "" ;
$reset = isset($_REQUEST['reset']) ? (int)$_REQUEST['reset'] : 1 ;
$attach = !empty($_REQUEST['attach']) ? $_REQUEST['attach'] : "" ;

if(!empty($do)) {
	if($do=='addcomment' && !empty($comment)) {
		$DB->AddComment($artworkID, $edit_page, $user_id, $comment, $attach, $boxID, $taskID, $is_guest, $name);
	}

	if($do=='delcomment' && !empty($ref)) {
		$DB->RemoveComment($ref);
	}
}

if(!empty($reset)) {
	$comment = "";
	$boxID = 0;
}

echo '<div class="toolIntro">'.$lang->display('Comments').'</div>';
echo '<div class="closeBtn"><a href="javascript:void(0);" onclick="hidediv(\'pageColR\');SetClassName(\'commentTool\',\'pageToolOff\');"><img src="'.IMG_PATH.'close_right.png" title="'.$lang->display('Close').'" /></a></div>';
echo '<div class="clear"></div>';
?>
<div id="addComment">
	<form
		action="modules/mod_attachment_upload.php"
		id="comment_form"
		name="comment_form"
		method="post"
		enctype="multipart/form-data"
		target="file_frame"
		onsubmit="hidediv('attachment');hidediv('file_frame');display('upload_loading');"
	>
	<table width="100%" cellspacing="0" cellpadding="3" border="0">
		<?php
			if($is_guest) {
				echo "<tr><td>";
				echo "<input
						type=\"text\"
						class=\"input\"
						onfocus=\"this.className='inputOn'\"
						onblur=\"this.className='input'\"
						name=\"name\"
						id=\"name\"
						value=\"$guest_name\" /> ({$lang->display('Name')})";
				echo "</td></tr>";
			}
		?>
		<tr>
			<td>
				<textarea
					class="input"
					onfocus="this.className='inputOn';doResize('comment',60);"
					onblur="this.className='input'"
					id="comment"
					name="comment"
					rows="1"
					cols="27"
				><?php echo $comment; ?></textarea>
			</td>
		</tr>
		<tr>
			<td>
				<a href="javascript:void(0);" onclick="openandclose('attachment')"><?php echo $lang->display('Attachment'); ?>?</a>
				<div id="attachment" style="display:none;">
					<input
						type="file"
						class="input"
						onfocus="this.className='inputOn'"
						onblur="this.className='input'"
						id="attachment"
						name="attachment"
						size="8"
					/>
					<input
						type="submit"
						class="btnDo"
						onmousemove="this.className='btnOn'"
						onmouseout="this.className='btnDo'"
						title="<?php echo $lang->display('Upload'); ?>"
						value="<?php echo $lang->display('Upload'); ?>"
					/>
				</div>
				<div id="upload_loading" class="loading" style="display:none;"><img src="images/loading.gif" /></div>
				<iframe id="file_frame" name="file_frame" class="iframe" style="display:none;"></iframe>
			</td>
		</tr>
		<tr>
			<td>
				<input
					type="button"
					class="btnDo"
					onmousemove="this.className='btnOn'"
					onmouseout="this.className='btnDo'"
					title="<?php echo $lang->display('Add a Comment'); ?>"
					value="<?php echo $lang->display('Add a Comment'); ?>"
					onclick="
						validateForm('comment','Comment','R');
						if(document.returnValue) {
							var iframe = document.getElementById('file_frame');
							var innerDoc = iframe.contentDocument || iframe.contentWindow.document;
							var name = '';
							if(document.getElementById('name')) name = document.getElementById('name').value;
							var attach = '';
							if(innerDoc.getElementById('attach')) attach = innerDoc.getElementById('attach').value;
							ResetDiv('loader');
							AjaxPost('artworkID=<?php echo $artworkID; ?>&page=<?php echo $page; ?>&edit_page=<?php echo $edit_page; ?>&taskID=<?php echo $taskID; ?>&boxID=<?php echo $boxID; ?>&do=addcomment&name='+name+'&comment='+document.getElementById('comment').value+'&attach='+attach,'pageColR','modules/mod_page_comments.php');
						}
					"
				/>
			</td>
		</tr>
	</table>
	</form>
</div>
<div id="commentlist">
	<?php
		$show_all = !empty($_REQUEST['showall']);
		$condition = empty($_REQUEST['showall']) ? sprintf(" AND comments.page = %d",$page) : "";
		$query = sprintf("SELECT comments.*,
						users.username, artwork_guests.name, artwork_guests.email
						FROM comments
						LEFT JOIN users ON users.userID = comments.user_id
						LEFT JOIN artwork_guests ON (artwork_guests.id = comments.user_id AND artwork_guests.artwork_id = comments.artwork_id)
						WHERE comments.artwork_id = %d
						AND comments.task_id = %d
						%s
						ORDER BY comments.time DESC",
						$artworkID,
						$taskID,
						mysql_real_escape_string($condition));
		$result = mysql_query($query, $conn) or die(mysql_error());
		echo '<div class="closeBtn"><input type="checkbox" name="showall" id="showall" value="1" onclick="ResetDiv(\'loader\');DoAjax(\'artworkID='.$artworkID.'&page='.$page.'&taskID='.$taskID.'&showall=\'+(this.checked?1:0),\'pageColR\',\'modules/mod_page_comments.php\');"';
		if($show_all) echo ' checked="checked"';
		echo '/>'.$lang->display('Show All').'</div>';
		echo '<div id="loader" class="toolIntro">'.$lang->display('Found').' '.mysql_num_rows($result).'</div>';
		echo '<div class="clear"></div>';
		echo '<hr />';
		while($row = mysql_fetch_assoc($result)) {
			echo '<div id="comment'.$row['id'].'" class="hover" onmouseover="this.className=\'bgWhite\';display(\'delcomment'.$row['id'].'\');" onmouseout="this.className=\'hover\';hidediv(\'delcomment'.$row['id'].'\');" style="padding:5px;">';
			echo '<div class="left"><img src="'.IMG_PATH.'ico_comment.png" /></div>';
			echo '<div class="left">';
			if($row['is_guest']) {
				$display_name = empty($row['name']) ? $row['email'] : $row['name'];
				echo "<a href=\"mailto:".$row['email']."\">$display_name</a>";
			} else {
				echo "<a href=\"javascript:void(0);\" onclick=\"goToURL('parent','index.php?layout=user&id=".$row['user_id']."');\">".$row['username']."</a>";
			}
			echo '</div>';
			if(!$is_guest && $acl->acl_check("taskworkflow","deletecomments",$_SESSION['companyID'],$_SESSION['userID'])) {
				echo "<div id=\"delcomment{$row['id']}\" class=\"right\" style=\"display:none;\"><a href=\"javascript:void(0);\" onclick=\"ResetDiv('loader');DoAjax('artworkID=$artworkID&page=$page&taskID=$taskID&do=delcomment&ref={$row['id']}&showall=".($show_all?1:0)."','pageColR','modules/mod_page_comments.php');\"><img src=\"".IMG_PATH."btn_s_delete.png\" title=\"".$lang->display('Delete')."\"></a></div>";
			}
			echo '<div class="clear"></div>';
			echo '<div class="grey">';
			echo '<div class="left">'.$lang->display('Page').' '.$row['page'].'</div>';
			if(!empty($row['box_id'])) {
				if(empty($taskID)) {
					$npv = "artworkID=$artworkID";
					$dest = "mod_art_amend.php";
				} else {
					$npv = "taskID=$taskID";
					$dest = "mod_proofread.php";
				}
				echo '<div class="left"><a href="javascript:void(0);" onclick="Popup(\'helper\',\'blur\');DoAjax(\''.$npv.'&page='.$page.'&edit_page='.$row['page'].'&boxID='.$row['box_id'].'\',\'window\',\'modules/'.$dest.'\');"><img src="'.IMG_PATH.'ico_window.png" title="'.$lang->display('Lookup').'" /></a></div>';
			}
			echo '<div class="right">'.date(FORMAT_TIME,strtotime($row['time'])).'</div>';
			echo '<div class="clear"></div>';
			echo '</div>';
			echo "<div>".html_display_para($row['comment'])."</div>";
			if(!empty($row['attachment'])) {
				echo "<div>";
				echo "<img src=\"".IMG_PATH."ico_attachment.png\" title=\"{$lang->display('Attachment')}\" /> <a href=\"download.php?attachment&File={$row['attachment']}&SaveAs={$row['attachment']}\">".$lang->display('Attachment')."</a>";
				if(ValidateMedia(REPOSITORY_DIR.$row['attachment'])) {
					echo " <a href=\"javascript:void(0);\" onclick=\"Popup('helper','blur');DoAjax('ref={$row['attachment']}','window','modules/mod_media_play.php');\"><img src=\"".IMG_PATH."arrow_right.png\" title=\"{$lang->display('Play')}\" />{$lang->display('Play')}</a>";
				}
				echo "</div>";
			}
			echo "</div>";
			echo "<hr />";
		}
	?>
</div>