<div id="loginwrapper">
	<?php require_once(MODULES.'mod_logo.php');?>
	<div id="systemBox">
		<div class="boxTitle"><?php echo $lang->display('System Message'); ?></div>
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td align="center">
					<div class="heading">
						<?php echo $lang->display($row_code['smTitle']); if($code==1) echo " ".$row_user['username']; ?>
					</div>
					<div class="bullets">
						<?php
							echo "<li>".$lang->display($row_code['smContent1']);
							if($code==1) {
								echo ": ".date(FORMAT_TIME,strtotime($row_user['lastLogin']));
							}
							echo "</li>";
							echo "<li>".$lang->display($row_code['smContent2']);
							if($code==1) {
								echo ": ".$row_user['lastIP'];
							}
							echo "</li>";
						?>
					</div>
					<p><?php echo $lang->display('System Redirection Message'); ?>...</p>
					<p><a href="<?php echo $restrictGoTo; ?>" target="_self"><?php echo $lang->display('Click Message'); ?></a></p>
				</td>
			</tr>
		</table>
	</div>
</div>