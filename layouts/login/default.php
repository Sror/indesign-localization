<div id="loginwrapper">
	<?php require_once(MODULES.'mod_logo.php'); ?>
	<div id="logBox">
		<form action="index.php" method="POST" name="loginForm" target="_self" id="loginForm">
			<table border="0" cellspacing="0" cellpadding="0" width="100%">
				<tr>
					<td>
						<table border="0" cellspacing="0" cellpadding="4" width="100%">
							<tr>
								<td><?php echo $lang->display('Username'); ?>:</td>
								<td>
									<input
										type="text"
										class="input"
										onfocus="this.className='inputOn'"
										onblur="this.className='input'"
										name="username"
										id="username"
									/>
								</td>
							</tr>
							<tr>
								<td><?php echo $lang->display('Password'); ?>:</td>
								<td>
									<input
										type="password"
										class="input"
										onfocus="this.className='inputOn'"
										onblur="this.className='input'"
										name="password"
										id="password"
									/>
								</td>
							</tr>
						</table>
					</td>
					<td>
						<input style="height:62px;width:65px;border-width:0px;" type="image" src="<?php echo IMG_PATH; ?>btn_login.png" title="Login" name="login" width="65" height="62" id="login" />
					</td>
				</tr>
			</table>
		</form>  
	</div>
	<?php BuildLangFlags(); ?>
	<?php require_once(MODULES.'mod_support.php'); ?>
</div>