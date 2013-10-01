<?php
BuildHelperDiv($lang->display('Create New Tasks').' - '.$lang->display('Step 1 of 3'));
$navStatus = array("campaigns");
require_once(MODULES.'mod_header.php');
BuildPageIntro('<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=campaigns\');">'.$lang->display('Campaigns').'</a>'.BREADCRUMBS_ARROW.'<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=campaign&id='.$artwork_row['campaignID'].'\');">'.DisplayString($artwork_row['campaignName']).'</a>'.BREADCRUMBS_ARROW.'<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=artwork&id='.$artworkID.'\');">'.$artworkName.'</a>'.BREADCRUMBS_ARROW.$lang->display('Create New Tasks'));
?>
<div id="wrapperWhite">
	<div class="controlScroll">
		<div class="controlselectScroll">
			<!-- Toolbar -->
			<div class="toolbar">
				<div class="title">
					<div class="ico">
						<?php echo '<img src="'.IMG_PATH.'header/ico_task.png">'; ?>
					</div>
					<div class="txt">
						<?php echo $lang->display('Create New Task'); ?>
						<div class="intro"><?php echo $lang->display('Start your translation task, online or offline.'); ?></div>
					</div>
				</div>
				<div class="options">
					<!-- Next -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Next'); ?>">
						<a href="javascript:void(0);" onclick="SubmitForm('newform','next');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_next_on.png">'; ?></div>
							<div><?php echo $lang->display('Next'); ?></div>
						</a>
					</div>
					<!-- Cancel -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Cancel'); ?>">
						<a href="javascript:void(0);" onclick="goToURL('parent','index.php?layout=artwork&id=<?php echo $artworkID; ?>');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_close.png">'; ?></div>
							<div><?php echo $lang->display('Cancel'); ?></div>
						</a>
					</div>
				</div>
				<div class="clear"></div>
			</div>
			<div class="mainwrap">
				<form
					id="newform"
					name="newform"
					action="index.php?layout=artwork&id=<?php echo $artworkID; ?>&task=step1"
					method="POST"
					enctype="multipart/form-data"
				>
					<div class="artworkdetailPanel">
						<div class="handle">
							<div class="left"><?php echo $lang->display('New Task Step 1 Title'); ?></div>
							<div class="right"><?php BuildStepIndicator(1); ?></div>
							<div class="clear"></div>
						</div>
						<div class="window">
                                                    <?php $checked = !empty($_SESSION['task_type']) ? $_SESSION['task_type'] : "default"; ?>
                                                    <p><input type="radio" name="task_type" value="default"<?php if($checked=="default") echo ' checked="checked"'; ?> /> <?php echo $lang->display('Assign Translators/Proofreaders'); ?></p>
                                                    <p><input type="radio" name="task_type" value="agent"<?php if($checked=="agent") echo ' checked="checked"'; ?> /> <?php echo $lang->display('Delegate to Agencies'); ?></p>
						</div>
					</div>
					<input type="hidden" name="form" id="form">
				</form>
			</div>
		</div>
	</div>
</div>
<?php require_once(MODULES.'mod_footer.php'); ?>
<script type="text/javascript" src="javascripts/ajax.js"></script>
<script type="text/javascript" src="javascripts/datepicker.js"></script>
<script type="text/javascript" src="javascripts/jscolor/jscolor.js"></script>
<script type="text/javascript" src="javascripts/dom_drag.js"></script>
<script language="javascript">Drag.init(document.getElementById('handle'),document.getElementById('root'));</script>