<?php
BuildHelperDiv($lang->display('Message Box'));
$navStatus = array();
require_once(MODULES.'mod_header.php');
BuildPageIntro($lang->display('Message Box'),$lang->display('Message Box Intro'));
?>
<div id="wrapperWhite">
	<div class="controlScroll">
		<div class="controlselectScroll">
			<!-- Toolbar -->
			<div class="toolbar">
				<div class="title">
					<div class="ico"><?php echo '<img src="'.IMG_PATH.'header/ico_inbox.png">'; ?></div>
					<div class="txt"><?php echo $lang->display('Message Details'); ?></div>
				</div>
				<div class="options">
					<!-- Reply -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Reply'); ?>">
						<a href="javascript:void(0);" onclick="Popup('helper','blur');DoAjax('id=<?php echo $id; ?>&do=reply','window','modules/mod_pm_new.php');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_reply.png">'; ?></div>
							<div><?php echo $lang->display('Reply'); ?></div>
						</a>
					</div>
					<!-- Forward -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Forward'); ?>">
						<a href="javascript:void(0);" onclick="Popup('helper','blur');DoAjax('id=<?php echo $id; ?>&do=forward','window','modules/mod_pm_new.php');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_forward.png">'; ?></div>
							<div><?php echo $lang->display('Forward'); ?></div>
						</a>
					</div>
					<?php if($is_trashed) { ?>
					<!-- Restore -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Restore'); ?>">
						<a href="javascript:void(0);" onclick="SubmitForm('listform','restore');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_restore.png">'; ?></div>
							<div><?php echo $lang->display('Restore'); ?></div>
						</a>
					</div>
					<?php } else { ?>
					<!-- Trash -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Trash'); ?>">
						<a href="javascript:void(0);" onclick="SubmitForm('listform','trash');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_trash.png">'; ?></div>
							<div><?php echo $lang->display('Trash'); ?></div>
						</a>
					</div>
					<?php } ?>
					<!-- Close -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Close'); ?>">
						<a href="javascript:void(0);" onclick="SubmitForm('listform','close');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_close.png">'; ?></div>
							<div><?php echo $lang->display('Close'); ?></div>
						</a>
					</div>
				</div>
				<div class="clear"></div>
			</div>
			<!-- Mainwrap -->
			<div class="mainwrap">
				<form
					id="listform"
					name="listform"
					action="index.php?layout=<?php echo $layout; ?>&task=view&id=<?php echo $id; ?>"
					method="POST"
					enctype="multipart/form-data"
					onsubmit="Popup('loadingme','waiting');"
				>
					<table width="100%" border="0" cellspacing="0" cellpadding="3">
						<tr>
							<td width="10%" class="subject"><?php echo $lang->display('From'); ?>:</td>
							<td width="70%"><?php echo $row['s_forename'].' '.$row['s_surname'].' ['.$row['s_username'].']'; ?></td>
							<td width="20%" class="subject" align="right"><?php echo date(FORMAT_TIME,strtotime($row['messageTime'])); ?></td>
						</tr>
						<tr>
							<td class="subject"><?php echo $lang->display('To'); ?>:</td>
							<td colspan="2"><?php echo $row['r_forename'].' '.$row['r_surname'].' ['.$row['r_username'].']'; ?></td>
						</tr>
						<tr>
							<td class="subject"><?php echo $lang->display('Subject'); ?>:</td>
							<td colspan="2">
								<?php
									if (empty($row['subject'])) {
										echo '<i>('.$lang->display('No Subject').')</i>';
									} else {
										echo $row['subject'];
									}
								?>
							</td>
						</tr>
						<tr>
							<td colspan="3">
								<div class="content">
									<?php echo nl2br($row['content']); ?>
								</div>
							</td>
						</tr>
					</table>
					<input type="hidden" name="form" id="form">
				</form>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="javascripts/ajax.js"></script>
<script type="text/javascript" src="javascripts/dom_drag.js"></script>
<script language="javascript">Drag.init(document.getElementById('handle'),document.getElementById('root'));</script>