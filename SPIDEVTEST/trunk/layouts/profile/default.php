<?php
BuildHelperDiv($lang->display('My Profile'));
$navStatus = array("profile");
require_once(MODULES.'mod_header.php');
BuildPageIntro($lang->display('My Profile'),$lang->display('My Profile Description'));
?>
<div id="wrapperWhite">
	<div class="controlScroll">
		<div class="controlselectScroll">
			<table width="100%" border="0" cellspacing="0" cellpadding="5">
				<tr valign="top">
					<td width="35%">
						<table width="100%" border="0" cellspacing="0" cellpadding="5">
							<tbody onmouseover="display('edit_photo');" onmouseout="hidediv('edit_photo');">
								<tr>
									<td colspan="2">
										<div>
											<div class="left">
												<div class="img">
													<a href="uploads/photos/<?php echo $row_profileRs['photo']; ?>" rel="lightbox" title="<?php echo $row_profileRs['username']; ?>">
														<img src="uploads/photos/<?php echo $row_profileRs['photo']; ?>" />
													</a>
												</div>
											</div>
											<div class="right" id="edit_photo" style="display:none;">
												<?php
													if ($acl->acl_check("profile","upPhoto",$_SESSION['companyID'],$_SESSION['userID'])) {
														echo '<a href="javascript:void(0);" onclick="Popup(\'helper\',\'blur\');DoAjax(\'id='.$_SESSION['userID'].'\',\'window\',\'modules/mod_edit_photo.php\');"><img src="'.IMG_PATH.'ico_edit.png" title="'.$lang->display('Edit').'" /></a>';
													}
												?>
											</div>
											<div class="clear"></div>
										</div>
									</td>
								</tr>
							</tbody>
							<tbody onmouseover="display('edit_account');" onmouseout="hidediv('edit_account');">
								<tr class="subject">
									<td width="50%"><?php echo $lang->display('Account Info'); ?></td>
									<td width="50%" align="right">
										<div id="edit_account" style="display:none;">
											<?php echo '<img src="'.IMG_PATH.'ico_locked.png" title="'.$lang->display('Edit Locked').'" />'; ?>
										</div>
									</td>
								</tr>
								<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
									<td><?php echo $lang->display('Username'); ?>:</td>
									<td>
										<b><?php echo $row_profileRs['username']; ?></b>
									</td>
								</tr>
								<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
									<td><?php echo $lang->display('Company'); ?>:</td>
									<td>
										<a href="javascript:void(0);" onclick="goToURL('parent','index.php?layout=company&id=<?php echo $_SESSION['companyID']; ?>');"><?php echo $row_profileRs['companyName']; ?></a>
									</td>
								</tr>
								<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
									<td><?php echo $lang->display('Agent'); ?>:</td>
									<td>
										<?php
											if($row_profileRs['agent']==1) {
												echo '<img src="'.IMG_PATH.'ico_enable.png">';
											} else {
												echo '<img src="'.IMG_PATH.'ico_disable.png">';
											}
										?>
									</td>
								</tr>
								<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
									<td><?php echo $lang->display('User Group'); ?>:</td>
									<td><?php echo $lang->display($row_profileRs['name']); ?></td>
								</tr>
								<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
									<td><?php echo $lang->display('Last Active Login'); ?>:</td>
									<td><?php echo date(FORMAT_TIME,strtotime($row_profileRs['lastLogin'])); ?></td>
								</tr>
									<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
									<td><?php echo $lang->display('Last IP Logged'); ?>:</td>
									<td><?php echo $row_profileRs['lastIP']; ?></td>
								</tr>
								<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
									<td><?php echo $lang->display('Status'); ?>:</td>
									<td><b><div class="<?php if($row_profileRs['online'] == 1) echo "green"; else echo "grey"; ?>"><?php if ($row_profileRs['online'] == 1) echo $lang->display('Online'); else echo $lang->display('Offline'); ?></div></b></td>
								</tr>
							</tbody>
							<tbody onmouseover="display('edit_password');" onmouseout="hidediv('edit_password');">
								<tr class="subject">
									<td width="50%"><?php echo $lang->display('Password'); ?></td>
									<td width="50%" align="right">
										<div id="edit_password" style="display:none;">
											<?php
												if ($acl->acl_check("profile","changePass",$_SESSION['companyID'],$_SESSION['userID'])) {
													echo '<a href="javascript:void(0);" onclick="Popup(\'helper\',\'blur\');DoAjax(\'id='.$_SESSION['userID'].'\',\'window\',\'modules/mod_edit_pwd.php\');"><img src="'.IMG_PATH.'ico_edit.png" title="'.$lang->display('Edit').'" /></a>';
												}
											?>
										</div>
									</td>
								</tr>
								<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
									<td><?php echo $lang->display('Current Password'); ?>:</td>
									<td><?php echo preg_replace('/[\s\S]/', '*', $row_profileRs['password']); ?></td>
								</tr>
							</tbody>
							<tbody onmouseover="display('edit_profile');" onmouseout="hidediv('edit_profile');">
								<tr class="subject">
									<td width="50%"><?php echo $lang->display('Personal Details'); ?></td>
									<td width="50%" align="right">
										<div id="edit_profile" style="display:none;">
											<?php
												if ($acl->acl_check("profile","editProfile",$_SESSION['companyID'],$_SESSION['userID'])) {
													echo '<a href="javascript:void(0);" onclick="Popup(\'helper\',\'blur\');DoAjax(\'id='.$_SESSION['userID'].'\',\'window\',\'modules/mod_edit_profile.php\');"><img src="'.IMG_PATH.'ico_edit.png" title="'.$lang->display('Edit').'" /></a>';
												}
											?>
										</div>
									</td>
								</tr>
								<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
									<td><?php echo $lang->display('Full Name'); ?>:</td>
									<td><?php echo $row_profileRs['forename']." ".$row_profileRs['surname']; ?></td>
								</tr>
								<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
									<td><?php echo $lang->display('Email'); ?>:</td>
									<td><a href="mailto:<?php echo $row_profileRs['email']; ?>"><?php echo $row_profileRs['email']; ?></a></td>
								</tr>
								<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
									<td><?php echo $lang->display('Telephone'); ?>:</td>
									<td><?php echo $row_profileRs['telephone']; ?></td>
								</tr>
								<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
									<td><?php echo $lang->display('Fax'); ?>:</td>
									<td><?php echo $row_profileRs['fax']; ?></td>
								</tr>
								<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
									<td><?php echo $lang->display('Mobile'); ?>:</td>
									<td><?php echo $row_profileRs['mobile']; ?></td>
								</tr>
							</tbody>
						</table>
					</td>
					<td width="35%">
						<table width="100%" border="0" cellspacing="0" cellpadding="5">
							<tbody onmouseover="display('edit_lang');" onmouseout="hidediv('edit_lang');">
								<tr class="subject">
									<td width="50%"><?php echo $lang->display('Language Capability'); ?></td>
									<td width="50%" align="right">
										<div id="edit_lang" style="display:none;">
											<?php
												if ($acl->acl_check("profile","editLang",$_SESSION['companyID'],$_SESSION['userID'])) {
													echo '<a href="javascript:void(0);" onclick="Popup(\'helper\',\'blur\');DoAjax(\'id='.$_SESSION['userID'].'\',\'window\',\'modules/mod_edit_lang.php\');"><img src="'.IMG_PATH.'ico_edit.png" title="'.$lang->display('Edit').'" /></a>';
												}
											?>
										</div>
									</td>
								</tr>
								<?php BuildUserLangList($_SESSION['userID']); ?>
							</tbody>
							<tbody onmouseover="display('edit_spec');" onmouseout="hidediv('edit_spec');">
								<tr class="subject">
									<td width="50%"><?php echo $lang->display('Specialisations'); ?></td>
									<td width="50%" align="right">
										<div id="edit_spec" style="display:none;">
											<?php
												if ($acl->acl_check("profile","editSpec",$_SESSION['companyID'],$_SESSION['userID'])) {
													echo '<a href="javascript:void(0);" onclick="Popup(\'helper\',\'blur\');DoAjax(\'id='.$_SESSION['userID'].'\',\'window\',\'modules/mod_edit_spec.php\');"><img src="'.IMG_PATH.'ico_edit.png" title="'.$lang->display('Edit').'" /></a>';
												}
											?>
										</div>
									</td>
								</tr>
								<?php BuildUserSpecList($_SESSION['userID']); ?>
							</tbody>
							<tbody onmouseover="display('edit_rate');" onmouseout="hidediv('edit_rate');">
								<tr class="subject">
									<td width="50%"><?php echo $lang->display('Minimum Rate Per Word'); ?></td>
									<td width="50%" align="right">
										<div id="edit_rate" style="display:none;">
											<?php
												if ($acl->acl_check("profile","eidtRate",$_SESSION['companyID'],$_SESSION['userID'])) {
													echo '<a href="javascript:void(0);" onclick="Popup(\'helper\',\'blur\');DoAjax(\'id='.$_SESSION['userID'].'\',\'window\',\'modules/mod_edit_rate.php\');"><img src="'.IMG_PATH.'ico_edit.png" title="'.$lang->display('Edit').'" /></a>';
												}
											?>
										</div>
									</td>
								</tr>
								<?php BuildUserRateList($_SESSION['userID']); ?>
							</tbody>
						</table>
					</td>
					<td width="30%">
						<table width="100%" border="0" cellspacing="0" cellpadding="5">
							<?php BuildUserACLList($acl,$_SESSION['userID'],$_SESSION['companyID']); ?>
						</table>
					</td>
				</tr>
			</table>
		</div>
	</div>
</div>
<script type="text/javascript" src="javascripts/ajax.js"></script>
<script type="text/javascript" src="javascripts/lightbox/prototype.js"></script>
<script type="text/javascript" src="javascripts/lightbox/scriptaculous.js?load=effects,builder"></script>
<script type="text/javascript" src="javascripts/lightbox/lightbox.js"></script>
<script type="text/javascript" src="javascripts/dom_drag.js"></script>
<script language="javascript">Drag.init(document.getElementById('handle'),document.getElementById('root'));</script>