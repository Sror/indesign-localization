<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("tasks","new");
require_once(MODULES.'mod_authorise.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$type = isset($_GET['type']) ? $_GET['type'] : ( isset($_SESSION['task_type']) ? $_SESSION['task_type'] : "assign" );
$_SESSION['task_type'] = $type;
$query = sprintf("SELECT artworks.subjectID, artworks.wordCount,
				campaigns.sourceLanguageID
				FROM artworks
				LEFT JOIN campaigns ON artworks.campaignID = campaigns.campaignID
				WHERE artworks.artworkID = %d
				LIMIT 1",
				$id);
$result = mysql_query($query, $conn) or die(mysql_error());
if(!mysql_num_rows($result)) access_denied();
$row = mysql_fetch_assoc($result);
?>
<form
	action="index.php?layout=artwork&id=<?php echo $id; ?>"
	name="taskForm"
	method="POST"
	enctype="multipart/form-data"
	onsubmit="hidediv('helper');Popup('loadingme','waiting');"
>
<table width="100%" cellspacing="0" cellpadding="3" border="0">
	<tr>
		<td width="50%">
			<div class="arrdwn"><?php echo $lang->display('New Task Step 2 Title'); ?></div>
		</td>
		<td width="50%" align="right">
			<?php BuildStepIndicator(2); ?>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<div class="greyBar">
				<table width="100%" cellspacing="0" cellpadding="3" border="0">
					<?php if($type == "assign") { ?>
					<tr>
						<td width="30%" class="highlight">* <?php echo $lang->display('Desired Language'); ?>:</td>
						<td width="70%">
							<select
								class="input"
								onfocus="this.className='inputOn'"
								onblur="this.className='input'"
								name="desiredLanguageID"
								id="desiredLanguageID"
								onChange="ResetDiv('mappedTranslators');DoAjax('source_lang_id=<?php echo $row['sourceLanguageID']; ?>&target_lang_id='+this.value+'&subject_id=<?php echo $row['subjectID']; ?>&word_count=<?php echo $row['wordCount']; ?>','mappedTranslators','modules/mod_task_tender.php');"
							>
							<?php BuildTargetLangList($row['sourceLanguageID']); ?>
							</select>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<div id="mappedTranslators"></div>
						</td>
					</tr>
					<?php } ?>
					<?php if($type == "delegate") { ?>
					<tr>
						<td width="30%" class="highlight" valign="top">* <?php echo $lang->display('Desired Language'); ?>:</td>
						<td width="70%"><div id="languageoptions"><?php BuildTargetLangOption($row['sourceLanguageID']); ?></div></td>
					</tr>
					<tr>
						<td class="highlight">* <?php echo $lang->display('Agency'); ?>:</td>
						<td>
							<select
								class="input"
								onfocus="this.className='inputOn'"
								onblur="this.className='input'"
								name="agencyID"
								id="agencyID"
								onchange="ResetDiv('mappedAgents');DoAjax('agency_id='+this.value,'mappedAgents','modules/mod_task_agent.php')"
							>
							<?php BuildAgencyList($_SESSION['companyID']); ?>
							</select>
						</td>
					</tr>
					<tr>
						<td class="highlight">* <?php echo $lang->display('Agent'); ?>:</td>
						<td>
							<div id="mappedAgents">
								<select
									class="input"
									onfocus="this.className='inputOn'"
									onblur="this.className='input'"
									name="agentID"
									id="agentID"
								>
									<option value="">- <?php echo $lang->display('Select Agent'); ?> -</option>
								</select>
							</div>
						</td>
					</tr>
					<?php } ?>
					<tr>
						<td class="highlight">* <?php echo $lang->display('Deadline')." (".$lang->display('Sign-off').")"; ?>:</td>
						<td>
							<input
								type="text"
								class="input"
								onfocus="this.className='inputOn'"
								onblur="this.className='input'"
								name="deadline"
								id="deadline"
								onclick="displayDatePicker('deadline')"
								readonly="readonly"
							/>
							<a href="javascript:void(0);" onclick="displayDatePicker('deadline');"><img src="<?php echo IMG_PATH; ?>ico_calendar.gif"></a>
						</td>
					</tr>
					<tr>
						<td class="highlight" valign="top"><?php echo $lang->display('Job Brief'); ?>:</td>
						<td>
							<textarea
								class="input"
								onfocus="this.className='inputOn'"
								onblur="this.className='input'"
								name="brief"
								id="brief"
								rows="3"
								cols="80"
							></textarea>
						</td>
					</tr>
					<tr>
						<td class="highlight"><?php echo $lang->display('Attachment'); ?>:</td>
						<td>
							<input
								type="file"
								class="input"
								onfocus="this.className='inputOn'"
								onblur="this.className='input'"
								name="attachment"
								id="attachment"
							/>
						</td>
					</tr>
					<tr>
						<td class="highlight"></td>
						<td>
							<input
								type="checkbox"
								name="trial"
								id="trial"
								value="1"
							/>
							<?php echo $lang->display('This is a trial run that only deals with headings.'); ?>
							<br />
							<input
								id="startOption"
								name="startOption"
								type="checkbox"
								value="1"
								checked="checked"
							/>
							<?php echo $lang->display('Start Task Now'); ?>
						</td>
					</tr>
				</table>
			</div>
		</td>
	</tr>
	<tr>
		<td align="left">
			<input
				type="button"
				class="btnOff"
				onmousemove="this.className='btnOn'"
				onmouseout="this.className='btnOff'"
				title="<?php echo $lang->display('Back'); ?>"
				value="<?php echo $lang->display('Back'); ?>"
				onclick="DoAjax('id=<?php echo $id; ?>&type=<?php echo $type; ?>','window','modules/mod_task_new_step_1.php');"
			/>
		</td>
		<td align="right">
			<input
				type="submit"
				class="btnDo"
				onmousemove="this.className='btnOn'"
				onmouseout="this.className='btnDo'"
				title="<?php echo $lang->display('Finish'); ?>"
				value="<?php echo $lang->display('Finish'); ?>"
				onclick="if(jQueryCheckLanguageSelection('languageoptions')) { validateForm('desiredLanguageID','Desired language','R','agencyID','Agency','R','agentID','Agent','R','deadline','Deadline','R');return document.returnValue; } else return false;"
			/>
		</td>
	</tr>
</table>
<input name="type" type="hidden" value="<?php echo $type; ?>">
<input name="update" type="hidden" value="taskForm">
</form>