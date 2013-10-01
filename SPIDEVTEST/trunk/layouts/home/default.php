<?php
BuildHelperDiv($lang->display('Campaigns'));
$navStatus = array("home");
require_once(MODULES.'mod_header.php');
BuildPageIntro($lang->display('Home'),$lang->display('Welcome Message 1').' '.SYSTEM_NAME.' '.SYSTEM_VERSION);
?>
<div id="wrapperWhite">
	<div class="controlScroll">
		<div class="controlselectScroll">
			<?php
				echo '<div class="right" style="width:40%">';
				BuildTipMsg($lang->display('Do you know that the system will never pick you up as any translator or proofreader if you do not specify your language skills and specialised areas in your profile?').' <a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=profile\');">'.$lang->display('Want more work? Update your profile now!').'</a>');
				if ($acl->acl_check("campaigns","new",$_SESSION['companyID'],$_SESSION['userID'])) {
					BuildTipMsg($lang->display('Do you know that you can now set up a campaign, upload your artworks, assign translators and proofreaders, and manage the entire workflow online?').' <a href="javascript:void(0);" onclick="Popup(\'helper\',\'blur\');DoAjax(\'\',\'window\',\'modules/mod_camp_new.php\');">'.$lang->display('Want a document translated? Create your campaign now!').'</a>');
				}
				echo '</div>';
			?>
			<div class="shortcuts">
			<div class="shortcuts">
				<?php if ($acl->acl_check("campaigns","new",$_SESSION['companyID'],$_SESSION['userID'])) { ?>
				<div class="shortcutOff" onmouseover="this.className='shortcutOn'" onmouseout="this.className='shortcutOff'" title="<?php echo $lang->display('New Campaign'); ?>">
					<a href="javascript:void(0);" onclick="Popup('helper','blur');DoAjax('','window','modules/mod_camp_new.php');">
						<div class="image"><img src="<?php echo IMG_PATH; ?>header/ico_campaign_new.png" /></div>
						<div class="label"><?php echo $lang->display('New Campaign'); ?></div>
					</a>
				</div>
				<?php } ?>
				
				<?php if ($acl->acl_check("artworks","new",$_SESSION['companyID'],$_SESSION['userID'])) { ?>
				<div class="shortcutOff" onmouseover="this.className='shortcutOn'" onmouseout="this.className='shortcutOff'" title="<?php echo $lang->display('Upload Artworks'); ?>">
					<a href="javascript:void(0);" onclick="Popup('helper','blur');DoAjax('','window','modules/mod_campaign_list.php');">
						<div class="image"><img src="<?php echo IMG_PATH; ?>header/ico_upload.png" /></div>
						<div class="label"><?php echo $lang->display('Upload Artworks'); ?></div>
					</a>
				</div>
				<?php } ?>
				
				<div class="shortcutOff" onmouseover="this.className='shortcutOn'" onmouseout="this.className='shortcutOff'" title="<?php echo $lang->display('My Tasks'); ?>">
					<a href="javascript:void(0);" onclick="goToURL('parent','index.php?layout=mytasks');">
						<div class="image"><img src="<?php echo IMG_PATH; ?>header/ico_intray.png" /></div>
						<div class="label">
						<?php
							if(!empty($stats_mytasks)) echo '<b>';
							echo $lang->display('My Tasks');
							if(!empty($stats_mytasks)) echo ' ('.$stats_mytasks.')</b>';
						?>
						</div>
					</a>
				</div>
				
				<?php if($acl->acl_check("system","cpanel",$_SESSION['companyID'],$_SESSION['userID'])) { ?>
				<div class="shortcutOff" onmouseover="this.className='shortcutOn'" onmouseout="this.className='shortcutOff'" title="<?php echo $lang->display('File Manager'); ?>">
					<a href="javascript:void(0);" onclick="goToURL('parent','index.php?layout=cp_file');">
						<div class="image"><img src="<?php echo IMG_PATH; ?>header/ico_ftp.png" /></div>
						<div class="label"><?php echo $lang->display('File Manager'); ?></div>
					</a>
				</div>
				<?php } ?>
				
				<?php if($acl->acl_check("system","cpanel",$_SESSION['companyID'],$_SESSION['userID'])) { ?>
				<div class="shortcutOff" onmouseover="this.className='shortcutOn'" onmouseout="this.className='shortcutOff'" title="<?php echo $lang->display('Font Manager'); ?>">
					<a href="javascript:void(0);" onclick="goToURL('parent','index.php?layout=cp_font');">
						<div class="image"><img src="<?php echo IMG_PATH; ?>header/ico_font.png" /></div>
						<div class="label"><?php echo $lang->display('Font Manager'); ?></div>
					</a>
				</div>
				<?php } ?>
				
				<?php if($acl->acl_check("system","cpanel",$_SESSION['companyID'],$_SESSION['userID'])) { ?>
				<div class="shortcutOff" onmouseover="this.className='shortcutOn'" onmouseout="this.className='shortcutOff'" title="<?php echo $lang->display('Company Manager'); ?>">
					<a href="javascript:void(0);" onclick="goToURL('parent','index.php?layout=cp_company');">
						<div class="image"><img src="<?php echo IMG_PATH; ?>header/ico_company.png" /></div>
						<div class="label"><?php echo $lang->display('Company Manager'); ?></div>
					</a>
				</div>
				<?php } ?>
			
				<?php if($acl->acl_check("system","cpanel",$_SESSION['companyID'],$_SESSION['userID'])) { ?>
				<div class="shortcutOff" onmouseover="this.className='shortcutOn'" onmouseout="this.className='shortcutOff'" title="<?php echo $lang->display('User Manager'); ?>">
					<a href="javascript:void(0);" onclick="goToURL('parent','index.php?layout=cp_user');">
						<div class="image"><img src="<?php echo IMG_PATH; ?>header/ico_user.png" /></div>
						<div class="label"><?php echo $lang->display('User Manager'); ?></div>
					</a>
				</div>
				<?php } ?>
				
				
				<?php if($acl->acl_check("system","cpanel",$_SESSION['companyID'],$_SESSION['userID'])) { ?>
				<div class="shortcutOff" onmouseover="this.className='shortcutOn'" onmouseout="this.className='shortcutOff'" title="<?php echo $lang->display('Brand Manager'); ?>">
					<a href="javascript:void(0);" onclick="goToURL('parent','index.php?layout=cp_brand');">
						<div class="image"><img src="<?php echo IMG_PATH; ?>header/ico_brand.png" /></div>
						<div class="label"><?php echo $lang->display('Brand Manager'); ?></div>
					</a>
				</div>
				<?php } ?>
			
				<div class="shortcutOff" onmouseover="this.className='shortcutOn'" onmouseout="this.className='shortcutOff'" title="<?php echo $lang->display('My Profile'); ?>">
					<a href="javascript:void(0);" onclick="goToURL('parent','index.php?layout=profile');">
						<div class="image"><img src="<?php echo IMG_PATH; ?>header/ico_profile.png" /></div>
						<div class="label"><?php echo $lang->display('My Profile'); ?></div>
					</a>
				</div>
				
				<div class="clear"></div>
		
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="javascripts/ajax.js"></script>
<script type="text/javascript" src="javascripts/dom_drag.js"></script>
<script language="javascript">Drag.init(document.getElementById('handle'),document.getElementById('root'));</script>