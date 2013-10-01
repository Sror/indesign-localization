<div id="loginwrapper">
	<?php require_once(MODULES.'mod_logo.php');?>
	<div id="systemBox">
			<div>
				<div class="left">
					<span class="boxTitle"><?php echo $lang->display('System Support'); ?></span>
				</div>
				<div class="right">
					<a href="index.php" target="_self"><?php echo $lang->display('Login'); ?></a>
				</div>
				<div class="clear">
				</div>
			</div>
			<div align="left">
			<p><?php echo $lang->display('Browser Support'); ?></p>
			</div>
			<div class="panelBox">
				<div class="titleBar">
					<div class="topic"><?php echo $lang->display('Supported Browsers'); ?></div>
					<div class="clear">
					</div>
				</div>
				<div class="myprofileScroll">
					<table cellspacing="0" class="supportTable" border="0">
						<tr>
							<td class="subject">Microsoft Windows - Internet Explorer 6.5 +</td>
							<td>
								<div class="mini_iconLink">
									<div class="icon">
										<a href="http://www.microsoft.com/windows/downloads/ie/getitnow.mspx" target="_blank"><img src="<?php echo IMG_PATH; ?>ico_s_download.gif" /></a>
									</div>
									<div class="link">
										<a href="http://www.microsoft.com/windows/downloads/ie/getitnow.mspx" target="_blank"><?php echo $lang->display('Download'); ?></a>
									</div>
								</div>
							</td>
						</tr>
						<tr>
							<td class="subject">Apple Macintosh OSX - Firefox 2.0 +</td>
							<td>
								<div class="mini_iconLink">
									<div class="icon">
										<a href="http://www.mozilla.com" target="_blank"><img src="<?php echo IMG_PATH; ?>ico_s_download.gif" /></a>
									</div>
									<div class="link">
										<a href="http://www.mozilla.com" target="_blank"><?php echo $lang->display('Download'); ?></a>
									</div>
								</div>
							</td>
						</tr>
					</table>
				</div>
				<div class="panelBox">
					<div class="titleBar">
						<div class="topic"><?php echo $lang->display('Browser Plugins'); ?></div>
						<div class="clear">
						</div>
					</div>
					<div class="greyBar">
						<table cellpadding="0" cellspacing="0" border="0" class="downloadTable">
							<tr>
								<td>
									<div class="iconBtn">
										<a href="http://www.adobe.com/products/acrobat/readstep2.html" target="_blank"><img src="<?php echo IMG_PATH; ?>ico_dia_pluginIE.gif" /></a>
										<br />
										<a href="http://www.adobe.com/products/acrobat/readstep2.html" target="_blank"><?php echo $lang->display('Plugin'); ?> for Internet Explorer</a>
									</div>
								</td>
								<td>
									<div class="iconBtn">
										<a href="http://www.mozilla.com" target="_blank"><img src="<?php echo IMG_PATH; ?>ico_dia_pluginFF.gif" /></a>
										<br />
										<a href="http://www.mozilla.com" target="_blank"><?php echo $lang->display('Plugin'); ?> for FireFox</a>
									</div>
								</td>
							</tr>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>