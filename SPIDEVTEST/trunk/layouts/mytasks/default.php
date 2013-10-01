<?php
$navStatus = array("mytasks");
require_once(MODULES.'mod_header.php');
BuildPageIntro('<a href="javascript:void(0);" onclick="goToURL(\'parent\',\'index.php?layout=mytasks\');" title="'.$lang->display('My Tasks').'">'.$lang->display('My Tasks').'</a>'.BREADCRUMBS_ARROW.$lang->display(ucfirst($view)));
?>
<div id="wrapperWhite">
	<div class="controlScroll">
		<div class="controlselectScroll">
			<!-- Toolbar -->
			<div class="toolbar">
				<div class="title">
					<div class="ico">
						<?php echo '<img src="'.IMG_PATH.'header/ico_'.$view.'.png">'; ?>
					</div>
					<div class="txt">
						<?php echo $lang->display(ucfirst($view)); ?>
						<div class="intro"><?php echo $lang->display('Tasktray Message'); ?></div>
					</div>
				</div>
				<div class="options">
					
				</div>
				<div class="clear"></div>
			</div>
			<form
				id="taskform"
				name="taskform"
				action="index.php?layout=<?php echo $layout; ?>&view=<?php echo $view; ?>"
				method="POST"
				enctype="multipart/form-data"
			>
			<div class="mainwrap">
				<?php require_once(MODULES.'mod_tasks.php'); ?>
			</div>
			<input type="hidden" name="by" id="by" value="<?php echo $by; ?>">
			<input type="hidden" name="order" id="order" value="<?php echo $order; ?>">
			<input type="hidden" name="form" id="form">
			</form>
		</div>
	</div>
</div>