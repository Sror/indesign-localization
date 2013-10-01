<?php
$navStatus = array("cpanel");
require_once(MODULES.'mod_header.php');
BuildPageIntro($lang->display('Control Panel'),$lang->display('CP Intro'));
?>
<div id="wrapperWhite">
	<div class="controlScroll">
		<div class="controlselectScroll">
			<!-- Toolbar -->
			<div class="toolbar">
				<div class="title">
					<div class="ico"><?php echo '<img src="'.IMG_PATH.'header/ico_translation_memory.png">'; ?></div>
					<div class="txt"><?php echo $lang->display('Search').': '.$lang->display('Translation Memory'); ?></div>
				</div>
				<div class="options">
					<!-- New -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('New'); ?>">
						<a href="javascript:void(0);" onclick="SubmitForm('searchform','new');">
							<div class="ico"><?php echo '<img src="'.IMG_PATH.'toolbar/ico_new.png">'; ?></div>
							<div><?php echo $lang->display('New'); ?></div>
						</a>
					</div>
					<!-- Close -->
					<div class="optionOff" onmouseover="this.className='optionOn'" onmouseout="this.className='optionOff'" title="<?php echo $lang->display('Close'); ?>">
						<a href="javascript:void(0);" onclick="SubmitForm('searchform','close');">
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
					id="searchform"
					name="searchform"
					action="index.php?layout=<?php echo $layout; ?>&task=<?php echo $task; ?>"
					method="POST"
					enctype="multipart/form-data"
				>
				<div class="leftwrap">
					<table width="100%" cellpadding="3" cellspacing="0" border="0">
						<tr>
							<td colspan="2">
								<textarea
									class="input"
									onfocus="this.className='inputOn'"
									onblur="this.className='input'"
									name="source"
									id="source"
									rows="5"
									style="width:99%"
								><?php if(!empty($_GET['para'])) echo "\n".$_GET['para'];?></textarea>
							</td>
						</tr>
						<tr>
							<td>
								<select
									class="input"
									onfocus="this.className='inputOn'"
									onblur="this.className='input'"
									name="fromLangID"
									id="fromLangID"
									title="<?php echo $lang->display('Select Language'); ?>"
								>
								<?php
									$langID = !empty($_GET['langID']) ? $_GET['langID'] : 1;
									BuildLangList($langID);
								?>
								</select>
								<?php echo '<img src="'.IMG_PATH.'flag_to.gif" title="'.$lang->display('to be translated to').'">'; ?>
								<select
									class="input"
									onfocus="this.className='inputOn'"
									onblur="this.className='input'"
									name="toLangID"
									id="toLangID"
									title="<?php echo $lang->display('Select Language'); ?>"
								>
								<?php BuildLangList(); ?>
								</select>
								<?php echo '<img src="'.IMG_PATH.'ico_swap.png" title="'.$lang->display('Swap').'" onclick="swapLang(\'fromLangID\',\'toLangID\')" style="cursor:pointer;">'; ?>
							</td>
							<td align="right">
								<input
									type="button"
									class="btnDo"
									onmousemove="this.className='btnOn'"
									onmouseout="this.className='btnDo'"
									id="search"
									name="search"
									value="<?php echo $lang->display('Search'); ?>"
									title="<?php echo $lang->display('Search'); ?>"
									onclick="AjaxPost('fromLangID='+document.getElementById('fromLangID').value+'&toLangID='+document.getElementById('toLangID').value+'&source='+document.getElementById('source').value,'translation','modules/mod_cp_tm_search.php');"
								/>
							</td>
						</tr>
					</table>
				</div>
				<div class="rightwrap">
					<table width="100%" cellpadding="3" cellspacing="0" border="0">
						<tr>
							<td valign="top">
								<div id="translation" class="tabcontentstyle">
									<!-- Translation will apear here -->
								</div>
							</td>
						</tr>
					</table>
				</div>
				<div class="clear"></div>
				<input type="hidden" name="form" id="form">
				</form>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="javascripts/ajax.js"></script>
<script type="text/javascript" src="javascripts/tm.js"></script>