<?php
require_once(dirname(__FILE__).'/../config.php');
$access = array("artworks","new");
require_once(MODULES.'mod_authorise.php');

$id = (isset($_GET['id'])) ? (int)$_GET['id'] : 0;
$query = sprintf("SELECT sourceLanguageID AS lang_id
					FROM campaigns
					WHERE campaignID = %d
					LIMIT 1",
					$id);
$result = mysql_query($query, $conn) or die(mysql_error());
if(!mysql_num_rows($result)) access_denied();
$row = mysql_fetch_assoc($result);
?>
<form
	action="index.php?layout=campaign&id=<?php echo $id; ?>"
	name="new_art_form"
	method="POST"
	enctype="multipart/form-data"
	onsubmit="hidediv('helper');Popup('loadingme','waiting');process_start(document.getElementById('token').value);"
>
	<table width="100%" border="0" cellspacing="0" cellpadding="3">
		<tr>
			<td width="30%" class="highlight"><?php echo $lang->display('Artwork Title'); ?>:</td>
			<td width="70%">
				<input
					type="text"
					class="input"
					onfocus="this.className='inputOn'"
					onblur="this.className='input'"
					name="artworkName"
					id="artworkName"
				/>
			</td>
		</tr>
		<tr>
			<td class="highlight">* <?php echo $lang->display('Version'); ?>:</td>
			<td>
				<input
					type="text"
					class="input"
					onfocus="this.className='inputOn'"
					onblur="this.className='input'"
					name="version"
					id="version"
					value="1.0.0"
					size="10"
					maxlength="20"
				/>
			</td>
		</tr>
		<tr>
			<td class="highlight"><?php echo $lang->display('Subject (Optional)'); ?>:</td>
			<td>
				<select
					class="input"
					onfocus="this.className='inputOn'"
					onblur="this.className='input'"
					name="subjectID"
					id="subjectID"
				>
					<?php BuildSubjectList(); ?>
				</select>
			</td>
		</tr>
		<tr>
			<td class="highlight">* <?php echo $lang->display('File Type'); ?>:</td>
			<td>
				<select
					class="input"
					onfocus="this.className='inputOn'"
					onblur="this.className='input'"
					name="artworkType"
					id="artworkType"
				>
					<?php BuildFileTypeList($_SESSION['packageID'],SERVICE_UPLOAD,TYPE_ORIGINAL,DEFAULT_FILE_TYPE,$lang->display('Auto Detect')); ?>
				</select>
			</td>
		</tr>
		<tr>
			<td class="highlight">* <?php echo $lang->display('Parser Type'); ?>:</td>
			<td>
				<select
					class="input"
					onfocus="this.className='inputOn'"
					onblur="this.className='input'"
					name="parse_type"
					id="parse_type"
				>
					<?php BuildParserType(); ?>
				</select>
				<input
					type="checkbox"
					class="input"
					onfocus="this.className='inputOn'"
					onblur="this.className='input'"
					name="break_softreturn"
					id="break_softreturn"
					value="1"
				/>
				<?php echo $lang->display('Break soft returns'); ?>
			</td>
		</tr>
		<tr>
			<td class="highlight" valign="top">* <?php echo $lang->display('Select File'); ?>:</td>
			<td><div id="uploader"><?php require_once(MODULES.'mod_art_uploader.php'); ?></div></td>
		</tr>
		<tr>
			<td colspan="2">
				<div class="arrrgt" id="advanced" onclick="ChangeArrow('advanced');showandhide('advancedoptions');">
					<?php echo $lang->display('Advanced Options'); ?>
				</div>
				<div id="advancedoptions" class="greyBar" style="display:none;">
					<table width="100%" border="0" cellspacing="0" cellpadding="3">
						<tr>
							<td class="highlight" width="30%"><?php echo $lang->display('Default Substitute Font'); ?>:</td>
							<td width="70%">
								<select
									class="input"
									onfocus="this.className='inputOn'"
									onblur="this.className='input'"
									name="default_sub_font_id"
									id="default_sub_font_id"
								>
									<?php BuildFontSubList(); ?>
								</select>
							</td>
						</tr>
						<tr>
							<td class="highlight" valign="top"><?php echo $lang->display('Default Image Folder'); ?>:</td>
							<td>
								<input
									type="text"
									class="input"
									onfocus="this.className='inputOn'"
									onblur="this.className='input'"
									name="default_img_dir"
									id="default_img_dir"
									onclick="display('local_ftp');ResetDiv('local_ftp');DoAjax('companyID=<?php echo $_SESSION['companyID']; ?>&dir='+this.value,'local_ftp','modules/mod_ftp_dir.php');"
									readonly="readonly"
								/>
								<a href="javascript:void(0);" onclick="setValue('default_img_dir','');hidediv('local_ftp');"><?php echo $lang->display('Reset'); ?></a>
								<div id="local_ftp"></div>
							</td>
						</tr>
						<?php if ($acl->acl_check("tasks","new",$_SESSION['companyID'],$_SESSION['userID'])) { ?>
						<tr>
							<td colspan="2">
								<div class="arrrgt" id="assignArrow" onclick="ChangeArrow('assignArrow');ResetArrow('delegateArrow');hidediv('delegate');openandclose('assign');">
									<?php echo $lang->display('Assign Translators/Proofreaders'); ?>
								</div>
								<div id="assign" class="greyBar" style="display:none;">
									<table width="100%" cellspacing="0" cellpadding="3" border="0">
										<tr>
											<td width="30%" class="highlight">* <?php echo $lang->display('Desired Language'); ?>:</td>
											<td width="70%">
												<select
													class="input"
													onfocus="this.className='inputOn'"
													onblur="this.className='input'"
													name="desiredLanguageID"
													id="desiredLanguageID"
													onChange="ResetDiv('mappedTranslators');DoAjax('source_lang_id=<?php echo $row['lang_id']; ?>&target_lang_id='+this.value+'&subject_id='+document.getElementById('subjectID').value,'mappedTranslators','modules/mod_task_tender.php');"
												>
													<?php BuildTargetLangList($row['lang_id']); ?>
												</select>
											</td>
										</tr>
										<tr>
											<td colspan="2">
												<div id="mappedTranslators"></div>
											</td>
										</tr>
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
													cols="60"
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
												/>
												<?php echo $lang->display('No Task Start'); ?>
											</td>
										</tr>
									</table>
								</div>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<div class="arrrgt" id="delegateArrow" onclick="ResetArrow('assignArrow');ChangeArrow('delegateArrow');hidediv('assign');openandclose('delegate');">
									<?php echo $lang->display('Delegate to Agencies'); ?>
								</div>
								<div id="delegate" class="greyBar" style="display:none;">
									<table width="100%" cellspacing="0" cellpadding="3" border="0">
										<tr>
											<td width="30%" class="highlight" valign="top">* <?php echo $lang->display('Desired Language'); ?>:</td>
											<td width="70%"><?php BuildTargetLangOption($row['lang_id']); ?></td>
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
										<tr>
											<td class="highlight">* <?php echo $lang->display('Deadline')." (".$lang->display('Sign-off').")"; ?>:</td>
											<td>
												<input
													type="text"
													class="input"
													onfocus="this.className='inputOn'"
													onblur="this.className='input'"
													name="timescale"
													id="timescale"
													onclick="displayDatePicker('timescale')"
													readonly="readonly"
												/>
												<a href="javascript:void(0);" onclick="displayDatePicker('timescale');"><img src="<?php echo IMG_PATH; ?>ico_calendar.gif"></a>
											</td>
										</tr>
										<tr>
											<td class="highlight" valign="top"><?php echo $lang->display('Job Brief'); ?>:</td>
											<td>
												<textarea
													class="input"
													onfocus="this.className='inputOn'"
													onblur="this.className='input'"
													name="jobbrief"
													id="jobbrief"
													rows="3"
													cols="60"
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
													name="jobattachment"
													id="jobattachment"
												/>
											</td>
										</tr>
										<tr>
											<td class="highlight"></td>
											<td>
												<input
													type="checkbox"
													name="trialOption"
													id="trialOption"
													value="1"
												/>
												<?php echo $lang->display('This is a trial run that only deals with headings.'); ?>
											</td>
										</tr>
									</table>
								</div>
							</td>
						</tr>
						<?php } ?>
					</table>
				</div>
			</td>
		</tr>

		<tr>
			<td></td>
			<td>
				<input
					type="submit"
					class="btnDo"
					onmousemove="this.className='btnOn'"
					onmouseout="this.className='btnDo'"
					title="<?php echo $lang->display('Upload Artworks'); ?>"
					value="<?php echo $lang->display('Upload Artworks'); ?>"
					onclick="validateForm('artworkType','Artwork type','R');return document.returnValue;"
				/>
				<input
					type="reset"
					class="btnOff"
					onmousemove="this.className='btnOn'"
					onmouseout="this.className='btnOff'"
					title="<?php echo $lang->display('Reset'); ?>"
					value="<?php echo $lang->display('Reset'); ?>"
				/>
			</td>
		</tr>
	</table>
	<input name="token" id="token" type="hidden" value="<?php echo "$id-{$_SESSION['userID']}-".md5(time().rand()); ?>">
	<input name="update" type="hidden" value="new_art_form">
</form>