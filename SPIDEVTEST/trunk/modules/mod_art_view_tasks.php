<?php
?>
<!-- Toolbar -->
<div class="toolbar" id="art_tasks">
	<div class="title">
		<div class="ico">
			<?php echo '<img src="'.IMG_PATH.'header/ico_task.png">'; ?>
		</div>
		<div class="txt">
			<?php echo $lang->display('Task Merge Manager'); ?>
			<div class="intro"><?php #echo $lang->display('Task Home Intro'); ?></div>
		</div>
	</div>
	<div class="options">
	<?php if($artwork_row['campaignStatus'] == STATUS_ACTIVE) { ?>
		<?php if ($acl->acl_check("tasks","delete",$_SESSION['companyID'],$_SESSION['userID'])) { ?>
		<!-- Delete -->
		<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Delete'); ?>">
			<a href="javascript:void(0);" onclick="if(CheckSelected('viewtaskform','id')) { if(confirm('<?php echo $lang->display('Are you sure you want to delete this task?'); ?>')) { SubmitForm('viewtaskform','delete'); } }">
				<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_delete.png">'; ?></div>
				<div><?php echo $lang->display('Delete'); ?></div>
			</a>
		</div>
		<?php } ?>
	<?php } ?>
	</div>
	<div class="clear"></div>
</div>
<form
	id="viewtaskform"
	name="viewtaskform"
	action="index.php?layout=artwork&id=<?php echo $artworkID; ?>"
	method="POST"
	enctype="multipart/form-data"
