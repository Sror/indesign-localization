<?php
BuildHelperDiv($lang->display('Message Box'));
$navStatus = array();
require_once(MODULES.'mod_header.php');
BuildPageIntro($row_publicRs['username'],$lang->display('Public Profile'));
?>
<div id="wrapperWhite">
	<div class="controlScroll">
		<div class="controlselectScroll">
			<?php
				if($isadmin && ($userID!=$_SESSION['userID'])) {
					BuildTipMsg($lang->display('Please click on the ACL indicators to grant or deny access privileges.'));
				}
			?>
			<table width="100%" border="0" cellspacing="0" cellpadding="5">
				<tr valign="top">
					<td width="35%">
						<table width="100%" border="0" cellspacing="0" cellpadding="5">
							<tr>
								<td colspan="2">
									<div class="left">
										<div class="img">
											<a href="uploads/photos/<?php echo $row_publicRs['photo']; ?>" rel="lightbox" title="<?php echo $row_publicRs['username']; ?>"><img src="uploads/photos/<?php echo $row_publicRs['photo']; ?>" /></a>
										</div>
									</div>
									<div class="right">
										<a href="javascript:void(0);" onclick="Popup('helper','blur');DoAjax('recipient=<?php echo $row_publicRs['username']; ?>','window','modules/mod_pm_new.php');"><img src="<?php echo IMG_PATH; ?>header/ico_inbox.png" title="<?php echo $lang->display('Send Message'); ?>" /></a>
									</div>
									<div class="clear"></div>
								</td>
							</tr>
							<tr>
								<td colspan="2"><div class="subject"><?php echo $lang->display('Account Info'); ?></div></td>
							</tr>
							<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
								<td width="50%"><?php echo $lang->display('Username'); ?>:</td>
								<td width="50%"><b><?php echo $row_publicRs['username']; ?></b></td>
							</tr>
							<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
								<td><?php echo $lang->display('Company'); ?>:</td>
								<td>
									<a href="javascript:void(0);" onclick="goToURL('parent','index.php?layout=company&id=<?php echo $row_publicRs['companyID']; ?>');"><?php echo $row_publicRs['companyName']; ?></a>
								</td>
							</tr>
							<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
								<td><?php echo $lang->display('Agent'); ?>:</td>
								<td>
									<?php
										if($row_publicRs['agent']==1) {
											echo '<img src="'.IMG_PATH.'ico_enable.png">';
										} else {
											echo '<img src="'.IMG_PATH.'ico_disable.png">';
										}
									?>
								</td>
							</tr>
							<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
								<td><?php echo $lang->display('User Group'); ?>:</td>
								<td><?php echo $lang->display($row_publicRs['name']); ?></td>
							</tr>
							<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
								<td><?php echo $lang->display('Last Active Login'); ?>:</td>
								<td><?php echo date(FORMAT_TIME,strtotime($row_publicRs['lastLogin'])); ?></td>
							</tr>
							<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
								<td><?php echo $lang->display('Last IP Logged'); ?>:</td>
								<td><?php echo $row_publicRs['lastIP']; ?></td>
							</tr>
							<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
								<td><?php echo $lang->display('Status'); ?>:</td>
								<td><b><div class="<?php if($row_publicRs['online'] == 1) echo "green"; else echo "grey"; ?>"><?php if ($row_publicRs['online'] == 1) echo $lang->display('Online'); else echo $lang->display('Offline'); ?></div></b></td>
							</tr>
							<tr>
								<td colspan="2"><div class="subject"><?php echo $lang->display('Personal Details'); ?></div></td>
							</tr>
							<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
								<td><?php echo $lang->display('Full Name'); ?>:</td>
								<td><?php echo $row_publicRs['forename']." ".$row_publicRs['surname']; ?></td>
							</tr>
							<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
								<td><?php echo $lang->display('Email'); ?>:</td>
								<td><a href="mailto:<?php echo $row_publicRs['email']; ?>"><?php echo $row_publicRs['email']; ?></a></td>
							</tr>
							<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
								<td><?php echo $lang->display('Telephone'); ?>:</td>
								<td><?php echo $row_publicRs['telephone']; ?></td>
							</tr>
							<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
								<td><?php echo $lang->display('Fax'); ?>:</td>
								<td><?php echo $row_publicRs['fax']; ?></td>
							</tr>
							<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
								<td><?php echo $lang->display('Mobile'); ?>:</td>
								<td><?php echo $row_publicRs['mobile']; ?></td>
							</tr>
						</table>
					</td>
					<td width="35%">
						<table width="100%" border="0" cellspacing="0" cellpadding="5">
							<tr>
								<td colspan="2"><div class="subject"><?php echo $lang->display('Language Capability'); ?></div></td>
							</tr>
							<?php BuildUserLangList($userID); ?>
							<tr>
								<td colspan="2"><div class="subject"><?php echo $lang->display('Specialisations'); ?></div></td>
							</tr>
							<?php BuildUserSpecList($userID); ?>
							<tr>
								<td colspan="2"><div class="subject"><?php echo $lang->display('Minimum Rate Per Word'); ?></div></td>
							</tr>
							<?php BuildUserRateList($userID); ?>
						</table>
					</td>
					<td width="30%">
						<table width="100%" border="0" cellspacing="0" cellpadding="5">
							<?php BuildUserACLList($acl,$userID,$row_publicRs['companyID']); ?>
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