<?php
$navStatus = array();
require_once(MODULES.'mod_header.php');
BuildPageIntro($row_company['companyName'],$row_company['country']);
?>
<div id="wrapperWhite">
	<div class="controlScroll">
		<div class="controlselectScroll">
			<table width="100%" border="0" cellspacing="0" cellpadding="5">
				<tr valign="top">
					<td width="35%">
						<table width="100%" border="0" cellspacing="0" cellpadding="5">
							<tr>
								<td colspan="2">
									<div class="img">
										<a href="<?php echo $row_company['companyWeb']; ?>" target="_blank"><img src="uploads/logos/<?php echo $row_company['companyLogo']; ?>" title="<?php echo $row_company['companyName']; ?>" /></a>
									</div>
								</td>
							</tr>
							<tr class="subject">
								<td colspan="2"><?php echo $lang->display('Company Information'); ?></td>
							</tr>
							<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
								<td width="50%" valign="top"><?php echo $lang->display('Company Name'); ?>:</td>
								<td width="50%" valign="top">
									<b><?php echo $row_company['companyName']; ?></b><br>
									<?php
										if ( $row_company['parentCompanyID'] > 0 ) {
											echo '( part of <a href="index.php?layout=company&id='.$row_company['parentCompanyID'].'">'.$row_parentCompanyRs['companyName'].'</a> )';
										}
									?>
								</td>
							</tr>
							<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
								<td><?php echo $lang->display('Agency'); ?>:</td>
								<td>
									<?php
										echo '<input type="checkbox"';
										if($row_company['agency']) echo ' checked="checked"';
										echo ' disabled>';
									?>
								</td>
							</tr>
							<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
								<td valign="top"><?php echo $lang->display('Company Address'); ?>:</td>
								<td>
									<div class="address"><?php echo $row_company['addressLine1']; ?></div>
									<div class="address"><?php echo $row_company['addressLine2']; ?></div>
									<div class="address"><?php echo $row_company['addressLine3']; ?></div>
									<div class="address"><?php echo $row_company['town']; ?></div>
									<div class="address"><?php echo $row_company['county']; ?></div>
									<div class="address"><?php echo $row_company['postcode']; ?></div>
									<div class="address"><?php echo $row_company['country']; ?></div>
								</td>
							</tr>
						</table>
					</td>
					<td width="35%">
						<table width="100%" border="0" cellspacing="0" cellpadding="5">
							<tr class="subject">
								<td colspan="2"><?php echo $lang->display('Company Contacts'); ?></td>
							</tr>
							<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
								<td width="50%"><?php echo $lang->display('First Contact'); ?>:</td>
								<td width="50%"><b><?php echo $row_company['firstContact']; ?></b></td>
							</tr>
							<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
								<td><?php echo $lang->display('Telephone'); ?>:</td>
								<td><?php echo $row_company['companyTelephone']; ?></td>
							</tr>
							<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
								<td><?php echo $lang->display('Fax'); ?>:</td>
								<td><?php echo $row_company['companyFax']; ?></td>
							</tr>
							<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
								<td><?php echo $lang->display('Email'); ?>:</td>
								<td><a href="mailto:<?php echo $row_company['companyEmail']; ?>"><?php echo $row_company['companyEmail']; ?></a></td>
							</tr>
							<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
								<td><?php echo $lang->display('Website'); ?>:</td>
								<td><a href="<?php echo $row_company['companyWeb']; ?>" target="_blank"><?php echo $row_company['companyWeb']; ?></a></td>
							</tr>
							<tr class="subject">
								<td colspan="2"><?php echo $lang->display('Registered Users'); ?></td>
							</tr>
							<?php
								$query = sprintf("SELECT * FROM users
												LEFT JOIN aro_groups ON users.userGroupID = aro_groups.id
												WHERE companyID = %d
												ORDER BY username ASC", $companyID);
								$result = mysql_query($query, $conn) or die(mysql_error());
								if(mysql_num_rows($result)) {
									while($row = mysql_fetch_assoc($result)) {
										echo '<tr class="bgWhite" onmouseover="this.className=\'hover\'" onmouseout="this.className=\'bgWhite\'">';
										echo '<td>';
										echo '<img src="'.IMG_PATH.'arrow_gold_rgt.png" /> <a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=user&id='.$row['userID'].'\');">'.$row['username'].'</a>';
										echo '</td>';
										echo '<td>'.$row['forename'].' '.$row['surname'].'</td>';
										echo '</tr>';
									}
								} else {
									echo '<tr class="bgWhite" onmouseover="this.className=\'hover\'" onmouseout="this.className=\'bgWhite\'">';
									echo '<td colspan="2"><i>'.$lang->display('N/A').'</i></td>';
									echo '</tr>';
								}
							?>
							<tr class="subject">
								<td colspan="2"><?php echo $lang->display('Advanced Properties'); ?></td>
							</tr>
							<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
								<td><?php echo $lang->display('System Name')." / FTP"; ?>:</td>
								<td><?php echo $row_company['systemName']; ?></td>
							</tr>
							<tr class="bgWhite" onmouseover="this.className='hover'" onmouseout="this.className='bgWhite'">
								<td valign="top"><?php echo $lang->display('Service Package'); ?>:</td>
								<td><?php echo $row_company['packageName']; ?></td>
							</tr>
						</table>
					</td>
					<td width="30%">
						<table width="100%" border="0" cellspacing="0" cellpadding="5">
							<tr class="subject">
								<td colspan="2"><?php echo $lang->display('Service Package Details'); ?></td>
							</tr>
							<?php
								$query = sprintf("SELECT id, notes
												FROM service_transaction_process
												ORDER BY serviceID,transactionID ASC");
								$result = mysql_query($query, $conn) or die(mysql_error());
								while($row = mysql_fetch_assoc($result)) {
									echo '<tr class="bgWhite" onmouseover="this.className=\'hover\'" onmouseout="this.className=\'bgWhite\'">';
									echo '<td>'.$lang->display($row['notes']).'</td>';
									$query_check = sprintf("SELECT *
															FROM service_package_items
															WHERE packageID = %d
															AND service_tID = %d
															LIMIT 1",
															$row_company['packageID'],
															$row['id']);
									$result_check = mysql_query($query_check, $conn) or die(mysql_error());
									if(mysql_num_rows($result_check)) {
										echo '<td>'.'<img src="'.IMG_PATH.'ico_enable.png" title="'.$lang->display('Allow').'" /></td>';
									} else {
										echo '<td>'.'<img src="'.IMG_PATH.'ico_disable.png" title="'.$lang->display('Deny').'" /></td>';
									}
									echo '</tr>';
								}
							?>
						</table>
					</td>
				</tr>
			</table>
		</div>
	</div>
</div>