>
<div class="mainwrap">
	<div class="artworkTable">
		<div class="languageTable">
			<table id="listview" cellspacing="0" cellpadding="0" border="0">
				<tr class="title">
					<th width="2%" title="#" align="center">
						<a href="javascript:void(0);">#</a>
					</th>
					<th width="2%" title="<?php echo $lang->display('Select All'); ?>">
						<input type="checkbox" class="checkbox" name="checkall" id="checkall" onclick="jQueryCheckAll('taskform',this.id,'#listview tr');">
					</th>
					<th width="8%" align="center"><?php echo $lang->display('Action'); ?></th>
					<th colspan="2" title="<?php echo $lang->display('Languages'); ?>">
						<a href="javascript:void(0);" onclick="SetOrder('taskform','languageName','<?php echo $preorder; ?>');">
							<span class="icon"><img src="<?php echo IMG_PATH; ?>ico_lang.png" /></span>
							<span class="heading"><?php echo $lang->display('Languages'); ?></span>
						</a>
					</th>

                                        <th title="<?php echo $lang->display('Translator'); ?>">
						<a href="javascript:void(0);" onclick="SetOrder('taskform','tforename','<?php echo $preorder; ?>');">
							<span class="icon"><img src="<?php echo IMG_PATH; ?>ico_user.png" /></span>
							<span class="heading"><?php echo $lang->display('Translator'); ?></span>
						</a>
					</th>
					<th title="<?php echo $lang->display('Proofreader'); ?>">
						<a href="javascript:void(0);" onclick="return false;">
							<span class="icon"><img src="<?php echo IMG_PATH; ?>ico_user.png" /></span>
							<span class="heading"><?php echo $lang->display('Proofreader'); ?></span>
						</a>
					</th>
					
					<th title="<?php echo $lang->display('Task Manager'); ?>">
						<a href="javascript:void(0);" onclick="SetOrder('taskform','cforename','<?php echo $preorder; ?>');">
							<span class="icon"><img src="<?php echo IMG_PATH; ?>ico_owner.png" /></span>
							<span class="heading"><?php echo $lang->display('Task Manager'); ?></span>
						</a>
					</th>
					<th title="<?php echo $lang->display('Progress'); ?>">
						<a href="javascript:void(0);" onclick="SetOrder('taskform','missingWords','<?php echo $preorder; ?>');">
							<span class="icon"><img src="<?php echo IMG_PATH; ?>ico_progress.png" /></span>
							<span class="heading"><?php echo $lang->display('Progress'); ?></span>
						</a>
					</th>
                                        
					<th width="2%" title="ID" align="center">
						<a href="javascript:void(0);" onclick="SetOrder('taskform','taskID','<?php echo $preorder; ?>');">ID</a>
					</th>
				</tr>
				<?php
					$query_viewtask = sprintf("SELECT taskID
                                                                FROM tasks
                                                                WHERE `artworkID`= %d
                                                                AND `taskStatus` = 0 
                                                                AND `desiredLanguageID` = 0 
                                                                AND `translatorID` IS NULL
                                                                ORDER BY taskID",
                                                                $artworkID);
                                        $result_viewtask = mysql_query($query_viewtask, $conn) or die(mysql_error());
					$counter = 0;
                                        $lastparent_task_id=null;
					while($row_viewtask = mysql_fetch_assoc($result_viewtask)) {
                                            $view_counter = 0;
                                            $query_task_files = sprintf("SELECT task_id,parent_task_id FROM `story_files_task`WHERE `task_id`= %d ORDER BY parent_task_id",$row_viewtask['taskID']);
                                            $result_task_files = mysql_query($query_task_files, $conn) or die(mysql_error());
                                            $counter++;
                                            $row_count = mysql_num_rows($result_task_files);
                                            $style = ($counter%2==0) ? 'even' : 'odd';
                                            while($row_task_files = mysql_fetch_assoc($result_task_files)) {
                                                if($lastparent_task_id==$row_task_files['parent_task_id']) continue;
                                                $lastparent_task_id=$row_task_files['parent_task_id'];
                                                $query_task = sprintf("SELECT tasks.taskID, tasks.taskStatus, tasks.deadline, tasks.tdeadline, tasks.notes, tasks.brief, tasks.trial,
                                                                        tasks.currencyID, tasks.cost, tasks.serviceCurrencyID, tasks.serviceCharge, tasks.attachment,
                                                                        tasks.userWords, tasks.tmWords, tasks.missingWords,
                                                                        languages.flag, languages.languageName,
                                                                        U1.userID AS cuid, U1.forename AS cforename, U1.surname AS csurname,
                                                                        U2.userID AS tuid, U2.forename AS tforename, U2.surname AS tsurname,
                                                                        U3.userID AS auid, U3.forename AS aforename, U3.surname AS asurname,
                                                                        companies.companyName AS agency,
                                                                        status.statusInfo,
                                                                        C1.currencySymbol, C1.currencyAb,
                                                                        C2.currencySymbol AS serviceCurrencySymbol, C2.currencyAb AS serviceCurrencyAb
                                                                        FROM tasks
                                                                        LEFT JOIN languages ON tasks.desiredLanguageID = languages.languageID
                                                                        LEFT JOIN users U1 ON tasks.creatorID = U1.userID
                                                                        LEFT JOIN users U2 ON tasks.translatorID = U2.userID
                                                                        LEFT JOIN users U3 ON tasks.agentID = U3.userID
                                                                        LEFT JOIN companies ON U3.companyID = companies.companyID
                                                                        LEFT JOIN status ON tasks.taskStatus = status.statusID
                                                                        LEFT JOIN currencies C1 ON tasks.currencyID = C1.currencyID
                                                                        LEFT JOIN currencies C2 ON tasks.serviceCurrencyID = C2.currencyID
                                                                        WHERE taskID = %d
                                                                        ORDER BY %s %s", 
                                                    $row_task_files['parent_task_id'], 
                                                    mysql_real_escape_string($by), 
                                                    mysql_real_escape_string($order)
                                                );
                                                
                                                $result_task = mysql_query($query_task, $conn) or die(mysql_error());
                                                while($row_task = mysql_fetch_assoc($result_task)) {
                                                    $view_counter++;
                                                    echo '<tr class="'.$style.'" title="'.$lang->display('Notes').': '.$row_viewtask['notes'].$lang->display('Job Brief').': '.$row_viewtask['brief'].'"'.'>';
                                                    if($view_counter==1){
                                                        echo '<td rowspan="'.$row_count.'" align="center">'.$counter.'</td>';
                                                        echo '<td rowspan="'.$row_count.'"><input
                                                                                type="checkbox"
                                                                                class="checkbox"
                                                                                name="id['.$row_task_files['task_id'].']"
                                                                                id="id['.$row_task_files['task_id'].']"
                                                                                value="'.$row_task_files['task_id'].'" /></td>';
                                                        echo '<td  rowspan="'.$row_count.'" align="center">';
                                                        echo '<div class="ico">';
                                                        echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=task&id='.$row_viewtask['taskID'].'\');" title="'.$lang->display('View').'"><img src="'.IMG_PATH.'toolbar/ico_view.png" /></a>';
                                                        echo '</div>';
                                                        echo '</td>';
                                                    }
                                                    
                                                    echo '<td align="center"><img src="images/flags/'.$row_task['flag'].'" title="'.$lang->display($row_task['languageName']).'" /></td>';
                                                    echo '<td>';
                                                    echo '<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=task&id='.$row_task['taskID'].'\');">'.$lang->display($row_task['languageName']).'</a>';
                                                    echo '</td>';
                                                    echo '<td>';
                                                    if(!empty($row_task['tuid'])) {
                                                            echo '<div><a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=user&id='.$row_task['tuid'].'\');">'.$row_task['tforename'].' '.$row_task['tsurname'].'</a></div>';
                                                            if(!empty($row_task['tdeadline'])) echo '<div class="grey">'.date(FORMAT_DATE,strtotime($row_task['tdeadline'])).'</div>';
                                                    } else {
                                                            echo '<i>'.$lang->display('N/S').'</i>';
                                                    }
                                                    echo '</td>';
                                                    echo '<td>';
                                                    $query = sprintf("SELECT task_proofreaders.order, task_proofreaders.deadline, task_proofreaders.done,
                                                                        users.userID, users.forename, users.surname
                                                                        FROM task_proofreaders
                                                                        LEFT JOIN users ON task_proofreaders.user_id = users.userID
                                                                        WHERE task_proofreaders.task_id = %d
                                                                        ORDER BY task_proofreaders.order ASC, task_proofreaders.deadline ASC, users.forename ASC",
                                                                        $row_task['taskID']);
                                                    $result = mysql_query($query, $conn) or die(mysql_error());
                                                    if(mysql_num_rows($result)) {
                                                            while($row = mysql_fetch_assoc($result)) {
                                                                    echo '<div>';
                                                                    echo $row['order'].'. <a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=user&id='.$row['userID'].'\');">'.$row['forename'].' '.$row['surname'].'</a>';
                                                                    if(!empty($row['done'])) echo ' <img src="'.IMG_PATH.'ico_enable.png" title="'.$lang->display('Done').'" />';
                                                                    echo '</div>';
                                                                    if(!empty($row['deadline'])) echo '<div class="grey">'.date(FORMAT_DATE,strtotime($row['deadline'])).'</div>';
                                                            }
                                                    } else {
                                                            echo '<i>'.$lang->display('N/S').'</i>';
                                                    }
                                                    echo '</td>';
                                                    echo '<td>';
                                                    if(!empty($row_task['auid'])) {
                                                            echo '<div><a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=user&id='.$row_task['auid'].'\');">'.$row_task['aforename'].' '.$row_task['asurname'].'</a></div>';
                                                            echo '<div class="grey">'.$row_task['agency'].'</div>';
                                                    } else {
                                                            echo '<i>'.$lang->display('N/S').'</i>';
                                                    }
                                                    echo '</td>';
                                                    echo '<td>';
                                                    BuildTaskProgressBar($row_task['taskID']);
                                                    echo '</td>';
                                                    echo '<td>';
                                                    echo $row_viewtask['taskID']." (".$row_task['taskID'].")";
                                                    echo '</td>';
                                                }
                                                
                                            }
                                            
					}
				?>
			</table>
		</div>
	</div>
</div>
<input type="hidden" name="by" id="by" value="<?php echo $by; ?>">
<input type="hidden" name="order" id="order" value="<?php echo $order; ?>">
<input type="hidden" name="form" id="form">
</form>